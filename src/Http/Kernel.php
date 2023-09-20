<?php

namespace Tavurn\Http;

use OpenSwoole\Core\Psr\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tavurn\Contracts\Container\Container;
use Tavurn\Contracts\Http\Kernel as KernelContract;

class Kernel implements KernelContract
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->container->contextual(ServerRequestInterface::class, $request);

        return new Response('Hello');
    }
}
