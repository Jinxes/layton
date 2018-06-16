<?php
require('vendor/autoload.php');
spl_autoload_register(function($class) {
    $class = strtr($class, '\\', '/');
    require_once($class . '.php');
});

use Layton\App;
use Layton\Accept;
use Layton\Library\Http\Response;

class Test1
{
    public function test()
    {
        
    }
}

class Midtest
{
    public function main(Test1 $test, $args)
    {
        $test->test();
    }
}

class Midtest2
{
    public function main($args)
    {
        return false;
    }
}

class Ctrl
{
    public function test(Response $response, $arg)
    {
        return $response->withBody('asd');
    }
}


$app = new App();

$app->get('/api/user/:num', Ctrl::class . '>test')->middleWare(Midtest::class, Midtest2::class);

$app->get('/api/admin', function (Response $response) {
    return $response->withBody('admin');
});

(new Accept($app))->send();
