<?php

declare(strict_types=1);

namespace AMC\Test\QueueSystem;

use AMC\QueueSystem\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testFactoryMustBeInvokable()
    {
        $configProvider = new ConfigProvider();

        $this->assertIsCallable($configProvider);
    }

    public function testInvoke()
    {
        $configProvider = new ConfigProvider();
        $config = $configProvider->__invoke();

        $expectedConfig = [];

        $this->assertJsonStringEqualsJsonString(json_encode($expectedConfig), json_encode($config));
    }
}
