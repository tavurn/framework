<?php

namespace Tavurn\Routing\Dispatcher;

use FastRoute\Dispatcher\MarkBased as Dispatcher;
use Tavurn\Contracts\Routing\MutableDispatcher;

class MarkBasedDispatcher extends Dispatcher implements MutableDispatcher
{
    use MutableFastRouteDispatcher;
}
