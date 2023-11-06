<?php

namespace Tavurn\Contracts\Http;

use Psr\Http\Message\ResponseInterface;
use Tavurn\Foundation\Middleware\Stack;

interface Middleware
{
    public function process(Request $request, Stack $next): ResponseInterface;
}
