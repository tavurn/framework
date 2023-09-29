<?php

namespace Tavurn\Providers;

use Tavurn\Facades\Config;
use Tavurn\Support\ServiceProvider;

class TavurnServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $providers = Config::get('app.providers', []);

        app()->register($providers);
    }
}