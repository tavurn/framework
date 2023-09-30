<?php

namespace Tavurn\Support\Facades;

use Psr\Http\Message\ServerRequestInterface;
use Tavurn\Support\Facade;

class Request extends Facade
{
    protected static function getContainerAccessor(): string
    {
        return ServerRequestInterface::class;
    }
}
