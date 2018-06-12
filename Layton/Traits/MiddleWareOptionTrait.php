<?php
namespace Layton\Traits;

trait MiddleWareOptionTrait
{
    /**
     * @param array $middleWare
     */
    public function middleWare(...$middleWare)
    {
        $this->middleWare = \array_merge($this->middleWare, $middleWare);
        return $this;
    }
}
