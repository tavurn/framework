<?php

namespace Tavurn\Routing;

use Exception;
use OpenSwoole\Core\Psr\Response;
use Psr\Http\Message\ResponseInterface;

class NotFoundException extends Exception
{
    public function render(): ResponseInterface
    {
        return new Response('404 | Not Found', 404);
    }
}
