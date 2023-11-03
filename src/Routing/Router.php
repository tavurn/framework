<?php

namespace Tavurn\Routing;

use FastRoute\RouteCollector;
use Illuminate\Contracts\Config\Repository;
use OpenSwoole\Core\Psr\Response;
use Psr\Http\Message\ResponseInterface;
use Tavurn\Contracts\Foundation\Application;
use Tavurn\Contracts\Http\Request;
use Tavurn\Contracts\Http\Responsable;
use Tavurn\Contracts\Routing\MutableDispatcher;
use Tavurn\Contracts\Routing\Registrar;
use Tavurn\Contracts\Routing\Router as RouterContract;
use Tavurn\Routing\Dispatcher\GroupCountBasedDispatcher;

class Router implements Registrar, RouterContract
{
    protected Application $app;

    protected RouteCollector $collector;

    protected MutableDispatcher $dispatcher;

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->collector = $this->app->make(RouteCollector::class);

        $dispatcher = $this->app->get(Repository::class)->get(
            'app.routing.dispatcher', GroupCountBasedDispatcher::class,
        );

        $this->dispatcher = new $dispatcher($this->collector->getData());
    }

    public function dispatch(Request $request): ResponseInterface
    {
        $resolved = $this->dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath(),
        );

        return match ($resolved[0]) {
            $this->dispatcher::NOT_FOUND => throw new NotFoundException,
            $this->dispatcher::METHOD_NOT_ALLOWED => throw new MethodNotAllowedException,
            $this->dispatcher::FOUND => $this->callRouteHandler($resolved[1], $request, $resolved[2]),
        };
    }

    protected function callRouteHandler(
        callable $handler,
        Request $request,
        array $parameters = [],
    ): ResponseInterface {
        $request = $this->registerRequestAttributes($request, $parameters);

        $this->app->contextual(Request::class, $request);

        $response = $this->app->call($handler);

        return match (true) {
            $response instanceof Responsable => $response->respond($request),
            is_string($response) => new Response($response),
            $response instanceof ResponseInterface => $response,
            default => new Response(''),
        };
    }

    protected function registerRequestAttributes(Request $request, array $attributes): Request
    {
        foreach ($attributes as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        return $request;
    }

    public function addRoute($methods, string $regex, $handler): void
    {
        $regex = '/' . ltrim(rtrim($regex, '/') ?: '/', '/');

        if (is_string($handler)) {
            $handler = $this->app->build($handler);
        } elseif (is_array($handler)) {
            $handler[0] = $this->app->make($handler[0]);
        }

        $this->collector->addRoute($methods, $regex, $handler);

        $this->reloadDispatcherData();
    }

    public function get(string $regex, $handler): void
    {
        $this->addRoute(['GET', 'HEAD'], $regex, $handler);
    }

    public function post(string $regex, $handler): void
    {
        $this->addRoute(['POST'], $regex, $handler);
    }

    public function put(string $regex, $handler): void
    {
        $this->addRoute(['PUT'], $regex, $handler);
    }

    public function patch(string $regex, $handler): void
    {
        $this->addRoute(['PATCH'], $regex, $handler);
    }

    public function delete(string $regex, $handler): void
    {
        $this->addRoute(['DELETE'], $regex, $handler);
    }

    public function options(string $regex, $handler): void
    {
        $this->addRoute(['OPTIONS'], $regex, $handler);
    }

    public function any(string $regex, $handler): void
    {
        $this->addRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $regex, $handler);
    }

    protected function reloadDispatcherData(): void
    {
        $this->dispatcher->updateData(
            $this->collector->getData(),
        );
    }
}
