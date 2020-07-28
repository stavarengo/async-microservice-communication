<?php

declare(strict_types=1);

namespace AMC\QueueSystem;

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
            Platform\PlatformInterface::class => autowire(Platform\RabbitMQ\RabbitMQPlatform::class),
            RabbitMQConnection::class => factory(Platform\RabbitMQ\RabbitMQConnectionFactory::class),
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
