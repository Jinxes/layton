<?php

require('vendor/autoload.php');
spl_autoload_register(function($class) {
    $class = strtr($class, '\\', '/');
    require_once($class . '.php');
});

use Layton\App;


$app = new App();

$app->group('/api', function($group) {
    $group->get('/user/:num', function($layton) {
        return 'hello user';
    })->name('user')->middleWare([4,5])->middleWare([6,7]);
})->middleWare([1,2,3]);

// $app->get('/user/:num', function($layton) {
//     return 'hello user';
// })->name('user');

$app->get('/admin', function($layton) {
    return 'hello admin';
})->name('user');

print_r($app->response());
