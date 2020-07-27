<?php

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

    if ($type == 'client') {
        $fibonacci_rpc = new \AMC\App\Client();
        $response = $fibonacci_rpc->call('Hi, ');
        echo ' [.] Got ', $response, "\n";
    } elseif ($type == 'broker') {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            (new \AMC\Broker\PostHandler())->__invoke();
        } else {
            (new \AMC\Broker\GetHandler())->__invoke();
        }
    }
})(
    $argv ?? [1 => 'broker']
);
