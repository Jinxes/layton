<?php
namespace Layton\Struct;


/**
 * @property Closure|string $controller
 * @property string $method
 * @property array $args
 * @property array $middleWares
 */
class AcceptStruct
{
    public $controller;
    public $method;
    public $args;
    public $middleWares;

    public function __construct($controller, $method = '__invoke', $args = [], $middleWares = [])
    {
        $this->controller = $controller;
        $this->method = $method;
        $this->args = $args;
        $this->middleWares = $middleWares;
    }
}