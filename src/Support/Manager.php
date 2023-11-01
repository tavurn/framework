<?php

namespace Tavurn\Support;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tavurn\Contracts\Container\Container;

/**
 * This class is a "modified" version of the illuminate Manager.
 *
 * @template T
 *
 * @link https://github.com/laravel/framework/blob/master/src/Illuminate/Support/Manager.php
 */
abstract class Manager
{
    protected Container $container;

    protected Repository $config;

    protected array $customCreators = [];

    protected array $drivers = [];

    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->config = $container->get(Repository::class);
    }

    abstract public function getDefaultDriver(): string;

    /**
     * @return T|mixed
     */
    public function driver(string $driver = null)
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if (is_null($driver)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to resolve NULL driver for [%s]', static::class
            ));
        }

        if (! isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }

        return $this->drivers[$driver];
    }

    protected function createDriver(string $driver)
    {
        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver);
        }

        $method = 'create' . Str::studly($driver) . 'Driver';

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        throw new InvalidArgumentException("Driver [{$driver}] not supported.");
    }

    public function callCustomCreator(string $driver)
    {
        return $this->customCreators[$driver]($this->container);
    }

    public function extend(string $driver, \Closure $callback): static
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    public function getDrivers(): array
    {
        return $this->drivers;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function setContainer(Container $container): static
    {
        $this->container = $container;

        return $this;
    }

    public function forgetDrivers(): static
    {
        $this->drivers = [];

        return $this;
    }

    public function __call(string $method, array $parameters)
    {
        return $this->driver()->{$method}(...$parameters);
    }
}
