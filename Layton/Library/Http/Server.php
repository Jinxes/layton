<?php
namespace Layton\Library\Http;

use Layton\Library\Standard\ArrayBucket;

class Server extends ArrayBucket
{
    public function __construct()
    {
        foreach ($_SERVER as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @param  string $key
     *
     * @return string Normalized header field name
     */
    public static function normalizeKey($key)
    {
        return strtr(strtolower($key), '_', '-');
    }

    /**
     * @param string $key
     * @param array|string $value
     */
    public function add($key, $value)
    {
        $oldValues = $this->get($key, []);
        $newValues = is_array($value) ? $value : [$value];
        $this->set($key, array_merge($oldValues, array_values($newValues)));
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function set($key, $value)
    {
        parent::set(static::normalizeKey($key), $value);
    }

    /**
     * @param mixed $offset
     * @param mixed $default
     * 
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return parent::get(static::normalizeKey($key), $default);
    }

    /**
     * @param  string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return parent::has($this->normalizeKey($key));
    }

    /**
     * Remove a header field by normalize key
     * 
     * @param  string $key
     */
    public function remove($key)
    {
        parent::remove($this->normalizeKey($key));
    }
}