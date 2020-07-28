<?php

declare(strict_types=1);


namespace AMC\Test\Broker\Persistence;


use AMC\Broker\Persistence\IDGenerator;
use PHPUnit\Framework\TestCase;

class IDGeneratorTest extends TestCase
{
    public function testGenerator()
    {
        $IDGenerator = new IDGenerator();

        $idsGenerated = [
            $IDGenerator->generate(),
            $IDGenerator->generate(),
            $IDGenerator->generate(),
        ];

        $this->assertSameSize($idsGenerated, array_unique($idsGenerated));
    }
}