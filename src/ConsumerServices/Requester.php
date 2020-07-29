<?php

declare(strict_types=1);

namespace AMC\ConsumerServices;

use AMC\ConsumerServices\BrokerClient\ClientInterface;
use AMC\ConsumerServices\BrokerClient\Exception\BrokerClientException;
use AMC\ConsumerServices\Exception\NoResponse;
use GuzzleHttp\Exception\ServerException;

class Requester
{
    private ClientInterface $api;

    public function __construct(ClientInterface $api)
    {
        $this->api = $api;
    }

    public function execute(): void
    {
        try {
            $entityId = $this->api->post('Hi, ');
        } catch (ServerException  $e) {
            die($e->getResponse()->getBody()->getContents());
        }

        echo sprintf("%s: New message registered with ID '%s'.\n", self::class, $entityId);

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

        echo sprintf(
            "%s: Answer for message '%s' is ready. Took '%s' microseconds.\n",
            self::class,
            $entityId,
            $timeWaitedInMicroseconds
        );

        echo "\n$message\n\n";
    }

}