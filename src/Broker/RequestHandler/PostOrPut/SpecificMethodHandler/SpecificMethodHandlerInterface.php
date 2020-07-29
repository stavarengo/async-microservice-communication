<?php

declare(strict_types=1);


namespace AMC\Broker\RequestHandler\PostOrPut\SpecificMethodHandler;


interface SpecificMethodHandlerInterface
{
    public function handle(string $message, ?string $id): HandlerResult;
}