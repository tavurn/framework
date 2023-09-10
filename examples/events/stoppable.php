<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection DuplicatedCode */

require __DIR__.'/../../vendor/autoload.php';

use Psr\EventDispatcher\ListenerProviderInterface;
use Tavurn\Contracts\Events\Stoppable;
use Tavurn\Events\Dispatcher;

class Event implements Stoppable
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
    public function handle(Event $event): void
    {
        echo $event->message;
    }
}

$provider = new class implements ListenerProviderInterface {
    protected array $listeners = [
        Event::class => [
            Listener::class,
        ],
    ];

    public function callableArrayFromClass($name): array
    {
        return [new $name, 'handle'];
    }

    public function getListenersForEvent(object $event): iterable
    {
        $listeners = $this->listeners[$event::class] ?? [];

        return array_map(
            fn($listener) => $this->callableArrayFromClass($listener),
            $listeners,
        );
    }
};

$dispatcher = new Dispatcher($provider);

$dispatcher->dispatch(new Event('Hello!'));
