<?php

namespace Tavurn;

use OpenSwoole\Core\Psr\Response;
use OpenSwoole\Server;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tavurn\Async\Coroutine;
use Tavurn\Container\Container;
use Tavurn\Support\ServiceProvider;

class Application extends Container implements RequestHandlerInterface
{
    protected static Application $instance;

    protected Server $server;

    /**
     * @var array<int, ServiceProvider>
     */
    protected array $providers = [];

    public function __construct(Server $server)
    {
        static::$instance = $this;

        $this->server = $server;

        $this->register(static::getCoreProviders());
    }

    public static function getCoreProviders(): array
    {
        return [
            \Tavurn\Providers\EventServiceProvider::class,
        ];
    }

    public static function instance(): self
    {
        return self::$instance;
    }

    /**
     * @param array<int, string>|string $providers
     * @return $this
     */
    public function register($providers): static
    {
        if (! is_array($providers) && $providers) {
            $providers = [$providers];
        }

        $instanced = array_map(fn ($provider) => new $provider($this), $providers);

        foreach ($instanced as $provider) {
            $provider->register();
        }

        $this->providers = array_merge($this->providers, $instanced);

        return $this;
    }

    public function getServer(): Server
    {
        return $this->server;
    }

    public function bootServiceProviders(array $providers): void
    {
        Coroutine::each(
            $providers,
            fn (ServiceProvider $provider) => $provider->booting(),
        );
    }

    public function start(): never
    {
        $this->bootServiceProviders($this->providers);

        $this->server->setHandler($this);

        $this->server->start();

        exit('The server has stopped' . PHP_EOL);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response('Hello, World!', headers: ['Content-Type' => 'text/html']);
    }
}
