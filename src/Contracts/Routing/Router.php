<?php

namespace Tavurn\Contracts\Routing;

use Psr\Http\Message\ResponseInterface;
use Tavurn\Contracts\Http\Request;

interface Router
{
    public function dispatch(Request $request): ResponseInterface;
}
