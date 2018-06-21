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
    public function handle(Request $request, $next, $id)
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
        // print_r($that);
        return $response->template('temp', [
            'mess' => $name
        ]);
    }
}


$app = new App();

/**
 * 将返回的数据用 json 格式输出
 */
$jsonDecorator = static function($callback) {
    return function(Request $request, Response $response) use ($callback) {
        $name = $request->getAttribute('name');
        $data = $callback($name);
        return $response->json($data);
    };
};

$app->route('/app/<id>/<name>', 'GET')->wrappers([
    $jsonDecorator
])
(Ctrl::class, 'test');


$app->start();
