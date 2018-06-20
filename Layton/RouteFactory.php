<?php
namespace Layton;

class RouteFactory
{
    public $match;

    public $methods;

    public $group;

    private $decorators = [];

    public function __construct($match, $methodsOrCallback, $routeService)
    {
        $this->match = $match;
        $this->methods = $methodsOrCallback;
        $this->routeService = $routeService;
    }

    public function __invoke($controller, $method = null)
    {
        if (is_null($method)) {
            $route = $this->routeService->attach($this->methods, $this->match, $controller);
        } else {
            $route = $this->routeService->attach($this->methods, $this->match, $controller . '>' . $method);
        }
        $route->setGroup($this->group);
        return $route;
    }

    public function decorators(...$decorators)
    {
        $this->decorators = $decorators;
        return $this;
    }

    public function setGroup($group)
    {
        $this->group = $group;
    }
}
