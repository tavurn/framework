<?php

namespace Tavurn\Contracts\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Tavurn\Contracts\Http\Request;
use Throwable;

interface Handler
{
    public function report(Throwable $error): void;

    public function shouldReport(Throwable $error): bool;

    public function render(Request $request, Throwable $error): ResponseInterface;
}
