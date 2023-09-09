<?php

use Tavurn\Application;
use Tavurn\Async\Future;

function app(): Application
{
    return Application::instance();
}

/**
 * Wraps the given function block in a lazy future.
 *
 * @see Future::$await
 *
 * @template T
 * @param callable(): T $block
 * @return Future<T>
 */
function async(callable $block): Future
{
    return new Future($block);
}