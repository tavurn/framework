<?php

namespace Tavurn\Contracts\Foundation;

use Psr\Http\Server\RequestHandlerInterface;
use Tavurn\Contracts\Container\Container as ContainerContract;
use OpenSwoole\Server;

interface Application extends RequestHandlerInterface, ContainerContract
{
    public static function getInstance(): self;

    public function isBooted(): bool;

    public function hasBeenBootstrapped(): bool;

    public function basePath(string $path = ''): string;

    public function getServer(): Server;

    public function start(): never;
}