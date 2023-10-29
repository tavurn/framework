<?php

namespace Tavurn\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Tavurn\Support\Manager;

/**
 * @extends Manager<Connection>
 */
class ConnectionManager extends Manager
{
    protected Configuration $configuration;

    public function getDefaultDriver(): string
    {
        return $this->config->get('database.drivers.default');
    }

    public function createSqliteDriver(): Connection
    {
        $parameters = $this->config->get(
            'database.drivers.sqlite',
            [],
        );

        return DriverManager::getConnection([
            'path' => $parameters['database'],
            ...$parameters,
        ], $this->configuration);
    }

    public function createMysqlDriver(): Connection
    {
        $parameters = $this->config->get(
            'database.drivers.mysql',
            [],
        );

        return DriverManager::getConnection([
            'dbname' => $parameters['database'],
            ...$parameters,
        ], $this->configuration);
    }

    public function getConfig(): Configuration
    {
        return $this->configuration;
    }

    public function setConfig(Configuration $configuration): static
    {
        $this->configuration = $configuration;

        return $this;
    }
}
