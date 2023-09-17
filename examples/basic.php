<?php

require __DIR__ . '/../vendor/autoload.php';

use OpenSwoole\Http\Server;
use Tavurn\Application;

$server = new Server('127.0.0.1', 8080);

$server->set([
    'worker_num' => 4,
]);

$application = new Application($server);

$application->start();
