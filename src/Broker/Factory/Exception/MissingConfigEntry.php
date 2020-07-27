<?php


namespace AMC\Broker\Factory\Exception;


class MissingConfigEntry extends \RuntimeException
{
    public static function create(string $configName, string $configOwner): self
    {
        return new self(sprintf('Missing config "%s" for "%s".', $configName, $configOwner));
    }
}