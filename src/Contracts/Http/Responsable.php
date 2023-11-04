<?php

namespace Tavurn\Contracts\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Responsable
{
    public function respond(ServerRequestInterface $request): ResponseInterface;
}
