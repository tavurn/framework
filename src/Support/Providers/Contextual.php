<?php

namespace Tavurn\Support\Providers;

use Tavurn\Contracts\Foundation\Application;

interface Contextual
{
    public function handling(Application $app): void;

    public function contextual(): array;
}