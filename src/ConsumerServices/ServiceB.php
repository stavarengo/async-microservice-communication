<?php

declare(strict_types=1);

namespace AMC\ConsumerServices;

use AMC\Broker\Entity\Message;
use AMC\Broker\Persistence\PersistenceInterface;
use AMC\QueueSystem\Message\QueueMessageInterface;
use AMC\QueueSystem\Platform\PlatformInterface;

class ServiceB
{
    private PlatformInterface $queuePlatform;
    private PersistenceInterface $persistence;

    public function __construct(
        PlatformInterface $queuePlatform,
        PersistenceInterface $persistence
    ) {
        $this->queuePlatform = $queuePlatform;
        $this->persistence = $persistence;
    }

    public function execute(): void
    {
        echo sprintf("%s: Waiting for messages....\n", self::class);

        $this->queuePlatform->consume([$this, 'consumeCallback']);
    }

    public function consumeCallback(QueueMessageInterface $message)
    {
        $originalMessage = trim($message->getBody());
        $newMessage = sprintf('%s %s', $originalMessage, 'Bye!');

        echo sprintf("%s: Received '%s', sending '%s' back to API.\n", self::class, $originalMessage, $newMessage);

        $this->persistence->update(new Message($message->getId(), $newMessage));
    }
}