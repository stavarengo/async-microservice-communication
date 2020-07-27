<?php


namespace AMC\Broker\RequestHandler;


use AMC\Broker\Persistence\PersistenceInterface;
use AMC\Broker\ResponseBody\Error;
use AMC\Broker\ResponseBody\ResponseWithMessage;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function GuzzleHttp\Psr7\stream_for;

class PostHandler implements RequestHandlerInterface
{
    private PersistenceInterface $persistence;

    public function __construct(PersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    public function handleIt(ServerRequestInterface $request): ResponseInterface
    {
        $bodyContent = $request->getBody()->getContents();
        $requestBody = $bodyContent ? (object)json_decode($bodyContent) : null;

        if (!$requestBody) {
            return new Response(
                422, ['Content-Type' => 'application/json'],
                stream_for(new Error('Missing body content.'))
            );
        }

        if (!isset($requestBody->message)) {
            return new Response(
                422, ['Content-Type' => 'application/json'],
                stream_for(new Error('Missing the "message" attribute.'))
            );
        }

        $message = $this->persistence->insert($requestBody->message);

        return new Response(
            201,
            [
                'Content-Type' => 'application/json'
            ],
            stream_for(new ResponseWithMessage($message))
        );
    }
}