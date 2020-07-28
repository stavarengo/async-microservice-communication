<?php
/** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);


namespace AMC\Test\QueueSystem\Platform\RabbitMQ;


use AMC\QueueSystem\ConfigProvider;
use AMC\QueueSystem\Platform\RabbitMQ\Exception\MissingConfigEntry;
use AMC\QueueSystem\Platform\RabbitMQ\RabbitMQConnectionFactory as Factory;
use Exception;
use PhpAmqpLib\Connection\AMQPLazyConnection;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class RabbitMQConnectionFactoryTest extends TestCase
{
    public function testFactoryMustBeInvokable()
    {
        $factory = new Factory();

        $this->assertIsCallable($factory);
    }

    /**
     * @dataProvider dataProviderFactoryMustGetTheConfigFromTheContainer
     */
    public function testFactoryMustGetTheConfigFromTheContainer(bool $isContainerWellConfigured, ?array $config)
    {
        $factory = new Factory();

        $container = $this->createMock(ContainerInterface::class);

        $doesContainerHasConfigEntry = $config !== null;

        $container->method('has')->willReturnMap([['config', $doesContainerHasConfigEntry]]);

        $mockContainerGetResultMap = [];

        if ($doesContainerHasConfigEntry) {
            $mockContainerGetResultMap[] = ['config', $config];
        } else {
            $container->method('get')->willThrowException(new Exception());
        }

        if (!$isContainerWellConfigured) {
            $this->expectException(MissingConfigEntry::class);
        }

        if ($mockContainerGetResultMap) {
            $container->method('get')->willReturnMap($mockContainerGetResultMap);
        }

        $this->assertInstanceOf(AMQPLazyConnection::class, $factory->__invoke($container));
    }

    public function dataProviderFactoryMustGetTheConfigFromTheContainer(): array
    {
        $getAllConfig = function (?string $removeThis = null) {
            return array_filter(
                [
                    Factory::HOSTNAME => 'host-name',
                    Factory::PORT => 223,
                    Factory::USERNAME => 'user-name',
                    Factory::PASSWORD => 'user-pass',
                ],
                function (string $key) use ($removeThis) {
                    return $key != $removeThis;
                },
                ARRAY_FILTER_USE_KEY
            );
        };

        return [
            [false, null],
            [false, []],
            [false, [ConfigProvider::class => []]],
            [false, [ConfigProvider::class => [Factory::class => [],],],],
            [false, [ConfigProvider::class => [Factory::class => $getAllConfig(Factory::PORT),],],],
            [false, [ConfigProvider::class => [Factory::class => $getAllConfig(Factory::HOSTNAME),],],],
            [false, [ConfigProvider::class => [Factory::class => $getAllConfig(Factory::USERNAME),],],],
            [false, [ConfigProvider::class => [Factory::class => $getAllConfig(Factory::PASSWORD),],],],
            [true, [ConfigProvider::class => [Factory::class => $getAllConfig(),],],],
        ];
    }
}