<?php
namespace Layton;

use Iterator;

class MiddleWares implements Iterator
{
    /** @var integer $position */
    private $position = 0;

    /** @var array $middlewares */
    protected $middlewares;

    /** @var callback $nextFunc */
    protected $nextFunc;

    protected $args = [];
    protected $nextArgs = [];

    public function __construct(array $middlewares)
    {
        $this->middlewares = $middlewares;
        $this->position = 0;
    }

    public function current()
    {
        return $this->middlewares[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        return isset($this->middlewares[$this->position]);
    }

    /**
     * Set next function.
     * 
     * @param callback $nextFunc
     */
    public function withNextCall($nextFunc)
    {
        $this->nextFunc = $nextFunc;
    }

    public function withNextArgs($next, $args)
    {
        $this->args = $args;
        $nextArg = [$args];
        array_unshift($nextArg, $next);
        // print_r($args);exit();
        $this->nextArgs = $nextArg;
    }

    public function getNextArgs()
    {
        return $this->nextArgs;
    }

    public function getOriginArgs()
    {
        return $this->args;
    }
}
