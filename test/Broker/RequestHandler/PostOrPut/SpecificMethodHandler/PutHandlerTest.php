<?php
/** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);

namespace AMC\Test\Broker\RequestHandler\PostOrHandle\SpecificMethodHandler;

use AMC\Broker\Entity\Message;
use AMC\Broker\Persistence\PersistenceInterface;
use AMC\Broker\RequestHandler\PostOrPut\SpecificMethodHandler\Put;
use PHPUnit\Framework\TestCase;

class PutHandlerTest extends TestCase
{
    public function testRequestSucceed()
    {
        $entityToTest = new Message('123-id', "Hi there");

        $persistent = $this->createMock(PersistenceInterface::class);

        $persistent->expects($this->once())
            ->method('get')
            ->willReturn($entityToTest);
        $persistent->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($entityToTest))
            ->willReturn($entityToTest);

        $result = (new Put($persistent))->handle($entityToTest->getMessage(), $entityToTest->getId());

        $this->assertNotNull($result->getMessage());
        $this->assertEquals($entityToTest->getMessage(), $result->getMessage()->getMessage());
        $this->assertEquals($entityToTest->getId(), $result->getMessage()->getId());

        $this->assertEquals(200, $result->getResponse()->getStatusCode());

        $responseBody = $result->getResponse()->getBody()->getContents();
        $this->assertJson($responseBody);

        $responseEntity = json_decode($responseBody);
        $this->assertObjectHasAttribute('id', $responseEntity);
        $this->assertEquals($result->getMessage()->getId(), $responseEntity->id);

        $this->assertObjectHasAttribute('message', $responseEntity);
        $this->assertEquals($result->getMessage()->getMessage(), $responseEntity->message);
    }

    public function testPutRequestReturns404()
    {
        $persistent = $this->createMock(PersistenceInterface::class);
        $persistent->expects($this->once())->method('get')->willReturn(null);

        $idToTest = '';
        $result = (new Put($persistent))->handle('', $idToTest);

        $this->assertNull($result->getMessage());

        $this->assertEquals(404, $result->getResponse()->getStatusCode());

        $responseBody = $result->getResponse()->getBody()->getContents();
        $this->assertJson($responseBody);

        $this->assertStringContainsString(
            json_encode(sprintf('Message "%s" not found.', $idToTest)),
            $responseBody
        );
    }
}
