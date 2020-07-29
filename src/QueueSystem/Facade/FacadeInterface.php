<?php

declare(strict_types=1);


namespace AMC\QueueSystem\Facade;


use AMC\QueueSystem\Exception\UnknownMessage;
use AMC\QueueSystem\Message\QueueMessageInterface;
use AMC\QueueSystem\Platform\Exception\FailedToConsumeQueue;

interface FacadeInterface
{
    /**
     * @param QueueMessageInterface $message
     * @throws UnknownMessage
     */
    public function publish(QueueMessageInterface $message): void;


    /**
     * @param callable $callback
     * @throws FailedToConsumeQueue
     */
    public function consumeQueueA(callable $callback): void;

    /**
     * @param callable $callback
     * @throws FailedToConsumeQueue
     */
    public function consumeQueueB(callable $callback): void;

}