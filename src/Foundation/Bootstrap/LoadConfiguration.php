<?php

namespace Tavurn\Foundation\Bootstrap;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Symfony\Component\Finder\Finder;
use Tavurn\Foundation\Application;

class LoadConfiguration
{
    public function bootstrap(Application $app): void
    {
        $config = new Repository;

        $this->loadConfiguration($app, $config);

        $app->instance(RepositoryContract::class, $config);
    }

    public function loadConfiguration(Application $app, RepositoryContract $repository): void
    {
        $files = $this->getConfigFiles($app);

        foreach ($files as $key => $path) {
            $repository->set($key, require $path);
        }
    }

    /**
     * @return array<string, string>
     */
    public function getConfigFiles(Application $app): iterable
    {
        $configPath = $app->basePath('config');

        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            yield $file->getBasename('.php') => $file->getRealPath();
        }
    }
}
