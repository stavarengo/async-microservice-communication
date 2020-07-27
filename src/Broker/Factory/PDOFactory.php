<?php


namespace AMC\Broker\Factory;


use AMC\Broker\ConfigProvider;
use AMC\Broker\Factory\Exception\MissingConfigEntry;
use Psr\Container\ContainerInterface;

class PDOFactory
{
    public const DRIVE_NAME = 'DRIVE_NAME';
    public const HOSTNAME = 'HOSTNAME';
    public const PORT = 'PORT';
    public const USERNAME = 'USERNAME';
    public const PASSWORD = 'PASSWORD';

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $pdoConfig = $config[ConfigProvider::class][self::class] ?? [];

        $requiredConfiguration = [
            self::DRIVE_NAME,
            self::HOSTNAME,
            self::USERNAME,
            self::PASSWORD,
        ];

        foreach ($requiredConfiguration as $configName) {
            if (!array_key_exists($configName, $pdoConfig)) {
                throw MissingConfigEntry::create($configName, self::class);
            }
        }

        return new \PDO(
            sprintf(
                '%s:host=%s',
                $pdoConfig[self::DRIVE_NAME] ?? '',
                $pdoConfig[self::HOSTNAME] ?? ''
            ),
            $pdoConfig[self::USERNAME] ?? '',
            $pdoConfig[self::PASSWORD] ?? null,
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]
        );
    }
}