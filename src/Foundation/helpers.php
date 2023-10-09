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
