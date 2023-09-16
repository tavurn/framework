<?php

require __DIR__.'/../../vendor/autoload.php';

use Tavurn\Support\Facade;
use OpenSwoole\Http\Server;
use Tavurn\Application;

$server = new Server('127.0.0.1', 8080);

$application = new Application($server);

interface GreeterInterface
{
    public function hello(): void;
}


$application->bind(GreeterInterface::class, function () {
    return new class implements GreeterInterface {
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
    static function getContainerAccessor(): string
    {
        return GreeterInterface::class;
    }
}

Greeter::hello();
