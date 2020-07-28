<?php

declare(strict_types=1);


namespace AMC\Broker\Persistence;


class IDGenerator implements IDGeneratorInterface
{
    public function generate(): string
    {
        return uniqid() . bin2hex(openssl_random_pseudo_bytes(1));
    }
}