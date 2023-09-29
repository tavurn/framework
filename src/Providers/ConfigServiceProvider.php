<?php

namespace Tavurn\Providers;

use Illuminate\Config\Repository;
use Tavurn\Contracts\Container\Container;
use Tavurn\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $items = [];

        $files = scandir($path = base_path('config'));

        $configFiles = array_filter($files, fn ($name) => str_ends_with($name, '.php'));

        foreach ($configFiles as $file) {
            $base = str_replace('.php', '', $file);

            $items[$base] = require ! str_ends_with($file, '/')
                ? "{$path}/{$file}"
                : $path . $file;
        }

        $this->container->singleton(
            \Illuminate\Contracts\Config\Repository::class,
            fn (Container $container) => new Repository($items),
        );
    }
}
