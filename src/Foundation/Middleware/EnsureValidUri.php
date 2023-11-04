<?php

namespace Tavurn\Foundation\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tavurn\Contracts\Http\Middleware;

class EnsureValidUri implements Middleware
{
    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, Stack $next): ResponseInterface
    {
        $uri = $request->getUri();

        $path = rtrim($uri->getPath(), '/') ?: '/';

        $request = $request->withUri(
            $uri->withPath($path),
        );

        return $next->process($request);
    }
}
