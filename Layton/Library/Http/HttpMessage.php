<?php
namespace Layton\Library\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class HttpMessage implements MessageInterface
{
    /**
     * @var string
     */
    protected $version = '1.1';

    /**
     * Headers
     *
     * @var \Layton\Library\Http\Headers
     */
    protected $headers;

    /**
     * @var \Psr\Http\Message\StreamInterface
     */
    protected $body;

    /**
     * Retrieves the HTTP version.
     * 
     * @return string HTTP version.
     */
    public function getProtocolVersion()
    {
        return $this->version;
    }

    /**
     * Set the Http version.
     * 
     * @param string $version http protocol version.
     * 
     * @return static
     */
    public function withProtocolVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Get header options as array.
     * 
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers->all();
    }

    /**
     * Get one header as array.
     * 
     * @param string $name
     * 
     * @return array
     */
    public function getHeader($name)
    {
        return $this->headers->get($name, []);
    }

    /**
     * Checks if a header exists.
     * 
     * @param string $name Header name.
     * 
     * @return bool
     */
    public function hasHeader($name)
    {
        return $this->headers->has($name);
    }

    /**
     * Set a http header.
     * 
     * @param string $name
     * @param string|string[]
     * 
     * @return static
     */
    public function withHeader($name, $value)
    {
        $this->headers->set($name, $value);
        return $this;
    }

    /**
     * Retrieves a comma-separated string of the values
     * 
     * @param string $name Case-insensitive header field name.
     * 
     * @return string
     */
    public function getHeaderLine($name)
    {
        return implode(',', $this->headers->get($name, []));
    }

    /**
     * Add headers
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value)
    {
        $this->headers->add($name, $value);

        return $this;
    }

    /**
     * Remove header by name.
     * @param string $name Case-insensitive header field name to remove.
     * @return static
     */
    public function withoutHeader($name)
    {
        $this->headers->remove($name);

        return $this;
    }

    /**
     * Set http response body.
     * 
     * @param StreamInterface|string $body
     * 
     * @throws \RuntimeException
     * 
     * @return static
     */
    public function withBody($body)
    {
        if (\is_string($body)) {
            $stream = Stream::createFromWrapper(Stream::WRAPPER_PHP_TEMP);
            $stream->write($body);
            $this->body = $body;
        } elseif ($body instanceof StreamInterface) {
            $this->body = $body;
        } else {
            throw new \RuntimeException('Body must be instance of Stream.');
        }
        return $this;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody()
    {
        return $this->body;
    }
}