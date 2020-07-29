<?php

declare(strict_types=1);


namespace AMC\QueueSystem\Facade;


use AMC\QueueSystem\Exception\UnknownMessage;
use AMC\QueueSystem\Message\QueueMessageInterface;
use AMC\QueueSystem\Platform\PlatformInterface;

class Facade implements FacadeInterface
{
    private PlatformInterface $queueA;
    private PlatformInterface $queueB;

    public function __construct(PlatformInterface $queueA, PlatformInterface $queueB)
    {
        $this->queueA = $queueA;
        $this->queueB = $queueB;
    }

    public function publish(QueueMessageInterface $message): void
    {
        $body = trim($message->getBody());

        if (preg_match('/^Hi,$/', $body)) {
            $this->queueA->publish($message);
        } elseif (preg_match('/^Hi, .+?\.$/', $body)) {
            $this->queueB->publish($message);
        } else {
            throw UnknownMessage::create($message->getBody());
        }
    }

    public function consumeQueueA(callable $callback): void
    {
        $this->queueA->consume($callback);
    }

    public function consumeQueueB(callable $callback): void
    {
        $this->queueB->consume($callback);
    }
}