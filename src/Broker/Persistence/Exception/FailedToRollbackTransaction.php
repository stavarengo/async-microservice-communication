<?php

declare(strict_types=1);


namespace AMC\Broker\Persistence\Exception;


use Throwable;

class FailedToRollbackTransaction extends PersistenceException
{
    public static function create(Throwable $reason): self
    {
        return new self('Failed to rollback transaction.', $reason->getCode(), $reason);
    }
}