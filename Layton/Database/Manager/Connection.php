<?php
namespace Database\Manager;

use PDO;

/**
 * @property PDO $dbh
 */
class Connection extends PDO
{
    public static $connections = [];

    public static function set($name, $config)
    {
        return new static($name, $config);
    }

    public static function get($name)
    {
        if (array_key_exists($name, static::$connections)) {
            return static::$connections[$name];
        }
        throw new \Exception('Connection setting ' . $name . ' not found.');
    }

    public static function has($name)
    {
        return (bool)array_key_exists($name, static::$connections);
    }

    public function __construct($dsn, $username = '', $password = '', $driver_options = [])
    {
        parent::__construct($dsn, $username, $password, $driver_options);
    }

    /**
     * Prepares and executes a prepared statement
     * 
     * @param string $sql
     * @param array $parameters
     * 
     * @return \PDOStatement
     */
    public function execute($sql, array $parameters = [])
    {
        $sth = $this->prepare($sql);
        $sth->execute($parameters);
        return $sth;
    }
}
