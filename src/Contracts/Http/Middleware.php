<?php

namespace Tavurn\Contracts\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tavurn\Foundation\Middleware\Stack;

interface Middleware
{
    /**
     * @return Responsable|ResponseInterface|string
     */
    public function process(ServerRequestInterface $request, Stack $next): mixed;
}
