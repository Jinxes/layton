<?php
namespace Layton\Traits;

use Layton\Services\RouteService;

trait RouteMapingTrait {
    /**
     * Regist a GET http route.
     * @param string $match
     * @param callback $callable
     * 
     * @return \Layton\Route
     */
    public function get($match, $callback)
    {
        return $this->map(RouteService::METHOD_GET, $match, $callback);
    }

    /**
     * Regist a POST http route.
     * @param string $match
     * @param callback $callable
     * 
     * @return \Layton\Route
     */
    public function post($match, $callback)
    {
        return $this->map(RouteService::METHOD_POST, $match, $callback);
    }

    /**
     * Regist a PUT http route.
     * @param string $match
     * @param callback $callable
     * 
     * @return \Layton\Route
     */
    public function put($match, $callback)
    {
        return $this->map(RouteService::METHOD_PUT, $match, $callback);
    }

    /**
     * Regist a DELETE http route.
     * @param string $match
     * @param callback $callable
     * 
     * @return \Layton\Route
     */
    public function delete($match, $callback)
    {
        return $this->map(RouteService::METHOD_DELETE, $match, $callback);
    }

    /**
     * Regist a PATCH http route.
     * @param string $match
     * @param callback $callable
     * 
     * @return \Layton\Route
     */
    public function patch($match, $callback)
    {
        return $this->map(RouteService::METHOD_PATCH, $match, $callback);
    }

    /**
     * Regist a HEAD http route.
     * @param string $match
     * @param callback $callable
     * 
     * @return \Layton\Route
     */
    public function head($match, $callback)
    {
        return $this->map(RouteService::METHOD_HEAD, $match, $callback);
    }
}
