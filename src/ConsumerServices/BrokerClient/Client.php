<?php

declare(strict_types=1);


namespace AMC\ConsumerServices\BrokerClient;


use AMC\ConsumerServices\BrokerClient\Exception\BrokerClientException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use stdClass;

class Client implements ClientInterface
{
    public const API_ADDRESS = 'API_ADDRESS';

    private \GuzzleHttp\Client $client;

    /**
     * Client constructor.
     * @param \GuzzleHttp\Client $client
     */
    public function __construct(\GuzzleHttp\Client $client)
    {
        $this->client = $client;
    }


    public function post(string $message): string
    {
        return $this->putOrPost(null, $message);
    }

    public function put(string $id, string $message): void
    {
        $this->putOrPost(null, $message);
    }

    public function get(string $id): ?string
    {
        $response = $this->client->get(`/$id`);

        $responseEntity = $this->validateResponse($response, 200, 'message');

        return (string)$responseEntity->message;
    }

    private function putOrPost(?string $id, string $message): string
    {
        $requestOptions = [
            RequestOptions::JSON => [
                'message' => $message,
            ]
        ];

        $expectedStatusCode = 201;
        $method = 'POST';
        $uri = '/';
        if ($id) {
            $expectedStatusCode = 200;
            $method = 'POST';
            $uri = `/$id`;
        }

        $response = $this->client->request($method, $uri, $requestOptions);

        $responseEntity = $this->validateResponse($response, $expectedStatusCode, 'id');

        return (string)$responseEntity->id;
    }

    private function validateResponse(
        ResponseInterface $response,
        int $expectedStatusCode,
        string $answerMustHaveThisAttribute
    ): stdClass {
        if ($response->getStatusCode() != $expectedStatusCode) {
            throw BrokerClientException::unexpectedStatusCode($expectedStatusCode, $response->getStatusCode());
        }

        $bodyAsString = $response->getBody()->getContents();
        $responseEntity = json_decode($bodyAsString);
        if (!$responseEntity) {
            throw BrokerClientException::invalidJsonString($bodyAsString);
        }

        if (!isset($responseEntity->$answerMustHaveThisAttribute) || !$responseEntity->$answerMustHaveThisAttribute) {
            throw BrokerClientException::invalidEntityReceived($answerMustHaveThisAttribute);
        }

        return $responseEntity;
    }
}