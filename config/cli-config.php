<?php

$loader = require __DIR__.'/../vendor/autoload.php';

$kernel = new Kernel(Kernel::ENVIRONMENT_DEVELOPMENT);

use Container\DoctrineContainer;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

return ConsoleRunner::createHelperSet(DoctrineContainer::getEntityManager());
