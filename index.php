<?php
require('vendor/autoload.php');
spl_autoload_register(function($class) {
    $class = strtr($class, '\\', '/');
    require_once($class . '.php');
});

use Layton\App;
use Layton\Accept;

class Midtest
{
    public function __invoke()
    {
        // return new \Layton\Library\Http\Response();
    }
}

class Midtest2
{
    public function __invoke()
    {
        return false;
    }
}

class Ctrl
{
    public function test()
    {
        echo 'test';
    }
}


$app = new App();

$app->get('/api/user', Ctrl::class . '::test')->middleWare(Midtest::class, Midtest2::class);


(new Accept($app))->send();
