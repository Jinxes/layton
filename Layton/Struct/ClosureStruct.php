<?php
namespace Layton\Struct;


/**
 * @property Container $container
 */
class ClosureStruct
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }
}
