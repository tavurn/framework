<?php

require __DIR__.'/../../vendor/autoload.php';

use Tavurn\Async\Coroutine;

Coroutine::run(function () {
    // Only takes 3 seconds!!
    [$greeting, $farewell] = Coroutine::wait([
        function () {
            Coroutine::usleep(500);
            return "hello";
        },
        function () {
            Coroutine::sleep(3);
            return "goodbye";
        }
    ]);

    echo $greeting, $farewell, PHP_EOL;
});
