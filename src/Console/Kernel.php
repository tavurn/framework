<?php

namespace Tavurn\Console;

use Illuminate\Support\Str;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface as Input;
use Symfony\Component\Console\Output\OutputInterface as Output;
use Symfony\Component\Finder\Finder;
use Tavurn\Contracts\Console\Kernel as KernelContract;
use Tavurn\Contracts\Foundation\Application;

class Kernel implements KernelContract
{
    protected Application $app;

    protected SymfonyApplication $symfony;

    /**
     * @var array<int, class-string>
     */
    protected static array $bootstrappers = [];

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

        $this->bootstrap();

        return $this->symfony->run($input, $output);
    }

    public function addCommands(array $commands): void
    {
        $this->symfony->addCommands($commands);
    }

    protected function commands(): void
    {
        //
    }

    protected function load(string $path)
    {
        foreach ((new Finder)->files()->in($path) as $file) {
            $name = $this->classNameFromFile($file);

            if (
                ! is_subclass_of($name, Command::class) &&
                ! (new \ReflectionClass($name))->isAbstract()
            ) {
                continue;
            }

            $this->symfony->add(
                $this->makeCommand($name),
            );
        }
    }

    protected function classNameFromFile(\SplFileInfo $file): string
    {
        $rel = Str::after(
            $file->getRealPath(),
            realpath(app()->basePath()) . DIRECTORY_SEPARATOR,
        );

        $name = str_replace(
            ['/', '.' . $file->getExtension()],
            ['\\', ''],
            $rel,
        );

        return Str::studly($name);
    }

    /**
     * @param class-string<Command> $class
     */
    protected function makeCommand(string $class): Command
    {
        return $this->app->make($class);
    }

    protected function bootstrap(): void
    {
        if (! $this->app->hasBeenBootstrapped()) {
            $bootstrappers = array_merge(
                static::$bootstrappers,
                $this->app::$bootstrappers ?? [],
            );

            $this->app->bootstrapWith($bootstrappers);
        }

        if (! $this->app->isBooted()) {
            $this->app->boot();
        }
    }
}
