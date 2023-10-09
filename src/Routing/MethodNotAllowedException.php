<?php

namespace Tavurn\Routing;

use Exception;
use OpenSwoole\Core\Psr\Response;

class MethodNotAllowedException extends Exception
{
    public function render()
    {
        return new Response('405 | Method Not Allowed', 405);
    }
}
