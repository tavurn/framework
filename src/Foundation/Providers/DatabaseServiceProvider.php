<?php

namespace Tavurn\Foundation\Providers;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Illuminate\Contracts\Config\Repository;
use Tavurn\Contracts\Console\Kernel;
use Tavurn\Contracts\Foundation\Application;
use Tavurn\Database\ConnectionManager;
use Tavurn\Database\Repository as ModelRepository;
use Tavurn\Support\ServiceProvider;
use Doctrine\ORM\Tools\Console\Command;

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

    public function booting(): void
    {
        $provider = new SingleManagerProvider(
            $this->app->get(EntityManager::class),
        );

        $this->app->get(Kernel::class)->addCommands([
            new Command\ClearCache\CollectionRegionCommand($provider),
            new Command\ClearCache\EntityRegionCommand($provider),
            new Command\ClearCache\MetadataCommand($provider),
            new Command\ClearCache\QueryCommand($provider),
            new Command\ClearCache\QueryRegionCommand($provider),
            new Command\ClearCache\ResultCommand($provider),
            new Command\SchemaTool\CreateCommand($provider),
            new Command\SchemaTool\UpdateCommand($provider),
            new Command\SchemaTool\DropCommand($provider),
            new Command\GenerateProxiesCommand($provider),
            new Command\RunDqlCommand($provider),
            new Command\ValidateSchemaCommand($provider),
            new Command\InfoCommand($provider),
            new Command\MappingDescribeCommand($provider),
        ]);
    }
}
