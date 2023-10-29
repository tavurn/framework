<?php

namespace Tavurn\Support\Facades;

use Doctrine\ORM\Configuration;
use Tavurn\Database\ConnectionManager;
use Tavurn\Support\Facade;

/**
 * @method static string getDefaultDriver
 * @method static \Doctrine\DBAL\Connection driver(string|null $name = null)
 * @method static Configuration getConfig
 * @method static ConnectionManager setConfig(Configuration $configuration)
 */
class Connection extends Facade
{
    protected static function getContainerAccessor(): string
    {
        return ConnectionManager::class;
    }
}