<?php
namespace Layton\Services;

use Layton\Route;

class RouteService extends LaytonService
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_HEAD = 'HEAD';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PATCH = 'PATCH';

    /** @var array $storage */
    protected $storage;

    /**
     * @param Container $container
     */
    public function __construct($container)
    {
        parent::__construct($container);
        $this->storage = [];
    }

    /**
     * Attach a route field to storage.
     * 
     * @param string $method Http method.
     * @param string $match
     * @param callback $callable
     * 
     * @return Route
     */
    public function attach($methods, $match, $callable)
    {
        if (is_string($methods)) {
            $methods = [$methods];
        }
        $route = new Route($methods, $callable);
        $this->storage[$match] = $route;
        return $route;
    }

    /**
     * Get the route storage.
     * 
     * @return array
     */
    public function getStorage()
    {
        return $this->storage;
    }
}
