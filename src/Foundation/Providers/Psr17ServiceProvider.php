<?php

namespace Tavurn\Foundation\Providers;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use Tavurn\Support\ServiceProvider;

class Psr17ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            Psr17Factory::class,
            Psr17Factory::class,
        );

        $this->app->singleton(ServerRequestCreatorInterface::class,
            fn ($app) => new ServerRequestCreator(
                $factory = $app->get(Psr17Factory::class),
                $factory,
                $factory,
                $factory,
            ),
        );
    }
}
