<?php
namespace Layton\Console;

use Layton\Exception\NotFoundException;
use Layton\Container;
use Layton\Services\DependentService;
use Layton\Struct\DependentStruct;


class Command
{
    private $commands = [];

    public function __construct()
    {
        $container = new Container();
        $container->dependentService = new DependentService($container);
        $this->dependentService = $container->dependentService;
    }

    /**
     * Regist a command line.
     * 
     * @param string $command
     * @param callback|string $callback
     */
    public function registor($command, $callback)
    {
        $this->commands[$command] = $callback;
    }

    /**
     * Match and run a command line.
     * 
     * @param array $argv The cli args
     */
    public function run($argv)
    {
        $args = $this->getArgs($argv);

        if (array_key_exists($args[1], $this->commands)) {
            $callback = $this->commands[$args[1]];
            $args = array_slice($args, 2);

            if (is_callable($callback)) {
                return $this->injectionClosure($callback, $args);
            }

            if (is_string($callback)) {
                if (strpos($callback, '>') !== false) {
                    list($controller, $method) = explode('>', $callback);
                    return $this->injectionClass($controller, $method, $args);
                }
                return $this->injectionClass($callback, '__invoke',  $args);
            }
        }
        throw new \Exception('command line callback not found.');
    }

    /**
     * Injection Types for Class
     * 
     * @param callback $controller
     * @param string $method
     * @param array $args
     * 
     * @return mixed
     */
    public function injectionClass($controller, $method, $args)
    {
        $dependentService = $this->dependentService;
        $refClass = $dependentService->newClass($controller);
        return $refClass->injection($method, $args);
    }

    /**
     * Injection Types for Closure.
     * 
     * @param callback $controller
     * @param array $args
     * 
     * @return mixed
     */
    public function injectionClosure($closure, $args = [])
    {
        return $this->dependentService->call($closure, $args);
    }

    /**
     * Get args from cli argv.
     * 
     * @param array $argv
     * 
     * @return array
     */
    private function getArgs($argv)
    {
        if (count($argv) < 2) {
            throw new \Exception('must input a command.');
        }
        return $argv;
    }
}
