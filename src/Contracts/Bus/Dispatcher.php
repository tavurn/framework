<?php

namespace Tavurn\Contracts\Bus;

interface Dispatcher
{
    public function dispatch($command): void;

    public function dispatchSync($command): void;

    public function dispatchIf($command): void;
}