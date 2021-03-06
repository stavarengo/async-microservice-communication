<?php

declare(strict_types=1);

namespace AMC\Test\Broker;

use AMC\Broker\ConfigProvider;
use AMC\Broker\Factory\PDOFactory;
use AMC\Broker\Persistence\IDGeneratorInterface;
use AMC\Broker\Persistence\PersistenceInterface;
use AMC\Broker\RequestHandler\PostOrPut\PostOrPutHandler;
use PDO;
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

        $this->assertArrayHasKey(PDO::class, $config);
        $this->assertArrayHasKey(PersistenceInterface::class, $config);
        $this->assertArrayHasKey(IDGeneratorInterface::class, $config);
        $this->assertArrayHasKey(PostOrPutHandler::class, $config);
    }

    public function testPDOConfig()
    {
        $configProvider = new ConfigProvider();

        $config = $configProvider->getPDOConfig();

        $this->assertCount(6, $config);
        $this->assertArrayHasKey(PDOFactory::DRIVE_NAME, $config);
        $this->assertArrayHasKey(PDOFactory::HOSTNAME, $config);
        $this->assertArrayHasKey(PDOFactory::PORT, $config);
        $this->assertArrayHasKey(PDOFactory::USERNAME, $config);
        $this->assertArrayHasKey(PDOFactory::PASSWORD, $config);
        $this->assertArrayHasKey(PDOFactory::HOW_MANY_CONNECTION_TRIES_BEFORE_FAIL, $config);
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
