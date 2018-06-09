<?php
namespace Layton;

class Route
{
    public $method;

    public function __construct($method)
    {
        $this->method = $method;
    }

    public function __get($name)
    {
        return ucwords($name) . 'Controller' . ':'. $this->method;
    }
}