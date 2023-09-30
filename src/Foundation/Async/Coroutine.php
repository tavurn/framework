<?php


namespace Tavurn\Foundation\Async;

use OpenSwoole\Core\Coroutine\WaitGroup;
use OpenSwoole\Coroutine\Context;
use RuntimeException;

final class Coroutine
{
    /**
     * Start a new coroutine context.
     */
    public static function run(callable $block): int
    {
        return \OpenSwoole\Coroutine::run($block);
    }

    /**
     * Waits for a group of blocks to be done executing and returns the results.
     *
     * @template TKey
     * @template TBlockReturn
     *
     * @param array<TKey, callable(): TBlockReturn> $group
     * @return array<TKey, TBlockReturn>
     */
    public static function wait(array $group, float $timeout = -1): array
    {
        $waitGroup = new WaitGroup();

        $results = [];

        foreach ($group as $identifier => $block) {
            Coroutine::go(function () use ($waitGroup, $identifier, &$results, $block) {
                $waitGroup->add();
                $results[$identifier] = $block();
                $waitGroup->done();
            });
        }

        $waitGroup->wait($timeout);

        return $results;
    }

    public static function waitSingle(callable $block): mixed
    {
        return Coroutine::wait([$block])[0];
    }

    public static function sleep(int $seconds): void
    {
        \OpenSwoole\Coroutine::sleep($seconds);
    }

    public static function usleep(int $milliseconds): void
    {
        \OpenSwoole\Coroutine::usleep($milliseconds);
    }

    /**
     * @param array<int, mixed> $items
     */
    public static function each(array $items, callable $block): void
    {
        foreach ($items as $item) {
            Coroutine::go($block, $item);
        }
    }

    public static function getContext(int $cid = 0): Context
    {
        $cid = $cid === 0 ? Coroutine::getCid() : $cid;

        if (is_null($context = \OpenSwoole\Coroutine::getContext($cid))) {
            throw new RuntimeException("Context for coroutine [{$cid}] is undefined");
        }

        return $context;
    }

    /**
     * Start a non-blocking task inside the current coroutine context.
     *
     * @param mixed ...$args
     *
     * @see Coroutine::run()
     */
    public static function go(callable $block, ...$args): int
    {
        return go($block, ...$args);
    }

    public static function getCid(): int
    {
        return \OpenSwoole\Coroutine::getCid();
    }

    public static function getPcid(): int
    {
        return \OpenSwoole\Coroutine::getPcid();
    }
}
