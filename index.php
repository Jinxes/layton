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
        $next();
    }
}

class Midtest2
{
    public function handle(Request $request, $next, $id)
    {
        $request->withQueryParam('c', 'Hello World');
        $next();
    }
}

class Ctrl
{
    public function test(Response $response, $id)
    {
        //$request->getParams()
        return $response->template('temp', [
            'mess' => 'Hello World'
        ]);
    }
}


$app = new App();

$app->get('/api/user/:num', Ctrl::class . '>test')->middleWare(Midtest2::class);

$app->get('/user/:num', function (Request $request, Response $response, $id) {
    return $response->json($request->getParams());
})->middleWare(Midtest2::class);

$app->start();
