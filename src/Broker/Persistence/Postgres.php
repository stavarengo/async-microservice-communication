<?php

declare(strict_types=1);


namespace AMC\Broker\Persistence;


use AMC\Broker\Entity\Message;
use AMC\Broker\Persistence\Exception\FailedToBeginTransaction;
use AMC\Broker\Persistence\Exception\FailedToCommitTransaction;
use AMC\Broker\Persistence\Exception\FailedToFetchRecord;
use AMC\Broker\Persistence\Exception\FailedToInsertNewRecord;
use AMC\Broker\Persistence\Exception\FailedToRollbackTransaction;
use AMC\Broker\Persistence\Exception\FailedToUpdateRecord;
use AMC\Broker\Persistence\Exception\RecordNotFound;
use PDO;
use Throwable;

class Postgres implements PersistenceInterface
{
    private PDO $pdo;
    /**
     * @var IDGeneratorInterface
     */
    private IDGeneratorInterface $IDGenerator;

    public function __construct(PDO $pdo, IDGeneratorInterface $IDGenerator)
    {
        $this->pdo = $pdo;
        $this->IDGenerator = $IDGenerator;
    }

    public function insert(string $message): Message
    {
        try {
            $messageEntity = new Message($this->IDGenerator->generate(), $message);

            $sql = /** @lang PostgreSQL */
                'INSERT INTO "Broker"."request" ("id", "message") VALUES (?, ?);';

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([$messageEntity->getId(), $messageEntity->getMessage()]);

            return $messageEntity;
        } catch (Throwable $e) {
            throw FailedToInsertNewRecord::create($e);
        }
    }

    public function update(Message $message): Message
    {
        try {
            $sql = /** @lang PostgreSQL */
                'UPDATE "Broker"."request" SET "message" = ? WHERE "id" = ?';

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([$message->getMessage(), $message->getId()]);

            return $message;
        } catch (Throwable $e) {
            throw FailedToUpdateRecord::create($e);
        }
    }

    public function get(string $id): ?Message
    {
        try {
            $sql = /** @lang PostgreSQL */
                'SELECT * FROM "Broker"."request" WHERE "id" = ?';
            $stmt = $this->pdo->prepare($sql);

            $message = null;

            if ($stmt->execute([$id]) && $fetchResult = $stmt->fetch(PDO::FETCH_OBJ)) {
                $message = new Message($fetchResult->id, $fetchResult->message);
            }

            return $message;
        } catch (Throwable $e) {
            throw FailedToFetchRecord::create($id, $e);
        }
    }

    public function beginTransaction(): void
    {
        try {
            $this->pdo->beginTransaction();
        } catch (Throwable $e) {
            throw FailedToBeginTransaction::create($e);
        }
    }

    public function commit(): void
    {
        try {
            $this->pdo->commit();
        } catch (Throwable $e) {
            throw FailedToCommitTransaction::create($e);
        }
    }

    public function rollBack(): void
    {
        try {
            $this->pdo->rollBack();
        } catch (Throwable $e) {
            throw FailedToRollbackTransaction::create($e);
        }
    }

    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }
}