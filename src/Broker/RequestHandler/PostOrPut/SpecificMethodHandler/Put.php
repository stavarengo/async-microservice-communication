<?php

declare(strict_types=1);


namespace AMC\Broker\RequestHandler\PostOrPut\SpecificMethodHandler;


use AMC\Broker\Persistence\PersistenceInterface;
use AMC\Broker\ResponseBody\Error;
use AMC\Broker\ResponseBody\ResponseWithMessage;
use GuzzleHttp\Psr7\Response;

use function GuzzleHttp\Psr7\stream_for;

class Put implements SpecificMethodHandlerInterface
{
    private PersistenceInterface $persistence;

    public function __construct(PersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    public function handle(string $message, ?string $id): HandlerResult
    {
        $messageEntity = $this->persistence->get($id);

        if (!$messageEntity) {
            return new HandlerResult(
                null,
                new Response(
                    404, ['Content-Type' => 'application/json'],
                    stream_for(new Error(sprintf('Message "%s" not found.', $id)))
                )
            );
        }

        $messageEntity->setMessage($message);
        $messageEntity = $this->persistence->update($messageEntity);

        return new HandlerResult(
            $messageEntity,
            new Response(
                200,
                [
                    'Content-Type' => 'application/json'
                ],
                stream_for(new ResponseWithMessage($messageEntity))
            )
        );
    }
}