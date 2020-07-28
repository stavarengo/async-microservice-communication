<?php

declare(strict_types=1);


namespace AMC\Broker\Persistence\Exception;


use Throwable;

class FailedToUpdateRecord extends PersistenceException
{
    public static function create(Throwable $reason): self
    {
        return new self('Failed to update a record.', $reason->getCode(), $reason);
    }
}