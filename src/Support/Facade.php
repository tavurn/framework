<?php

namespace Tavurn\Support;

abstract class Facade
{
    protected static mixed $instance;

    abstract protected static function getContainerAccessor(): string;

    public static function __callStatic(string $name, array $arguments)
    {
        if (! isset(static::$instance)) {
            static::$instance = app(static::getContainerAccessor());
        }

        static::$instance->{$name}(...$arguments);
    }
}
