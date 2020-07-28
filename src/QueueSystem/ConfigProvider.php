<?php

declare(strict_types=1);

namespace AMC\QueueSystem;

use AMC\QueueSystem\Platform\PlatformInterface;
use PhpAmqpLib\Connection\AbstractConnection as RabbitMQConnection;

use function DI\autowire;
use function DI\factory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'container_definitions' => $this->getContainerDefinitions(),
            self::class => [
                Platform\RabbitMQ\RabbitMQConnectionFactory::class => $this->getConnectionConfig(),
            ]
        ];
    }

    public function getContainerDefinitions(): array
    {
        return [
            RabbitMQConnection::class => factory(Platform\RabbitMQ\RabbitMQConnectionFactory::class),

            PlatformInterface::SERVICE_NAME_QUEUE_TOPIC_A => autowire(Platform\RabbitMQ\RabbitMQPlatform::class)
                ->constructorParameter('queueName', 'topic-a'),
            PlatformInterface::SERVICE_NAME_QUEUE_TOPIC_B => autowire(Platform\RabbitMQ\RabbitMQPlatform::class)
                ->constructorParameter('queueName', 'topic-b'),
        ];
    }

    public function getConnectionConfig(): array
    {
        return [
            Platform\RabbitMQ\RabbitMQConnectionFactory::HOSTNAME => '127.0.0.1',
            Platform\RabbitMQ\RabbitMQConnectionFactory::PORT => 5432,
            Platform\RabbitMQ\RabbitMQConnectionFactory::USERNAME => 'postgres',
            Platform\RabbitMQ\RabbitMQConnectionFactory::PASSWORD => 'root',
        ];
    }
}
