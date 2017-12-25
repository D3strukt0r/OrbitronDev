<?php

namespace Container;

use Swift_Mailer;
use Swift_SmtpTransport;

class SwiftMailerContainer
{
    /**
     * Templating constructor.
     *
     * @param \Kernel $kernel
     */
    public function __construct(\Kernel $kernel)
    {
        $config = $kernel->get('config')['parameters'];

        $transport = (new Swift_SmtpTransport($config['mailer_host'], $config['mailer_port'], $config['mailer_security']))
            ->setUsername($config['mailer_user'])
            ->setPassword($config['mailer_password']);
        $kernel->set('mailer', new Swift_Mailer($transport));
    }
}
