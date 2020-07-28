<?php
/** @noinspection PhpDocSignatureInspection */

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);


namespace AMC\Test\Broker\Persistence;


use AMC\Broker\Entity\Message;
use AMC\Broker\Persistence\Exception\FailedToFetchRecord;
use AMC\Broker\Persistence\Exception\FailedToInsertNewRecord;
use AMC\Broker\Persistence\Exception\PersistenceException;
use AMC\Broker\Persistence\IDGeneratorInterface;
use AMC\Broker\Persistence\Postgres;
use PDO;
use PDOException;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class PostgresTest extends TestCase
{

    /**
     * @dataProvider dataProviderInsertRecordThrowException
     */
    public function testInsertRecordThrowException(callable $invokeRealMethod, PersistenceException $expectedException)
    {
        $pdoStub = $this->createStub(PDO::class);
        $pdoStub->method('prepare')->willThrowException($expectedException->getPrevious());

        $this->expectExceptionObject($expectedException);
        $invokeRealMethod(new Postgres($pdoStub, $this->stubIDGenerator('0')));
    }

    public function dataProviderInsertRecordThrowException(): array
    {
        $fetchId = 'id-123';

        return [
            [
                function (Postgres $persistence) {
                    $persistence->insert('Test');
                },
                FailedToInsertNewRecord::create(new PDOException('Test Insert Exception', 333)),
            ],
            [
                function (Postgres $persistence) use ($fetchId) {
                    $persistence->get($fetchId);
                },
                FailedToFetchRecord::create($fetchId, new PDOException('Test Get Exception', 444)),
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

    private function stubIDGenerator(string $id)
    {
        $IDGeneratorStub = $this->createStub(IDGeneratorInterface::class);
        $IDGeneratorStub->method('generate')->willReturn($id);

        return $IDGeneratorStub;
    }
}