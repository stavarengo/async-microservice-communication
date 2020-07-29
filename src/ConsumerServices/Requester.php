<?php

declare(strict_types=1);

namespace AMC\ConsumerServices;

use AMC\ConsumerServices\BrokerClient\ClientInterface;
use AMC\ConsumerServices\BrokerClient\Exception\BrokerClientException;
use AMC\ConsumerServices\Exception\NoResponse;
use GuzzleHttp\Exception\ServerException;
use Psr\Log\LoggerInterface;

class Requester
{
    private ClientInterface $api;
    private LoggerInterface $logger;

    public function __construct(ClientInterface $api, LoggerInterface $logger)
    {
        $this->api = $api;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        try {
            $entityId = $this->api->post('Hi, ');
        } catch (ServerException  $e) {
            die($e->getResponse()->getBody()->getContents());
        }

        $this->logger->debug(sprintf('New message registered with ID "%s".', $entityId), ['caller' => self::class]);

        $maximumTimeToWaitInMicroseconds = 1000000;
        $intervalInMicrosecondsBetweenEachTry = 50000;
        $timeWaitedInMicroseconds = 0;
        do {
            usleep($intervalInMicrosecondsBetweenEachTry);
            $timeWaitedInMicroseconds += $intervalInMicrosecondsBetweenEachTry;

            $message = null;
            try {
                $message = $this->api->get($entityId);
                if (!preg_match('/^Hi, .+?\. Bye!$/', $message)) {
                    $message = null;
                }
            } catch (BrokerClientException $e) {
            }
        } while ($timeWaitedInMicroseconds < $maximumTimeToWaitInMicroseconds && !$message);

        if (!$message) {
            throw NoResponse::create();
        }

        $this->logger->debug(
            sprintf(
                'Answer for message "%s" is ready. Took "%s" microseconds.',
                $entityId,
                $timeWaitedInMicroseconds
            ),
            ['caller' => self::class]
        );

        echo "\n$message\n\n";
    }

}