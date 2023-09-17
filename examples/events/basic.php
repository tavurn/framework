<?php

require __DIR__ . '/../../vendor/autoload.php';

use Tavurn\Container\Container;
use Tavurn\Events\Dispatcher;
use Tavurn\Providers\EventServiceProvider as ServiceProvider;

class MessageEvent
{
    public string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
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
