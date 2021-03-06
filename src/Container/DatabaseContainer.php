<?php

namespace Container;

use PDO;
use PDOException;
use Symfony\Component\Config\Definition\Exception\Exception;

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
    public function __construct(\Kernel $kernel)
    {
        $config = $kernel->get('config');

        try {
            $dbSetup = $config['parameters']['database_driver'];
            $dbSetup .= ':host='.$config['parameters']['database_host'];
            $dbSetup .= ';dbname='.$config['parameters']['database_name'];

            $db = new PDO($dbSetup, $config['parameters']['database_user'], $config['parameters']['database_password']);
        } catch (PDOException $error) {
            throw new Exception('Cannot connect to database: '.$error->getMessage());
        }

        DatabaseContainer::$database = $db;
        $kernel->set('database', $db);
    }

    /**
     * Get the database object
     *
     * @return \PDO
     * @throws \Exception
     */
    public static function getDatabase()
    {
        if (is_null(self::$database)) {
            throw new \Exception('A database connection is required');
        }

        return self::$database;
    }
}
