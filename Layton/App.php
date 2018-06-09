<?php
namespace Layton;

/**
 * @access public 
 * @property Container $container
 */
class App
{
    public $container;

    public function __construct()
    {
        $this->container = new Container();
    }

    public function get()
    {
        return new Route('GET');
    }

    public function __get($func)
    {
        if (method_exists(static::class, $func)) {
            return $this->$func();
        }
        throw new \Exception('404 not found');
    }
}