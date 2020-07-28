<?php

declare(strict_types=1);

namespace AMC\Broker;

use PDO;

use function DI\autowire;
use function DI\factory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'container_definitions' => $this->getContainerDefinitions(),
            self::class => [
                Factory\PDOFactory::class => $this->getPDOConfig(),
            ]
        ];
    }

    public function getContainerDefinitions(): array
    {
        return [
            PDO::class => factory(Factory\PDOFactory::class),

            Persistence\PersistenceInterface::class => autowire(Persistence\Postgres::class),
            Persistence\IDGeneratorInterface::class => autowire(Persistence\IDGenerator::class),

            RequestHandler\PostHandler::class => factory(RequestHandler\PostHandlerFactory::class),
        ];
    }

    public function getPDOConfig(): array
    {
        return [
            Factory\PDOFactory::DRIVE_NAME => 'pgsql',
            Factory\PDOFactory::HOSTNAME => '127.0.0.1',
            Factory\PDOFactory::PORT => 5432,
            Factory\PDOFactory::USERNAME => 'postgres',
            Factory\PDOFactory::PASSWORD => 'root',
        ];
    }
}
