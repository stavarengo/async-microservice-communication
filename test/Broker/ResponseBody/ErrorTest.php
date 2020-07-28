<?php

declare(strict_types=1);

namespace AMC\Test\Broker\ResponseBody;

use AMC\Broker\ResponseBody\Error;
use PHPUnit\Framework\TestCase;

class ErrorTest extends TestCase
{
    /**
     * @dataProvider errorProvider
     * @param string $detail
     */
    public function testError(string $detail)
    {
        $error = new Error($detail);

        $this->assertEquals($detail, $error->getDetail());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => true, 'detail' => $detail]),
            $error->__toString()
        );
    }

    public function errorProvider(): array
    {
        return [
            ['Error detail 1'],
            ['Error detail 2'],
        ];
    }
}
