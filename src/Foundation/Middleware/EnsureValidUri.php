<?php

namespace Tavurn\Foundation\Middleware;

use Psr\Http\Message\ResponseInterface;
use Tavurn\Contracts\Http\Middleware;
use Tavurn\Contracts\Http\Request;

class EnsureValidUri implements Middleware
{
    /**
     * {@inheritdoc}
     */
    public function process(Request $request, Stack $next): ResponseInterface
    {
        $uri = $request->getUri();

        $path = rtrim($uri->getPath(), '/') . '/';

        $request = $request->withUri(
            $uri->withPath($path),
        );

        return $next->process($request);
    }
}
