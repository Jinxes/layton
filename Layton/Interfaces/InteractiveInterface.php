<?php
namespace Layton\Interfaces;

interface InteractiveInterface
{
    public function set($key, $value);

    public function get($key, $default = null);

    public function has($key);

    public function remove($key);

    public function all();
}
