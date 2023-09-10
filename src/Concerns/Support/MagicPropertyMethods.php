<?php

namespace Tavurn\Concerns\Support;


use InvalidArgumentException;

trait MagicPropertyMethods
{
    public function __get(string $name)
    {
        if (! isset($this->allowedCalls)) {
            $this->allowedCalls = [];
        }

        $allowed = array_is_list($this->allowedCalls)
            ? array_values($this->allowedCalls)
            : array_keys($this->allowedCalls);

        if (! in_array($name, $allowed)) {
            throw new InvalidArgumentException("property [$name] does not exist on ".static::class);
        }

        if (isset($this->allowedCalls[$name])) {
            $name = $this->allowedCalls[$name];
        }

        return $this->{$name}();
    }
}