<?php

namespace Tavurn\Http\Traits;

use Psr\Http\Message\UriInterface;
use Tavurn\Contracts\MutablePsr7\MutableRequest;

/**
 * This class is a modified version of the RequestTrait trait
 * found in Nyholm/psr7 on GitHub.
 *
 * @link https://github.com/Nyholm/psr7
 */
trait RequestTrait
{
    use MessageTrait;

    /** @var string */
    private $method;

    /** @var string|null */
    private $requestTarget;

    /** @var UriInterface|null */
    private $uri;

    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        if ('' === $target = $this->uri->getPath()) {
            $target = '/';
        }
        if ($this->uri->getQuery() !== '') {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target;
    }

    /**
     * @return static
     */
    public function withRequestTarget(string $requestTarget): MutableRequest
    {
        $this->requestTarget = $requestTarget;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return static
     */
    public function withMethod(string $method): MutableRequest
    {
        $this->method = $method;

        return $this;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false): MutableRequest
    {
        $this->uri = $uri;

        if (! $preserveHost || ! $this->hasHeader('Host')) {
            $this->updateHostFromUri();
        }

        return $this;
    }

    private function updateHostFromUri(): void
    {
        if ('' === $host = $this->uri->getHost()) {
            return;
        }

        if (null !== ($port = $this->uri->getPort())) {
            $host .= ':' . $port;
        }

        if (isset($this->headerNames['host'])) {
            $header = $this->headerNames['host'];
        } else {
            $this->headerNames['host'] = $header = 'Host';
        }

        // Ensure Host is the first header.
        // See: http://tools.ietf.org/html/rfc7230#section-5.4
        $this->headers = [$header => [$host]] + $this->headers;
    }
}
