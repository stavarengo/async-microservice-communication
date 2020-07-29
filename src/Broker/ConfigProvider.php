<?php

declare(strict_types=1);

namespace AMC\Broker;

use AMC\Broker\RequestHandler\PostOrPut\PostOrPutHandler;
use AMC\Broker\RequestHandler\PostOrPut\SpecificMethodHandler\Post;
use AMC\Broker\RequestHandler\PostOrPut\SpecificMethodHandler\Put;
use PDO;

use function DI\autowire;
use function DI\factory;
use function DI\get;

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

            RequestHandler\PostOrPut\PostOrPutHandler::class => autowire(PostOrPutHandler::class)
                ->constructorParameter('postHandler', get(Post::class))
                ->constructorParameter('putHandler', get(Put::class)),
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

            Factory\PDOFactory::HOW_MANY_CONNECTION_TRIES_BEFORE_FAIL => 0,
        ];
    }
}
