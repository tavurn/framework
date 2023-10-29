<?php

namespace Tavurn\Foundation\Providers;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Illuminate\Contracts\Config\Repository;
use Tavurn\Database\ConnectionManager;
use Tavurn\Database\Repository as ModelRepository;
use Tavurn\Contracts\Foundation\Application;
use Tavurn\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Configuration::class, function (Application $app) {
            $config = $app->get(Repository::class);

            $config = ORMSetup::createAttributeMetadataConfiguration(
                [$config->get('database.models')],
                $config->get('database.dev'),
            );

            $config->setDefaultRepositoryClassName(
                config('database.repository', ModelRepository::class)
            );

            return $config;
        });

        $this->app->singleton(ConnectionManager::class, function (Application $app) {
            $configuration = $app->get(Configuration::class);

            return (new ConnectionManager($app))->setConfig($configuration);
        });

        $this->app->singleton(EntityManager::class, function (Application $app) {
            return new EntityManager(
                $app->get(ConnectionManager::class)->driver(),
                $app->get(Configuration::class),
            );
        });
    }
}