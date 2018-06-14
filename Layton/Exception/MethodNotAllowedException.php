<?php
namespace Layton\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Abort 404 http code by default.
 */
class MethodNotAllowedException extends \Exception implements NotFoundExceptionInterface
{
    /**
     * @param string Exception message.
     */
    public function __construct($message = '405 method not allowed.')
    {
        parent::__construct($message, 405);
        http_response_code(405);
    }
}
