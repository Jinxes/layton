<?php
namespace Layton;

use Layton\Library\Http\Response;
use Layton\Exception\NotFoundException;

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
    }

    public function send()
    {
        $acceptStruct = $this->getAcceptStruct();
        if ($acceptStruct === false) {
            throw new NotFoundException();
        }

        if ($this->connectMiddleWare($acceptStruct->middleWares)) {
            if (\method_exists($acceptStruct->controller, '__invoke')) {
                return \call_user_func_array($acceptStruct->controller, [$this->app]);
            }
            return \call_user_func_array([
                new $acceptStruct->controller,
                $acceptStruct->method
            ], [$this->app]);
        }
    }

    /**
     * Run all middleware if failed return false.
     * 
     * @param array $middleWares
     */
    public function connectMiddleWare($middleWares)
    {
        foreach ($middleWares as $middleWare) {
            $ware = new $middleWare($this->app);
            $result = $ware();
            if ($result instanceof Response) {
                // TODO: call response sender
                return false;
            }
        }
        return true;
    }

    /**
     * Get accept struct from app
     * 
     * @return \Layton\Struct\AcceptStruct|false
     */
    public function getAcceptStruct()
    {
        return $this->app->accept();
    }
}