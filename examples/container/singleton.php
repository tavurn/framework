<?php

require __DIR__.'/../../vendor/autoload.php';

use Tavurn\Container\Container;

class A {}

$container = new Container;

$container->singleton(A::class, function () {
    return new A;
});

$a1 = $container->get(A::class);
$a2 = $container->get(A::class);

assert($a1 === $a2);

/**
 * $a2 is a reference to the same object $a1 is a reference to,
 * they are the same unique object.
 */
