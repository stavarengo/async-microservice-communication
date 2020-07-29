<?php

declare(strict_types=1);


namespace AMC\QueueSystem\Exception;


use RuntimeException;

class UnknownMessage extends RuntimeException
{
    public static function create(string $unknownMessage): self
    {
        return new self(sprintf('I don\'t know which queue this message belongs to: "%s".', $unknownMessage));
    }
}