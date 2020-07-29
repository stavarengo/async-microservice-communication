<?php

declare(strict_types=1);

namespace AMC\ConsumerServices;

use AMC\Broker\Entity\Message;
use AMC\Broker\Persistence\PersistenceInterface;
use AMC\QueueSystem\Facade\FacadeInterface;
use AMC\QueueSystem\Message\QueueMessageInterface;

class ServiceB
{
    private FacadeInterface $queue;
    private PersistenceInterface $persistence;

    public function __construct(
        FacadeInterface $queuePlatform,
        PersistenceInterface $persistence
    ) {
        $this->queue = $queuePlatform;
        $this->persistence = $persistence;
    }

    public function execute(): void
    {
        echo sprintf("%s: Waiting for messages....\n", self::class);

        $this->queue->consumeQueueB([$this, 'consumeCallback']);
    }

    public function consumeCallback(QueueMessageInterface $message)
    {
        $originalMessage = trim($message->getBody());
        $newMessage = sprintf('%s %s', $originalMessage, 'Bye!');

        echo sprintf("%s: Received '%s', sending '%s' back to API.\n", self::class, $originalMessage, $newMessage);

        $this->persistence->update(new Message($message->getId(), $newMessage));
    }
}