<?php

namespace Tavurn\Contracts\Routing;

interface Registrar
{
    /**
     * @param string|array<int, string> $methods
     * @param callable|string|string[] $handler
     */
    public function addRoute($methods, string $regex, $handler): void;

    /**
     * @param callable|string|string[] $handler
     */
    public function get(string $regex, $handler): void;

    /**
     * @param callable|string|string[] $handler
     */
    public function post(string $regex, $handler): void;

    /**
     * @param callable|string|string[] $handler
     */
    public function put(string $regex, $handler): void;

    /**
     * @param callable|string|string[] $handler
     */
    public function patch(string $regex, $handler): void;

    /**
     * @param callable|string|string[] $handler
     */
    public function options(string $regex, $handler): void;

    /**
     * @param callable|string|string[] $handler
     */
    public function any(string $regex, $handler): void;
}
