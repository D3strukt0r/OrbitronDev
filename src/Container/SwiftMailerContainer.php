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
        try {
            $config = Yaml::parse(file_get_contents($kernel->rootDir.'/app/config/parameters.yml'));
        } catch (ParseException $e) {
            throw new Exception("Unable to load Parameters. Unable to parse the YAML string: %s", $e->getMessage());
        }

        $transport = Swift_SmtpTransport::newInstance($config['parameters']['mailer_host'], $config['parameters']['mailer_port'], $config['parameters']['mailer_security'])
            ->setUsername($config['parameters']['mailer_user'])
            ->setPassword($config['parameters']['mailer_password']);
        $kernel->set('mailer', new Swift_Mailer($transport));
    }
}