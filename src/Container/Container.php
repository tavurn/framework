<?php

namespace Tavurn\Container;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use Tavurn\Async\Context;
use Tavurn\Contracts\Container\Container as ContainerContract;
use Tavurn\Contracts\Container\Contextual;

class Container implements ContainerContract
{
    protected array $bindings = [];

    protected array $instances = [];

    protected array $resolved = [];

    protected array $contextual = [];

    /**
     * @param string $abstract
     * @param callable|class-string $concrete
     * @param bool $singleton
     * @return void
     */
    public function bind(
        string $abstract,
        $concrete,
        bool $singleton = false,
    ): void {
        if (is_string($concrete)) {
            $concrete = $this->getClosureFor($concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'singleton');
    }

    /**
     * @param string $abstract
     * @param callable|class-string $concrete
     * @return void
     */
    public function singleton(string $abstract, $concrete): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function contextual(string $abstract, mixed $instance): void
    {
        Context::set($abstract, $instance);

        $this->contextual[$abstract] = true;
    }

    protected function getClosureFor(string $abstract): Closure
    {
        return function (ContainerContract $app) use ($abstract) {
            return $app->make($abstract);
        };
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

        return $this->build($abstract);
    }

    /**
     * @throws ContainerException
     * @throws EntryNotFoundException
     * @throws ReflectionException
     */
    public function build(string $class): mixed
    {
        $constructor = (new ReflectionClass($class))->getConstructor();

        if (! $constructor) {
            return new $class;
        }

        $parameters = $this->getParametersFor($constructor);

        return new $class(...$parameters);
    }

    /**
     * @throws EntryNotFoundException
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function get(string $id): mixed
    {
        if ($this->isContextual($id)) {
            return Context::get($id);
        }

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
                'abstract' => $parameter->getType()?->getName(),
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
     *
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
            } elseif (is_null($abstract) || ! $this->has($abstract) && ! $this->isContextual($abstract)) {
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

    public function isContextual(string $abstract): bool
    {
        if (! isset($this->contextual[$abstract])) {
            $this->contextual[$abstract] = $contextual = in_array(Contextual::class, class_implements($abstract));

            return $contextual;
        }

        return $this->contextual[$abstract];
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }
}
