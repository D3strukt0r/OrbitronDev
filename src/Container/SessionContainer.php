<?php

namespace Container;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class SessionContainer
{
    public static $settings = array(
        'save_to' => 'pdo', // pdo or file
    );

    /**
     * Templating constructor.
     *
     * @param \Kernel $kernel
     */
    public function __construct(\Kernel $kernel)
    {
        $sessionMetaDataBag = new MetadataBag('_meta', '0');
        $kernel->set('session.storage.metadata_bag', $sessionMetaDataBag);

        // Save session in file
        $sessionHandler = new NativeFileSessionHandler(realpath('./../var/sessions'));
        $kernel->set('session.handler', $sessionHandler);

        // Save session in database
        if (!$kernel->has('database')) {
            $kernel->loadDatabase();
        }
        $kernel->get('database')->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $sessionStorageDatabase = new PdoSessionHandler($kernel->get('database'), array(
            'db_table' => 'app_sessions',
        ));
        $kernel->set('session.handler.pdo', $sessionStorageDatabase);

        // Create cookie
        $handler = (self::$settings['save_to'] === 'file' ? $kernel->get('session.handler') :
                   (self::$settings['save_to'] === 'pdo' ? $kernel->get('session.handler.pdo') :
                   null));

        $sessionStorage = new NativeSessionStorage(array(
            'cookie_domain'   => 'orbitrondev.org',
            'cookie_httponly' => true,
            'gc_probability'  => 1,
        ), $handler, $kernel->get('session.storage.metadata_bag'));
        $kernel->set('session.storage.native', $sessionStorage);

        // Create session
        $session = new Session($kernel->get('session.storage.native'), new AttributeBag(), new FlashBag());
        $session->setName('_session');
        $session->start();
        $kernel->set('session', $session);
    }
}
