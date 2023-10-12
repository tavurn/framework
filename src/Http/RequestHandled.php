<?php

namespace Tavurn\Http;

use Psr\Http\Message\ResponseInterface;
use Tavurn\Contracts\Http\Request;

readonly class RequestHandled
{
    public Request $request;

    public ResponseInterface $response;

    public function __construct(Request $request, ResponseInterface $response)
    {
        $this->request = $request;

        $this->response = $response;
    }
}
