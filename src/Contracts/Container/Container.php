<?php

namespace Tavurn\Contracts\Container;

use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface
{
    /**
     * @param callable|class-string $concrete
     */
    public function bind(string $abstract, $concrete, bool $singleton = false): void;

    /**
     * @param callable|class-string $concrete
     */
    public function singleton(string $abstract, $concrete): void;

    public function contextual(string $abstract, mixed $instance): void;

    public function alias(string $abstract, string $alias): void;

    /**
     * @template T
     *
     * @param class-string<T> $abstract
     * @return T
     */
    public function make(string $abstract): mixed;

    /**
     * @template T
     *
     * @param class-string<T> $class
     * @return T
     */
    public function build(string $class): mixed;

    /**
     * @template T
     *
     * @param array<int, string>|callable(mixed ...): T $block
     * @return T
     */
    public function call($block, mixed ...$parameters): mixed;

    /**
     * @template T
     *
     * @param class-string<T> $id
     * @return T
     */
    public function get(string $id);

    public function isAlias(string $alias): bool;
}
