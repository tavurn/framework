<?php

namespace Tavurn\Support;

use Tavurn\Foundation\Application;

abstract class ServiceProvider
{
    protected Application $app;

    public function __construct(Application $application)
    {
        $this->app = $application;
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
