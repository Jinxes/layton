<?php
namespace Layton\Library\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Layton\Library\Standard\Alternative;

class Request extends HttpMessage
{
    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PURGE = 'PURGE';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_TRACE = 'TRACE';
    const METHOD_CONNECT = 'CONNECT';

    /** @var string $method_override */
    protected $method_override;

    /** @var string $method */
    protected $method;

    /** @var array $params */
    protected $params;

    /** @var array $bodyParams */
    protected $bodyParams;

    /** @var array $queryParams */
    protected $queryParams;

    /** @var array $attributes */
    protected $attributes = [];

    /**
     * Create request.
     * 
     * @param Alternative $alternative
     * @param Headers $headers
     */
    public function __construct(Stream $body, Alternative $alternative)
    {
        $this->headers = Headers::create($alternative->getAllHeaders());
        $this->body = $body;
        $this->body->attach(fopen(Stream::WRAPPER_PHP_INPUT, 'r+'));
        $this->server = new Server();

        if ($this->headers->has('X_HTTP_METHOD_OVERRIDE')) {
            $this->method_override = $this->headers->get('X_HTTP_METHOD_OVERRIDE');
        }
    }

    /**
     * Get Http Method to lower case.
     * 
     * @return string
     */
    public function getMethod()
    {
        if ($this->method === null) {
            if (is_null($this->method_override)) {
                $this->method = $this->server->get('request-method');
            } else {
                $this->method = $this->method_override;
            }
            $this->method = strtoupper($this->method);
        }
        return $this->method;
    }

    /**
     * Set Http Method for header and request.
     * 
     * @param string $method
     */
    public function withMethod($method)
    {
        $this->headers->set('method', $method);
        $this->method = $method;
    }

    /**
     * if method is matched.
     * 
     * @param string $method
     * 
     * @return bool
     */
    public function isMethod($method)
    {
        if (strtoupper($method) === $this->getMethod()) {
            return true;
        }
        return false;
    }

    /**
     * is this a GET request?
     * 
     * @return bool
     */
    public function isGet()
    {
        return $this->isMethod(static::METHOD_GET);
    }

    /**
     * is this a DELETE request?
     * 
     * @return bool
     */
    public function isDelete()
    {
        return $this->isMethod(static::METHOD_DELETE);
    }

    /**
     * is this a POST request?
     * 
     * @return bool
     */
    public function isPost()
    {
        return $this->isMethod(static::METHOD_POST);
    }

    /**
     * Is this a PUT request?
     * 
     * @return bool
     */
    public function isPut()
    {
        return $this->isMethod(static::METHOD_PUT);
    }

    /**
     * Is this a HEAD request?
     * 
     * @return bool
     */
    public function isHead()
    {
        return $this->isMethod(static::METHOD_HEAD);
    }

    /**
     * Is this a OPTIONS request?
     * 
     * @return bool
     */
    public function isOptions()
    {
        return $this->isMethod(static::METHOD_OPTIONS);
    }

    /**
     * Is this a PATCH request?
     * 
     * @return bool
     */
    public function isPatch()
    {
        return $this->isMethod(static::METHOD_PATCH);
    }

    /**
     * Get all params of input body.
     * 
     * @return array
     */
    public function getBodyParams()
    {
        if (is_array($this->bodyParams)) {
            return $this->bodyParams;
        }

        $content = $this->getBody()->getContents();
        $contentType = $this->headers->get('content-type');
        if ($contentType === 'application/json') {
            $bodyParams = json_decode($content, true);
            $this->bodyParams = $bodyParams ? $bodyParams : [];
        } else {
            parse_str($content, $this->bodyParams);
        }
        return $this->bodyParams;
    }

    /**
     * Get one param of input body.
     * 
     * @param string $key
     * 
     * @return mixed
     */
    public function getBodyParam($key, $default = null)
    {
        $params = $this->getBodyParams();
        if (array_key_exists($key, $params)) {
            return $params[$key];
        }
        return $default;
    }

    /**
     * Set a body param.
     * 
     * @param string $key
     * @param string $value
     */
    public function withBodyParam($key, $value)
    {
        $params = $this->getBodyParams();
        $params[$key] = $value;
        $this->bodyParams = $params;
    }

    /**
     * Set many body param.
     * 
     * @param array $values
     */
    public function withBodyParams(array $values)
    {
        $params = $this->getBodyParams();
        foreach ($values as $key => $value) {
            $params[$key] = $value;
        }
        $this->bodyParams = $params;
    }

    /**
     * Get url params.
     * 
     * @return array
     */
    public function getQueryParams()
    {
        if (is_array($this->queryParams)) {
            return $this->queryParams;
        }

        if (empty($_SERVER['QUERY_STRING'])) {
            return [];
        }

        parse_str($_SERVER['QUERY_STRING'], $this->queryParams);

        return $this->queryParams;
    }

    /**
     * Set many query param.
     * 
     * @param array $values
     */
    public function withQueryParams(array $query)
    {
        $params = $this->getQueryParams();

        foreach ($query as $key => $value) {
            $params[$key] = $value;
        }

        $this->queryParams = $params;
    }

    /**
     * Set a query param.
     * 
     * @param string $key
     * @param string $value
     */
    public function withQueryParam($key, $value)
    {
        $params = $this->getQueryParams();
        $params[$key] = $value;
        $this->queryParams = $params;
    }

    public function getParams()
    {
        if (is_array($this->params)) {
            return $this->params;
        }

        $queryParams = $this->getQueryParams();
        if ($this->isPost() || $this->isPut()) {
            $bodyParams = $this->getBodyParams();
            $this->params = array_merge_recursive($queryParams, $bodyParams);
        } else {
            $this->params = $queryParams;
        }
        return $this->params;
    }

    /**
     * If is XHR request return True or return false.
     *
     * @return bool
     */
    public function isXhr()
    {
        return $this->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Get request content type.
     *
     * @return string|null
     */
    public function getContentType()
    {
        return $this->getHeader('Content-Type');
    }

    /**
     * Get request content length, if known.
     *
     * @return int|null
     */
    public function getContentLength()
    {
        $result = $this->getHeader('Content-Length');
    }

    /**
     * Set a user attribute.
     * 
     * @param string|int $key
     * @param mixed $value
     */
    public function withAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Set a list of user attributes.
     * 
     * @param array $attributes
     */
    public function withAttributes($attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->withAttribute($key, $value);
        }
    }

    /**
     * Get all user attributes
     * 
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get one user attribute
     * 
     * @return mixed
     */
    public function getAttribute($key)
    {
        $attributes = $this->getAttributes();
        return $attributes[$key];
    }
}
