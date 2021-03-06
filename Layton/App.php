<?php
namespace Layton;

use Layton\Exception\NotFoundException;
use Layton\Exception\MethodNotAllowedException;
use Layton\Traits\RouteMapingTrait;
use Layton\Services\RouteService;
use Layton\Services\DependentService;
use Layton\Library\Standard\ArrayBucket;
use Layton\Library\Http\Request;
use Layton\Library\Http\Response;
use Layton\Struct\DependentStruct;
use Layton\Struct\ClosureStruct;

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

    /**
     * This method is part of the Symfony
     * {@link https://github.com/symfony/http-foundation/blob/master/Response.php#L1193} 
     * 
     * Cleans or flushes output buffers up to target level.
     *
     * Resulting level can be greater than target level if a non-removable buffer has been encountered.
     */
    public static function closeOutputBuffers($targetLevel, $flush)
    {
        $status = ob_get_status(true);
        $level = count($status);
        $flags = PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? PHP_OUTPUT_HANDLER_FLUSHABLE : PHP_OUTPUT_HANDLER_CLEANABLE);
        while ($level-- > $targetLevel && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }

    public function __construct()
    {
        $this->container = new Container();

        $this->container->dependentService = new DependentService($this->container);
        $this->request = $this->container->dependentService->instance(Request::class);

        $this->container->routeService = function($c) {
            return new RouteService($c);
        };

        $this->container->closureStruct = function($c) {
            return new ClosureStruct($c);
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
    public function map($methods, $match, $callback)
    {
        return $this->routeService->attach($methods, $match, $callback);
    }

    public function route($match, $methodsOrCallback)
    {
        if ($methodsOrCallback instanceOf \Closure) {
            return $this->group($match, $methodsOrCallback);
        }
        return new RouteFactory($match, $methodsOrCallback, $this->routeService);
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
     */
    public function start()
    {
        $routeMethodSep = '>';
        $storage = $this->routeService->getStorage();
        foreach ($storage as $match => $route) {
            if (($matched = $this->matchHttpRequest($match)) !== false) {
                if (!in_array($this->request->getMethod(), $route->methods)) {
                    throw new MethodNotAllowedException();
                }
                $this->request->withAttributes($matched);

                $middleWares = new MiddleWares($this->getMiddleWareFromRoute($route));
                $decorators = $route->decorators;

                if (\is_string($route->callback)) {
                    if (strpos($route->callback, $routeMethodSep) !== false) {
                        list($controller, $method) = explode($routeMethodSep, $route->callback);
                        return $this->connectMiddlewares($controller, $method, $middleWares, $decorators);
                    }
                }

                return $this->connectMiddlewares($route->callback, '__invoke', $middleWares, $decorators);
            }
        }

        throw new NotFoundException();
    }

    /**
     * Get $next Closure for invoke class
     * 
     * @param callback $controller
     * @param array $args
     * @param MiddleWares $middleWares
     * 
     * @return callback
     */
    public function getInvokeMiddlewareNext($controller, MiddleWares $middleWares, $decorators)
    {
        return $this->nextClosure($middleWares, function() use ($controller, $decorators) {
            $controller = $this->closureBinder($controller);
            return $this->injectionClosure($controller, $decorators);
        });
    }

    /**
     * Get $next Closure for controller class
     * 
     * @param callback $controller
     * @param string $method
     * @param array $args
     * @param MiddleWares $middleWares
     * 
     * @return callback
     */
    public function getControllerMiddlewareNext($controller, $method, MiddleWares $middleWares, $decorators)
    {
        return $this->nextClosure($middleWares, function() use ($controller, $method, $decorators) {
            return $this->injectionClass($controller, $method, [], $decorators);
        });
    }

    /**
     * Call middlewares and controller.
     * 
     * @param callback $controller
     * @param string $method
     * @param MiddleWares $middleWares
     */
    public function connectMiddlewares($controller, $method, MiddleWares $middleWares, $decorators = [])
    {
        $isClosure = is_callable($controller);
        if ($middleWares->valid()) {
            if ($isClosure) {
                $next = $this->getInvokeMiddlewareNext($controller, $middleWares, $decorators);
            } else {
                $next = $this->getControllerMiddlewareNext($controller, $method, $middleWares, $decorators);
            }
            $middleWares->withNextArgs($next);
            $response = $this->injectionClass($middleWares->current(), 'handle', $middleWares->getNextArgs());
        } else {
            if ($isClosure) {
                $controller = $this->closureBinder($controller);
                $response = $this->injectionClosure($controller, $decorators);
            } else {
                $response = $this->injectionClass($controller, $method, [], $decorators);
            }
        }

        if ($response instanceof Response) {
            $this->sendByResponse($response);
        }
    }

    /**
     * Injection Types for Closure.
     * 
     * @param callback $controller
     * @param array $args
     * 
     * @return mixed
     */
    public function injectionClosure($controller, $decorators, $args = [])
    {
        foreach ($decorators as $decorator) {
            $controller = $decorator(
                $this->closureWrapper($controller)
            );
        }
        return $this->container->dependentService->call($controller, $args);
    }

    /**
     * Injection Types for Class
     * 
     * @param callback $controller
     * @param string $method
     * @param array $args
     * 
     * @return mixed
     */
    public function injectionClass($controller, $method, $args, $decorators = [])
    {
        /**
         * @var DependentService $dependentService
         * @var DependentStruct $refClass
         */
        $dependentService = $this->container->dependentService;
        $refClass = $dependentService->newClass($controller);
        $closure = $refClass->getClosure($method);
        foreach ($decorators as $decorator) {
            $closure = $decorator(
                $this->controllerWrapper($closure)
            );
        }
        return $dependentService->call($closure, $args);
    }

    /**
     * Get a decorator for decorators callback arg.
     * 
     * @param callback $controller
     * 
     * @return callback
     */
    public function closureWrapper($closure)
    {
        $dependentService = $this->container->dependentService;
        return function (...$args) use ($closure, $dependentService) {
            return $dependentService->call($closure, $args);
        };
    }

    /**
     * Get a decorator for decorators callback arg.
     * 
     * @param callback $controller
     * 
     * @return callback
     */
    public function controllerWrapper($closure)
    {
        $dependentService = $this->container->dependentService;
        return function (...$args) use ($closure, $dependentService) {
            return $dependentService->call($closure, $args);
        };
    }

    /**
     * Bind ClosureStruct this to $closure.
     * 
     * @param \Closure $closure
     * 
     * @return \Closure new closure
     */
    public function closureBinder($closure)
    {
        return \Closure::bind(
            $closure,
            $this->container->closureStruct,
            ClosureStruct::class
        );
    }

    /**
     * Make the `next()` function for middlewares.
     * 
     * @param callback $callback For all middleware valid.
     */
    public function nextClosure($middleWares, $callback)
    {
        return function () use ($middleWares, $callback) {
            $middleWares->next();
            if ($middleWares->valid()) {
                $args = $middleWares->getNextArgs();
                $response = $this->injectionClass($middleWares->current(), 'handle', $args);
            } else {
                $response = $callback();
            }

            if ($response instanceof Response) {
                $this->sendByResponse($response);
            }
        };
    }

    /**
     * Send response.
     * 
     * @param Response $response
     */
    public function sendByResponse(Response $response)
    {
        $this->sendHeaders($response);
        $this->sendBody($response);
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            static::closeOutputBuffers(0, true);
        }
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
        $pattern = $this->replaceKeyWorlds($pattern);
        \preg_match_all('/\<(.*?)\>/iu', $pattern, $attrKeys);
        \array_shift($attrKeys);
        $attrKeys = $attrKeys[0];

        $pathInfo = $this->request->server->get('path-info', '/');
        $pattern = $this->replacePatternKeyword($pattern);
        $regexp = '/^'. $pattern .'\/?$/';
        if (\preg_match($regexp, $pathInfo, $matched)) {
            \array_shift($matched);
            $result = [];
            foreach ($attrKeys as $num => $key) {
                $result[$key] = $matched[$num];
            }
            return $result;
        }
        return false;
    }

    private function replaceKeyWorlds($pattern)
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
        return str_replace(
            \array_keys($regexKeywords),
            \array_values($regexKeywords),
            $pattern
        );
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
        $customKeyword = [
            '/\//' => '\\/',
            '/\<(.*?)\>/' => '(.*?)'
        ];
        return preg_replace(\array_keys($customKeyword), \array_values($customKeyword), $pattern);
    }

    /**
     * Sends HTTP headers.
     *
     * @return static
     */
    public function sendHeaders(Response $response)
    {
        if (headers_sent()) {
            return $response;
        }

        $statusCode = $response->getStatusCode();
        $protocolVersion = $response->getProtocolVersion();
        $statusText = $response->getReasonPhrase();
        foreach ($response->headers->allPreserveCase() as $name => $values) {
            foreach ($values as $value) {
                header($name.': '.$value, false, $statusCode);
            }
        }
        $statusHeader = sprintf('HTTP/%s %s %s', $protocolVersion, $statusCode, $statusText);
        header($statusHeader, true, $statusCode);

        return $response;
    }

    /**
     * Sends body for the current web response.
     *
     * @return $this
     */
    public function sendBody(Response $response)
    {
        $body = $response->getBody();
        $body->rewind();
        echo $body->getContents();
        return $response;
    }
}
