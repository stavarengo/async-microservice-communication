<?php

declare(strict_types=1);

namespace AMC\ConsumerServices;

use AMC\ConsumerServices\BrokerClient\ClientInterface;
use AMC\ConsumerServices\NameProvider\NameProviderInterface;
use AMC\QueueSystem\Facade\FacadeInterface;
use AMC\QueueSystem\Message\QueueMessageInterface;
use Psr\Log\LoggerInterface;

class ServiceA
{
    private FacadeInterface $queue;
    private NameProviderInterface $nameProvider;
    private ClientInterface $brokerClient;
    private LoggerInterface $logger;

    public function __construct(
        FacadeInterface $queue,
        NameProviderInterface $nameProvider,
        ClientInterface $brokerClient,
        LoggerInterface $logger
    ) {
        $this->queue = $queue;
        $this->nameProvider = $nameProvider;
        $this->brokerClient = $brokerClient;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        $this->logger->debug('Waiting for messages....', ['caller' => self::class]);

        $this->queue->consumeQueueA([$this, 'consumeCallback']);
    }

    public function consumeCallback(QueueMessageInterface $message)
    {
        $originalMessage = trim($message->getBody());
        $newMessage = sprintf('%s %s.', $originalMessage, $this->nameProvider->getName());

        $this->logger->debug(
            sprintf('Received "%s", sending "%s" back to API.', $originalMessage, $newMessage),
            ['caller' => self::class]
        );

        $this->brokerClient->put($message->getId(), $newMessage);
    }
}