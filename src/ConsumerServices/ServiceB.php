<?php

declare(strict_types=1);

namespace AMC\ConsumerServices;

use AMC\Broker\Entity\Message;
use AMC\Broker\Persistence\PersistenceInterface;
use AMC\QueueSystem\Facade\FacadeInterface;
use AMC\QueueSystem\Message\QueueMessageInterface;
use Psr\Log\LoggerInterface;

class ServiceB
{
    private FacadeInterface $queue;
    private PersistenceInterface $persistence;
    private LoggerInterface $logger;

    public function __construct(
        FacadeInterface $queuePlatform,
        PersistenceInterface $persistence,
        LoggerInterface $logger
    ) {
        $this->queue = $queuePlatform;
        $this->persistence = $persistence;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        $this->logger->debug('Waiting for messages....', ['caller' => self::class]);

        $this->queue->consumeQueueB([$this, 'consumeCallback']);
    }

    public function consumeCallback(QueueMessageInterface $message)
    {
        $originalMessage = trim($message->getBody());
        $newMessage = sprintf('%s %s', $originalMessage, 'Bye!');

        $this->logger->debug(
            sprintf('Received "%s", sending "%s" back to API.', $originalMessage, $newMessage),
            ['caller' => self::class]
        );

        $this->persistence->update(new Message($message->getId(), $newMessage));
    }
}