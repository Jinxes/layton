<?php
require('vendor/autoload.php');
spl_autoload_register(function($class) {
    $class = strtr($class, '\\', '/');
    require_once($class . '.php');
});

use Layton\App;
use Layton\Accept;
use Layton\Library\Http\Response;
use Layton\Library\Http\Request;
use Layton\Container;

class Test1
{
    public function test()
    {
        
    }
}

class Midtest
{
    public function handle(Request $request, Response $response, $next, $num)
    {
        return $response->json($request->getParams());
    }
}

class Midtest2
{
    public function handle(Request $request, $next, $num)
    {
        echo 2;
        $next();
    }
}

class Ctrl
{
    public function test(Request $request, Response $response, $id)
    {
        return $response->json($request->getParams());
    }
}


$app = new App();

$app->get('/api/user/:num', Ctrl::class . '>test')->middleWare(Midtest::class, Midtest2::class);


$app->get('/api/admin/:num', function (Request $request, Response $response, $num) use ($app) {
    return $response->json($request->getParams());
})->middleWare(Midtest::class);

(new Accept($app))->send();
