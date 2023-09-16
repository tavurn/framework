<?php

namespace Tavurn\Contracts\Container;

use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface
{
    public function bind(string $abstract, callable $concrete, bool $singleton = false): void;

    public function singleton(string $abstract, callable $concrete): void;

    /**
     * @template T
     * @param class-string<T> $abstract
     * @return T
     */
    public function make(string $abstract): mixed;

    /**
     * @template T
     * @param callable(): T $block
     * @return T
     */
    public function call(callable $block): mixed;

    /**
     * @template T
     * @param class-string<T> $id
     * @return T
     */
    public function get(string $id);
}