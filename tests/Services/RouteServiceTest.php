<?php
declare(strict_types=1);
namespace Test\Services;

use PHPUnit\Framework\TestCase;
use Layton\Container;
use Layton\Exception\StoreReattachException;
use Layton\Exception\UnknownIdentifierException;
use Layton\Services\DependentService;
use Layton\Library\Http\Response;
use Layton\Struct\DependentStruct;
use Layton\Services\RouteService;
use Layton\Route;

final class RouteServiceTest extends TestCase
{
    private function getService()
    {
        $container = new Container();
        return new RouteService($container);
    }

    public function testAttachWillReturnRoute()
    {
        $service = $this->getService();
        $result = $service->attach(RouteService::METHOD_GET, '/app', function() {
            return true;
        });
        $this->assertInstanceOf(Route::class, $result);
        $this->assertTrue(is_array($result->methods));
        $this->assertEquals($result->methods[0], RouteService::METHOD_GET);
    }

    public function testGetStorage()
    {
        $service = $this->getService();
        $storage = $service->getStorage();
        $this->assertTrue(is_array($storage));
    }
}
