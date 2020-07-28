<?php

declare(strict_types=1);


namespace AMC\Broker\Persistence;


use AMC\Broker\Entity\Message;
use AMC\Broker\Persistence\Exception\FailedToFetchRecord;
use AMC\Broker\Persistence\Exception\FailedToInsertNewRecord;
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
}