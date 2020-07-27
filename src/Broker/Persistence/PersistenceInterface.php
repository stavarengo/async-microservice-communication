<?php


namespace AMC\Broker\Persistence;


use AMC\Broker\Entity\Message;
use AMC\Broker\Persistence\Exception\FailedToFetchRecord;
use AMC\Broker\Persistence\Exception\FailedToInsertNewRecord;

interface PersistenceInterface
{
    /**
     * Persists a new message and return the entity that represents it.
     *
     * @param string $message
     * @return Message
     *
     * @throws FailedToInsertNewRecord
     */
    public function insert(string $message): Message;

    /**
     * Returns a message from the persistence service.
     * If there is no message with $id, returns null.
     *
     * @param string $id
     * @return Message|null
     *
     * @throws FailedToFetchRecord
     */
    public function get(string $id): ?Message;
}