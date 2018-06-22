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
    public function handle(Request $request, Response $response, $next)
    {
        $request->withAttribute('a', 1);
        $next();
    }
}

class Midtest2
{
    public function handle(Request $request, $next)
    {
        $request->withAttribute('b', 2);
        // $request->withQueryParam('c', 'Hello World');
        $next();
    }
}

/**
 * @Annotation
 */
class Ctrl
{
    public function test(Request $request, Response $response, $name)
    {
        return $response->template('temp', [
            'mess' => $name
        ]);
    }
}

class Controller
{
    public function test()
    {
        echo 'Hello World';
    }
}


$app = new App();

/**
 * 将返回的数据用 json 格式输出
 */
function jsonDecorator($callback) {
    return function(Response $response) use ($callback) {
        $data = $callback();
        return $response->json($data);
    };
}

function jsonDecorator2($callback) {
    return function() use ($callback) {
        $name = 'hello';
        return $callback($name);
    };
}

$app->get('/app/<name>', function(Request $request, Response $response) {
    $name = $request->getAttribute('name');
    return $response->html($name);
});

// $app->route('/app', ['GET'])
// (function() {
//     return ['hello' => 'world'];
// })->wrappers([jsonDecorator::class]);

// $app->route('/app/<id>/<name>', 'GET')->wrappers([jsonDecorator::class])
// (Ctrl::class, 'test');

// $app->route('/app', 'GET')('Controller', 'test');

$app->group('/app', function($route) {
    $route->group('/hello', function($route) {
        $route->get('/world', function() {
            echo 'Hello World';
        });
    });
});

// $app->get('/app/<id>/<name>', function(Request $request, Response $response) {
//     print_r($this);
//     return ['name' => '$name'];
// })
// ->middleWare(Midtest::class)
// ->wrappers([jsonDecorator::class, jsonDecorator2::class]);


// $app->get('/app', 'Controller>test');


$app->start();
