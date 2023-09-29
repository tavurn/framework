<?php

namespace Tavurn\Http;

use OpenSwoole\Core\Psr\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tavurn\Contracts\Container\Container;
use Tavurn\Contracts\Events\Dispatcher;
use Tavurn\Contracts\Http\Kernel as KernelContract;
use Tavurn\Facades\Exception;
use Throwable;

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

        try {
            $response = new Response('Hello');
        } catch (Throwable $e) {
            if (Exception::shouldReport($e)) {
                report($e);
            }

            $response = Exception::render($request, $e);
        }

        $this->container->get(Dispatcher::class)->dispatch(
            new RequestHandled($request, $response),
        );

        return $response;
    }
}
