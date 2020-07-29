<?php

declare(strict_types=1);

namespace AMC\ConsumerServices;

use AMC\ConsumerServices\BrokerClient\ClientInterface;
use AMC\ConsumerServices\NameProvider\NameProviderInterface;
use AMC\QueueSystem\Facade\FacadeInterface;
use AMC\QueueSystem\Message\QueueMessageInterface;

class ServiceA
{
    private FacadeInterface $queue;
    private NameProviderInterface $nameProvider;
    private ClientInterface $brokerClient;

    public function __construct(
        FacadeInterface $queue,
        NameProviderInterface $nameProvider,
        ClientInterface $brokerClient
    ) {
        $this->queue = $queue;
        $this->nameProvider = $nameProvider;
        $this->brokerClient = $brokerClient;
    }

    public function execute(): void
    {
        echo sprintf("%s: Waiting for messages....\n", self::class);
        $this->queue->consume([$this, 'consumeCallback']);
    }

    public function consumeCallback(QueueMessageInterface $message)
    {
        $originalMessage = trim($message->getBody());
        $newMessage = sprintf('%s %s.', $originalMessage, $this->nameProvider->getName());

        echo sprintf("%s: Received '%s', sending '%s' back to API.\n", self::class, $originalMessage, $newMessage);

        $this->brokerClient->put($message->getId(), $newMessage);
    }
}