<?php

declare(strict_types=1);

namespace AMC\Test\QueueSystem\Facade;

use AMC\QueueSystem\Exception\UnknownMessage;
use AMC\QueueSystem\Facade\Facade;
use AMC\QueueSystem\Message\QueueMessageInterface;
use AMC\QueueSystem\Platform\PlatformInterface;
use PHPUnit\Framework\TestCase;

class FacadeTest extends TestCase
{
    /**
     * @dataProvider dataProviderSuccessPublish
     */
    public function testSuccessPublish(?string $messageBody, string $expectation)
    {
        $message = $this->createStub(QueueMessageInterface::class);
        $message->method('getBody')->willReturn($messageBody);

        $queueA = $this->createMock(PlatformInterface::class);
        $queueB = $this->createMock(PlatformInterface::class);


        if ($expectation == 'queue-a') {
            $queueToPublish = $queueA;
            $queueNotToPublish = $queueB;
        } elseif ($expectation == 'queue-b') {
            $queueToPublish = $queueB;
            $queueNotToPublish = $queueA;
        } else {
            $this->fail('Unknown type of expectation provided.');
        }

        $queueToPublish->expects($this->once())->method('publish')->with($this->identicalTo($message));
        $queueNotToPublish->expects($this->never())->method('publish');

        (new Facade($queueA, $queueB))->publish($message);
    }

    public function dataProviderSuccessPublish()
    {
        return [
            ['Hi,', 'queue-a'],
            [' Hi, ', 'queue-a'],
            ['   Hi,     ', 'queue-a'],
            ['Hi, Rafa.', 'queue-b'],
            [' Hi, Rafa. ', 'queue-b'],
            ['   Hi, Rafa.     ', 'queue-b'],
        ];
    }

    /**
     * @dataProvider dataPublishInvalidMessage
     */
    public function testPublishInvalidMessage(?string $messageBody)
    {
        $message = $this->createStub(QueueMessageInterface::class);
        $message->method('getBody')->willReturn($messageBody);

        $queueA = $this->createMock(PlatformInterface::class);
        $queueA->expects($this->never())->method('publish');
        $queueB = $this->createMock(PlatformInterface::class);
        $queueB->expects($this->never())->method('publish');

        $this->expectExceptionObject(UnknownMessage::create($message->getBody()));

        (new Facade($queueA, $queueB))->publish($message);
    }

    public function dataPublishInvalidMessage()
    {
        return [
            [''],
            ['    '],
            ['01'],
            ['xxx'],
            ['Hi'],
            ['hi rafa'],
            ['hi, rafa'],
            ['Hi, Rafa'],
            ['Hi, Rafa. Bye!'],
        ];
    }

    public function testConsumeQueueA()
    {
        $callback = function () {
        };

        $queueA = $this->createMock(PlatformInterface::class);
        $queueB = $this->createMock(PlatformInterface::class);

        $queueA->expects($this->once())->method('consume')->with($this->identicalTo($callback));
        $queueB->expects($this->never())->method('consume');

        (new Facade($queueA, $queueB))->consumeQueueA($callback);
    }

    public function testConsumeQueueB()
    {
        $callback = function () {
        };

        $queueA = $this->createMock(PlatformInterface::class);
        $queueB = $this->createMock(PlatformInterface::class);


        $queueA->expects($this->never())->method('consume');
        $queueB->expects($this->once())->method('consume')->with($this->identicalTo($callback));

        (new Facade($queueA, $queueB))->consumeQueueB($callback);
    }
}
