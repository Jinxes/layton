<?php
namespace Layton\Library\Standard;

use Layton\Interfaces\InteractiveInterface;

class ArrayBucket implements \Countable, InteractiveInterface
{
    /** @var array $data */
    protected $data = [];

    /**
     * @param string $key
     * @param string $value
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param mixed $offset
     * @param mixed $default
     * 
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * @param mixed $key
     * 
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @param mixed $key
     */
    public function remove($key)
    {
        unset($this->data[$key]);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Remove all items.
     */
    public function clear()
    {
        $this->data = [];
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * @param mixed $offset
     * 
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @param mixed $offset
     * 
     * @return mixed
     */
    public function offsetGet($offset, $default)
    {
        return $this->get($offset, $default);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}
