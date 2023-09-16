<?php

namespace Tavurn\Container;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use Tavurn\Contracts\Container\Container as ContainerContract;

class Container implements ContainerContract
{
    protected array $bindings = [];

    protected array $instances = [];

    protected array $resolved = [];

    public function bind(string $abstract, callable $concrete, bool $singleton = false): void
    {
        $this->bindings[$abstract] = compact('concrete', 'singleton');
    }

    public function singleton(string $abstract, callable $concrete): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * @throws ReflectionException
     * @throws ContainerException
     * @throws EntryNotFoundException
     */
    public function make(string $abstract): mixed
    {
        if ($this->has($abstract)) {
            return $this->bindings[$abstract]['concrete']($this);
        }

        if (! class_exists($abstract)) {
            throw new ContainerException("can not instantiate [$abstract] because it is not bound nor a class");
        }

        $constructor = (new ReflectionClass($abstract))->getConstructor();

        if (! $constructor) {
            throw new ContainerException("can not instantiate [$abstract] because it doesn't have a constructor");
        }

        $parameters = $this->getParametersFor($constructor);

        return new $abstract(...$parameters);
    }

    /**
     * @throws EntryNotFoundException
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function get(string $id): mixed
    {
        if (! $this->has($id)) {
            throw new EntryNotFoundException("cannot find entry [$id]");
        }

        if (! $this->isSingleton($id)) {
            return $this->make($id);
        }

        if (! $this->hasSingleton($id)) {
            $this->instances[$id] = $this->make($id);
        }

        return $this->instances[$id];
    }

    /**
     * @template T
     * @param string[]|callable(mixed ...): T $block
     * @param mixed ...$parameters
     * @return T|mixed
     * @throws EntryNotFoundException
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function call($block, ...$parameters): mixed
    {
        $block = $block(...);

        $parameters = $this->getParametersFor($block, $parameters);

        return $block(...$parameters);
    }

    protected function resolve(ReflectionFunctionAbstract $function): void
    {
        $scope = $function->getClosureScopeClass()?->getName();

        $name = $function->getName();

        $parameters = array_map(
            fn ($parameter) => [
                'name' => $parameter->getName(),
                'abstract' => $parameter->getType()->getName(),
            ],
            $function->getParameters()
        );

        match (is_null($scope)) {
            true => $this->resolved[$name] = $parameters,
            false => $this->resolved[$scope][$name] = $parameters,
        };
    }

    /**
     * @param Closure|ReflectionFunctionAbstract $closure
     * @param array $merge
     * @return iterable
     * @throws EntryNotFoundException
     * @throws ReflectionException
     * @throws ContainerException
     */
    protected function getParametersFor($closure, array $merge = []): iterable
    {
        $function = is_callable($closure) ? new ReflectionFunction($closure) : $closure;

        $scope = $function->getClosureScopeClass()?->getName();

        $name = $function->getName();

        if (! $this->isResolved($scope, $name)) {
            $this->resolve($function);
        }

        $parameters = is_null($scope)
            ? $this->resolved[$name]
            : $this->resolved[$scope][$name];

        foreach ($parameters as $parameter) {
            $abstract = $parameter['abstract'];
            $name = $parameter['name'];

            if (in_array($name, array_keys($merge))) {
                $built = $merge[$name];
            } elseif (! $this->has($abstract)) {
                $built = array_shift($merge);
            } else {
                $built = $this->get($abstract);
            }

            yield $name => $built;
        }
    }

    protected function isResolved(?string $scope, string $name): bool
    {
        return is_null($scope)
            ? isset($this->resolved[$name])
            : isset($this->resolved[$scope][$name]);
    }

    public function isSingleton(string $abstract): bool
    {
        return $this->has($abstract)
            && $this->bindings[$abstract]['singleton'];
    }

    public function hasSingleton(string $abstract): bool
    {
        return isset($this->instances[$abstract]);
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }
}