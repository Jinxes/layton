<?php
namespace Layton;

use Layton\Exception\NotFoundException;
use Layton\Traits\RouteMapingTrait;
use Layton\Services\RouteService;

/**
 * @access public 
 * @property Container $container
 * @property \Layton\Services\RouteService $routeService
 */
class App
{
    use RouteMapingTrait;

    public $container;
    public $routeService;

    public function __construct()
    {
        $this->container = new Container();
        $this->container->routeService = function($c) {
            return new RouteService($c);
        };
        $this->routeService = $this->container->routeService;
    }

    /**
     * Regist a HEAD http route.
     * 
     * @param string $method
     * @param string $match
     * @param callback $callable
     * 
     * @return Route
     */
    public function map($method, $match, $callback)
    {
        return $this->routeService->attach($method, $match, $callback);
    }

    public function group($match, $callback)
    {
        $group = new RouteGroup($this->container, $match);
        $callback($group);
        return $group;
    }

    /**
     * Match routers and call the callback.
     * 
     * @return array|string|Response
     */
    public function response()
    {
        $storage = $this->routeService->getStorage();
        foreach ($storage as $match => $route) {
            if ($this->matchHttpRequest($match) !== false) {
                print_r($this->getMiddleWareFromRoute($route));
                return \call_user_func_array($route->callback, [$this]);
            }
        }
        throw new NotFoundException();
    }

    /**
     * Get middle ware from Route
     * 
     * @param Route $route
     * 
     * @return array
     */
    private function getMiddleWareFromRoute(Route $route)
    {
        $groupMiddleWare = $route->group->middleWare;
        if ($groupMiddleWare) {
            return \array_merge($groupMiddleWare, $route->middleWare);
        }
        return $route->middleWare;
    }

    /**
     * Match route storage by request url and return params.
     * 
     * @param string $url
     * 
     * @return array|false
     */
    private function matchHttpRequest($pattern)
    {
        $pathInfo = empty($_SERVER['PATH_INFO']) ? '' : $_SERVER['PATH_INFO'];
        $pattern = $this->replacePatternKeyword($pattern);
        $regexp = '/^'. $pattern .'\/?$/';
        if (\preg_match($regexp, $pathInfo, $matched)) {
            \array_shift($matched);
            return $matched;
        }
        return false;
    }

    /**
     * Replace the regex key words and return.
     * 
     * @param string $pattern
     * 
     * @return string
     */
    private function replacePatternKeyword($pattern)
    {
        $regexKeywords = [
            '.' => '\\.',
            '*' => '\\*',
            '$' => '\\$',
            '[' => '\\[',
            ']' => '\\]',
            '(' => '\\(',
            ')' => '\\)'
        ];
        $pattern = str_replace(\array_keys($regexKeywords), \array_values($regexKeywords), $pattern);
        
        $customKeyword = [
            '/' => '\\/',
            ':str' => '([a-zA-Z0-9-_]+)',
            ':num' => '([0-9]+)',
            ':any' => '(.*+)'
        ];
        return str_replace(\array_keys($customKeyword), \array_values($customKeyword), $pattern);
    }
}
