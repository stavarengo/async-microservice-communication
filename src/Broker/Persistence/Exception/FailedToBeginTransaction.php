<?php

declare(strict_types=1);


namespace AMC\Broker\Persistence\Exception;


use Throwable;

class FailedToBeginTransaction extends PersistenceException
{
    public static function create(Throwable $reason): self
    {
        return new self('Failed to begin transaction.', $reason->getCode(), $reason);
    }
}