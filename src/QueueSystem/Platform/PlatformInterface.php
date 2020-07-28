<?php

declare(strict_types=1);


namespace AMC\QueueSystem\Platform;


use AMC\QueueSystem\Message\QueueMessageInterface;
use AMC\QueueSystem\Platform\Exception\FailedToConsumeQueue;
use AMC\QueueSystem\Platform\Exception\FailedToPublishMessage;

interface PlatformInterface
{
    public const SERVICE_NAME_QUEUE_TOPIC_A = self::class . '.queue-topic-a';
    public const SERVICE_NAME_QUEUE_TOPIC_B = self::class . '.queue-topic-b';

    /**
     * @param callable $callback
     * @throws FailedToConsumeQueue
     */
    public function consume(callable $callback): void;

    /**
     * @param string $queueName
     * @param QueueMessageInterface $queueMessage
     * @throws FailedToPublishMessage
     */
    public function publish(QueueMessageInterface $queueMessage): void;
}