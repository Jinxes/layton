<?php
namespace Layton\Services;

use ReflectionClass;
use ReflectionFunction;
use Layton\Container;
use Layton\Struct\DependentStruct;

class DependentService extends LaytonService
{
    public function __construct($container)
    {
        parent::__construct($container);
    }

    /**
     * @param string $class
     * 
     * @return DependentStruct
     */
    public function new($class)
    {
        if ($this->container->dependent_store->has($class)) {
            return $this->container->dependent_store[$class];
        }

        $dependentStruct = new DependentStruct($this->container, $class);
        $this->container->dependent_store[$class] = $dependentStruct;
        return $dependentStruct;
    }

    /**
     * reverse a function and save singleton
     * 
     * @param callback $subject
     * @param array $inherentParams
     * 
     * @return mixed
     */
    public function call($subject, $inherentParams = [])
    {
        $reflection = new ReflectionFunction($subject);
        $reflectionInstances = $this->getReflectionInstances($reflection, count($inherentParams));
        $instanceArray = [];
        foreach($reflectionInstances as $instance) {
            $instanceArray[] = $this->getAndRegistReflection($instance)->getInstance();
        }
        $params = array_merge($instanceArray, $inherentParams);
        return $reflection->invokeArgs($params);
    }

    /**
     * instantiation params
     * 
     * @param string
     * 
     * @return mixed
     * 
     * @throw \BadMethodCallException
     */
    public function getAndRegistReflection($paramType)
    {
        $_objectClass = $paramType->getClass();
        if (is_null($_objectClass)) {
            throw new \BadMethodCallException('The method parameters not exists.');
        }
        $class = $_objectClass->getName();

        if (array_key_exists($class, $this->container->dependent_store)) {
            return $this->container->dependent_store[$class];
        }
        return $this->new($class);
    }

    /**
     * get instances of params from Reflection
     * 
     * @param \Reflector $reflection
     * @param num $inherentNumber
     * 
     * @return array
     */
    public function getReflectionInstances($reflection, $inherentNumber)
    {
        $paramTypes = $reflection->getParameters();
        return array_slice($paramTypes, 0, count($paramTypes) - $inherentNumber);
    }
}