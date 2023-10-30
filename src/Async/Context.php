<?php

namespace Tavurn\Async;

final class Context
{
    public static function set(string $key, mixed $value, int $cid = 0): void
    {
        Coroutine::getContext($cid)[$key] = $value;
    }

    public static function has(string $key, int $cid = 0): bool
    {
        if (! Coroutine::exists($cid)) {
            return false;
        }

        return isset(Coroutine::getContext($cid)[$key]);
    }

    /**
     * @template T
     *
     * @param class-string<T> $key
     * @return null|T
     */
    public static function get(string $key, int $cid = 0): mixed
    {
        $cid = $cid === 0 ? Coroutine::getCid() : $cid;

        do {
            $context = Coroutine::getContext($cid);

            if (! isset($context[$key])) {
                $cid = Coroutine::getPcid();

                continue;
            }

            return $context[$key];
        } while ($cid !== -1);

        return null;
    }
}
