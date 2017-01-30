<?php

namespace Container;

use PDO;
use PDOException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class DatabaseContainer
{
    /** @var \PDO $database */
    public static $database = null;
    /**
     * Database constructor.
     *
     * @param \Kernel $kernel
     *
     * @throws \Exception
     */
    function __construct($kernel)
    {
        $config = $kernel->get('config');

        // TODO: Only access database if required
        try {
            $dbSetup = $config['parameters']['database_driver'];
            $dbSetup .= ':host=' . $config['parameters']['database_host'];
            $dbSetup .= ';dbname=' . $config['parameters']['database_name'];

            $db = new PDO($dbSetup, $config['parameters']['database_user'], $config['parameters']['database_password']);
        } catch (PDOException $error) {
            throw new Exception('Cannot connect to database: ' . $error->getMessage());
        }

        DatabaseContainer::$database = $db;
        $kernel->set('database', $db);
    }
}
