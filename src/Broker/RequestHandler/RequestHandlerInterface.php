<?php

declare(strict_types=1);


namespace AMC\Broker\RequestHandler;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RequestHandlerInterface
{
    /**
     * Handle the received request.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handleIt(ServerRequestInterface $request): ResponseInterface;
}