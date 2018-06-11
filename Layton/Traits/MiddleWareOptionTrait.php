<?php
namespace Layton\Traits;

trait MiddleWareOptionTrait
{
    /**
     * @param array $middleWare
     */
    public function middleWare(array $middleWare)
    {
        $this->middleWare = \array_merge($this->middleWare, $middleWare);
        return $this;
    }
}
