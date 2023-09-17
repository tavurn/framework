<?php

require __DIR__ . '/../../vendor/autoload.php';

use Tavurn\Async\Coroutine;

Coroutine::run(function () {
    Coroutine::go(function () {
        Coroutine::sleep(1);

        echo 'Hello 2';
    });

    // This gets echoed first.
    Coroutine::go(function () {
        echo 'Hello 1';
    });
});
