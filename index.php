<?php

require('vendor/autoload.php');
spl_autoload_register(function($class) {
    $class = strtr($class, '\\', '/');
    require_once($class . '.php');
});

use Layton\App;
use Layton\RouteService;


$app = new App();

$app->get('/user/:num', function($layton) {
    return 'hello user';
})->name('user');

$app->get('/admin', function($layton) {
    return $layton->test;
})->name('user');

print_r($app->response());
