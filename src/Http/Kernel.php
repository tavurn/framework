<?php

namespace Tavurn\Http;

use Psr\Http\Message\ResponseInterface;
use Tavurn\Contracts\Events\Dispatcher;
use Tavurn\Contracts\Exceptions\Handler;
use Tavurn\Contracts\Foundation\Application;
use Tavurn\Contracts\Http\Kernel as KernelContract;
use Tavurn\Contracts\Http\Middleware;
use Tavurn\Contracts\Http\Request as RequestContract;
use Tavurn\Contracts\Routing\Router;
use Tavurn\Foundation\Middleware\Stack;
use Throwable;

class Kernel implements KernelContract
{
    protected Application $app;

    protected Handler $handler;

    protected Dispatcher $dispatcher;

    protected Router $router;

    /**
     * @var array<int, class-string<Middleware>>
     */
    protected array $middleware = [];

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->handler = $this->app->get(Handler::class);

        $this->dispatcher = $this->app->get(Dispatcher::class);

        $this->router = $this->app->get(Router::class);

        $this->middleware = $this->buildMiddleware();
    }

    public function handle(RequestContract $request): ResponseInterface
    {
        try {
            $response = $this->dispatchToRouter($request);
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

    protected function dispatchToRouter(RequestContract $request): ResponseInterface
    {
        $stack = (new Stack($this->middleware))
            ->handler($this->router->dispatch(...));

        return $stack->process($request);
    }

    protected function buildMiddleware(): array
    {
        $built = [];

        foreach ($this->middleware as $middleware) {
            $built[] = $this->app->build($middleware);
        }

        return $built;
    }
}
