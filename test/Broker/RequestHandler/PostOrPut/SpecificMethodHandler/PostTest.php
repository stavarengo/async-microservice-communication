<?php
/** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);

namespace AMC\Test\Broker\RequestHandler\PostOrHandle\SpecificMethodHandler;

use AMC\Broker\Entity\Message;
use AMC\Broker\Persistence\PersistenceInterface;
use AMC\Broker\RequestHandler\PostOrPut\SpecificMethodHandler\Post;
use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    public function testRequestSucceed()
    {
        $entityToTest = new Message('id', 'message');
        $persistent = $this->createMock(PersistenceInterface::class);

        $persistent->expects($this->once())
            ->method('insert')
            ->with($entityToTest->getMessage())
            ->willReturn($entityToTest);

        $result = (new Post($persistent))->handle($entityToTest->getMessage(), null);

        $this->assertSame($entityToTest, $result->getMessage());

        $this->assertEquals(201, $result->getResponse()->getStatusCode());

        $responseBody = $result->getResponse()->getBody()->getContents();
        $this->assertJson($responseBody);

        $responseEntity = json_decode($responseBody);
        $this->assertObjectHasAttribute('id', $responseEntity);
        $this->assertEquals($result->getMessage()->getId(), $responseEntity->id);

        $this->assertObjectHasAttribute('message', $responseEntity);
        $this->assertEquals($result->getMessage()->getMessage(), $responseEntity->message);
    }

    public function testMustIgnoreId()
    {
        $entityToTest = new Message('id', 'message');
        $idToIgnore = 'ignore-id';

        $this->assertNotEquals($entityToTest->getId(), $idToIgnore);

        $persistent = $this->createStub(PersistenceInterface::class);
        $persistent->method('insert')->willReturn($entityToTest);

        $result = (new Post($persistent))->handle($entityToTest->getMessage(), $idToIgnore);

        $this->assertSame($entityToTest, $result->getMessage());
        $this->assertNotEquals($entityToTest->getId(), $idToIgnore);
        $this->assertEquals($entityToTest->getId(), $result->getMessage()->getId());

        $responseEntity = json_decode($result->getResponse()->getBody()->getContents());
        $this->assertEquals($result->getMessage()->getId(), $responseEntity->id);
    }

}
