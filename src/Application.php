<?php

namespace Tavurn;

use OpenSwoole\Core\Psr\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Application implements RequestHandlerInterface
{
    protected static Application $instance;

    public function __construct()
    {
        static::$instance = $this;
    }

    public static function instance(): self
    {
        return self::$instance;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response('Hello, World!', headers: ['Content-Type' => 'text/html']);
    }
}