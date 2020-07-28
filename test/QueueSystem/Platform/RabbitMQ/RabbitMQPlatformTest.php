<?php

declare(strict_types=1);


namespace AMC\Test\QueueSystem\Platform\RabbitMQ;


use AMC\QueueSystem\Message\QueueMessageInterface;
use AMC\QueueSystem\Platform\Exception\FailedToConsumeQueue;
use AMC\QueueSystem\Platform\Exception\FailedToPublishMessage;
use AMC\QueueSystem\Platform\RabbitMQ\RabbitMQPlatform;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RabbitMQPlatformTest extends TestCase
{
    public function testConsume()
    {
        $queueName = 'queue-name-test';
        $expectedCallback = function () {
        };

        $rabbitChannel = $this->createMock(AMQPChannel::class);
        $rabbitChannel->expects($this->once())
            ->method('basic_consume')
            ->with(
                $queueName,
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->identicalTo($expectedCallback),
            );

        $rabbitConnection = $this->createMock(AbstractConnection::class);
        $rabbitConnection->expects($this->once())->method('channel')->willReturn($rabbitChannel);

        (new RabbitMQPlatform($rabbitConnection, $queueName))->consume($expectedCallback);
    }

    public function testPublish()
    {
        $queueName = 'queue-name-test';
        $expectedMessage = $this->createStub(QueueMessageInterface::class);

        $rabbitChannel = $this->createMock(AMQPChannel::class);
        $rabbitChannel->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(
                    function (AMQPMessage $AMQPMessage) use ($expectedMessage) {
                        return $AMQPMessage->getBody() === $expectedMessage;
                    }
                ),
                $this->anything(),
                $queueName,
            );

        $rabbitConnection = $this->createMock(AbstractConnection::class);
        $rabbitConnection->expects($this->once())->method('channel')->willReturn($rabbitChannel);

        (new RabbitMQPlatform($rabbitConnection, $queueName))->publish($expectedMessage);
    }

    public function testConsumeThrowException()
    {
        $queueName = '';
        $queueMessage = $this->createStub(QueueMessageInterface::class);
        $emptyCallback = function () {
        };

        $unexpectedException = new RuntimeException('Consume Exception');
        $rabbitChannel = $this->createMock(AMQPChannel::class);
        $rabbitChannel->method('basic_consume')->willThrowException($unexpectedException);

        $rabbitConnection = $this->createMock(AbstractConnection::class);
        $rabbitConnection->method('channel')->willReturn($rabbitChannel);

        $this->expectExceptionObject(FailedToConsumeQueue::create($queueName, $unexpectedException));

        (new RabbitMQPlatform($rabbitConnection, $queueName))->consume($emptyCallback);
    }

    public function testPublishThrowException()
    {
        $queueMessage = $this->createStub(QueueMessageInterface::class);

        $unexpectedException = new RuntimeException('Publish Exception');
        $rabbitChannel = $this->createMock(AMQPChannel::class);
        $rabbitChannel->method('basic_publish')->willThrowException($unexpectedException);

        $rabbitConnection = $this->createMock(AbstractConnection::class);
        $rabbitConnection->method('channel')->willReturn($rabbitChannel);

        $this->expectExceptionObject(FailedToPublishMessage::create($queueMessage, $unexpectedException));

        (new RabbitMQPlatform($rabbitConnection, ''))->publish($queueMessage);
    }

    public function testConsumeThrowExceptionWhenTriesToCreateChannel()
    {
        $queueName = '';
        $emptyCallback = function () {
        };

        $unexpectedException = new RuntimeException('Failed to Create Channel Exception');
        $rabbitConnection = $this->createMock(AbstractConnection::class);
        $rabbitConnection->method('channel')->willThrowException($unexpectedException);

        $this->expectExceptionObject(FailedToConsumeQueue::create($queueName, $unexpectedException));
        (new RabbitMQPlatform($rabbitConnection, $queueName))->consume($emptyCallback);
    }

    public function testPublishThrowExceptionWhenTriesToCreateChannel()
    {
        $queueMessage = $this->createStub(QueueMessageInterface::class);

        $unexpectedException = new RuntimeException('Failed to Create Channel Exception');
        $rabbitConnection = $this->createMock(AbstractConnection::class);
        $rabbitConnection->method('channel')->willThrowException($unexpectedException);

        $this->expectExceptionObject(FailedToPublishMessage::create($queueMessage, $unexpectedException));
        (new RabbitMQPlatform($rabbitConnection, ''))->publish($queueMessage);
    }

}