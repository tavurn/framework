<?php

namespace Tavurn\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface as Input;
use Symfony\Component\Console\Output\OutputInterface as Output;
use Tavurn\Contracts\Foundation\Application;

class Kernel
{
    protected Application $app;

    protected SymfonyApplication $symfony;

    /**
     * @var array<int, class-string>
     */
    protected static array $bootstrappers = [];

    /**
     * @var array<int, Command>
     */
    protected array $commands = [];

    protected bool $commandsLoaded = false;

    public function __construct(Application $app, SymfonyApplication $symfony = null)
    {
        $this->app = $app;

        $this->symfony = $symfony ?? new SymfonyApplication;
    }

    public function run(Input $input, Output $output): int
    {
        if (! $this->commandsLoaded) {
            $this->commands();

            $this->commandsLoaded = true;
        }

        return $this->symfony->run($input, $output);
    }

    protected function commands(): void
    {
        //
    }

    protected function bootstrap(): void
    {
        if (! $this->app->hasBeenBootstrapped()) {
            $bootstrappers = array_merge(
                static::$bootstrappers,
                $this->app::$boostrappers ?? [],
            );

            $this->app->bootstrapWith($bootstrappers);
        }
    }
}
