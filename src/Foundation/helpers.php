<?php

if (! function_exists('app')) {
    /**
     * Get an instance of the global application instance.
     * If an `$abstract` parameter is passed, you will be returned an instance
     * bound to that abstract.
     *
     * @template T
     *
     * @param class-string<T>|null $abstract
     * @return \Tavurn\Foundation\Application | T
     */
    function app(string $abstract = null): mixed
    {
        if (! is_null($abstract)) {
            return \Tavurn\Foundation\Application::getInstance()->get($abstract);
        }

        return \Tavurn\Foundation\Application::getInstance();
    }
}

if (! function_exists('event')) {
    /**
     * Dispatch an event to the appropriate event listeners.
     */
    function event(object $event): void
    {
        app(\Tavurn\Contracts\Events\Dispatcher::class)->dispatch($event);
    }
}

if (! function_exists('config')) {
    /**
     * Get a value from the config with the specified key.
     *
     * @template T
     *
     * @param string|array<int, string> $key
     * @param T $default
     * @return mixed | T
     */
    function config($key, $default = null): mixed
    {
        /**
         * @var \Illuminate\Contracts\Config\Repository $repository
         */
        $repository = app(\Illuminate\Contracts\Config\Repository::class);

        return $repository->get($key, $default);
    }
}

if (! function_exists('async')) {
    /**
     * Wraps the given function block in a lazy future.
     *
     * @template T
     *
     * @param callable(): T $block
     * @return \Tavurn\Foundation\Async\Future<T>
     *
     * @see Future::$await
     */
    function async(callable $block): Tavurn\Foundation\Async\Future
    {
        return new \Tavurn\Foundation\Async\Future($block);
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
     */
    function base_path(string $path = ''): string
    {
        return app()->basePath($path);
    }
}

/**
 * OpenSwoole already provides a `go` function.
 * This function will basically never be created,
 * but serves as a stub for better IDE support.
 */
if (! function_exists('go')) {
    /**
     * Start a new coroutine with the passed in function,
     * this function will be provided the passed in arguments.
     *
     * @param callable(mixed ...): void $block
     * @param mixed ...$args
     * @return int
     */
    function go(callable $block, mixed ...$args): int { }
}