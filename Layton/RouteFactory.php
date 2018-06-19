<?php
namespace Layton;

class RouteFactory
{
    public $match;

    public $method;

    public $group;

    public function __construct($match, $methodsOrCallback, $routeService)
    {
        $this->match = $match;
        $this->method = $methodsOrCallback;
        $this->routeService = $routeService;
    }

    public function __invoke($controller, $method = null)
    {
        if (is_null($method)) {
            $route = $this->routeService->attach($this->method, $this->match, $controller);
        } else {
            $route = $this->routeService->attach($this->method, $this->match, $controller . '>' . $method);
        }
        // if ($this->group) {
        //     $route->setGroup($this->group);
        // }
        return $route;
    }

    public function setGroup($group)
    {
        $this->group = $group;
    }
}
