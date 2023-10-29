<?php

namespace Tavurn\Http;

use OpenSwoole\Core\Psr\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Tavurn\Contracts\Http\Request as RequestContract;

class Request extends ServerRequest implements RequestContract
{
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->getQueryParam($key, $default);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getAttribute($key, $default);
    }

    public static function gather(ServerRequestInterface $request)
    {
        return new self(
            $request->getUri(),
            $request->getMethod(),
            $request->getBody(),
            $request->getHeaders(),
            $request->getCookieParams(),
            $request->getQueryParams(),
            $request->getServerParams(),
            $request->getUploadedFiles(),
            $request->getParsedBody(),
            $request->getProtocolVersion(),
        );
    }
}
