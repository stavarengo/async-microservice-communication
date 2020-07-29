<?php
/** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);

namespace AMC\Test\Broker\RequestHandler\PostOrHandle;

use AMC\Broker\Entity\Message;
use AMC\Broker\Persistence\PersistenceInterface;
use AMC\Broker\RequestHandler\PostOrPut\PostOrPutHandler;
use AMC\Broker\RequestHandler\PostOrPut\SpecificMethodHandler\HandlerResult;
use AMC\Broker\RequestHandler\PostOrPut\SpecificMethodHandler\SpecificMethodHandlerInterface;
use AMC\QueueSystem\Facade\FacadeInterface;
use AMC\QueueSystem\Message\QueueMessage;
use AMC\QueueSystem\Message\QueueMessageInterface;
use AMC\QueueSystem\Platform\Exception\FailedToPublishMessage;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class PostOrPutHandlerTest extends TestCase
{
    public function testRequest()
    {
        $entity = new Message('', '');

        $queue = $this->createStub(FacadeInterface::class);
        $persistent = $this->createMock(PersistenceInterface::class);
        $handler = $this->createMock(SpecificMethodHandlerInterface::class);

        $successResult = new HandlerResult($entity, new Response(200));

        $handler->expects($this->once())
            ->method('handle')
            ->willReturnOnConsecutiveCalls($successResult);

        $postOrPutHandler = new PostOrPutHandler($handler, $handler, $persistent, $queue);
        $response = $postOrPutHandler->handleIt($this->mockRequestWithEntity($entity, 'POST'));

        $this->assertSame($successResult->getResponse(), $response);
    }

    public function testMustSendMessageToQueueSystemWhenRequestIsSuccessful()
    {
        $entity = new Message('id', 'message');

        $persistence = $this->createStub(PersistenceInterface::class);

        $handler = $this->createStub(SpecificMethodHandlerInterface::class);
        $handler->method('handle')->willReturn(new HandlerResult($entity, new Response(200)));

        $queue = $this->createMock(FacadeInterface::class);
        $queue->expects($this->once())
            ->method('publish')
            ->with(
                $this->callback(
                    function (QueueMessage $queueMessage) use ($entity) {
                        return $queueMessage->getId() === $entity->getId()
                            && $queueMessage->getBody() === $entity->getMessage();
                    }
                )
            );

        $postOrPutHandler = new PostOrPutHandler($handler, $handler, $persistence, $queue);
        $postOrPutHandler->handleIt($this->mockRequestWithEntity($entity, 'POST'));
    }

    /**
     * @dataProvider dataProviderMustSendMessageToQueueSystemWhenRequestIsFailed
     */
    public function testMustSendMessageToQueueSystemWhenRequestIsFailed(HandlerResult $handlerResult)
    {
        $persistence = $this->createStub(PersistenceInterface::class);

        $handler = $this->createStub(SpecificMethodHandlerInterface::class);
        $handler->method('handle')->willReturn($handlerResult);

        $queue = $this->createMock(FacadeInterface::class);
        $queue->expects($this->never())->method('publish');

        $postOrPutHandler = new PostOrPutHandler($handler, $handler, $persistence, $queue);
        $postOrPutHandler->handleIt($this->mockRequestWithEntity(new Message('id', 'message'), 'POST'));
    }

    /**
     * @dataProvider dataProviderForDifferentRequestMethods
     */
    public function testMustRollbackIfFailToSendMessageToQueue(Message $entity, string $method)
    {
        $persistence = $this->createStub(PersistenceInterface::class);
        $persistence->expects($this->once())->method('beginTransaction');
        $persistence->expects($this->once())->method('rollback');

        $handler = $this->createStub(SpecificMethodHandlerInterface::class);
        $handler->method('handle')->willReturn(new HandlerResult(new Message('id', 'message'), new Response(200)));

        $failedToPublishMessageException = FailedToPublishMessage::create(
            $this->createStub(QueueMessageInterface::class),
            new RuntimeException('Test Exception ' . $method)
        );

        $queue = $this->createMock(FacadeInterface::class);
        $queue->method('publish')->willThrowException($failedToPublishMessageException);

        $this->expectExceptionObject($failedToPublishMessageException);

        $postOrPutHandler = new PostOrPutHandler($handler, $handler, $persistence, $queue);
        $postOrPutHandler->handleIt($this->mockRequestWithEntity($entity, $method));
    }

    public function testRequestMethodMapToSpecificHandler()
    {
        $persistence = $this->createStub(PersistenceInterface::class);
        $queue = $this->createStub(FacadeInterface::class);

        $postEntity = new Message(null, 'Post entity');
        $postHandler = $this->createMock(SpecificMethodHandlerInterface::class);
        $postHandler->expects($this->once())->method('handle')->with($postEntity->getMessage(), $postEntity->getId());

        $putEntity = new Message('put-id', 'Put entity');
        $putHandler = $this->createMock(SpecificMethodHandlerInterface::class);
        $putHandler->expects($this->once())->method('handle')->with($putEntity->getMessage(), $putEntity->getId());

        $postOrPutHandler = new PostOrPutHandler($postHandler, $putHandler, $persistence, $queue);

        $postOrPutHandler->handleIt($this->mockRequestWithEntity($postEntity, 'POST'));
        $postOrPutHandler->handleIt($this->mockRequestWithEntity($putEntity, 'PUT'));
    }

    /**
     * @dataProvider  dataProviderRequestWithNotSupportedMethod
     */
    public function testRequestWithNotSupportedMethod(string $method)
    {
        $persistence = $this->createStub(PersistenceInterface::class);
        $queue = $this->createStub(FacadeInterface::class);

        $handler = $this->createMock(SpecificMethodHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $postOrPutHandler = new PostOrPutHandler($handler, $handler, $persistence, $queue);

        $response = $postOrPutHandler->handleIt($this->mockRequestWithEntity(new Message('', 'Message'), $method));

        $this->assertEquals(405, $response->getStatusCode());

        $this->assertStringContainsString(
            'Method not allowed. Use one of the following:',
            $response->getBody()->getContents()
        );
    }

    public function dataProviderRequestWithNotSupportedMethod(): array
    {
        return [
            ['GET'],
            ['HEAD'],
            ['invalid'],
            ['_-'],
            ['DELETE'],
            ['CONNECT'],
            ['OPTIONS'],
            ['TRACE'],
            ['PATCH'],
        ];
    }

    /**
     * @dataProvider requestWithIncompleteBodyProvider
     */
    public function testRequestWithIncompleteBody(
        string $expectedErrorMessage,
        int $expectedStatusCode,
        ServerRequestInterface $request
    ) {
        $persistence = $this->createStub(PersistenceInterface::class);

        $handler = $this->createMock(SpecificMethodHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $queue = $this->createStub(FacadeInterface::class);

        $postOrPutHandler = new PostOrPutHandler($handler, $handler, $persistence, $queue);
        $response = $postOrPutHandler->handleIt($request);

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

    private function mockRequestWithEntity(Message $message, string $method): ServerRequestInterface
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(
            json_encode(
                [
                    'message' => $message->getMessage()
                ]
            )
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getBody')->willReturn($stream);
        $request->method('getMethod')->willReturn($method);

        if ($method == 'PUT') {
            $queryParams = [
                'id' => $message->getId(),
            ];
            $request->method('getQueryParams')->willReturn($queryParams);
        }

        return $request;
    }

    public function dataProviderMustSendMessageToQueueSystemWhenRequestIsFailed()
    {
        $successResponse = $this->createStub(ResponseInterface::class);
        $successResponse->method('getStatusCode')->willReturn(200);
        $failResponse = $this->createStub(ResponseInterface::class);
        $failResponse->method('getStatusCode')->willReturn(400);

        return [
            [new HandlerResult(new Message('', ''), $failResponse)],
            [new HandlerResult(null, $failResponse)],
            [new HandlerResult(null, $successResponse)],
        ];
    }

    public function dataProviderForDifferentRequestMethods()
    {
        return [
            [new Message('123-id-insert', "Hi insert"), 'POST', 201],
            [new Message('123-id-update', "Hi update"), 'PUT', 200],
        ];
    }

}
