<?php

require __DIR__.'/../vendor/autoload.php';

use OpenSwoole\Http\Server;
use Tavurn\Application;

$application = new Application;

$server = new Server('127.0.0.1', 8080);

$server->set([
    'worker_num' => 4,
    'task_worker_num' => 0,
]);


// TODO: make application an actual application 🗣️🗣️
$server->setHandler($application);

$server->start();
