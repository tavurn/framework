<?php

namespace Tavurn\Support\Providers;

use FastRoute\DataGenerator;
use FastRoute\RouteParser;
use Illuminate\Support\Traits\ForwardsCalls;
use Tavurn\Contracts\Routing\Router as RouterContract;
use Tavurn\Routing\Router;
use Tavurn\Support\Facades\Config;
use Tavurn\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    use ForwardsCalls;

    private RouterContract $router;

    public function register(): void
    {
        $this->app->singleton(
            RouteParser::class,
            Config::get('app.routing.parser', RouteParser\Std::class),
        );

        $this->app->singleton(
            DataGenerator::class,
            Config::get('app.routing.generator', DataGenerator\GroupCountBased::class),
        );

        $this->app->singleton(
            RouterContract::class,
            Router::class,
        );
    }

    public function __call(string $name, array $arguments)
    {
        if (! isset($this->router)) {
            $this->router = $this->app->get(RouterContract::class);
        }

        $this->forwardCallTo(
            $this->router, $name, $arguments
        );
    }
}
