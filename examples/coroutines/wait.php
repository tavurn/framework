<?php

require __DIR__.'/../../vendor/autoload.php';

use Tavurn\Async\Coroutine;

Coroutine::run(function () {
    $value = Coroutine::waitSingle(function () {
        Coroutine::sleep(1);

        return 1;
    });

    echo $value, PHP_EOL;
});
