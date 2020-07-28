<?php

declare(strict_types=1);


namespace AMC\Broker\ResponseBody;


use AMC\Broker\Entity\Message;

class ResponseWithMessage implements ResponseBodyInterface
{
    private Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function __toString(): string
    {
        return json_encode(
            [
                'id' => $this->message->getId(),
                'message' => $this->message->getMessage(),
            ]
        );
    }
}