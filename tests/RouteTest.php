<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Layton\Container;
use Layton\Exception\StoreReattachException;
use Layton\Exception\UnknownIdentifierException;
use Layton\Services\DependentService;
use Layton\Library\Http\Response;
use Layton\Struct\DependentStruct;
use Layton\Services\RouteService;
use Layton\Route;
use Layton\RouteGroup;

final class RouteTest extends TestCase
{
    public function testName()
    {
        $callback = function() {
            return true;
        };
        $route = new Route([RouteService::METHOD_DELETE], $callback);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals($callback, $route->callback);
        $this->assertEquals($route->methods, [RouteService::METHOD_DELETE]);
    }

    public function testGroup()
    {
        $callback = function() {
            return true;
        };
        $container = new Container();
        $container->routeService = new RouteService($container);
        $group = new RouteGroup($container, null);
        $route = new Route([RouteService::METHOD_DELETE], $callback);
        $route = $route->setGroup($group);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals($group, $route->group);
    }

    public function testNamex()
    {
        $callback = function() {
            return true;
        };
        $container = new Container();
        $container->routeService = new RouteService($container);
        $group = new RouteGroup($container, null);
        $route = new Route([RouteService::METHOD_DELETE], $callback);
        $route = $route->name('name');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('name', $route->name);
    }

    public function testWrappers()
    {
        $callback = function() {
            return true;
        };
        $container = new Container();
        $container->routeService = new RouteService($container);
        $group = new RouteGroup($container, null);
        $route = new Route([RouteService::METHOD_DELETE], $callback);
        $route = $route->wrappers([1]);
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals([1], $route->decorators);
    }
}
