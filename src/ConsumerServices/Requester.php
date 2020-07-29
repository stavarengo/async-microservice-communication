<?php

declare(strict_types=1);

namespace AMC\ConsumerServices;

use AMC\ConsumerServices\BrokerClient\ClientInterface;
use AMC\ConsumerServices\BrokerClient\Exception\BrokerClientException;
use AMC\ConsumerServices\Exception\NoResponse;

class Requester
{
    private ClientInterface $api;

    public function __construct(ClientInterface $api)
    {
        $this->api = $api;
    }

    public function execute(): void
    {
        $entityId = $this->api->post('Hi, ');

        echo sprintf("%s: New message registered with ID '%s'.\n", self::class, $entityId);

        $maximumTimeToWaitInMicroseconds = 1000000 * 60 * 5;
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

        echo "$message\n";
    }

}