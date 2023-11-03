<?php

namespace Tavurn\View;

class VariableContainer
{
    /**
     * @var array<string, mixed>
     */
    private array $variables;

    /**
     * @param array<string, mixed> $variables
     */
    public function __construct(array $variables = [])
    {
        $this->variables = $variables;
    }

    /**
     * @template T
     *
     * @param \Closure(): T $block
     * @return T
     */
    public function run(\Closure $block, array $arguments = []): mixed
    {
        return $block->call($this, $arguments);
    }

    /**
     * @param string|int $key
     */
    protected function e($key, $default = null): mixed
    {
        return $this->variables[$key] ?? $default;
    }
}
