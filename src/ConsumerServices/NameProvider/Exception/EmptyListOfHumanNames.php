<?php

declare(strict_types=1);


namespace AMC\ConsumerServices\NameProvider\Exception;


use RuntimeException;

class EmptyListOfHumanNames extends RuntimeException
{
    public static function create(): self
    {
        return new self('Empty list of human names.');
    }
}