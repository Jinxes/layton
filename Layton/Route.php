<?php
namespace Layton;

class Route
{
    protected static $storage = [];

    public $method;

    public $callback;
    
    public $name;

    public $middleWare = [];

    /**
     * @param string $method
     * @param callback $callback
     */
    public function __construct(string $method, $callback)
    {
        $this->method = $method;
        $this->callback = $callback;
    }
    
    /**
     * @param string $name
     */
    public function name(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param array $middleWare
     */
    public function middleWare(array $middleWare)
    {
        $this->middleWare = $middleWare;
        return $this;
    }
}