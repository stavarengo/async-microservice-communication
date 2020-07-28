<?php

declare(strict_types=1);

namespace AMC\ConsumerServices;

use AMC\ConsumerServices\BrokerClient\ClientInterface;
use AMC\ConsumerServices\NameProvider\NameProviderInterface;
use AMC\QueueSystem\Message\QueueMessageInterface;
use AMC\QueueSystem\Platform\PlatformInterface;

class ServiceA
{
    private PlatformInterface $queuePlatform;
    private NameProviderInterface $nameProvider;
    private ClientInterface $brokerClient;

    public function __construct(
        PlatformInterface $queuePlatform,
        NameProviderInterface $nameProvider,
        ClientInterface $brokerClient
    ) {
        $this->queuePlatform = $queuePlatform;
        $this->nameProvider = $nameProvider;
        $this->brokerClient = $brokerClient;
    }

    public function execute(): void
    {
        $this->queuePlatform->consume([$this, 'consumeCallback']);
    }

    public function consumeCallback(QueueMessageInterface $message)
    {
        $newMessage = sprintf('%s %s', $message->getBody(), $this->nameProvider->getName());

        $this->brokerClient->put($message->getId(), $newMessage);
    }
}