<?php

declare(strict_types=1);


namespace AMC\QueueSystem\Platform;


use AMC\QueueSystem\Exception\FailedToPublishMessage;
use AMC\QueueSystem\Message\QueueMessageInterface;

interface PlatformInterface
{
    public function consume(string $queueName, callable $callback): void;

    /**
     * @param string $queueName
     * @param QueueMessageInterface $queueMessage
     * @throws FailedToPublishMessage
     */
    public function publish(string $queueName, QueueMessageInterface $queueMessage): void;
}