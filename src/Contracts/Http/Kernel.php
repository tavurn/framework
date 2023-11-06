<?php

namespace Tavurn\Contracts\Http;

use Psr\Http\Message\ResponseInterface;

interface Kernel
{
    public function handle(Request $request): ResponseInterface;
}
