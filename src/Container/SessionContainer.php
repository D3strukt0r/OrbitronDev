<?php

namespace Container;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class SessionContainer
{
    /**
     * Templating constructor.
     *
     * @param \Kernel $kernel
     */
    function __construct($kernel)
    {
        $sessionHandler = new NativeFileSessionHandler(realpath('/../var/sessions'));
        $kernel->set('session.handler', $sessionHandler);

        $sessionMetaDataBag = new MetadataBag('_sf2_meta', '0');
        $kernel->set('session.storage.metadata_bag', $sessionMetaDataBag);

        $sessionStorage = new NativeSessionStorage(array(
            'cookie_domain'   => 'orbitrondev.org',
            'cookie_httponly' => true,
            'gc_probability'  => 1,
        ), $kernel->get('session.handler'), $kernel->get('session.storage.metadata_bag'));
        $kernel->set('session.storage.native', $sessionStorage);

        $session = new Session($kernel->get('session.storage.native'));
        $session->setName('_session');
        $session->start();
        $kernel->set('session', $session);
    }
}