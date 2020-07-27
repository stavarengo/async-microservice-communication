<?php


namespace AMC\Broker\Entity;


class Message
{
    private ?string $id;
    private ?string $message;

    public function __construct(?string $id = null, ?string $message = null)
    {
        $this->id = $id;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

}