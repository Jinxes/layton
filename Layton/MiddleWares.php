<?php
namespace Layton;

use Iterator;

class MiddleWares implements Iterator
{
    /** @var integer $position */
    private $position = 0;

    /** @var array $middlewares */
    protected $middlewares;

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
}
