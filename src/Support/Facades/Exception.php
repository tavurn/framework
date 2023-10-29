<?php

namespace Tavurn\Support\Facades;

use Psr\Http\Message\ResponseInterface;
use Tavurn\Contracts\Http\Request;
use Tavurn\Support\Facade;

/**
 * @method static void handle(string $exception, callable $handler)
 * @method static void report(\Throwable $error)
 * @method static bool shouldReport(\Throwable $error)
 * @method static ResponseInterface render(Request $request, \Throwable $error)
 */
class Exception extends Facade
{
    protected static function getContainerAccessor(): string
    {
        return \Tavurn\Contracts\Exceptions\Handler::class;
    }
}
