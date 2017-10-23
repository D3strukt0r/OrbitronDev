<?php

namespace Container;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Kernel;

class DoctrineContainer
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
    public function __construct($kernel)
    {
        $config = $kernel->get('config');

        $paths = array(
            __DIR__.'/../App/Account/Entity',
            __DIR__.'/../App/Blog/Entity',
            __DIR__.'/../App/Forum/Entity',
            __DIR__.'/../App/Store/Entity',
        );

        if ($kernel->environment == 'dev') {
            $isDevMode = true;
        } else {
            $isDevMode = false;
        }

        // the connection configuration
        $dbParams = array(
            'driver'   => 'pdo_mysql',
            'user'     => $config['parameters']['database_user'],
            'password' => $config['parameters']['database_password'],
            'dbname'   => $config['parameters']['database_name'],
        );

        $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
        $entityManager = EntityManager::create($dbParams, $config);

        $kernel->set('doctrine.entitymanager', $entityManager);
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     * @throws \Exception
     */
    public static function getEntityManager()
    {
        if (Kernel::getIntent()->has('doctrine.entitymanager')) {
            throw new \Exception('[System][Doctrine] A doctrine connection is required');
        }

        return Kernel::getIntent()->get('doctrine.entitymanager');
    }
}
