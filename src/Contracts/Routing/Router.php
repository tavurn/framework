<?php

namespace Tavurn\Contracts\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Router
{
    public function dispatch(ServerRequestInterface $request): ResponseInterface;
}
