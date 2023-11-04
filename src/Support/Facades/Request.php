<?php

namespace Tavurn\Support\Facades;

use Tavurn\Support\Facade;

class Request extends Facade
{
    protected static function getContainerAccessor(): string
    {
        return \Psr\Http\Message\ServerRequestInterface::class;
    }
}
