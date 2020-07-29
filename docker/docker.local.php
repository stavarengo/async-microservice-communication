<?php

declare(strict_types=1);

use AMC\Broker\Factory\PDOFactory;
use AMC\ConsumerServices\BrokerClient\Client;
use AMC\ConsumerServices\ConfigProvider;
use AMC\QueueSystem\Platform\RabbitMQ\RabbitMQConnectionFactory;

return [
    \AMC\Broker\ConfigProvider::class => [
        PDOFactory::class => [
            PDOFactory::DRIVE_NAME => 'pgsql',
            PDOFactory::HOSTNAME => 'db',
            PDOFactory::PORT => 5432,
            PDOFactory::USERNAME => 'postgres',
            PDOFactory::PASSWORD => 'root',

            PDOFactory::HOW_MANY_CONNECTION_TRIES_BEFORE_FAIL => 5,
        ],
    ],
    \AMC\QueueSystem\ConfigProvider::class => [
        RabbitMQConnectionFactory::class => [
            RabbitMQConnectionFactory::HOSTNAME => 'message-broker',
            RabbitMQConnectionFactory::PORT => 5672,
            RabbitMQConnectionFactory::USERNAME => 'guest',
            RabbitMQConnectionFactory::PASSWORD => 'guest',
        ]
    ],
    ConfigProvider::class => [
        Client::class => [
            Client::API_ADDRESS => 'http://php-api:4000'
        ]
    ]
];