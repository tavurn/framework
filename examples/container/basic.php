<?php

require __DIR__ . '/../../vendor/autoload.php';

use Tavurn\Container\Container;

class A
{
    //
}

$container = new Container;

$container->bind(A::class, function () {
    return new A;
});

$a1 = $container->get(A::class);
$a2 = $container->get(A::class);

assert($a1 !== $a2);

/**
 * $a2 is not a reference to the same object as $a1,
 * they are two separate instances of A::class.
 */
