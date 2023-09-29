<?php

namespace Tavurn\Http;

use OpenSwoole\Core\Psr\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tavurn\Contracts\Container\Container;
use Tavurn\Contracts\Events\Dispatcher;
use Tavurn\Contracts\Exceptions\Handler;
use Tavurn\Contracts\Http\Kernel as KernelContract;
use Throwable;

class Kernel implements KernelContract
{
    protected Container $container;

    protected Handler $handler;

    public function __construct(Container $container, Handler $handler)
    {
        $this->container = $container;

        $this->handler = $handler;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->container->contextual(ServerRequestInterface::class, $request);

        try {
            $response = new Response('Hello');
        } catch (Throwable $e) {
            if ($this->handler->shouldReport($e)) {
                $this->handler->report($e);
            }

            $response = $this->handler->render($request, $e);
        }

        $this->container->get(Dispatcher::class)->dispatch(
            new RequestHandled($request, $response),
        );

        return $response;
    }
}
