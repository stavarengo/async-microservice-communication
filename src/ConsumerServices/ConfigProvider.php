<?php

declare(strict_types=1);

namespace AMC\ConsumerServices;

use AMC\ConsumerServices\BrokerClient\ClientFactory;
use AMC\ConsumerServices\NameProvider\HumanNameProvider;
use AMC\ConsumerServices\NameProvider\HumanNameProviderFactory;
use AMC\QueueSystem\Platform\PlatformInterface;

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
                BrokerClient\Client::class => $this->getBrokerClientConfig(),
                NameProvider\HumanNameProvider::class => $this->getHumanNameProviderConfig(),
            ]
        ];
    }

    public function getContainerDefinitions(): array
    {
        return [
            ServiceA::class => autowire(ServiceA::class)->constructorParameter(
                'queuePlatform',
                get(PlatformInterface::SERVICE_NAME_QUEUE_TOPIC_A)
            ),
            ServiceB::class => autowire(ServiceB::class)->constructorParameter(
                'queuePlatform',
                get(PlatformInterface::SERVICE_NAME_QUEUE_TOPIC_B)
            ),

            BrokerClient\ClientInterface::class => factory(ClientFactory::class),

            NameProvider\NameProviderInterface::class => factory(HumanNameProviderFactory::class),
        ];
    }

    public function getHumanNameProviderConfig(): array
    {
        return [
            HumanNameProvider::LIST_OF_NAMES => [
                'Joao',
                'Bram',
                'Gabriel',
                'Fehim',
                'Eni',
                'Patrick',
                'Micha',
                'Mirzet',
                'Liliana',
                'Sebastien',
            ],
        ];
    }

    public function getBrokerClientConfig(): array
    {
        return [
            BrokerClient\Client::API_ADDRESS => 'http://amc.stavarengo.lc',
        ];
    }
}
