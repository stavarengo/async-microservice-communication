<?php
/** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);

namespace AMC\Test\Broker\RequestHandler;

use AMC\Broker\Entity\Message;
use AMC\Broker\Persistence\PersistenceInterface;
use AMC\Broker\RequestHandler\PostOrPutHandler;
use AMC\QueueSystem\Facade\FacadeInterface;
use AMC\QueueSystem\Message\QueueMessage;
use AMC\QueueSystem\Message\QueueMessageInterface;
use AMC\QueueSystem\Platform\Exception\FailedToPublishMessage;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class PostOrPutHandlerTest extends TestCase
{
    /**
     * @dataProvider dataProviderForDifferentRequestMethods
     */
    public function testRequestSucceed(Message $entity, string $method, int $expectedHttpStatusCode)
    {
        $queue = $this->createStub(FacadeInterface::class);

        $persistent = $this->createMock(PersistenceInterface::class);

        if ($method == 'POST') {
            $persistent->expects($this->once())
                ->method('insert')
                ->with($entity->getMessage())
                ->willReturn($entity);
        } else {
            $persistent->expects($this->once())
                ->method('update')
                ->with($this->identicalTo($entity))
                ->willReturn($entity);
            $persistent->expects($this->once())
                ->method('get')
                ->willReturn($entity);
        }

        $response = (new PostOrPutHandler($persistent, $queue))->handleIt(
            $this->mockRequestWithEntity($entity, $method)
        );
        $this->assertSuccessfulResponse($response, $expectedHttpStatusCode, $entity);
    }

    public function testPutRequestReturns404()
    {
        $queue = $this->createStub(FacadeInterface::class);

        $persistent = $this->createMock(PersistenceInterface::class);
        $persistent->expects($this->once())->method('get')->willReturn(null);

        $message = new Message('', '');
        $response = (new PostOrPutHandler($persistent, $queue))->handleIt(
            $this->mockRequestWithEntity($message, 'PUT')
        );

        $this->assertEquals(404, $response->getStatusCode());

        $this->assertStringContainsString(
            json_encode(sprintf('Message "%s" not found.', $message->getId())),
            $response->getBody()->getContents()
        );
    }

    /**
     * @dataProvider dataProviderForDifferentRequestMethods
     */
    public function testMustSendMessageToQueueSystemWhenRequestIsSuccessful(Message $entity, string $method)
    {
        $persistence = $this->createMock(PersistenceInterface::class);
        $persistence->method('insert')->willReturn($entity);
        $persistence->method('update')->willReturn($entity);
        $persistence->method('get')->willReturn($entity);

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

        (new PostOrPutHandler($persistence, $queue))->handleIt(
            $this->mockRequestWithEntity($entity, $method)
        );
    }

    /**
     * @dataProvider dataProviderForDifferentRequestMethods
     */
    public function testMustRollbackIfFailToSendMessageToQueue(Message $entity, string $method)
    {
        $persistence = $this->createMock(PersistenceInterface::class);
        $persistence->method('insert')->willReturn($entity);
        $persistence->method('update')->willReturn($entity);
        $persistence->method('get')->willReturn($entity);
        $persistence->expects($this->once())->method('rollback');

        $queueMessage = $this->createStub(QueueMessageInterface::class);

        $failedToPublishMessageException = FailedToPublishMessage::create(
            $queueMessage,
            new RuntimeException('Test Exception ' . $method)
        );

        $queue = $this->createMock(FacadeInterface::class);
        $queue->method('publish')->willThrowException($failedToPublishMessageException);

        $this->expectExceptionObject($failedToPublishMessageException);

        (new PostOrPutHandler($persistence, $queue))->handleIt($this->mockRequestWithEntity($entity, $method));
    }

    /**
     * @dataProvider requestWithIncompleteBodyProvider
     */
    public function testRequestWithIncompleteBody(
        string $expectedErrorMessage,
        int $expectedStatusCode,
        ServerRequestInterface $request
    ) {
        $entity = new Message('', '');
        $persistence = $this->createStub(PersistenceInterface::class);
        $persistence->method('get')->willReturn($entity);
        $persistence->method('insert')->willReturn($entity);
        $persistence->method('update')->willReturn($entity);

        $requestHandler = new PostOrPutHandler(
            $persistence,
            $this->createStub(FacadeInterface::class)
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

    private function assertSuccessfulResponse(
        ResponseInterface $actualResponse,
        int $expectedCode,
        Message $expectedEntity
    ): void {
        $this->assertEquals($expectedCode, $actualResponse->getStatusCode());

        $responseBody = $actualResponse->getBody()->getContents();
        $this->assertJson($responseBody);

        $responseEntity = json_decode($responseBody);
        $this->assertObjectHasAttribute('id', $responseEntity);
        $this->assertEquals($expectedEntity->getId(), $responseEntity->id);

        $this->assertObjectHasAttribute('message', $responseEntity);
        $this->assertEquals($expectedEntity->getMessage(), $responseEntity->message);
    }

    public function dataProviderForDifferentRequestMethods()
    {
        return [
            [new Message('123-id-insert', "Hi insert"), 'POST', 201],
            [new Message('123-id-update', "Hi update"), 'PUT', 200],
        ];
    }

}
