<?php

namespace Tavurn\Foundation;

use OpenSwoole\Core\Psr\Stream;
use OpenSwoole\Core\Psr\UploadedFile;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use OpenSwoole\Server;
use Psr\Http\Message\ResponseInterface;
use Tavurn\Async\Context;
use Tavurn\Async\Coroutine;
use Tavurn\Async\Coroutine as Co;
use Tavurn\Container\Container;
use Tavurn\Contracts\Container\Container as ContainerContract;
use Tavurn\Contracts\Foundation\Application as ApplicationContract;
use Tavurn\Contracts\Http\Kernel;
use Tavurn\Contracts\Http\Request as RequestContract;
use Tavurn\Support\Providers\Contextual;
use Tavurn\Support\ServiceProvider;

class Application extends Container implements ApplicationContract
{
    protected static Application $instance;

    protected Kernel $kernel;

    protected string $basePath;

    protected bool $isBooted = false;

    protected bool $hasBeenBootstrapped = false;

    /**
     * @var array<class-string, ServiceProvider>
     */
    protected array $providers = [];

    public static array $bootstrappers = [
        \Tavurn\Foundation\Bootstrap\LoadConfiguration::class,
        \Tavurn\Foundation\Bootstrap\RegisterConfiguredProviders::class,
    ];

    public function __construct(string $basePath = null)
    {
        if ($basePath) {
            $this->basePath = $basePath;
        }

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
    }

    public function serve(Server $server): bool
    {
        $this->instance(Server::class, $server);

        $this->boot();

        $this->bootstrapWith(static::$bootstrappers);

        $server->on('request', function (Request $request, Response $pending) {
            $response = $this->handle(
                $this->gatherRequest($request)
            );

            $pending->header = $response->getHeaders();

            $pending->status($response->getStatusCode(), $response->getReasonPhrase());

            $pending->end($response->getBody()->getContents());
        });

        return $server->start();
    }

    public function hasBeenBootstrapped(): bool
    {
        return $this->hasBeenBootstrapped;
    }

    public function bootstrapWith(array $bootstrappers = []): void
    {
        if ($this->hasBeenBootstrapped()) {
            return;
        }

        $this->hasBeenBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }
    }

    public function boot(): void
    {
        if ($this->isBooted()) {
            return;
        }

        Co::run(function () {
            Co::waitSingle(function () {
                array_walk($this->providers,
                    fn (ServiceProvider $provider) => go($provider->booting(...)),
                );
            });

            $this->isBooted = true;
        });
    }

    public function bootContextualProviders(): void
    {
        $providers = array_filter(
            $this->providers,
            fn ($provider) => $provider instanceof Contextual,
        );

        Co::each($providers, function (Contextual $provider) {
            $provider->handling($this);

            $parent = Coroutine::getPcid();

            foreach ($provider->contextual() as $item) {
                Context::set($item, Context::get($item), $parent);
            }
        });
    }

    public function setKernel(Kernel $kernel): void
    {
        $this->kernel = $kernel;
    }

    public function handle(RequestContract $request): ResponseInterface
    {
        $this->bootContextualProviders();

        $kernel = $this->kernel ??= $this->get(Kernel::class);

        return $kernel->handle($request);
    }

    private function gatherRequest(Request $request): RequestContract
    {
        foreach ($request->files ??= [] as $name => $fileData) {
            $request->files[$name] = new UploadedFile(
                Stream::createStreamFromFile($fileData['tmp_name']),
                $fileData['size'],
                $fileData['error'],
                $fileData['name'],
                $fileData['type'],
            );
        }

        return (new \Tavurn\Http\Request(
            $request->server['request_method'],
            $request->server['request_uri'],
            $request->header,
            $request->rawContent() ?: '',
            $request->server['server_protocol'],
            $request->server,
        ))
            ->withUploadedFiles($request->files)
            ->withCookieParams($request->cookie ?? [])
            ->withQueryParams($request->get ?? [])
            ->withParsedBody($request->post ?? []);
    }
}
