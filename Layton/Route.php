<?php
namespace Layton;

use Layton\Interfaces\RouteConfigureInterface;
use Layton\Traits\MiddleWareOptionTrait;

class Route implements RouteConfigureInterface
{
    use MiddleWareOptionTrait;

    protected static $storage = [];

    protected $container;

    public $method;

    public $callback;
    
    public $name;

    public $middleWare = [];

    public $group = null;

    /**
     * @param string $method
     * @param callback $callback
     */
    public function __construct($method, $callback)
    {
        $this->method = $method;
        $this->callback = $callback;
    }
    
    /**
     * @param string $name
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param RouteGroup $group
     */
    public function setGroup(RouteGroup $group)
    {
        $this->group = $group;
        return $this;
    }
}
