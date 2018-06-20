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

        $params = $this->getParams($reflectionClass, '__construct');
        $this->singletons = $reflectionClass->newInstanceArgs($params);
        $this->singletons->container = $container;
        $this->reflections = $reflectionClass;
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
    public function injection($method, $inherentParams=[])
    {
        if (! $this->reflections->hasMethod($method)) {
            throw new \InvalidArgumentException('Method not exists.');
        }
        $instances = $this->getParams($this->reflections, $method, count($inherentParams));
        $params = array_merge($instances, $inherentParams);
        return call_user_func_array([$this->singletons, $method], $params);
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
        $instances = [];
        if (!$reflectionClass->hasMethod($method)) {
            return $instances;
        }

        $reflection = $reflectionClass->getMethod($method);
        $reflectionParameters = $this->dependentService->getReflectionParameters($reflection, $inherentNumber);

        foreach($reflectionParameters as $reflectionParameter) {
            $instances[] = $this->dependentService->getDependentByParameter($reflectionParameter)->getInstance();
        }
        return $instances;
    }
}
