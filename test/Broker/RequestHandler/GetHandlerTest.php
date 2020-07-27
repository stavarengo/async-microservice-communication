<?php

declare(strict_types=1);

namespace AMC\Test\Broker\RequestHandler;

use AMC\Broker\Entity\Message;
use AMC\Broker\Persistence\PersistenceInterface;
use AMC\Broker\RequestHandler\GetHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class GetHandlerTest extends TestCase
{
    public function testSuccessfulRequest()
    {
        $expectedMessage = new Message('123-id');

        $persistenceMock = $this->createMock(PersistenceInterface::class);
        $persistenceMock->method('get')
            ->with($expectedMessage->getId())
            ->willReturn($expectedMessage);

        $requestHandler = new GetHandler($persistenceMock);
        $response = $requestHandler->handleIt($this->mockRequest(['id' => $expectedMessage->getId()]));

        $this->assertEquals(200, $response->getStatusCode());

        $responseBody = $response->getBody()->getContents();
        $this->assertJson($responseBody);
        $responseAsObject = json_decode($responseBody, true);
        $this->assertArrayHasKey('id', $responseAsObject);
        $this->assertEquals($expectedMessage->getId(), $responseAsObject['id']);
    }

    public function testRequestNonExistID()
    {
        $id = 'id-123';

        $persistenceMock = $this->createMock(PersistenceInterface::class);
        $persistenceMock->method('get')
            ->with($id)
            ->willReturn(null);

        $requestHandler = new GetHandler($persistenceMock);
        $response = $requestHandler->handleIt($this->mockRequest(['id' => $id]));

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString(
            sprintf('Entity \"%s\" not found.', $id),
            $response->getBody()->getContents()
        );
    }

    private function mockRequest(array $queryParams): ServerRequestInterface
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->method('getQueryParams')->willReturn($queryParams);

        return $mockRequest;
    }
}
