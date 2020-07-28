<?php
/** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);


namespace AMC\Test\Broker\RequestHandler;


use AMC\Broker\Persistence\PersistenceInterface;
use AMC\Broker\RequestHandler\PostOrPutHandler;
use AMC\Broker\RequestHandler\PostOrPutHandlerFactory;
use AMC\QueueSystem\Platform\PlatformInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class PostOrPutHandlerFactoryTest extends TestCase
{
    public function testFactoryMustBeInvokable()
    {
        $factory = new PostOrPutHandlerFactory();

        $this->assertIsCallable($factory);
    }

    public function testFactory()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [$this->equalTo(PersistenceInterface::class)],
                [$this->equalTo(PlatformInterface::SERVICE_NAME_QUEUE_TOPIC_A)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->createStub(PersistenceInterface::class),
                $this->createStub(PlatformInterface::class),
            );

        $factory = new PostOrPutHandlerFactory();
        $this->assertInstanceOf(PostOrPutHandler::class, $factory($container));
    }
}