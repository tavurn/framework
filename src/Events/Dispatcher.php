<?php

namespace Tavurn\Events;

use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Tavurn\Contracts\Events\Dispatcher as DispatcherContract;
use Tavurn\Contracts\Events\Stoppable;

class Dispatcher implements DispatcherContract
{
    protected ListenerProviderInterface $provider;

    public function __construct(ListenerProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function eventIsStopped(object $event): bool
    {
        return $event instanceof Stoppable
            && $event->isPropagationStopped();
    }

    public function dispatch(object $event): object
    {
        if ($this->eventIsStopped($event)) {
            return $event;
        }

        $listeners = $this->provider->getListenersForEvent($event);

        foreach ($listeners as $listener) {
            $listener($event);
        }

        return $event;
    }
}