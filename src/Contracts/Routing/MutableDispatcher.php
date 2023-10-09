<?php

namespace Tavurn\Contracts\Routing;

use FastRoute\Dispatcher;

interface MutableDispatcher extends Dispatcher
{
    public function updateData(array $data): void;

    public function setStaticRouteMap(array $data): void;

    public function setVariableRouteData(array $data): void;
}
