# Layton Web Framework.

## Layton 是什么？

Layton 是一个基于 PHP 语言的网络框架  

Layton 提倡用最简单直接的方式进行开发，从零开始构建自己的应用程序  

Layton 提供一个极度自由的松耦合架构，最大限度的迎合使用者的思维方式和编程习惯。 

## Layton 提供了什么？

* 标准的 PSR-7 http 通信组件

* 高度自由的 URL 路由

* PSR-11 标准容器(PSR Container)

* 高效的依赖注入驱动

## 如何安装？

```php

composer require layton/layton dev-master

```

## Layton 的 Hello World

```php

use Layton\Library\Http\Response;

$app = new \Layton\App();

$app->get('/user/:num', function (Response $response, $id) {

    return $response->html('Hello World!');

});

$app->start();

```

## 获取更多帮助
https://github.com/Jinxes/layton/wiki

