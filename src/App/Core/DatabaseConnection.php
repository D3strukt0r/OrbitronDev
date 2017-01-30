<?php

namespace App\Core;

/**
 * CONFIG SETUP
 * array(
 *     'driver'   => 'mysql',
 *     'hostname' => 'localhost',
 *     'username' => 'root',
 *     'password' => '',
 *     'database' => 'website',
 * );
 *
 *
 * Class DatabaseConnection
 *
 * @package App\Core
 */
class DatabaseConnection
{
    public static $database = null;

    /**
     * @throws \Exception if Could not connect to database
     */
    public static function createConnection()
    {
        $config = require './app/config/database.config.php';

        try {
            $connection = new \PDO($config['driver'] . ':host=' . $config['hostname'] . ';dbname=' . $config['database'],
                $config['username'], $config['password']);
        } catch (\PDOException $error) {
            throw new \Exception('[Database][ERROR] ' . $error->getMessage());
        }

        self::$database = $connection;
    }
}