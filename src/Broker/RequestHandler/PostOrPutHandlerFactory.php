<?php

declare(strict_types=1);


namespace AMC\Broker\RequestHandler;


use AMC\Broker\Persistence\PersistenceInterface;
use AMC\QueueSystem\Platform\PlatformInterface;
use Psr\Container\ContainerInterface;

class PostOrPutHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PostOrPutHandler(
            $container->get(PersistenceInterface::class),
            $container->get(PlatformInterface::SERVICE_NAME_QUEUE_TOPIC_A)
        );
    }
}