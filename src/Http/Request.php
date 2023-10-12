<?php

namespace Tavurn\Http;

use OpenSwoole\Core\Psr\ServerRequest;
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
}
