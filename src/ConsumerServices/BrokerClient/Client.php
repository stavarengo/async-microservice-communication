<?php

declare(strict_types=1);


namespace AMC\ConsumerServices\BrokerClient;


use AMC\ConsumerServices\BrokerClient\Exception\BrokerClientException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use Throwable;

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
        $this->putOrPost($id, $message);
    }

    public function get(string $id): ?string
    {
        try {
            $response = $this->client->get(
                '/',
                [
                    RequestOptions::QUERY => [
                        'id' => $id,
                    ]
                ]
            );
        } catch (Throwable $e) {
            throw BrokerClientException::create($e);
        }

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
        if ($id) {
            $expectedStatusCode = 200;
            $method = 'PUT';
            $requestOptions[RequestOptions::QUERY] = [
                'id' => $id,
            ];
        }

        try {
            $response = $this->client->request($method, '/', $requestOptions);
        } catch (Throwable $e) {
            throw BrokerClientException::create($e);
        }

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