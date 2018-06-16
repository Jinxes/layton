<?php
namespace Layton\Struct;

use ReflectionClass;
use Layton\Container;

class DependentStruct
{
    /** @var array */
    private $singletons;

    /** @var array */
    private $reflections;

    /**
     * @param string $class
     */
    public function __construct($container, $class)
    {
        $this->container = $container;
        $this->dependent_store = $this->container->dependent_store;

        $refObject = new ReflectionClass($class);
        $params = $this->getParams($refObject, '__construct');
        $this->singletons = $refObject->newInstanceArgs($params);
        $this->reflections = $refObject;
    }

    /**
     * Get the Instance of registed object.
     * 
     * @return mixed
     */
    public function getInstance()
    {
        return $this->singletons;
    }

    /**
     * @param string $method
     * @param array $inherentParams
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function reverse($method, $inherentParams=[])
    {
        if (! $this->reflections->hasMethod($method)) {
            throw new \InvalidArgumentException('Method not exists.');
        }
        $instanceArray = $this->getParams($this->reflections, $method, count($inherentParams));
        $params = array_merge($instanceArray, $inherentParams);
        $refmethod = $this->reflections->getMethod($method);
        return $refmethod->invokeArgs(
            $this->singletons,
            $params
        );
    }

    /**
     * instantiation param list of method and save
     * 
     * @param ReflectionClass $refClass
     * @param string $method
     * @param int inherentNumber
     * 
     * @return array
     */
    private function getParams($reflectionClass, $method, $inherentNumber = 0)
    {
        $instanceArray = [];
        if (!$reflectionClass->hasMethod($method)) {
            return $instanceArray;
        }

        $reflection = $reflectionClass->getMethod($method);
        $reflectionInstances = $this->container->dependentService->getReflectionInstances($reflection, $inherentNumber);
        
        foreach($reflectionInstances as $instance) {
            $instanceArray[] = $this->container->dependentService->getAndRegistReflection($instance)->getInstance();
        }
        return $instanceArray;
    }
}