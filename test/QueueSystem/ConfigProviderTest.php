<?php

declare(strict_types=1);

namespace AMC\Test\QueueSystem;

use AMC\QueueSystem\ConfigProvider;
use AMC\QueueSystem\Facade\FacadeInterface;
use AMC\QueueSystem\Platform\PlatformInterface;
use AMC\QueueSystem\Platform\RabbitMQ\RabbitMQConnectionFactory;
use PhpAmqpLib\Connection\AbstractConnection;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testFactoryMustBeInvokable()
    {
        $configProvider = new ConfigProvider();

        $this->assertIsCallable($configProvider);
    }

    public function testContainerDefinitions()
    {
        $configProvider = new ConfigProvider();

        $config = $configProvider->getContainerDefinitions();

        $this->assertCount(4, $config);

        $this->assertArrayHasKey(FacadeInterface::class, $config);
        $this->assertArrayHasKey(PlatformInterface::SERVICE_NAME_QUEUE_TOPIC_A, $config);
        $this->assertArrayHasKey(PlatformInterface::SERVICE_NAME_QUEUE_TOPIC_B, $config);
        $this->assertArrayHasKey(AbstractConnection::class, $config);
    }

    public function testConnectionConfig()
    {
        $configProvider = new ConfigProvider();

        $config = $configProvider->getConnectionConfig();

        $this->assertCount(4, $config);
        $this->assertArrayHasKey(RabbitMQConnectionFactory::HOSTNAME, $config);
        $this->assertArrayHasKey(RabbitMQConnectionFactory::PORT, $config);
        $this->assertArrayHasKey(RabbitMQConnectionFactory::USERNAME, $config);
        $this->assertArrayHasKey(RabbitMQConnectionFactory::PASSWORD, $config);
    }

    public function testInvoke()
    {
        $configProvider = new ConfigProvider();
        $config = $configProvider->__invoke();

        $expectedConfig = [
            'container_definitions' => $configProvider->getContainerDefinitions(),
            ConfigProvider::class => [
                RabbitMQConnectionFactory::class => $configProvider->getConnectionConfig(),
            ],
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($expectedConfig), json_encode($config));
    }
}
