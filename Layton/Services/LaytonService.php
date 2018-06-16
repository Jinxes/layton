<?php
namespace Layton\Services;

use Layton\Container;

class LaytonService
{
    /** @var Container $container */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
}