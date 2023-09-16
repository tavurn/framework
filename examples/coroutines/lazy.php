<?php

use Tavurn\Async\Coroutine;

require __DIR__.'/../../vendor/autoload.php';

Coroutine::run(function () {
    $future = async(function () {
        Coroutine::sleep(5);

        return rand();
    });

    echo "future is lazy\n";

    echo $future->await;
});
