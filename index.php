<?php

require('vendor/autoload.php');
spl_autoload_register(function($class) {
    $class = strtr($class, '\\', '/');
    require_once($class . '.php');
});

use Layton\App;

class Abc
{
    public $counter = 0;

    public function add()
    {
        $this->counter++;
    }

    public function get()
    {
        return $this->counter;
    }
}

$app = new App();
$app->container->abc = 'abc';
echo $app->container->abc;
echo $app->container->abc;
echo $app->container->abc;
// $app->container->clear();
echo $app->container->abc;
$app->container->clear();
