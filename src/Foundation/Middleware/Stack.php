<?php

namespace Tavurn\Foundation\Middleware;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Tavurn\Contracts\Http\Middleware;
use Tavurn\Contracts\Http\Request;

final class Stack
{
    /**
     * @var array<int, Middleware>
     */
    protected array $middleware = [];

    protected Closure $destination;

    /**
     * @param array<int, Middleware> $middleware
     */
    public function __construct(array $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * The callback that will be run at the end of the stack.
     *
     * @param Closure(Request): ResponseInterface $destination
     */
    public function handler(Closure $destination): Stack
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Execute the next "step" (middleware) in the stack.
     */
    public function process(Request $request): ResponseInterface
    {
        return $this->next($request);
    }

    /**
     * Execute the next "step" (middleware) in the stack.
     * This method is an alias of Stack::process()
     */
    public function __invoke(Request $request): ResponseInterface
    {
        return $this->next($request);
    }

    protected function next(Request $request): ResponseInterface
    {
        $middleware = array_shift($this->middleware) ?? false;

        if ($middleware) {
            return $middleware->process($request, $this);
        }

        return ($this->destination)($request);
    }
}