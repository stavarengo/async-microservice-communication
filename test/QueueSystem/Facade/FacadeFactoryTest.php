<?php
/** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);


namespace AMC\Test\QueueSystem\Facade;


use AMC\QueueSystem\Facade\Facade;
use AMC\QueueSystem\Facade\FacadeFactory;
use AMC\QueueSystem\Platform\PlatformInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class FacadeFactoryTest extends TestCase
{
    public function testFactoryMustBeInvokable()
    {
        $factory = new FacadeFactory();

        $this->assertIsCallable($factory);
    }

    public function testFactory()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [$this->equalTo(PlatformInterface::SERVICE_NAME_QUEUE_TOPIC_A)],
                [$this->equalTo(PlatformInterface::SERVICE_NAME_QUEUE_TOPIC_B)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->createStub(PlatformInterface::class),
                $this->createStub(PlatformInterface::class),
            );

        $factory = new FacadeFactory();
        $this->assertInstanceOf(Facade::class, $factory($container));
    }
}