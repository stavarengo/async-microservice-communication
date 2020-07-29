<?php

declare(strict_types=1);


namespace AMC\Broker\Factory;


use AMC\Broker\ConfigProvider;
use AMC\Broker\Factory\Exception\MissingConfigEntry;
use PDO;
use PDOException;
use Psr\Container\ContainerInterface;

class PDOFactory
{
    public const DRIVE_NAME = 'DRIVE_NAME';
    public const HOSTNAME = 'HOSTNAME';
    public const PORT = 'PORT';
    public const USERNAME = 'USERNAME';
    public const PASSWORD = 'PASSWORD';
    public const HOW_MANY_CONNECTION_TRIES_BEFORE_FAIL = 'HOW_MANY_CONNECTION_TRIES_BEFORE_FAIL';

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

        $dsn = sprintf(
            '%s:host=%s',
            $pdoConfig[self::DRIVE_NAME] ?? '',
            $pdoConfig[self::HOSTNAME] ?? ''
        );
        $username = $pdoConfig[self::USERNAME] ?? '';
        $password = $pdoConfig[self::PASSWORD] ?? null;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $howManyTries = $pdoConfig[self::HOW_MANY_CONNECTION_TRIES_BEFORE_FAIL] ?? 0;

        return $this->getPDO($howManyTries, $dsn, $username, $password, $options);
    }

    /**
     * @param int $howManyTries
     * @param string $dsn
     * @param string $username
     * @param string|null $password
     * @param array $options
     * @return PDO
     */
    private function getPDO(int $howManyTries, string $dsn, string $username, ?string $password, array $options): PDO
    {
        try {
            $howManyTries--;

            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            if ($howManyTries > 0) {
                // @codeCoverageIgnoreStart
                sleep(1);

                return $this->getPDO($howManyTries, $dsn, $username, $password, $options);
                // @codeCoverageIgnoreEnd
            }

            throw $e;
        }
    }
}