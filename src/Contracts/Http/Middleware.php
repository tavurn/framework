<?php

namespace Tavurn\Contracts\Http;

use Psr\Http\Message\ResponseInterface;
use Tavurn\Foundation\Middleware\Stack;

interface Middleware
{
    /**
     * @return Responsable|ResponseInterface|string
     */
    public function process(Request $request, Stack $next): mixed;
}
