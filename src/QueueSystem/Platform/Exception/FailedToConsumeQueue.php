<?php

declare(strict_types=1);


namespace AMC\QueueSystem\Platform\Exception;


use Throwable;

class FailedToConsumeQueue extends PlatformException
{
    public static function create(string $queueName, Throwable $reason): self
    {
        return new self(sprintf('Failed to consume queue "%s".', $queueName), $reason->getCode(), $reason);
    }

}