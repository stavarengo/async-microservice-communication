<?php
/** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);

namespace AMC\Test\Broker\ResponseBody;

use AMC\Broker\Entity\Message;
use AMC\Broker\ResponseBody\ResponseWithMessage;
use PHPUnit\Framework\TestCase;

class ResponseWithMessageTest extends TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testResponseBody(Message $message)
    {
        $responseBody = new ResponseWithMessage($message);

        $this->assertJsonStringEqualsJsonString(
            json_encode(['id' => $message->getId(), 'message' => $message->getMessage()]),
            $responseBody->__toString()
        );
    }

    public function responseProvider(): array
    {
        return [
            [new Message('1', 'First message.')],
            [new Message('2', 'Second message.')],
            [new Message()],
        ];
    }
}
