<?php
namespace Layton\Library\Http;

use Layton\Library\Standard\ArrayBucket;

class Headers extends ArrayBucket
{
    /**
     * @param  string $key
     *
     * @return string Normalized header field name
     */
    public static function normalizeKey($key)
    {
        $key = strtr(strtolower($key), '_', '-');

        return $key;
    }

    /**
     * Replace name to preserve case format.
     * 
     * @param string $name
     * 
     * @return string
     */
    public static function toPreserveCase($name)
    {
        $params = explode('-', $name);
        $newName = [];
        foreach ($params as $param) {
            $newName[] = ucwords($param);
        }
        return implode('-', $newName);
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
        if (!is_array($value)) {
            $value = [$value];
        }
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

    /**
     * Get all header fields with Preserve Case format.
     * 
     * @return array
     */
    public function allPreserveCase()
    {
        $fields = $this->all();
        $raw = [];
        foreach ($fields as $key => $field) {
            $raw[static::toPreserveCase($key)] = $field;
        }
        return $raw;
    }
}
