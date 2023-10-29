<?php

namespace Tavurn\Foundation;

use OpenSwoole\Server;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tavurn\Async\Coroutine;
use Tavurn\Container\Container;
use Tavurn\Contracts\Container\Container as ContainerContract;
use Tavurn\Contracts\Foundation\Application as ApplicationContract;
use Tavurn\Contracts\Http\Kernel;
use Tavurn\Support\ServiceProvider;

class Application extends Container implements ApplicationContract
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

    protected static array $bootstrappers = [
        \Tavurn\Foundation\Bootstrap\LoadConfiguration::class,
        \Tavurn\Foundation\Bootstrap\RegisterConfiguredProviders::class,
    ];

    public function __construct(Server $server, string $basePath = null)
    {
        if ($basePath) {
            $this->basePath = $basePath;
        }

        $this->server = $server;

        $this->registerBaseBindings();

        $this->registerCoreServices();
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
            \Tavurn\Foundation\Providers\DatabaseServiceProvider::class,
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

    public function registerCoreServices(): void
    {
        foreach (static::getCoreProviders() as $provider) {
            $this->register($provider);
        }
    }

    public function registerBaseBindings(): void
    {
        static::$instance = $this;

        $this->instance(
            ContainerContract::class,
            $this,
        );

        $this->instance(
            ApplicationContract::class,
            $this,
        );

        $this->instance(
            Server::class,
            $this->server,
        );
    }

    public function instance(string $abstract, mixed $instance): void
    {
        $this->singleton($abstract, fn () => $instance);
    }

    public function getServer(): Server
    {
        return $this->server;
    }

    public function start(): never
    {
        $this->boot();

        $this->bootstrapWith(static::$bootstrappers);

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
        if ($this->hasBeenBootstrapped()) {
            return;
        }

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
                array_walk($this->providers,
                    fn (ServiceProvider $provider) => go($provider->booting(...)),
                );
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
