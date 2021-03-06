<?php

declare(strict_types=1);

namespace AMC\QueueSystem;

use AMC\QueueSystem\Facade\FacadeFactory;
use AMC\QueueSystem\Facade\FacadeInterface;
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

            FacadeInterface::class => factory(FacadeFactory::class),

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
            Platform\RabbitMQ\RabbitMQConnectionFactory::PORT => 5672,
            Platform\RabbitMQ\RabbitMQConnectionFactory::USERNAME => 'guest',
            Platform\RabbitMQ\RabbitMQConnectionFactory::PASSWORD => 'guest',
        ];
    }
}
