<?php

declare(strict_types=1);


namespace AMC\QueueSystem;


interface QueueMessageInterface
{
    public function getId(): string;

    public function setId(string $id): void;

    public function getBody(): string;

    public function setBody(string $body): void;
}