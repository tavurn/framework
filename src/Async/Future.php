<?php

namespace Tavurn\Async;

use Closure;
use RuntimeException;
use Tavurn\Concerns\Support\MagicPropertyMethods;

/**
 * A future is essentially a lazy coroutine that returns a value when awaited.
 * This concept is quite popular in other languages that support async/await.
 *
 * @template T
 *
 * @property-read T await
 */
final class Future
{
    use MagicPropertyMethods;

    /**
     * @var Closure(): T
     */
    private Closure $block;

    private bool $resolved = false;

    protected array $allowedCalls = [
        'await',
    ];

    /**
     * @param callable(): T $block
     */
    public function __construct(callable $block)
    {
        $this->block = $block(...);
    }

    /**
     * @return T
     */
    protected function await(): mixed
    {
        if ($this->resolved) {
            throw new RuntimeException('Future has already been resolved');
        }

        $result = Coroutine::waitSingle($this->block);

        $this->resolved = true;

        return $result;
    }
}
