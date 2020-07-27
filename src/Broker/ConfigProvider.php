<?php

namespace AMC\Broker;

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
            \PDO::class => \DI\factory(Factory\PDOFactory::class),

            Persistence\PersistenceInterface::class => \DI\autowire(Persistence\Postgres::class),
            Persistence\IDGeneratorInterface::class => \DI\autowire(Persistence\IDGenerator::class),

            RequestHandler\PostHandler::class => \DI\autowire(RequestHandler\PostHandler::class),
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
