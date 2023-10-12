<?php

namespace Tavurn\Contracts\Http;

use Psr\Http\Message\ServerRequestInterface;

interface Request extends ServerRequestInterface
{
    /**
     * Get a value from the query string.
     *
     * @template Default
     *
     * @param Default $default
     * @return string|Default
     */
    public function query(string $key, mixed $default = null): mixed;

    /**
     * Get a value from the route parameters.
     *
     * @template Default
     *
     * @param Default $default
     * @return string|Default
     */
    public function get(string $key, mixed $default = null): mixed;
}
