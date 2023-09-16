<?php

require __DIR__.'/../../vendor/autoload.php';

use Tavurn\Container\Container;

class A {}

$container = new Container;

$container->bind(A::class, function () {
    return new A;
});

function hello(A $a) {}

$container->call('hello');
