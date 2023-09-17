<?php

namespace Tavurn\Support;

use Tavurn\Contracts\Container\Container;

abstract class ServiceProvider
{
    protected readonly Container $container;

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
