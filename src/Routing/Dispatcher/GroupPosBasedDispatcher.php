<?php

namespace Tavurn\Routing\Dispatcher;

use FastRoute\Dispatcher\GroupPosBased as Dispatcher;
use Tavurn\Contracts\Routing\MutableDispatcher;

class GroupPosBasedDispatcher extends Dispatcher implements MutableDispatcher
{
    use MutableFastRouteDispatcher;
}
