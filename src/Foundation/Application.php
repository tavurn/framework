<?php

namespace Tavurn\Foundation;

use OpenSwoole\Server;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tavurn\Container\Container;
use Tavurn\Contracts\Container\Container as ContainerContract;
use Tavurn\Contracts\Http\Kernel;
use Tavurn\Foundation\Async\Coroutine;
use Tavurn\Support\ServiceProvider;

class Application extends Container implements RequestHandlerInterface
{
    protected static Application $instance;

    protected string $basePath;

    protected Server $server;

    protected bool $isBooted = false;

    protected bool $hasBeenBootstrapped = false;

    /**
     * @var array<class-string, ServiceProvider>
     */
    protected array $providers = [];

    public function __construct(Server $server, string $basePath = null)
    {
        if ($basePath) {
            $this->basePath = $basePath;
        }

        $this->server = $server;

        $this->registerBaseBindings();
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function isBooted(): bool
    {
        return $this->isBooted;
    }

    public function basePath(string $path = ''): string
    {
        return realpath($this->basePath . '/' . $path);
    }

    public static function getCoreProviders(): array
    {
        return [
            \Tavurn\Foundation\Providers\ExceptionServiceProvider::class,
        ];
    }

    /**
     * @template T
     *
     * @param class-string<T> $provider
     * @return T|null
     */
    public function getProvider(string $provider): ?ServiceProvider
    {
        if (! isset($this->providers[$provider])) {
            return null;
        }

        return $this->providers[$provider];
    }

    /**
     * @template T
     *
     * @param class-string<T> $provider
     * @return T
     */
    public function register(string $provider): ServiceProvider
    {
        if ($registered = $this->getProvider($provider)) {
            return $registered;
        }

        $instance = new $provider($this);

        $instance->register();

        $this->providers[$provider] = $instance;

        if ($this->isBooted) {
            $instance->booting();
        }

        return $instance;
    }

    public function registerBaseBindings(): void
    {
        static::$instance = $this;

        $this->instance(
            ContainerContract::class,
            $this,
        );

        $this->instance(
            Application::class,
            $this,
        );

        $this->instance(
            Server::class,
            $this->server,
        );
    }

    public function instance(string $abstract, mixed $instance): void
    {
        $this->singleton($abstract, function () use ($instance) {
            return $instance;
        });
    }

    public function getServer(): Server
    {
        return $this->server;
    }

    public function start(): never
    {
        $this->boot();

        $this->get(Kernel::class)->bootstrap();

        $this->server->setHandler($this);

        $this->server->start();

        exit('The server has stopped' . PHP_EOL);
    }

    public function hasBeenBootstrapped(): bool
    {
        return $this->hasBeenBootstrapped;
    }

    public function bootstrapWith(array $bootstrappers): void
    {
        $this->hasBeenBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap(static::getInstance());
        }
    }

    public function boot(): void
    {
        if ($this->isBooted()) {
            return;
        }

        Coroutine::run(function () {
            Coroutine::waitSingle(function () {
                array_walk($this->providers, function (ServiceProvider $provider) {
                    Coroutine::go($provider->booting(...));
                });
            });

            $this->isBooted = true;
        });
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $kernel = $this->get(Kernel::class);

        return $kernel->handle($request);
    }
}
