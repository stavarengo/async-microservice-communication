<?php

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace AMC\Test\Broker;

use AMC\Broker\ConfigProvider;
use AMC\Broker\Factory\PDOFactory;
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

        $this->assertCount(1, $config);

        $this->assertArrayHasKey(\PDO::class, $config);
    }

    public function testDispatcherConfig()
    {
        $configProvider = new ConfigProvider();

        $config = $configProvider->getPDOConfig();

        $this->assertCount(5, $config);
        $this->assertArrayHasKey(PDOFactory::DRIVE_NAME, $config);
        $this->assertArrayHasKey(PDOFactory::HOSTNAME, $config);
        $this->assertArrayHasKey(PDOFactory::PORT, $config);
        $this->assertArrayHasKey(PDOFactory::USERNAME, $config);
        $this->assertArrayHasKey(PDOFactory::PASSWORD, $config);
    }

    public function testInvoke()
    {
        $configProvider = new ConfigProvider();
        $config = $configProvider->__invoke();

        $expectedConfig = [
            'container_definitions' => $configProvider->getContainerDefinitions(),
            ConfigProvider::class => [
                PDOFactory::class => $configProvider->getPDOConfig(),
            ],
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($expectedConfig), json_encode($config));
    }
}
