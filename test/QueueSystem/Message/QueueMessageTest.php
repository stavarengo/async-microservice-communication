<?php

declare(strict_types=1);

namespace AMC\Test\QueueSystem\Message;

use AMC\QueueSystem\Message\QueueMessage;
use PHPUnit\Framework\TestCase;

class QueueMessageTest extends TestCase
{
    public function testMessage()
    {
        $id = 'id-133';
        $body = 'body message';
        $queueMessage = new QueueMessage($id, $body);

        $this->assertEquals($id, $queueMessage->getId());
        $this->assertEquals($body, $queueMessage->getBody());
    }

    public function testSetMethods()
    {
        $queueMessage = new QueueMessage('', '');

        $id = 'id-778';
        $body = 'body message set methods';

        $this->assertNotEquals($id, $queueMessage->getId());
        $this->assertNotEquals($body, $queueMessage->getBody());

        $queueMessage->setId($id);
        $queueMessage->setBody($body);

        $this->assertEquals($id, $queueMessage->getId());
        $this->assertEquals($body, $queueMessage->getBody());
    }
}
