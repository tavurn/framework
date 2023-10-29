<?php

namespace Tavurn\Support\Facades;

use Tavurn\Contracts\Events\Dispatcher;
use Tavurn\Support\Facade;

/**
 * @method static object dispatch(object $event)
 */
class Event extends Facade
{
    protected static function getContainerAccessor(): string
    {
        return Dispatcher::class;
    }
}
