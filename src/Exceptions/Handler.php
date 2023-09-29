<?php

namespace Tavurn\Exceptions;

use OpenSwoole\Core\Psr\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tavurn\Contracts\Container\Container;
use Tavurn\Contracts\Exceptions\Handler as HandlerContract;
use Throwable;

class Handler implements HandlerContract
{
    protected Container $container;

    /**
     * @var array<class-string, callable[]>
     */
    protected array $exceptionMapping = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function handle(string $exception, callable $handler): static
    {
        $this->exceptionMapping[$exception][] = $handler;

        return $this;
    }

    public function report(Throwable $error): void
    {
        $handlers = $this->exceptionMapping[$error::class] ?? [];

        foreach ($handlers as $handler) {
            $continue = $handler($error);

            if ($continue === false) {
                break;
            }
        }
    }

    public function shouldReport(Throwable $error): bool
    {
        if (! method_exists($error, 'report')) {
            return true;
        }

        return (bool) $error->report();
    }

    public function render(ServerRequestInterface $request, Throwable $error): ResponseInterface
    {
        if (! method_exists($error, 'render')) {
            return new Response('500 | Internal Server Error');
        }

        return $this->container->call(
            $error->render(...),
            ['request' => $request],
        );
    }
}
