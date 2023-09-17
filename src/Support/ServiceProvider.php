<?php

namespace Tavurn\Support;

use Tavurn\Contracts\Container\Container;

abstract class ServiceProvider
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function register(): void
    {
        //
    }

    public function booting(): void
    {
        //
    }
}
