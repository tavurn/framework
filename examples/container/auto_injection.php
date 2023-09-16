<?php

require __DIR__.'/../../vendor/autoload.php';

use Tavurn\Container\Container;

class Greeter
{
    public function hello(string $name)
    {
        echo "Hello, {$name}!";
    }
}

class A
{
    public function __construct(public Greeter $greeter)
    {
        //
    }
}

$container = new Container;

$container->bind(Greeter::class, function () {
    return new Greeter;
});

$a = $container->make(A::class);
$a->greeter->hello('David');
