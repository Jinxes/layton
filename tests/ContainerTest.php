<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Layton\Container;
use Layton\Exception\StoreReattachException;
use Layton\Exception\UnknownIdentifierException;

final class ContainerTest extends TestCase
{
    public function testContructWithoutEntry(): void
    {
        $container = new \Layton\Container();
        $this->assertEquals($container->_store, []);
    }

    public function testOffsetExists()
    {
        $container = new \Layton\Container();
        $container->offsetSet('test', 1);
        $result = $container->offsetExists('test');
        $this->assertTrue($result);
    }

    public function testOffsetNotExists()
    {
        $container = new \Layton\Container();
        $result = $container->offsetExists('test');
        $this->assertFalse($result);
    }

    public function testOffsetSet()
    {
        $container = new \Layton\Container();
        $container->offsetSet('test', 1);
        $this->assertEquals($container->_store, ['test' => 1]);
        $this->assertArrayNotHasKey('test', $container->_frozen);
    }

    /**
     * @expectedException \Layton\Exception\StoreReattachException
     */
    public function testOffsetSetWithException()
    {
        $container = new \Layton\Container();
        $container->offsetSet('test', 1);
        $container->offsetSet('test', 2);
    }

    public function testOffsetUnset()
    {
        $container = new \Layton\Container();
        $container->offsetSet('test', 1);
        $this->assertArrayHasKey('test', $container->_store);
        $container->offsetUnset('test');
        $this->assertArrayNotHasKey('test', $container->_store);
    }

    public function testOffsetGetWithScalar()
    {
        $container = new \Layton\Container();
        $container->offsetSet('test1', 1);
        $this->assertArrayHasKey('test1', $container->_store);
        $this->assertEquals(1, $container->_store['test1']);
        $result = $container->offsetGet('test1');
        $this->assertArrayHasKey('test1', $container->_frozen);
        $this->assertEquals($result, 1);
    }

    public function testOffsetGetWithFactory()
    {
        $container = new \Layton\Container();
        $func = function($c) {
            return $c;
        };
        $container->offsetSet('test1', $func);
        $this->assertArrayHasKey('test1', $container->_store);
        $this->assertInstanceOf(\Closure::class, $container->_store['test1']);
        $this->assertEquals($func, $container->_store['test1']);
        $result = $container->offsetGet('test1');
        $this->assertArrayHasKey('test1', $container->_frozen);
        $this->assertInstanceOf(\Layton\Container::class, $result);
        $this->assertEquals($result, $container);
    }

    /**
     * @expectedException \Layton\Exception\UnknownIdentifierException
     */
    public function testOffsetGetWithExpection()
    {
        $container = new \Layton\Container();
        $container->offsetSet('test1', 1);
        $container->offsetGet('test2');
    }

    public function testClear()
    {
        $container = new \Layton\Container();
        $container->offsetSet('test1', 1);
        $container->offsetSet('test2', 2);
        $container->offsetGet('test1');
        $container->clear();
        $this->assertEmpty($container->_store);
        $this->assertEmpty($container->_frozen);
    }

    public function testKeys()
    {
        $container = new \Layton\Container();
        $keys = $container->keys();
        $this->assertCount(0, $keys);
        $container->offsetSet('test1', 1);
        $container->offsetSet('test2', 2);
        $keys = $container->keys();
        $this->assertCount(2, $keys);
        $this->assertEquals($keys, ['test1', 'test2']);
    }

    public function test__GetWithScalar()
    {
        $container = new \Layton\Container();
        $container->offsetSet('test1', 1);
        $this->assertEquals(1, $container->test1);
    }

    public function test__Set()
    {
        $container = new \Layton\Container();
        $container->test = 1;
        $this->assertCount(1, $container->_store);
        $this->assertArrayHasKey('test', $container->_store);
        $this->assertEquals(1, $container->_store['test']);
    }
}
