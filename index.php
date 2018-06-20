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
    public function handle(Request $request, Response $response, $next, $args)
    {
        print_r($args);
        $request->withAttribute('a', 1);
        $next();
    }
}

class Midtest2
{
    public function handle(Request $request, $next, $id)
    {
        $request->withAttribute('b', 2);
        // $request->withQueryParam('c', 'Hello World');
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

// $app->get('/api/user/:num', Ctrl::class . '>test')->middleWare(Midtest2::class);

// $app->get('/', function (Request $request, Response $response) {
//     return $response->html('hello world');
// });

// $app->route('/app', 'GET')
// (function(Request $request, Response $response) {
//     return $response->html('hello world');
// });

// $app->route('/app/:num', 'GET')(Ctrl::class, 'test');

$app->route('/app', function($route) {

    $route('/kiss/:num', ['GET'])
    (function(Request $request, Response $response, $id) {
        $attr = $request->getAttributes();
        return $response->json($attr);
    })->middleWare(Midtest::class);

});

$app->route('/app/:num', Request::METHOD_GET)
(function(Request $request, Response $response) {
    return $response->html('Hello World');
});


$app->start();
