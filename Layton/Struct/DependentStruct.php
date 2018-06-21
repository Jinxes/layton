<?php
namespace Layton\Struct;

use ReflectionClass;
use Layton\Container;

/**
 * @property array singleton
 * @property ReflectionClass reflection
 */
class DependentStruct
{
    private $singleton;

    private $reflection;

    /**
     * @param Container $container
     * @param string $className
     */
    public function __construct(Container $container, $className)
    {
        $this->container = $container;
        $this->dependentService = $this->container->dependentService;

        $reflectionClass = new ReflectionClass($className);
        if (!$reflectionClass->isInstantiable()) {
            throw new \Exception('Can\'t instantiate ' . $className);
        }

        $params = $this->dependentService->getParams($reflectionClass, '__construct');
        $this->singleton = $reflectionClass->newInstanceArgs($params);
        $this->singleton->container = $container;
        $this->reflection = $reflectionClass;
    }

    /**
     * Get ReflectionMethod from ReflectionClass
     * 
     * @param string $name
     * 
     * @return ReflectionMethod
     */
    public function getMethod($name)
    {
        return $this->reflection->getMethod($name);
    }

    public function getClosure($method)
    {
        return $this->getMethod($method)->getClosure($this->getInstance());
    }

    /**
     * Get the Instance of registed object.
     * 
     * @return mixed
     */
    public function getInstance()
    {
        return $this->singleton;
    }

    /**
     * @param string $method
     * @param array $inherentParams
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function injection($method, $inherentParams=[])
    {
        if (! $this->reflection->hasMethod($method)) {
            throw new \InvalidArgumentException('Method not exists.');
        }
        $instances = $this->dependentService->getParams($this->reflection, $method, count($inherentParams));
        $params = array_merge($instances, $inherentParams);
        return call_user_func_array([$this->singleton, $method], $params);
    }

    /**
     * @param string $method
     * @param array $inherentParams
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function injectionByClosure($closure, $method, $inherentParams=[])
    {
        if (! ($closure instanceof \Closure)) {
            throw new \InvalidArgumentException('Method 0 must be a Closure.');
        }
        $instances = $this->dependentService->getParams($this->reflection, $method, count($inherentParams));
        $params = array_merge($instances, $inherentParams);
        return call_user_func_array($closure, $params);
    }
}
