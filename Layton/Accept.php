<?php
namespace Layton;

use Layton\Library\Http\Response;
use Layton\Exception\NotFoundException;
use Layton\Library\Standard\DI;
use Layton\Struct\AcceptStruct;

/**
 * @property \Layton\Struct\AcceptStruct $acceptStruct
 * @property MiddleWares $middleWares
 */
class Accept
{
    /** @var App $app */
    protected $app;

    /**
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->dependentService = $this->app->container->dependentService;
        $this->acceptStruct = $this->app->accept();
        $this->middleWares = new MiddleWares($this->acceptStruct->middleWares);
    }

    /**
     * Call middlewares and the controller.
     * 
     * @return static
     */
    public function send()
    {
        if ($this->middleWares->valid()) {
            if (\method_exists($this->acceptStruct->controller, '__invoke')) {
                $next = $this->nextFactory(function() {
                    array_shift($this->acceptStruct->args);
                    return $this->injectionClosure();
                });
            } else {
                $next = $this->nextFactory(function() {
                    array_shift($this->acceptStruct->args);
                    return $this->injectionController();
                });
            }

            array_unshift($this->acceptStruct->args, $next);
            $response = $this->dependentService->newClass($this->middleWares->current())
                ->injection('handle', $this->acceptStruct->args);
        } else {
            if (\method_exists($this->acceptStruct->controller, '__invoke')) {
                $response = $this->injectionClosure();
            } else {
                $response = $this->injectionController();
            }
        }

        if ($response instanceof Response) {
            $this->sendByResponse($response);
        }
    }

    public function injectionController()
    {
        return $this->dependentService
            ->newClass($this->acceptStruct->controller)
            ->injection(
                $this->acceptStruct->method,
                $this->acceptStruct->args
            );
    }

    public function injectionClosure()
    {
        return $this->dependentService
            ->call(
                $this->acceptStruct->controller,
                $this->acceptStruct->args
            );
    }

    /**
     * Make the `next()` function for middlewares.
     * 
     * @param callback $callback For all middleware valid.
     */
    public function nextFactory($callback)
    {
        return function () use ($callback) {
            $this->middleWares->next();
            if ($this->middleWares->valid()) {
                $response = $this->dependentService
                    ->newClass($this->middleWares->current())
                    ->injection('handle', $this->acceptStruct->args);
            } else {
                $response = $callback();
            }

            if ($response instanceof Response) {
                $this->sendByResponse($response);
            }
        };
    }

    /**
     * Send response.
     * 
     * @param Response $response
     */
    public function sendByResponse(Response $response)
    {
        $response->sendHeaders();
        $response->sendBody();
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            static::closeOutputBuffers(0, true);
        }
    }

    /**
     * This method is part of the Symfony
     * {@link https://github.com/symfony/http-foundation/blob/master/Response.php#L1193} 
     * 
     * Cleans or flushes output buffers up to target level.
     *
     * Resulting level can be greater than target level if a non-removable buffer has been encountered.
     *
     * @final
     */
    public static function closeOutputBuffers($targetLevel, $flush)
    {
        $status = ob_get_status(true);
        $level = count($status);
        $flags = PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? PHP_OUTPUT_HANDLER_FLUSHABLE : PHP_OUTPUT_HANDLER_CLEANABLE);
        while ($level-- > $targetLevel && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }

    /**
     * Send a HTTP Response.
     * 
     * @param Response $response
     * 
     * @return mixed
     */
    public function httpAccept(Response $response)
    {
        return $response->send();
    }
}
