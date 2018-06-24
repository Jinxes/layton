<?php
declare(strict_types=1);
namespace Test\Struct;

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

final class DependentStructTest extends TestCase
{
    private static function getStruct()
    {
        $container = new Container();
        $container->dependentService = new DependentService($container);
        $struct = new DependentStruct($container, Test::class);
        return $struct;
    }

    public function testGetMethod(): void
    {
        $struct = $this->getStruct();
        $result = $struct->getMethod('test1');
        $this->assertInstanceOf(\ReflectionMethod::class, $result);
    }

    public function testGetClosure(): void
    {
        $struct = $this->getStruct();
        $result = $struct->getClosure('test1');
        $this->assertInstanceOf(\Closure::class, $result);
    }

    public function testGetInstance(): void
    {
        $struct = $this->getStruct();
        $result = $struct->getInstance();
        $this->assertInstanceOf(Test::class, $result);
    }

    public function testInjection()
    {
        $struct = $this->getStruct();
        $result = $struct->injection('test1');
        $this->assertEquals(1, $result);
    }

    public function testGetInjectionByClosure()
    {
        $struct = $this->getStruct();
        $closure = $struct->getClosure('test1');
        $result = $struct->injectionByClosure($closure, 'test1');
        $this->assertEquals(1, $result);
    }

    /**
     * @expectedException \Exception
     */
    public function testConstructWithException()
    {
        $container = new Container();
        $container->dependentService = new DependentService($container);
        $struct = new DependentStruct($container, 'Nothing');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInjectionWithException()
    {
        $struct = $this->getStruct();
        $result = $struct->injection('testNothing');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetInjectionByClosureWithException()
    {
        $struct = $this->getStruct();
        $result = $struct->injectionByClosure('nothing', 'test1');
    }
}
