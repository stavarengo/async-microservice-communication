<?php


namespace AMC\Broker\RequestHandler;


use AMC\Broker\Persistence\PersistenceInterface;
use AMC\Broker\ResponseBody\Error;
use AMC\Broker\ResponseBody\ResponseWithMessage;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function GuzzleHttp\Psr7\stream_for;

class GetHandler implements RequestHandlerInterface
{
    private PersistenceInterface $persistence;

    public function __construct(PersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    public function handleIt(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getQueryParams()['id'] ?? null;

        $message = $this->persistence->get($id);

        if (!$message) {
            return new Response(
                404, ['Content-Type' => 'application/json'],
                stream_for(new Error(sprintf('Entity "%s" not found.', $id)))
            );
        }

        return new Response(
            200,
            [
                'Content-Type' => 'application/json'
            ],
            stream_for(new ResponseWithMessage($message))
        );
    }
}