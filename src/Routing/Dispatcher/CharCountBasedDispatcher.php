<?php

namespace Tavurn\Routing\Dispatcher;

use FastRoute\Dispatcher\CharCountBased as Dispatcher;
use Tavurn\Contracts\Routing\MutableDispatcher;

class CharCountBasedDispatcher extends Dispatcher implements MutableDispatcher
{
    use MutableFastRouteDispatcher;
}
