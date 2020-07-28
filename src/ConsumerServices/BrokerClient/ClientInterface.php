<?php

declare(strict_types=1);


namespace AMC\ConsumerServices\BrokerClient;


interface ClientInterface
{
    public function post(string $message): string;

    public function put(string $id, string $message): void;

    public function get(string $id): ?string;
}