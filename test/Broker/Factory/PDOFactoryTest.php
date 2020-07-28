<?php
/** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);


namespace AMC\Test\Broker\Factory;


use AMC\Broker\ConfigProvider;
use AMC\Broker\Factory\Exception\MissingConfigEntry;
use AMC\Broker\Factory\PDOFactory;
use Exception;
use PDOException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class PDOFactoryTest extends TestCase
{
    public function testFactoryMustBeInvokable()
    {
        $factory = new PDOFactory();

        $this->assertIsCallable($factory);
    }

    /**
     * @dataProvider dataProviderFactoryMustGetTheConfigFromTheContainer
     */
    public function testFactoryMustGetTheConfigFromTheContainer(bool $isContainerWellConfigured, ?array $config)
    {
        $factory = new PDOFactory();

        $mockContainer = $this->createMock(ContainerInterface::class);

        $doesContainerHasConfigEntry = $config !== null;

        $mockContainer->method('has')->willReturnMap([['config', $doesContainerHasConfigEntry]]);

        $mockContainerGetResultMap = [];

        if ($doesContainerHasConfigEntry) {
            $mockContainerGetResultMap[] = ['config', $config];
        } else {
            $mockContainer->method('get')->willThrowException(new Exception());
        }

        if ($isContainerWellConfigured) {
            $this->expectException(PDOException::class);
        } else {
            $this->expectException(MissingConfigEntry::class);
        }

        if ($mockContainerGetResultMap) {
            $mockContainer->method('get')->willReturnMap($mockContainerGetResultMap);
        }

        $factory->__invoke($mockContainer);
    }

    public function dataProviderFactoryMustGetTheConfigFromTheContainer(): array
    {
        $getAllConfig = function (?string $removeThis = null) {
            return array_filter(
                [
                    PDOFactory::DRIVE_NAME => 'drive-name',
                    PDOFactory::HOSTNAME => 'host-name',
                    PDOFactory::PORT => 1111,
                    PDOFactory::USERNAME => 'user-name',
                    PDOFactory::PASSWORD => 'user-pass',
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
            [false, [ConfigProvider::class => [PDOFactory::class => [],],],],
            [false, [ConfigProvider::class => [PDOFactory::class => $getAllConfig(PDOFactory::DRIVE_NAME),],],],
            [false, [ConfigProvider::class => [PDOFactory::class => $getAllConfig(PDOFactory::HOSTNAME),],],],
            [false, [ConfigProvider::class => [PDOFactory::class => $getAllConfig(PDOFactory::USERNAME),],],],
            [false, [ConfigProvider::class => [PDOFactory::class => $getAllConfig(PDOFactory::PASSWORD),],],],
            [true, [ConfigProvider::class => [PDOFactory::class => $getAllConfig(),],],],
        ];
    }
}