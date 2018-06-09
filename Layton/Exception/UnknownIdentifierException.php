<?php
namespace Layton\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * The unknown identifier exception.
 */
class UnknownIdentifierException extends \InvalidArgumentException implements NotFoundExceptionInterface
{
    /**
     * @param string $id The unknown identifier
     */
    public function __construct($id)
    {
        parent::__construct("Identifier \"$id\" is not defined.");
    }
}
