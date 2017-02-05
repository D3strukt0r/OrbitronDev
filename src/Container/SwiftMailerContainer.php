<?php

namespace Container;

use Exception;
use Swift_Mailer;
use Swift_SmtpTransport;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class SwiftMailerContainer
{
    /**
     * Templating constructor.
     *
     * @param \Kernel $kernel
     *
     * @throws Exception
     */
    function __construct($kernel)
    {
        $config = $kernel->get('config');

        $transport = Swift_SmtpTransport::newInstance($config['parameters']['mailer_host'], $config['parameters']['mailer_port'], $config['parameters']['mailer_security'])
            ->setUsername($config['parameters']['mailer_user'])
            ->setPassword($config['parameters']['mailer_password']);
        $kernel->set('mailer', new Swift_Mailer($transport));
    }
}