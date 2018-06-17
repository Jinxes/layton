<?php
namespace Layton\Library\Http;

use Layton\Library\Standard\ArrayBucket;

class Headers extends ArrayBucket
{
    /**
     * Create the default values from httpMessages array.
     * 
     * @param array $httpMessages
     * 
     * @return static
     */
    public static function create(array $params = [])
    {
        $headers = new static();
        foreach ($params as $key => $value) {
            $headers->set($key, $value);
        }
        return $headers;
    }

    /**
     * @param  string $key
     *
     * @return string Normalized header field name
     */
    public static function normalizeKey($key)
    {
        $key = strtr(strtolower($key), '_', '-');
        if (strpos($key, 'http-') === 0) {
            $key = substr($key, 5);
        }

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
        $values = parent::get(static::normalizeKey($key), $default);

        if (is_array($values) && count($values) === 1) {
            return $values[0];
        }
        return $values;
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
