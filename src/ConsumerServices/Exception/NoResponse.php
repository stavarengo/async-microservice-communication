<?php

declare(strict_types=1);


namespace AMC\ConsumerServices\Exception;


use RuntimeException;

class NoResponse extends RuntimeException
{
    public static function create()
    {
        return new self('No response.');
    }
}