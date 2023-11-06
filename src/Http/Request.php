<?php

namespace Tavurn\Http;

use OpenSwoole\Core\Psr\Stream;
use OpenSwoole\Core\Psr\Uri;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Tavurn\Contracts\Http\Request as RequestContract;

/**
 * This class is a modified version of the ServerRequest class
 * found in Nyholm/psr7 on GitHub.
 *
 * @link https://github.com/Nyholm/psr7
 */
class Request implements RequestContract
{
    use Traits\MessageTrait;
    use Traits\RequestTrait;

    /** @var array */
    private $attributes = [];

    /** @var array */
    private $cookieParams = [];

    /** @var array|object|null */
    private $parsedBody;

    /** @var array */
    private $queryParams = [];

    /** @var array */
    private $serverParams;

    /** @var UploadedFileInterface[] */
    private $uploadedFiles = [];

    /**
     * @param string $method HTTP method
     * @param string|UriInterface $uri URI
     * @param array $headers Request headers
     * @param string|resource|StreamInterface|null $body Request body
     * @param string $version Protocol version
     * @param array $serverParams Typically the $_SERVER superglobal
     */
    public function __construct(string $method, $uri, array $headers = [], $body = null, string $version = '1.1', array $serverParams = [])
    {
        $this->serverParams = $serverParams;

        if (! ($uri instanceof UriInterface)) {
            $uri = new Uri($uri);
        }

        $this->method = $method;
        $this->uri = $uri;
        $this->setHeaders($headers);
        $this->protocol = $version;
        \parse_str($uri->getQuery(), $this->queryParams);

        if (! $this->hasHeader('Host')) {
            $this->updateHostFromUri();
        }

        if ($body instanceof StreamInterface) {
            $this->stream = $body;
        } elseif ($body !== '' && $body !== null) {
            // If we got no body, defer initialization of the stream until ServerRequest::getBody()
            $this->stream = Stream::streamFor($body);
        }
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles): RequestContract
    {
        $this->uploadedFiles = $uploadedFiles;

        return $this;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * @return static
     */
    public function withCookieParams(array $cookies): RequestContract
    {
        $this->cookieParams = $cookies;

        return $this;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @return static
     */
    public function withQueryParams(array $query): RequestContract
    {
        $this->queryParams = $query;

        return $this;
    }

    /**
     * @return array|object|null
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @return static
     */
    public function withParsedBody($data): RequestContract
    {
        $this->parsedBody = $data;

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * @return static
     */
    public function withAttribute(string $name, $value): RequestContract
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @return static
     */
    public function withoutAttribute(string $name): RequestContract
    {
        unset($this->attributes[$name]);

        return $this;
    }
}
