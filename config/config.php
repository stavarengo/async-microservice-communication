<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */

declare(strict_types=1);

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

// Using a self-called anonymous function to create its own scope and keep the variables away from the global scope.
return (function () {
    $cacheDir = __DIR__ . '/../var/cache';
    $cacheFile = $cacheDir . '/config-cache.php';
    if (!file_exists($cacheDir)) {
        mkdir($cacheDir, 0777, true);
    }

    $aggregator = new ConfigAggregator(
        [
            new ArrayProvider(
                [
                    ConfigAggregator::ENABLE_CACHE => false,
                    ConfigAggregator::CACHE_FILEMODE => 0660,
                ]
            ),

            \AMC\Broker\ConfigProvider::class,
            \AMC\ConsumerServices\ConfigProvider::class,
            \AMC\QueueSystem\ConfigProvider::class,

            new PhpFileProvider(__DIR__ . '/config/autoload/{{,*.}global,{,*.}local}.php'),
        ],
        $cacheFile
    );

    return $aggregator->getMergedConfig();
})();
