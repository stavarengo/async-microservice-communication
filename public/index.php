<?php
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */

/** @noinspection PhpFullyQualifiedNameUsageInspection */

declare(strict_types=1);

chdir(dirname(__DIR__));

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $msg = 'Did you forget to run `composer install`?' . PHP_EOL . 'Unable to load the "./vendor/autoload.php".';
    throw new RuntimeException($msg);
}
require __DIR__ . '/../vendor/autoload.php';

/**
 * I'm using a self-called anonymous function to create its own scope and keep the the variables created here away from
 * the global scope.
 */
(function ($argv) {
    /** @var \Psr\Container\ContainerInterface $container */
    $container = include_once __DIR__ . '/../config/container.php';

    $type = $argv[1] ?? null;

    switch ($type) {
        case 'service-a';
            $service = $container->get(\AMC\ConsumerServices\ServiceA::class);
            $service->execute();
            exit;
        case 'service-b';
            $service = $container->get(\AMC\ConsumerServices\ServiceB::class);
            $service->execute();
            exit;
        case 'requester';
            $requester = $container->get(\AMC\ConsumerServices\Requester::class);
            echo $requester->execute();
            exit;
        default:
            try {
                /** @var \AMC\Broker\RequestHandler\RequestHandlerInterface $requestHandler */
                $requestHandler = null;

                if ($_SERVER['REQUEST_METHOD'] == 'POST'
                    || $_SERVER['REQUEST_METHOD'] == 'PUT'
                ) {
                    $requestHandler = $container->get(\AMC\Broker\RequestHandler\PostOrPutHandler::class);
                } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
                    $requestHandler = $container->get(\AMC\Broker\RequestHandler\GetHandler::class);
                }

                if ($requestHandler) {
                    $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
                    $response = $requestHandler->handleIt($request);
                } else {
                    $response = new \GuzzleHttp\Psr7\Response(
                        404,
                        ['Content-Type' => 'text/html; charset=UTF-8'],
                        \GuzzleHttp\Psr7\stream_for('Not found!')
                    );
                }
            } catch (\Throwable $e) {
                $response = new \GuzzleHttp\Psr7\Response(
                    500,
                    ['Content-Type' => 'text/html; charset=UTF-8'],
                    \GuzzleHttp\Psr7\stream_for(sprintf("Server error!\n\n%s", $e))
                );
            }

            http_response_code($response->getStatusCode());
            foreach ($response->getHeaders() as $headerName => $headerValue) {
                header(sprintf('%s: %s', $headerName, $response->getHeaderLine($headerName)));
            }
            echo $response->getBody()->getContents();
            exit;
    }
})(
    $argv ?? [1 => 'broker']
);
