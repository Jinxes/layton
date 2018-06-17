<?php
namespace Layton\Library\Http;

use Psr\Http\Message\ResponseInterface;


class Response extends HttpMessage
{
    /**
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * @var string
     */
    protected $reasonPhrase = '';

    /**
     * @var int
     */
    protected $status = 200;

    public function __construct(Headers $headers)
    {
        $this->status = Response::HTTP_OK;
        $this->headers = Headers::create();
        $this->body = Stream::createFromWrapper(Stream::WRAPPER_PHP_TEMP);
    }

    /**
     * Get current Http status code.
     * 
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * Set current Http status code.
     * 
     * @param $code integer
     * 
     * @return static
     */
    public function withStatusCode($code)
    {
        $this->status = $code;
        return $this;
    }

    /**
     * Sends HTTP headers.
     *
     * @return static
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
            return $this;
        }

        $statusCode = $this->getStatusCode();
        $protocolVersion = $this->getProtocolVersion();
        $statusText = $this->getReasonPhrase();
        foreach ($this->headers->allPreserveCase() as $name => $values) {
            foreach ($values as $value) {
                header($name.': '.$value, false, $statusCode);
            }
        }
        $statusHeader = sprintf('HTTP/%s %s %s', $protocolVersion, $statusCode, $statusText);
        header($statusHeader, true, $statusCode);

        return $this;
    }

    /**
     * Sends body for the current web response.
     *
     * @return $this
     */
    public function sendBody()
    {
        $this->body->rewind();
        echo $this->body->getContents();
        return $this;
    }

    /**
     * Retrieves the response charset.
     *
     * @final
     */
    public function getCharset(): ?string
    {
        return $this->charset;
    }

    /**
     * Sets the response charset.
     *
     * @return $this
     *
     * @final
     */
    public function setCharset(string $charset)
    {
        $this->charset = $charset;
        return $this;
    }

    public function getReasonPhrase()
    {
        if ($this->reasonPhrase) {
            return $this->reasonPhrase;
        }
        if (isset(static::$statusTexts[$this->status])) {
            return static::$statusTexts[$this->status];
        }
        return '';
    }

    public function isValidateable()
    {
        return $this->headers->has('Last-Modified') || $this->headers->has('ETag');
    }

    /**
     * Redirect.
     * 
     * @param  string|UriInterface $url
     * @param  int|null $status
     * @return static
     */
    public function redirect($url, $status = null)
    {
        $this->withHeader('Location', (string)$url);

        if (is_null($status) && $this->getStatusCode() === HttpMessage::HTTP_OK) {
            $status = 302;
        }
    }

    /**
     * Json response.
     * 
     * @param array|object $data
     */
    public function json($data, $status = HttpMessage::HTTP_OK, $encodingOptions = 0)
    {
        $json = json_encode($data, $encodingOptions);
        return $this->withBody($json)
            ->withStatusCode($status)
            ->withHeader('content-type', 'application/json');
    }
}
