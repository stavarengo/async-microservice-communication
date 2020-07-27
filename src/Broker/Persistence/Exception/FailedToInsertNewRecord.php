<?php


namespace AMC\Broker\Persistence\Exception;


class FailedToInsertNewRecord extends PersistenceException
{
    public static function create(\Throwable $reason): self
    {
        return new self('Failed to create a new record.', $reason->getCode(), $reason);
    }
}