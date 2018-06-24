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

class TestParam
{
    public function test1()
    {
        return 1;
    }
}

class Test
{
    public function test1(TestParam $tp)
    {
        return $tp->test1();
    }
}

function testFunc(TestParam $testParam, $num) {
    return $testParam->test1() + $num;
}

final class DependentServiceTest extends TestCase
{
    private function getService()
    {
        $container = new Container();
        $container->dependentService = new DependentService($container);
        $struct = new DependentStruct($container, Test::class);
        return $container->dependentService;
    }

    public function testNewClass()
    {
        $service = $this->getService();
        $result = $service->newClass(Test::class);
        $this->assertInstanceOf(DependentStruct::class, $result);
    }

    public function testInstance()
    {
        $service = $this->getService();
        $result = $service->instance(Test::class);
        $this->assertInstanceOf(Test::class, $result);
    }

    public function testCall()
    {
        $service = $this->getService();
        // 1 + 1 = 2
        $result = $service->call(testFunc::class, [1]);
        $this->assertEquals(2, $result);
    }

    public function testGetParams()
    {
        $container = new Container();
        $container->dependentService = new DependentService($container);
        $struct = new DependentStruct($container, Test::class);
        $params = $container->dependentService->getParams($struct->getReflection(), 'test1');
        $this->assertTrue(is_array($params));
        $this->assertCount(1, $params);
        $this->assertInstanceOf(TestParam::class, $params[0]);
    }
}
