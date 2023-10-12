<?php

namespace Tavurn\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tavurn\Contracts\Events\Dispatcher;
use Tavurn\Contracts\Exceptions\Handler;
use Tavurn\Contracts\Http\Kernel as KernelContract;
use Tavurn\Contracts\Http\Middleware;
use Tavurn\Contracts\Http\Request as RequestContract;
use Tavurn\Contracts\Routing\Router;
use Tavurn\Foundation\Application;
use Tavurn\Pipeline\Pipeline;
use Throwable;

class Kernel implements KernelContract
{
    protected Application $app;

    protected Handler $handler;

    protected Dispatcher $dispatcher;

    protected Router $router;

    protected Pipeline $pipeline;

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

        $this->pipeline = Pipeline::new()
            ->through($this->middleware);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $request = $this->gatherRequest($request);

        $this->app->contextual(RequestContract::class, $request);

        try {
            $response = $this->pipeline
                ->send($request)
                ->then($this->router->dispatch(...));
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

    protected function buildMiddleware(): array
    {
        $built = [];

        foreach ($this->middleware as $middleware) {
            $built[] = $this->app->build($middleware)->process(...);
        }

        return $built;
    }

    protected function gatherRequest(ServerRequestInterface $request): RequestContract
    {
        return new Request(
            $request->getUri(),
            $request->getMethod(),
            $request->getBody(),
            $request->getHeaders(),
            $request->getCookieParams(),
            $request->getQueryParams(),
            $request->getServerParams(),
            $request->getUploadedFiles(),
            $request->getParsedBody(),
            $request->getProtocolVersion(),
        );
    }
}
