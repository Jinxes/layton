<?php
namespace Layton;

use Layton\Exception\NotFoundException;

/**
 * @access public 
 * @property Container $container
 * @property RouteService $routeService
 */
class App
{
    public $container;
    public $routeService;

    public $test = '123123';

    public function __construct()
    {
        $this->container = new Container();
        $this->container->routeService = function($c) {
            return new RouteService($c);
        };
        $this->routeService = $this->container->routeService;
    }

    /**
     * Regist a GET http route.
     * @param string $match
     * @param callback $callable
     * 
     * @return Route
     */
    public function get($match, $callback)
    {
        return $this->routeService->attach(RouteService::METHOD_GET, $match, $callback);
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
                return \call_user_func_array($route->callback, [$this]);
            }
        }

        throw new NotFoundException();
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
