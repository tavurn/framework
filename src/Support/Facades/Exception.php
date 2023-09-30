<?php

namespace Tavurn\Support\Facades;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tavurn\Support\Facade;

/**
 * @method static \void report(\Throwable $error)
 * @method static \bool shouldReport(\Throwable $error)
 * @method static ResponseInterface render(ServerRequestInterface $request, \Throwable $error)
 */
class Exception extends Facade
{
    protected static function getContainerAccessor(): string
    {
        return \Tavurn\Contracts\Exceptions\Handler::class;
    }
}