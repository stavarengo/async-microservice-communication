<?php

declare(strict_types=1);


namespace AMC\Broker\RequestHandler\PostOrPut\SpecificMethodHandler;


use AMC\Broker\Entity\Message;
use Psr\Http\Message\ResponseInterface;

class HandlerResult
{
    private ?Message $message;
    private ResponseInterface $response;

    public function __construct(?Message $message, ResponseInterface $response)
    {
        $this->message = $message;
        $this->response = $response;
    }

    /**
     * @return ?Message
     */
    public function getMessage(): ?Message
    {
        return $this->message;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}