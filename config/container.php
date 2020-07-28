<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */

declare(strict_types=1);

// Using a self-called anonymous function to create its own scope and keep the variables away from the global scope.
return (function () {
    /** @var array $config */
    $config = include __DIR__ . '/config.php';

    $builder = new \DI\ContainerBuilder();
    $builder->addDefinitions($config['container_definitions'] ?? []);

    $container = $builder->build();

    // Inject config
    $container->set('config', $config);

    return $container;
})();
