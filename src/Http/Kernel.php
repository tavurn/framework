<?php

namespace Tavurn\Http;

use OpenSwoole\Core\Psr\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tavurn\Contracts\Events\Dispatcher;
use Tavurn\Contracts\Http\Kernel as KernelContract;
use Tavurn\Foundation\Application;
use Tavurn\Support\Facades\Exception;
use Throwable;

class Kernel implements KernelContract
{
    protected Application $app;

    protected static array $bootstrappers = [
        \Tavurn\Foundation\Bootstrap\LoadConfiguration::class,
        \Tavurn\Foundation\Bootstrap\RegisterConfiguredProviders::class,
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->app->contextual(ServerRequestInterface::class, $request);

        try {
            $response = new Response('Hello');
        } catch (Throwable $e) {
            if (Exception::shouldReport($e)) {
                report($e);
            }

            $response = Exception::render($request, $e);
        }

        $this->app->get(Dispatcher::class)->dispatch(
            new RequestHandled($request, $response),
        );

        return $response;
    }

    public function bootstrap(): void
    {
        if (! $this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith(static::$bootstrappers);
        }
    }
}
