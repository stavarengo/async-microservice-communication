<?php

declare(strict_types=1);


namespace AMC\QueueSystem\Platform\RabbitMQ;


use AMC\Broker\ConfigProvider;
use AMC\Broker\Factory\Exception\MissingConfigEntry;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Container\ContainerInterface;

class RabbitMQConnectionFactory
{
    public const HOSTNAME = 'HOSTNAME';
    public const PORT = 'PORT';
    public const USERNAME = 'USERNAME';
    public const PASSWORD = 'PASSWORD';

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $rabbitMQConfig = $config[ConfigProvider::class][self::class] ?? [];

        $requiredConfiguration = [
            self::HOSTNAME,
            self::PORT,
            self::USERNAME,
            self::PASSWORD,
        ];

        foreach ($requiredConfiguration as $configName) {
            if (!array_key_exists($configName, $rabbitMQConfig)) {
                throw MissingConfigEntry::create($configName, self::class);
            }
        }

        return new AMQPStreamConnection(
            $rabbitMQConfig[self::HOSTNAME],
            $rabbitMQConfig[self::PORT],
            $rabbitMQConfig[self::USERNAME],
            $rabbitMQConfig[self::PASSWORD]
        );
    }
}