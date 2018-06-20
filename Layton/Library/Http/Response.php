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
     * Retrieves the response charset.
     *
     * @final
     */
    public function getCharset()
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
    public function setCharset($charset)
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
     * @param integer $status
     * @param mixed $encodingOptions
     * 
     * @return static
     */
    public function json($data, $status = HttpMessage::HTTP_OK, $encodingOptions = 0)
    {
        $json = json_encode($data, $encodingOptions);
        $this->withStatusCode($status)
            ->withHeader('content-type', 'application/json')
            ->getBody()->write($json);
        return $this;
    }

    /**
     * Raw Response.
     * 
     * @param string $data
     * @param integer $status
     * 
     * @return static
     */
    public function text($data, $status = HttpMessage::HTTP_OK)
    {
        $this->withStatusCode($status)
            ->withHeader('content-type', 'text/plain')
            ->getBody()->write((string)$data);
        return $this;
    }

    /**
     * Html Response.
     * 
     * @param string $data
     * @param integer $status
     * 
     * @return static
     */
    public function html($data, $status = HttpMessage::HTTP_OK)
    {
        $this->withStatusCode($status)
            ->withHeader('content-type', 'text/html')
            ->getBody()->write((string)$data);
        return $this;
    }

    /**
     * Render a html template.
     * 
     * @param string $path
     * @param array $data
     * @param integer $status
     * 
     * @return static
     */
    public function template($path, $data = [], $status = HttpMessage::HTTP_OK)
    {
        $tempFile = $path . '.php';
        if (! is_file($tempFile)) {
            throw new \RuntimeException('Could not find the template file <' . $path . '>');
        }
        ob_start();
        extract($data);
        include($path . '.php');
        $content = ob_get_clean();
        return $this->html($content, $status);
    }
}
