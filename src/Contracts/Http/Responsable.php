<?php

namespace Tavurn\Contracts\Http;

use Psr\Http\Message\ResponseInterface;

interface Responsable
{
    public function respond(Request $request): ResponseInterface;
}
