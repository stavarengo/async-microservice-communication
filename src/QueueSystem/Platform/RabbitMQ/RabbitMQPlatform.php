<?php

declare(strict_types=1);


namespace AMC\QueueSystem\Platform\RabbitMQ;


use AMC\QueueSystem\Message\QueueMessageInterface;
use AMC\QueueSystem\Platform\Exception\FailedToConsumeQueue;
use AMC\QueueSystem\Platform\Exception\FailedToPublishMessage;
use AMC\QueueSystem\Platform\PlatformInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class RabbitMQPlatform implements PlatformInterface
{
    private AbstractConnection $rabbitConnection;
    private ?AMQPChannel $channel = null;
    private string $queueName;

    public function __construct(AbstractConnection $rabbitConnection, string $queueName)
    {
        $this->rabbitConnection = $rabbitConnection;
        $this->queueName = $queueName;
    }

    public function consume(callable $callback): void
    {
        try {
            $this->getChannel()->basic_consume($this->queueName, '', false, true, false, false, $callback);
        } catch (Throwable $e) {
            throw FailedToConsumeQueue::create($this->queueName, $e);
        }
    }

    public function publish(QueueMessageInterface $queueMessage): void
    {
        try {
            $this->getChannel()->basic_publish(new AMQPMessage(serialize($queueMessage)), '', $this->queueName);
        } catch (Throwable $e) {
            throw FailedToPublishMessage::create($queueMessage, $e);
        }
    }

    /**
     * @return AMQPChannel
     */
    private function getChannel(): AMQPChannel
    {
        if (!$this->channel) {
            $this->channel = $this->rabbitConnection->channel();
            $this->channel->queue_declare(
                $this->queueName,
                false,
                false,
                false,
                false
            );
        }

        return $this->channel;
    }
}