<?php
/** @noinspection PhpDocSignatureInspection */

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);


namespace AMC\Test\Broker\Persistence;


use AMC\Broker\Entity\Message;
use AMC\Broker\Persistence\Exception\FailedToBeginTransaction;
use AMC\Broker\Persistence\Exception\FailedToCommitTransaction;
use AMC\Broker\Persistence\Exception\FailedToFetchRecord;
use AMC\Broker\Persistence\Exception\FailedToInsertNewRecord;
use AMC\Broker\Persistence\Exception\FailedToRollbackTransaction;
use AMC\Broker\Persistence\Exception\FailedToUpdateRecord;
use AMC\Broker\Persistence\Exception\PersistenceException;
use AMC\Broker\Persistence\IDGeneratorInterface;
use AMC\Broker\Persistence\Postgres;
use PDO;
use PDOException;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PostgresTest extends TestCase
{
    /**
     * @dataProvider dataProviderInsertRecordThrowException
     */
    public function testThrowException(callable $invokeRealMethod, PersistenceException $expectedException)
    {
        $pdoStub = $this->createStub(PDO::class);
        $pdoStub->method('prepare')->willThrowException($expectedException->getPrevious());

        $this->expectExceptionObject($expectedException);
        $invokeRealMethod(new Postgres($pdoStub, $this->stubIDGenerator('01')));
    }

    public function dataProviderInsertRecordThrowException(): array
    {
        $idToGet = 'id-123';

        return [
            [
                function (Postgres $persistence) {
                    $persistence->insert('Test');
                },
                FailedToInsertNewRecord::create(new PDOException('Test Insert Exception', 333)),
            ],
            [
                function (Postgres $persistence) use ($idToGet) {
                    $persistence->get($idToGet);
                },
                FailedToFetchRecord::create($idToGet, new PDOException('Test Get Exception', 444)),
            ],
            [
                function (Postgres $persistence) {
                    $persistence->update(new Message());
                },
                FailedToUpdateRecord::create(new PDOException('Test Update Exception', 555)),
            ],
        ];
    }

    public function testInsertRecord()
    {
        $id = '123-id';
        $message = 'First message';

        $pdoStub = $this->createStub(PDO::class);
        $pdoStub->method('prepare')->willReturn($this->createMock(PDOStatement::class));

        $persistence = new Postgres($pdoStub, $this->stubIDGenerator($id));
        $messageEntity = $persistence->insert($message);

        $this->assertEquals($id, $messageEntity->getId());
        $this->assertEquals($message, $messageEntity->getMessage());
    }

    public function testUpdateRecord()
    {
        $message = new Message('id-update', 'Update message');

        $pdoStub = $this->createStub(PDO::class);
        $pdoStub->method('prepare')->willReturn($this->createMock(PDOStatement::class));

        $persistence = new Postgres($pdoStub, $this->stubIDGenerator(''));
        $messageEntity = $persistence->update($message);

        $this->assertEquals($message->getId(), $messageEntity->getId());
        $this->assertEquals($message->getMessage(), $messageEntity->getMessage());
    }

    public function testGetRecord()
    {
        $expectedEntity = new Message('test-123-id', 'Test message');

        $PDOStatementStub = $this->createStub(PDOStatement::class);
        $PDOStatementStub->method('execute')->willReturn(true);
        $PDOStatementStub->method('fetch')->willReturn(
            (object)[
                'id' => $expectedEntity->getId(),
                'message' => $expectedEntity->getMessage(),
            ]
        );

        $pdoStub = $this->createStub(PDO::class);
        $pdoStub->method('prepare')->willReturn($PDOStatementStub);

        $persistence = new Postgres($pdoStub, $this->stubIDGenerator($expectedEntity->getId()));
        $actualEntity = $persistence->get($expectedEntity->getId());

        $this->assertNotNull($actualEntity);
        $this->assertEquals($expectedEntity->getId(), $actualEntity->getId());
        $this->assertEquals($expectedEntity->getMessage(), $actualEntity->getMessage());
    }

    public function testGetRecordThatDoesNotExists()
    {
        $PDOStatementStub = $this->createStub(PDOStatement::class);

        $pdoStub = $this->createStub(PDO::class);
        $pdoStub->method('prepare')->willReturn($PDOStatementStub);

        $persistence = new Postgres($pdoStub, $this->stubIDGenerator('id-123'));

        $PDOStatementStub->method('execute')->willReturnOnConsecutiveCalls(false, true);
        $this->assertNull($persistence->get('id-123'));

        $PDOStatementStub->method('fetch')->willReturn(false);
        $this->assertNull($persistence->get('id-123'));
    }

    /**
     * @dataProvider dataProviderTransactionMethods
     */
    public function testTransactionMethods(string $transactionMethod, PersistenceException $expectedException): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())->method($transactionMethod);
        (new Postgres($pdo, $this->stubIDGenerator('1')))->$transactionMethod();

        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method($transactionMethod)
            ->willThrowException($expectedException->getPrevious());
        $this->expectExceptionObject($expectedException);
        (new Postgres($pdo, $this->stubIDGenerator('1')))->$transactionMethod();
    }

    public function dataProviderTransactionMethods(): array
    {
        return [
            ['beginTransaction', FailedToBeginTransaction::create(new RuntimeException('Test Begin Exception'))],
            ['commit', FailedToCommitTransaction::create(new RuntimeException('Test Commit Exception'))],
            ['rollback', FailedToRollbackTransaction::create(new RuntimeException('Test Rollback Exception'))],
        ];
    }

    public function testInTransaction()
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())->method('inTransaction')->willReturn(true);

        $this->assertTrue((new Postgres($pdo, $this->stubIDGenerator('1')))->inTransaction());
    }

    private function stubIDGenerator(string $id)
    {
        $IDGeneratorStub = $this->createStub(IDGeneratorInterface::class);
        $IDGeneratorStub->method('generate')->willReturn($id);

        return $IDGeneratorStub;
    }
}