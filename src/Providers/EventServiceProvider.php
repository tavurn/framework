<?php

namespace Tavurn\Providers;

use Psr\EventDispatcher\ListenerProviderInterface;
use Tavurn\Contracts\Container\Container;
use Tavurn\Contracts\Events\Dispatcher;
use Tavurn\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider implements ListenerProviderInterface
{
    protected array $listeners = [];

    public function register(): void
    {
        $provider = $this;

        $this->container->singleton(ListenerProviderInterface::class, function () use ($provider) {
            return $provider;
        });

        $this->container->singleton(Dispatcher::class, function (Container $container) {
            return new \Tavurn\Events\Dispatcher($container->get(ListenerProviderInterface::class));
        });
    }

    public function callableArrayFromClass($name): array
    {
        return [$this->container->make($name), 'handle'];
    }

    public function getListenersForEvent(object $event): iterable
    {
        $listeners = $this->listeners[$event::class] ?? [];

        return array_map(
            fn ($listener) => $this->callableArrayFromClass($listener),
            $listeners,
        );
    }
}
