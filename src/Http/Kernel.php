<?php

namespace Tavurn\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tavurn\Contracts\Events\Dispatcher;
use Tavurn\Contracts\Exceptions\Handler;
use Tavurn\Contracts\Http\Kernel as KernelContract;
use Tavurn\Contracts\Routing\Router;
use Tavurn\Foundation\Application;
use Throwable;

class Kernel implements KernelContract
{
    protected Application $app;

    protected Handler $handler;

    protected Dispatcher $dispatcher;

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->handler = $this->app->get(Handler::class);

        $this->dispatcher = $this->app->get(Dispatcher::class);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->app->contextual(ServerRequestInterface::class, $request);

        try {
            $response = $this->sendRequestThroughRouter($request);
        } catch (Throwable $e) {
            if ($this->handler->shouldReport($e)) {
                report($e);
            }

            $response = $this->handler->render($request, $e);
        }

        $this->dispatcher->dispatch(
            new RequestHandled($request, $response),
        );

        return $response;
    }

    protected function sendRequestThroughRouter(ServerRequestInterface $request): ResponseInterface
    {
        $router = $this->app->get(Router::class);

        return $router->dispatch($request);
    }
}
