<?php

namespace Tavurn\Foundation\Bootstrap;

use Illuminate\Contracts\Config\Repository;
use Tavurn\Contracts\Foundation\Application;

class RegisterConfiguredProviders
{
    public function bootstrap(Application $app): void
    {
        $config = $app->get(Repository::class);

        $providers = $config->get('app.providers', []);

        foreach ($providers as $provider) {
            $app->register($provider);
        }
    }
}
