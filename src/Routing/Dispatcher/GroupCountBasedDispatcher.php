<?php

namespace Tavurn\Routing\Dispatcher;

use FastRoute\Dispatcher\GroupCountBased as Dispatcher;
use Tavurn\Contracts\Routing\MutableDispatcher;

class GroupCountBasedDispatcher extends Dispatcher implements MutableDispatcher
{
    use MutableFastRouteDispatcher;
}
