<?php
namespace Layton;

use Layton\Exception\NotFoundException;
use Layton\Exception\MethodNotAllowedException;
use Layton\Traits\RouteMapingTrait;
use Layton\Services\RouteService;
use Layton\Struct\AcceptStruct;
use Layton\Services\DependentService;
use Layton\Library\Standard\ArrayBucket;
use Layton\Library\Http\Request;
use Layton\Library\Http\Response;

/**
 * @access public 
 * @property Container $container
 * @property RouteService $routeService
 * @property Request $request
 */
class App
{
    use RouteMapingTrait;

    public $container;
    public $routeService;

    public function __construct(array $config = [])
    {
        $this->container = new Container();

        $this->container->dependentService = new DependentService($this->container);
        $this->request = $this->container->dependentService->instance(Request::class);
        $this->response = $this->container->dependentService->instance(Response::class);

        $defaultConfig = new ArrayBucket();
        $defaultConfig->fill($config);
        $this->container->config = $defaultConfig;

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

    /**
     * Route group.
     * 
     * @param string $match
     * @param callback $callback
     * 
     * @return RouteGroup
     */
    public function group($match, $callback)
    {
        $group = new RouteGroup($this->container, $match);
        $callback($group);
        return $group;
    }

    /**
     * Match routers and call the callback.
     * 
     * @throws NotFoundException
     * @throws MethodNotAllowedException
     * 
     * @return AcceptStruct
     */
    public function accept()
    {
        $routeMethodSep = $this->container->config->get('Route-Method-Sep', '>');
        $storage = $this->routeService->getStorage();
        foreach ($storage as $match => $route) {
            $matched = $this->matchHttpRequest($match);
            if ($matched !== false) {
                if (!$this->request->isMethod($route->method)) {
                    throw new MethodNotAllowedException();
                }

                $middleWares = $this->getMiddleWareFromRoute($route);
                if (\is_string($route->callback)) {
                    if (strpos($route->callback, $routeMethodSep) !== false) {
                        list($controller, $method) = explode($routeMethodSep, $route->callback);
                        return new AcceptStruct($controller, $method, $matched, $middleWares);
                    }
                }

                return new AcceptStruct($route->callback, '__invoke', $matched, $middleWares);
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
        if (!$route->group) {
            return $route->middleWare;
        }
        $groupMiddleWare = $this->getMiddleWareFromGroup($route->group);
        return \array_merge($groupMiddleWare, $route->middleWare);
    }

    /**
     * Merge all middlewares from group and parent-group.
     * 
     * @param RouteGroup $group The first route group.
     * @param array $middleWareList Swap of middlewares.
     * 
     * @return array All middlewares.
     */
    private function getMiddleWareFromGroup(RouteGroup $group, array $middleWareList = [])
    {
        if ($group->middleWare) {
            $middleWareList = \array_merge($group->middleWare, $middleWareList);
        }
        if (\is_null($group->parentGroup)) {
            return $middleWareList;
        }
        return $this->getMiddleWareFromGroup($group->parentGroup, $middleWareList);
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
        $pathInfo = $this->request->server->get('path-info', '');
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
