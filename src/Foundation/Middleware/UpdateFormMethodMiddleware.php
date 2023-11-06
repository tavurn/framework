<?php

namespace Tavurn\Foundation\Middleware;

use Psr\Http\Message\ResponseInterface;
use Tavurn\Contracts\Http\Middleware;
use Tavurn\Contracts\Http\Request;

class UpdateFormMethodMiddleware implements Middleware
{
    public function process(Request $request, Stack $next): ResponseInterface
    {
        if ($request->getMethod() !== 'POST') {
            return $next($request);
        }

        $body = $request->getParsedBody();

        if (isset($body['_method'])) {
            $request->withMethod($body['_method']);
        }

        return $next($request);
    }
}
