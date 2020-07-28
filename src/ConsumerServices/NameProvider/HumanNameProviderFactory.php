<?php

declare(strict_types=1);


namespace AMC\ConsumerServices\NameProvider;


use AMC\ConsumerServices\ConfigProvider;
use AMC\ConsumerServices\NameProvider\Exception\MissingConfigEntry;
use Psr\Container\ContainerInterface;

class HumanNameProviderFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $humanNameProviderConfig = $config[ConfigProvider::class][HumanNameProvider::class] ?? [];

        if (!array_key_exists(HumanNameProvider::LIST_OF_NAMES, $humanNameProviderConfig)) {
            throw MissingConfigEntry::create(HumanNameProvider::LIST_OF_NAMES, self::class);
        }

        return new HumanNameProvider(...$humanNameProviderConfig[HumanNameProvider::LIST_OF_NAMES]);
    }
}