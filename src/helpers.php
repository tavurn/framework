<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */
if (! function_exists('app')) {
    /**
     * Get an instance of the global application instance.
     * If an `$abstract` parameter is passed, you will be returned an instance
     * bound to that abstract.
     *
     * @param class-string<T>|null $abstract
     * @return \Tavurn\Application|T
     *
     * @template T
     */
    function app(string $abstract = null): mixed
    {
        if (! is_null($abstract)) {
            return \Tavurn\Application::instance()->get($abstract);
        }

        return \Tavurn\Application::instance();
    }
}

if (! function_exists('async')) {
    /**
     * Wraps the given function block in a lazy future.
     *
     * @param callable(): T $block
     * @return \Tavurn\Async\Future<T>
     *
     * @see Future::$await
     *
     * @template T
     */
    function async(callable $block): Tavurn\Async\Future
    {
        return new \Tavurn\Async\Future($block);
    }
}

if (! function_exists('report')) {
    /**
     * Passes a throwable into the bound Handler.
     */
    function report(Throwable $error): void
    {
        app(\Tavurn\Contracts\Exceptions\Handler::class)->report($error);
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the project's base path.
     *
     * @param string $path
     *
     * @return string
     */
    function base_path(string $path = ''): string
    {
        return app()->basePath($path);
    }
}
