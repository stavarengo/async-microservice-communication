<?php

declare(strict_types=1);


namespace AMC\ConsumerServices\BrokerClient;


use AMC\ConsumerServices\BrokerClient\Exception\MissingConfigEntry;
use AMC\ConsumerServices\ConfigProvider;
use GuzzleHttp\RequestOptions;
use Psr\Container\ContainerInterface;

class ClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $clientConfig = $config[ConfigProvider::class][Client::class] ?? [];

        if (!array_key_exists(Client::API_ADDRESS, $clientConfig)) {
            throw MissingConfigEntry::create(Client::API_ADDRESS, self::class);
        }

        $httpClient = new \GuzzleHttp\Client(
            [
                'base_uri' => $clientConfig[Client::API_ADDRESS],
                RequestOptions::TIMEOUT => 6,
            ]
        );

        return new Client($httpClient);
    }
}