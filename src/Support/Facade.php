<?php

namespace Tavurn\Support;

abstract class Facade
{
    abstract protected static function getContainerAccessor(): string;

    public static function __callStatic(string $name, array $arguments): mixed
    {
        return app(static::getContainerAccessor())->{$name}(...$arguments);
    }
}
