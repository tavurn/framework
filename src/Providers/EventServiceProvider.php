<?php

namespace Tavurn\Providers;

use Psr\EventDispatcher\ListenerProviderInterface;
use Tavurn\Contracts\Events\Dispatcher as DispatcherContract;
use Tavurn\Events\Dispatcher;
use Tavurn\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider implements ListenerProviderInterface
{
    protected array $listeners = [];

    public function register(): void
    {
        $this->container->singleton(ListenerProviderInterface::class, function () {
            return $this;
        });

        $this->container->singleton(
            DispatcherContract::class,
            Dispatcher::class,
        );
    }

    public function callableArrayFromClass(string $name): array
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
