<?php

declare(strict_types=1);


namespace AMC\Broker\Persistence\Exception;


use Throwable;

class FailedToFetchRecord extends PersistenceException
{
    public static function create(string $id, Throwable $reason): self
    {
        return new self(sprintf('Failed to fetch the record "%s".', $id), $reason->getCode(), $reason);
    }
}