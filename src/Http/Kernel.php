<?php

namespace Tavurn\Http;

use OpenSwoole\Core\Psr\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tavurn\Async\Context;
use Tavurn\Contracts\Container\Container;
use Tavurn\Contracts\Http\Kernel as KernelContract;

class Kernel implements KernelContract
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    function test(ServerRequestInterface $request)
    {
        echo $request->getUri();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->container->contextual(ServerRequestInterface::class, $request);

        $this->container->call($this->test(...));

        return new Response('Hello');
    }
}
