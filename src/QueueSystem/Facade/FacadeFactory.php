<?php

declare(strict_types=1);


namespace AMC\QueueSystem\Facade;


use AMC\QueueSystem\Platform\PlatformInterface;
use Psr\Container\ContainerInterface;

class FacadeFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Facade(
            $container->get(PlatformInterface::SERVICE_NAME_QUEUE_TOPIC_A),
            $container->get(PlatformInterface::SERVICE_NAME_QUEUE_TOPIC_B)
        );
    }
}