<?php

namespace Tavurn\Http;

use OpenSwoole\Core\Psr\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tavurn\Contracts\Container\Container;
use Tavurn\Contracts\Events\Dispatcher;
use Tavurn\Contracts\Http\Kernel as KernelContract;
use Tavurn\Support\Facades\Exception;
use Throwable;

class Kernel implements KernelContract
{
    protected Container $container;

    protected static array $bootstrappers = [
        \Tavurn\Foundation\Bootstrap\LoadConfiguration::class,
        \Tavurn\Foundation\Bootstrap\RegisterConfiguredProviders::class,
    ];

    public function __construct()
    {
        $this->container = app(Container::class);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->container->contextual(ServerRequestInterface::class, $request);

        if (! app()->hasBeenBootstrapped()) {
            app()->bootstrapWith(static::$bootstrappers);
        }

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
