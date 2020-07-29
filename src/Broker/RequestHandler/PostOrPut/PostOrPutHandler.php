<?php

declare(strict_types=1);


namespace AMC\Broker\RequestHandler\PostOrPut;


use AMC\Broker\Persistence\PersistenceInterface;
use AMC\Broker\RequestHandler\PostOrPut\SpecificMethodHandler\SpecificMethodHandlerInterface;
use AMC\Broker\RequestHandler\RequestHandlerInterface;
use AMC\Broker\ResponseBody\Error;
use AMC\QueueSystem\Facade\FacadeInterface;
use AMC\QueueSystem\Message\QueueMessage;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use function GuzzleHttp\Psr7\stream_for;

class PostOrPutHandler implements RequestHandlerInterface
{
    private FacadeInterface $queue;
    private SpecificMethodHandlerInterface $postHandle;
    private SpecificMethodHandlerInterface $putHandle;
    private PersistenceInterface $persistence;

    public function __construct(
        SpecificMethodHandlerInterface $postHandle,
        SpecificMethodHandlerInterface $putHandle,
        PersistenceInterface $persistence,
        FacadeInterface $queue
    ) {
        $this->postHandle = $postHandle;
        $this->putHandle = $putHandle;
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

        $requestHandlerMap = [
            'POST' => $this->postHandle,
            'PUT' => $this->putHandle,
        ];

        /** @var SpecificMethodHandlerInterface $handler */
        $handler = $requestHandlerMap[$request->getMethod()] ?? null;

        if (!$handler) {
            $allowedMethods = implode(', ', array_keys($requestHandlerMap));
            $headers = ['Allowed' => $allowedMethods];

            return new Response(
                405,
                $headers,
                stream_for(new Error(sprintf('Method not allowed. Use one of the following: %s.', $allowedMethods)))
            );
        }

        $id = $request->getQueryParams()['id'] ?? null;
        $handlerResult = $handler->handle($requestBody->message, $id);
        $messageEntity = $handlerResult->getMessage();

        try {
            $statusCode = $handlerResult->getResponse()->getStatusCode();
            if ($messageEntity && $statusCode >= 200 && $statusCode < 300) {
                $this->queue->publish(new QueueMessage($messageEntity->getId(), $messageEntity->getMessage()));
            }
        } catch (Throwable $e) {
            $this->persistence->rollBack();
            throw $e;
        }
        $this->persistence->commit();

        return $handlerResult->getResponse();
    }
}