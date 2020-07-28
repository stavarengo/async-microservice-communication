<?php
/** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);

namespace AMC\Test\Broker\RequestHandler;

use AMC\Broker\Entity\Message;
use AMC\Broker\Persistence\PersistenceInterface;
use AMC\Broker\RequestHandler\PostHandler;
use AMC\QueueSystem\Message\QueueMessage;
use AMC\QueueSystem\Message\QueueMessageInterface;
use AMC\QueueSystem\Platform\Exception\FailedToPublishMessage;
use AMC\QueueSystem\Platform\PlatformInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class PostHandlerTest extends TestCase
{
    public function testSuccessfulRequest()
    {
        $messageEntity = new Message('123-random-id', "Hi, ");

        $persistentMock = $this->createMock(PersistenceInterface::class);
        $persistentMock->method('insert')
            ->with($messageEntity->getMessage())
            ->willReturn($messageEntity);

        $queueMock = $this->createMock(PlatformInterface::class);
        $queueMock->expects($this->once())
            ->method('publish')
            ->with(
                $this->callback(
                    function (QueueMessage $queueMessage) use ($messageEntity) {
                        return $queueMessage->getId() === $messageEntity->getId()
                            && $queueMessage->getBody() === $messageEntity->getMessage();
                    }
                )
            );

        $requestHandler = new PostHandler($persistentMock, $queueMock);

        $response = $requestHandler->handleIt(
            $this->mockRequest(
                json_encode(
                    [
                        'message' => $messageEntity->getMessage()
                    ]
                )
            )
        );

        $this->assertEquals(201, $response->getStatusCode());

        $responseBody = $response->getBody()->getContents();
        $this->assertJson($responseBody);

        $responseEntity = json_decode($responseBody);
        $this->assertObjectHasAttribute('id', $responseEntity);
        $this->assertEquals($messageEntity->getId(), $responseEntity->id);

        $this->assertObjectHasAttribute('message', $responseEntity);
        $this->assertEquals($messageEntity->getMessage(), $responseEntity->message);
    }

    public function testMustRollbackIfFailToSendMessageToQueue()
    {
        $messageEntity = new Message('123-another-random-id', "Hi, Test");

        $persistence = $this->createMock(PersistenceInterface::class);
        $persistence->method('insert')->willReturn($messageEntity);
        $persistence->expects($this->once())->method('rollback');

        $queueMessage = $this->createStub(QueueMessageInterface::class);
        $queueMessage->method('getId')->willReturn($messageEntity->getId());

        $failedToPublishMessageException = FailedToPublishMessage::create(
            $queueMessage,
            new RuntimeException('Test Exception')
        );

        $queue = $this->createMock(PlatformInterface::class);
        $queue->method('publish')
            ->willThrowException($failedToPublishMessageException);

        $requestHandler = new PostHandler($persistence, $queue);

        $this->expectExceptionObject($failedToPublishMessageException);

        $requestHandler->handleIt(
            $this->mockRequest(
                json_encode(
                    [
                        'message' => $messageEntity->getMessage()
                    ]
                )
            )
        );
    }

    /**
     * @dataProvider requestWithIncompleteBodyProvider
     */
    public function testRequestWithIncompleteBody(
        string $expectedErrorMessage,
        int $expectedStatusCode,
        ServerRequestInterface $request
    ) {
        $requestHandler = new PostHandler(
            $this->createMock(PersistenceInterface::class),
            $this->createMock(PlatformInterface::class)
        );
        $response = $requestHandler->handleIt($request);

        $this->assertEquals($expectedStatusCode, $response->getStatusCode());

        $this->assertStringContainsString($expectedErrorMessage, $response->getBody()->getContents());
    }

    public function requestWithIncompleteBodyProvider(): array
    {
        return [
            [json_encode('Missing body content.'), 422, $this->mockRequest(null)],
            [json_encode('Missing body content.'), 422, $this->mockRequest('')],
            [json_encode('Missing the "message" attribute.'), 422, $this->mockRequest('[]')],
            [json_encode('Missing the "message" attribute.'), 422, $this->mockRequest('{}')],
        ];
    }

    private function mockRequest(?string $bodyContent): ServerRequestInterface
    {
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($bodyContent);

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->method('getBody')->willReturn($mockStream);

        return $mockRequest;
    }
}
