<?php
namespace Layton\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Abort 404 http code by default.
 */
class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
    /**
     * @param string Exception message.
     */
    public function __construct($message = '404 not found.')
    {
        parent::__construct($message, 404);
        http_response_code(404);
    }
}
