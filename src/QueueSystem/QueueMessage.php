<?php

declare(strict_types=1);


namespace AMC\QueueSystem;


class QueueMessage implements QueueMessageInterface
{
    private string $id;
    private string $body;

    public function __construct(string $id, string $body)
    {
        $this->setId($id);
        $this->setBody($body);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }
}