<?php
namespace Layton;

use Layton\Interfaces\RouteConfigureInterface;
use Layton\Traits\MiddleWareOptionTrait;

class Route implements RouteConfigureInterface
{
    use MiddleWareOptionTrait;

    protected static $storage = [];

    protected $container;

    public $methods;

    public $callback;
    
    public $name;

    public $middleWare = [];

    public $group = null;

    public $decorators = [];

    /**
     * @param string $method
     * @param callback $callback
     */
    public function __construct(array $methods, $callback)
    {
        $this->methods = $methods;
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
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    public function setDecorators(...$decorators)
    {
        $this->decorators = $decorators;
    }
}
