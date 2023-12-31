<?php

namespace Tavurn\Http\Traits;

use OpenSwoole\Core\Psr\Stream;
use Psr\Http\Message\StreamInterface;
use Tavurn\Contracts\MutablePsr7\MutableMessage;

/**
 * This class is a modified version of the MessageTrait trait
 * found in Nyholm/psr7 on GitHub.
 *
 * @link https://github.com/Nyholm/psr7
 */
trait MessageTrait
{
    /** @var array Map of all registered headers, as original name => array of values */
    private $headers = [];

    /** @var array Map of lowercase header name => original name at registration */
    private $headerNames = [];

    /** @var string */
    private $protocol = '1.1';

    /** @var StreamInterface|null */
    private $stream;

    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /**
     * @return static
     */
    public function withProtocolVersion(string $version): MutableMessage
    {
        $this->protocol = $version;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($header): bool
    {
        return isset($this->headerNames[\strtr($header, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')]);
    }

    public function getHeader(string $header): array
    {
        $header = strtolower($header);

        $header = $this->headerNames[$header] ?? null;

        return is_null($header) ? []
            : $this->headers[$header];
    }

    public function getHeaderLine($header): string
    {
        return \implode(', ', $this->getHeader($header));
    }

    /**
     * @return static
     */
    public function withHeader($header, $value): MutableMessage
    {
        $value = $this->validateAndTrimHeader($header, $value);
        $normalized = strtolower($header);

        $this->headerNames[$normalized] = $header;
        $this->headers[$header] = $value;

        return $this;
    }

    /**
     * @return static
     */
    public function withAddedHeader(string $header, $value): MutableMessage
    {
        $this->setHeaders([$header => $value]);

        return $this;
    }

    /**
     * @return static
     */
    public function withoutHeader(string $header): MutableMessage
    {
        $normalized = strtolower($header);

        if (! isset($this->headerNames[$normalized])) {
            return $this;
        }

        $header = $this->headerNames[$normalized];

        unset($this->headers[$header], $this->headerNames[$normalized]);

        return $this;
    }

    public function getBody(): StreamInterface
    {
        return $this->stream ?? Stream::streamFor();
    }

    /**
     * @return static
     */
    public function withBody(StreamInterface $body): MutableMessage
    {
        $this->stream = $body;

        return $this;
    }

    private function setHeaders(array $headers): void
    {
        foreach ($headers as $header => $value) {
            $header = (string) $header;
            $value = $this->validateAndTrimHeader($header, $value);
            $normalized = strtolower($header);
            if (isset($this->headerNames[$normalized])) {
                $header = $this->headerNames[$normalized];
                $this->headers[$header] = \array_merge($this->headers[$header], $value);
            } else {
                $this->headerNames[$normalized] = $header;
                $this->headers[$header] = $value;
            }
        }
    }

    /**
     * Make sure the header complies with RFC 7230.
     *
     * Header names must be a non-empty string consisting of token characters.
     *
     * Header values must be strings consisting of visible characters with all optional
     * leading and trailing whitespace stripped. This method will always strip such
     * optional whitespace. Note that the method does not allow folding whitespace within
     * the values as this was deprecated for almost all instances by the RFC.
     *
     * header-field = field-name ":" OWS field-value OWS
     * field-name   = 1*( "!" / "#" / "$" / "%" / "&" / "'" / "*" / "+" / "-" / "." / "^"
     *              / "_" / "`" / "|" / "~" / %x30-39 / ( %x41-5A / %x61-7A ) )
     * OWS          = *( SP / HTAB )
     * field-value  = *( ( %x21-7E / %x80-FF ) [ 1*( SP / HTAB ) ( %x21-7E / %x80-FF ) ] )
     *
     * @see https://tools.ietf.org/html/rfc7230#section-3.2.4
     */
    private function validateAndTrimHeader($header, $values): array
    {
        if (! \is_string($header) || \preg_match("@^[!#$%&'*+.^_`|~0-9A-Za-z-]+$@D", $header) !== 1) {
            throw new \InvalidArgumentException('Header name must be an RFC 7230 compatible string');
        }

        if (! \is_array($values)) {
            // This is simple, just one value.
            if ((! \is_numeric($values) && ! \is_string($values)) || \preg_match("@^[ \t\x21-\x7E\x80-\xFF]*$@", (string) $values) !== 1) {
                throw new \InvalidArgumentException('Header values must be RFC 7230 compatible strings');
            }

            return [\trim((string) $values, " \t")];
        }

        if (empty($values)) {
            throw new \InvalidArgumentException('Header values must be a string or an array of strings, empty array given');
        }

        // Assert Non empty array
        $returnValues = [];
        foreach ($values as $v) {
            if ((! \is_numeric($v) && ! \is_string($v)) || \preg_match("@^[ \t\x21-\x7E\x80-\xFF]*$@D", (string) $v) !== 1) {
                throw new \InvalidArgumentException('Header values must be RFC 7230 compatible strings');
            }

            $returnValues[] = \trim((string) $v, " \t");
        }

        return $returnValues;
    }
}
