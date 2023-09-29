<?php

namespace Tavurn\Contracts\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface Handler
{
    public function report(Throwable $error): void;

    public function shouldReport(Throwable $error): bool;

    public function render(ServerRequestInterface $request, Throwable $error): ResponseInterface;
}
