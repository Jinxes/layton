<?php
namespace Layton\Interfaces;


interface RouteConfigureInterface
{
    public function name(string $name);

    public function middleWare(array $middleWare);
}
