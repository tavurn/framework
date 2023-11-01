<?php

namespace Tavurn\Container;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Tavurn\Async\Context;
use Tavurn\Contracts\Container\Container as ContainerContract;

class Container implements ContainerContract
{
    protected array $bindings = [];

    protected array $instances = [];

    protected array $resolved = [];

    protected array $contextual = [];

    protected array $aliases = [];

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

    public function instance(string $abstract, mixed $instance): void
    {
        $this->singleton($abstract, fn () => $instance);
    }

    public function contextual(string $abstract, mixed $instance): void
    {
        Context::set($abstract, $instance);

        $this->contextual[$abstract] = true;
    }

    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }

    protected function getClosureFor(string $abstract): Closure
    {
        return function (ContainerContract $container) use ($abstract) {
            return $container->build($abstract);
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

        if (is_null($constructor)) {
            return new $class;
        }

        $parameters = $this->getParametersFor($constructor);

        return new $class(...$parameters);
    }

    public function get(string $id): mixed
    {
        $id = $this->isAlias($id) ? $this->aliases[$id] : $id;

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

        $name = $this->getReflectionFunctionIdentifier($function);

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

        $name = $this->getReflectionFunctionIdentifier($function);

        if (! $this->isResolved($scope, $name)) {
            $this->resolve($function);
        }

        $parameters = ! $scope
            ? $this->resolved[$name]
            : $this->resolved[$scope][$name];

        foreach ($parameters as $parameter) {
            extract($parameter);

            $abstract = $this->isAlias($abstract)
                ? $this->aliases[$abstract]
                : $abstract;

            if (is_null($abstract) || ! $this->has($abstract) && ! $this->isContextual($abstract)) {
                if (empty($merge)) {
                    continue;
                }

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

    protected function getReflectionFunctionIdentifier(ReflectionFunctionAbstract $function): string
    {
        return "{$function->getName()}::L{$function->getStartLine()}-L{$function->getEndLine()}";
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
        return $this->contextual[$abstract]
            ?? $this->contextual[$abstract] = Context::has($abstract);
    }

    public function isAlias(string $alias): bool
    {
        return isset($this->aliases[$alias]);
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }
}
