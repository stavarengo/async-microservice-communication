<?php

declare(strict_types=1);


namespace AMC\QueueSystem\Platform\Exception;


use AMC\QueueSystem\Message\QueueMessageInterface;
use Throwable;

class FailedToPublishMessage extends PlatformException
{
    public static function create(QueueMessageInterface $message, Throwable $reason): self
    {
        return new self(sprintf('Failed to publish message "%s".', $message->getId()), $reason->getCode(), $reason);
    }

}