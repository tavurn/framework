<?php

namespace Tavurn\Support\Facades;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tavurn\Support\Facade;

/**
 * @method static void get(string $uri, callable $handler)
 * @method static void post(string $uri, callable $handler)
 * @method static void put(string $uri, callable $handler)
 * @method static void patch(string $uri, callable $handler)
 * @method static void delete(string $uri, callable $handler)
 * @method static void options(string $uri, callable $handler)
 * @method static void addRoute(string|string[] $methods, string $uri, callable $handler)
 * @method static ResponseInterface dispatch(RequestInterface $request)
 */
class Route extends Facade
{
    protected static function getContainerAccessor(): string
    {
        return \Tavurn\Contracts\Routing\Router::class;
    }
}
