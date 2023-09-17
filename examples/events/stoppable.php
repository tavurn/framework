<?php

require __DIR__.'/../../vendor/autoload.php';

use Tavurn\Container\Container;
use Tavurn\Contracts\Events\Stoppable;
use Tavurn\Events\Dispatcher;
use Tavurn\Providers\EventServiceProvider as ServiceProvider;

class MessageEvent implements Stoppable
{
    public string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function isPropagationStopped(): bool
    {
        return true;
    }
}

class Listener
{
    public function handle(MessageEvent $event): void
    {
        echo $event->message;
    }
}

class EventServiceProvider extends ServiceProvider
{
    protected array $listeners = [
        MessageEvent::class => [
            Listener::class,
        ],
    ];
}

$container = new Container;

$dispatcher = new Dispatcher(new EventServiceProvider($container));

$dispatcher->dispatch(new MessageEvent('Hello!'));

// MessageEvent does not get emitted, and the listeners will not be called.
