<?php

require __DIR__.'/../../vendor/autoload.php';

use OpenSwoole\Http\Server;
use Tavurn\Application;
use Tavurn\Support\Facade;

$server = new Server('127.0.0.1', 8080);

$application = new Application($server);

interface GreeterInterface
{
    public function hello(): void;
}

$application->bind(GreeterInterface::class, function () {
    return new class implements GreeterInterface
    {
        public function hello(): void
        {
            echo "Hello!\n";
        }
    };
});

/**
 * @method static void hello()
 */
class Greeter extends Facade
{
    public static function getContainerAccessor(): string
    {
        return GreeterInterface::class;
    }
}

Greeter::hello();
