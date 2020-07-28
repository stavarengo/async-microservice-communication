<?php

declare(strict_types=1);


namespace AMC\ConsumerServices\BrokerClient\Exception;


use RuntimeException;

class BrokerClientException extends RuntimeException
{
    public static function unexpectedStatusCode(int $expectedCode, int $actualCode): self
    {
        return new self(
            sprintf('Request finished with code "%s", but was expecting "%s".', $actualCode, $expectedCode)
        );
    }

    public static function invalidJsonString(string $invalidJsonString)
    {
        return new self(sprintf('Received and invalid JSON response: "%s".', $invalidJsonString));
    }

    public static function invalidEntityReceived(string $nameOfTheMissingAttribute)
    {
        return new self(
            sprintf('Received and answer that does not contain the attribute "%s".', $nameOfTheMissingAttribute)
        );
    }

}