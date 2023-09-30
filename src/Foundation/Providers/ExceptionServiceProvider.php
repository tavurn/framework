<?php

namespace Tavurn\Foundation\Providers;

use Tavurn\Contracts\Container\Container;
use Tavurn\Contracts\Exceptions\Handler as HandlerContract;
use Tavurn\Exceptions\Handler;
use Tavurn\Support\ServiceProvider;

class ExceptionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(HandlerContract::class, function (Container $container) {
            return new Handler($container);
        });
    }
}
