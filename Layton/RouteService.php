<?php
namespace Layton;

class RouteService
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_HEAD = 'HEAD';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PATCH = 'PATCH';

    /** @var array $storage */
    protected $storage;

    /** @var Container $container */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
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
    public function attach(string $method, string $match, $callable)
    {
        $route = new Route($method, $callable);
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
