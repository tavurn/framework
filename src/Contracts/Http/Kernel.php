<?php

namespace Tavurn\Contracts\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Kernel
{
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
