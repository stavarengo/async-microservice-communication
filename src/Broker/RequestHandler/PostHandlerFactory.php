<?php

declare(strict_types=1);


namespace AMC\Broker\RequestHandler;


use AMC\Broker\Persistence\PersistenceInterface;
use AMC\QueueSystem\Platform\PlatformInterface;
use Psr\Container\ContainerInterface;

class PostHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PostHandler(
            $container->get(PersistenceInterface::class),
            $container->get(PlatformInterface::SERVICE_NAME_QUEUE_TOPIC_A)
        );
    }
}