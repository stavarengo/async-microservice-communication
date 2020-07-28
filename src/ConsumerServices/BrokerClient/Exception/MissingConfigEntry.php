<?php

declare(strict_types=1);


namespace AMC\ConsumerServices\BrokerClient\Exception;


use RuntimeException;

class MissingConfigEntry extends RuntimeException
{
    public static function create(string $configName, string $configOwner): self
    {
        return new self(sprintf('Missing config "%s" for "%s".', $configName, $configOwner));
    }
}