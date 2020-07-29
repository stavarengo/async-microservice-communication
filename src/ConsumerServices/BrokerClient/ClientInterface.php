<?php

declare(strict_types=1);


namespace AMC\ConsumerServices\BrokerClient;


use AMC\ConsumerServices\BrokerClient\Exception\BrokerClientException;

interface ClientInterface
{
    /**
     * Creates a new records and return the ID.
     *
     * @param string $message
     * @return string
     * @throws BrokerClientException
     */
    public function post(string $message): string;

    /**
     * Updates a record and returns its ID.
     *
     * @param string $id
     * @param string $message
     * @throws BrokerClientException
     */
    public function put(string $id, string $message): void;

    /**
     * Returns a message of one record or null if the record $id does not exists.
     * @param string $id
     * @return string|null
     * @throws BrokerClientException
     */
    public function get(string $id): ?string;
}