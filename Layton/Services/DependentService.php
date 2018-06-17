<?php
namespace Layton\Services;

use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;
use Layton\Container;
use Layton\Struct\DependentStruct;

class DependentService extends LaytonService
{
    public function __construct($container)
    {
        parent::__construct($container);
        $this->dependent_store = $this->container->dependent_store;
    }

    /**
     * @param string $class
     * 
     * @return DependentStruct
     */
    public function newClass($className)
    {
        if ($this->dependent_store->has($className)) {
            return $this->dependent_store[$className];
        }

        $dependentStruct = new DependentStruct($this->container, $className);
        $this->dependent_store[$className] = $dependentStruct;
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

        return $reflectionFunction->invokeArgs($params);
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

        if (array_key_exists($className, $this->dependent_store)) {
            return $this->dependent_store[$className];
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
}
