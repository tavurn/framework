<?php

require __DIR__.'/../../vendor/autoload.php';

use Tavurn\Async\Coroutine;

Coroutine::run(function () {
    [$greeting, $farewell] = Coroutine::wait([
        function () {
            Coroutine::usleep(500);

            return 'hello';
        },
        function () {
            Coroutine::sleep(3);

            return 'goodbye';
        },
    ]);

    // Only takes 3, not 3.5, seconds.
    // Thanks to coroutines!!

    echo $greeting, $farewell, PHP_EOL;
});
