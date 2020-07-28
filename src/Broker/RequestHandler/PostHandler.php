<?php

declare(strict_types=1);


namespace AMC\Broker\RequestHandler;


use AMC\Broker\Persistence\PersistenceInterface;
use AMC\Broker\ResponseBody\Error;
use AMC\Broker\ResponseBody\ResponseWithMessage;
use AMC\QueueSystem\Message\QueueMessage;
use AMC\QueueSystem\Platform\PlatformInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use function GuzzleHttp\Psr7\stream_for;

class PostHandler implements RequestHandlerInterface
{
    private PersistenceInterface $persistence;
    /**
     * @var PlatformInterface
     */
    private PlatformInterface $queue;

    public function __construct(PersistenceInterface $persistence, PlatformInterface $queue)
    {
        $this->persistence = $persistence;
        $this->queue = $queue;
    }

    /** @noinspection PhpUnhandledExceptionInspection */
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

        $this->persistence->beginTransaction();
        $message = $this->persistence->insert($requestBody->message);
        try {
            $this->queue->publish(new QueueMessage($message->getId(), $message->getMessage()));
        } catch (Throwable $e) {
            $this->persistence->rollBack();
            throw $e;
        }
        $this->persistence->commit();

        return new Response(
            201,
            [
                'Content-Type' => 'application/json'
            ],
            stream_for(new ResponseWithMessage($message))
        );
    }
}