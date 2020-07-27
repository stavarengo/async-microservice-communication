<?php


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

        $this->assertEquals(count($idsGenerated), count(array_unique($idsGenerated)));
    }
}