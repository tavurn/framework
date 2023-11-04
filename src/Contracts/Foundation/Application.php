<?php

namespace Tavurn\Contracts\Foundation;

use OpenSwoole\Server;
use Psr\Http\Server\RequestHandlerInterface;
use Tavurn\Contracts\Container\Container as ContainerContract;

interface Application extends ContainerContract, RequestHandlerInterface
{
    public static function getInstance(): self;

    public function isBooted(): bool;

    public function boot(): void;

    public function hasBeenBootstrapped(): bool;

    public function bootstrapWith(array $bootstrappers): void;

    public function basePath(string $path = ''): string;

    public function serve(Server $server): bool;
}
