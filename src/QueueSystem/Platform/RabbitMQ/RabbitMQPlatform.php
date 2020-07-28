<?php

declare(strict_types=1);


namespace AMC\QueueSystem\Platform\RabbitMQ;


use AMC\QueueSystem\Platform\PlatformInterface;
use AMC\QueueSystem\QueueMessageInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQPlatform implements PlatformInterface
{
    private AbstractConnection $rabbitConnection;
    private AMQPChannel $channel;

    /**
     * RabbitMQ constructor.
     * @param AbstractConnection $rabbitConnection
     */
    public function __construct(AbstractConnection $rabbitConnection)
    {
        $this->rabbitConnection = $rabbitConnection;
    }

    public function consume(string $queueName, callable $callback): void
    {
        $this->getChannel()->basic_consume($queueName, '', false, true, false, false, $callback);
    }

    public function publish(string $queueName, QueueMessageInterface $queueMessage): void
    {
        $this->getChannel()->basic_publish(new AMQPMessage($queueMessage), '', $queueName);
    }

    /**
     * @return AMQPChannel
     */
    private function getChannel(): AMQPChannel
    {
        if (!$this->channel) {
            $this->channel = $this->rabbitConnection->channel();
        }

        return $this->channel;
    }
}