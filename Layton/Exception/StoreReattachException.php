<?php
namespace Layton\Exception;

use Psr\Container\ContainerExceptionInterface;

class StoreReattachException extends \RuntimeException implements ContainerExceptionInterface
{
    /**
     * @param string $id Identifier of the store entry
     */
    public function __construct($id)
    {
        parent::__construct("Cannot override store offset \"$id\".");
    }
}
