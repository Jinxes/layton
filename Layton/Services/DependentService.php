<?php
namespace Layton\Services;

use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;
use Layton\Container;
use Layton\Struct\DependentStruct;

class DependentService extends LaytonService
{
    /**
     * @param string $className
     * 
     * @return DependentStruct
     */
    public function newClass($className)
    {
        if ($this->container->has($className)) {
            return $this->container[$className];
        }

        $dependentStruct = new DependentStruct($this->container, $className);
        $this->container[$className] = $dependentStruct;
        return $dependentStruct;
    }

    /**
     * Regist and instance an object.
     * 
     * @param string $className
     * 
     * @return object
     */
    public function instance($className)
    {
        return $this->newClass($className)->getInstance();
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
        $reflectionFunction = new ReflectionFunction($subject);
        $reflectionParameters = $this->getReflectionParameters(
            $reflectionFunction,
            count($inherentParams)
        );

        $dependentInstances = [];
        foreach($reflectionParameters as $reflectionParameter) {
            $dependentInstances[] = $this->getDependentByParameter($reflectionParameter)
                ->getInstance();
        }
        $params = array_merge($dependentInstances, $inherentParams);
        $closure = $reflectionFunction->getClosure();
        return call_user_func_array($closure, $params);
    }

    /**
     * instantiation params
     * 
     * @param ReflectionParameter $paramType
     * 
     * @return object
     * 
     * @throw \BadMethodCallException
     */
    public function getDependentByParameter(ReflectionParameter $reflectionParameter)
    {
        $reflectionClass = $reflectionParameter->getClass();
        if (is_null($reflectionClass)) {
            throw new \BadMethodCallException('The method parameters not exists.');
        }
        $className = $reflectionClass->getName();

        if (array_key_exists($className, $this->container)) {
            return $this->container[$className];
        }
        return $this->newClass($className);
    }

    /**
     * get instances of params from Reflection
     * 
     * @param \Reflector $reflection
     * @param num $inherentNumber
     * 
     * @return ReflectionParameter[]
     */
    public function getReflectionParameters($reflection, $inherentNumber)
    {
        $reflectionParameters = $reflection->getParameters();
        return array_slice($reflectionParameters, 0, count($reflectionParameters) - $inherentNumber);
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
    public function getParams($reflectionClass, $method, $inherentNumber = 0)
    {
        $instances = [];
        if (!$reflectionClass->hasMethod($method)) {
            return $instances;
        }

        $reflection = $reflectionClass->getMethod($method);
        $reflectionParameters = $this->getReflectionParameters($reflection, $inherentNumber);

        foreach($reflectionParameters as $reflectionParameter) {
            $instances[] = $this->getDependentByParameter($reflectionParameter)->getInstance();
        }
        return $instances;
    }
}
