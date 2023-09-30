<?php

namespace Tavurn\Container;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use Tavurn\Contracts\Container\Container as ContainerContract;
use Tavurn\Contracts\Container\Contextual;
use Tavurn\Foundation\Async\Context;

class Container implements ContainerContract
{
    protected array $bindings = [];

    protected array $instances = [];

    protected array $resolved = [];

    protected array $contextual = [];

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
        return function (ContainerContract $container) use ($abstract) {
            return $container->make($abstract);
        };
    }

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

    public function build(string $class): mixed
    {
        $constructor = (new ReflectionClass($class))->getConstructor();

        if (! $constructor) {
            return new $class;
        }

        $parameters = $this->getParametersFor($constructor);

        return new $class(...$parameters);
    }

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
            $this->instances[$id] = $this->bindings[$id]['concrete']($this);
        }

        return $this->instances[$id];
    }

    public function call($block, ...$parameters): mixed
    {
        $block = $block(...);

        $parameters = $this->getParametersFor($block, $parameters);

        return $block(...$parameters);
    }

    protected function resolve(ReflectionFunctionAbstract $function): void
    {
        $scope = $this->getDeclaringClass($function);

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
     */
    protected function getParametersFor($closure, array $merge = []): iterable
    {
        $function = $closure instanceof Closure ? new ReflectionFunction($closure) : $closure;

        $scope = $this->getDeclaringClass($function);

        $name = $function->getName();

        if (! $this->isResolved($scope, $name)) {
            $this->resolve($function);
        }

        $parameters = ! $scope
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

    protected function getDeclaringClass(ReflectionFunctionAbstract $function): ?string
    {
        return $function instanceof ReflectionMethod
            ? $function->getDeclaringClass()->getName()
            : $function->getClosureScopeClass()?->getName();
    }

    protected function isResolved(?string $scope, string $name): bool
    {
        return is_null($scope)
            ? isset($this->resolved[$name])
            : isset($this->resolved[$scope][$name]);
    }

    public function isSingleton(string $abstract): bool
    {
        return $this->bindings[$abstract]['singleton'];
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
