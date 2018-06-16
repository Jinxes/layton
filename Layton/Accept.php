<?php
namespace Layton;

use Layton\Library\Http\Response;
use Layton\Exception\NotFoundException;
use Layton\Library\Standard\DI;

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
    }

    /**
     * Call middlewares and the controller.
     * 
     * @return static
     */
    public function send()
    {
        /** @var \Layton\Struct\AcceptStruct $acceptStruct */
        $acceptStruct = $this->app->accept();

        if ($this->connectMiddleWare($acceptStruct->middleWares, $acceptStruct->args)) {
            if (\method_exists($acceptStruct->controller, '__invoke')) {
                // return \call_user_func_array($acceptStruct->controller, [$this->app]);
                $response = $this->dependentService->call($acceptStruct->controller);
            } else {
                $response = $this->dependentService
                ->new($acceptStruct->controller)
                ->reverse(
                    $acceptStruct->method,
                    $acceptStruct->args
                );
            }

            $this->sendByResponse($response);
            return $this;
        }
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
    public static function closeOutputBuffers(int $targetLevel, bool $flush)
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

    /**
     * Run all middleware if failed return false.
     * 
     * @param array $middleWares
     */
    public function connectMiddleWare($middleWares, $args = [])
    {
        // $dependentService = $this->app->container->dependentService;
        foreach ($middleWares as $middleWare) {
            $result = $this->app->container->dependentService->new($middleWare)->reverse('main', $args);
            if ($result instanceof Response) {
                $this->sendByResponse($result);
                return false;
            }
        }
        return true;
    }
}
