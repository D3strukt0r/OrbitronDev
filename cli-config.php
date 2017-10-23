<?php

$loader = require __DIR__ . '/vendor/autoload.php';

define('APPLICATION_ENV', 'dev'); // Use: 'dev' or 'prod'
$kernel = new Kernel(APPLICATION_ENV);

use Container\DoctrineContainer;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

return ConsoleRunner::createHelperSet(DoctrineContainer::getEntityManager());
