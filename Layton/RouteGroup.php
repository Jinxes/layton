<?php
namespace Layton;

use Layton\Interfaces\RouteConfigureInterface;
use Layton\Traits\RouteMapingTrait;
use Layton\Traits\MiddleWareOptionTrait;

class RouteGroup implements RouteConfigureInterface
{
    use RouteMapingTrait,
        MiddleWareOptionTrait;

    public $base = '';
    public $name;
    public $middleWare = [];
    public $parentGroup = null;

    public function __construct($container, $base)
    {
        $this->container = $container;
        $this->base = $base;
        $this->routeService = $this->container->routeService;
    }

    /**
     * Regist a http route.
     * 
     * @param string $method
     * @param string $match
     * @param callback $callable
     * 
     * @return Route
     */
    public function map($method, $match, $callback)
    {
        $match = $this->base . $match;
        /** @var Route $route */
        $route = $this->routeService
            ->attach($method, $match, $callback)
            ->setGroup($this);
        return $route;
    }

    public function __invoke($match, $methodsOrCallback)
    {
        if ($methodsOrCallback instanceOf \Closure) {
            return $this->group($match, $methodsOrCallback);
        }
        $match = $this->base . $match;
        $routeFactory = new RouteFactory($match, $methodsOrCallback, $this->routeService);
        $routeFactory->setGroup($this);
        return $routeFactory;
    }

    public function group($match, $callback)
    {
        $match = $this->base . $match;
        $group = new static($this->container, $match);

        $group->parentGroup = $this;
        $callback($group);

        return $group;
    }

    /**
     * @param string $name
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }
}