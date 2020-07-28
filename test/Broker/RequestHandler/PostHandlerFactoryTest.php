<?php
/** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);


namespace AMC\Test\Broker\RequestHandler;


use AMC\Broker\Persistence\PersistenceInterface;
use AMC\Broker\RequestHandler\PostHandler;
use AMC\Broker\RequestHandler\PostHandlerFactory;
use AMC\QueueSystem\Platform\PlatformInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class PostHandlerFactoryTest extends TestCase
{
    public function testFactoryMustBeInvokable()
    {
        $factory = new PostHandlerFactory();

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

        $factory = new PostHandlerFactory();
        $this->assertInstanceOf(PostHandler::class, $factory($container));
    }
}