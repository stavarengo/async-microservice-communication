<?php

declare(strict_types=1);

namespace AMC\Test\Broker\Entity;

use AMC\Broker\Entity\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testEntity(?string $id, ?string $message)
    {
        $entity1 = new Message($id, $message);

        $this->assertEquals($id, $entity1->getId());
        $this->assertEquals($message, $entity1->getMessage());

        $entity2 = new Message('zzz', 'yyy');

        $this->assertNotEquals($entity2->getId(), $id);
        $this->assertNotEquals($entity2->getMessage(), $message);

        $entity2->setId($id);
        $entity2->setMessage($message);

        $this->assertEquals($id, $entity2->getId());
        $this->assertEquals($message, $entity2->getMessage());
    }

    public function dataProvider(): array
    {
        return [
            [null, null],
            [null, 'Message ID Null'],
            ['ID-message-null', null],
            ['1', 'Message 1'],
            ['2', 'Message 2'],
        ];
    }
}
