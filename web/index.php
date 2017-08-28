<?php

use Symfony\Component\Debug\Debug;

// Force https if not accessing over localhost
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

// Add www. before domain
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    if (count(explode('.', $_SERVER['HTTP_HOST'])) < 2) {
        header('Location: https://www.' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }
} else {
    if (count(explode('.', $_SERVER['HTTP_HOST'])) < 3) {
        header('Location: https://www.' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }
}
// Add www. before domain (even if there is a sub-domain)
/*
if ($_SERVER['HTTP_HOST'] != 'localhost') {
    if (strpos($_SERVER['HTTP_HOST'], 'www.') === false) {
        header('Location: https://www.' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }
}
*/

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

define('EXEC_START', microtime(true));
define('MAINTENANCE', false);
define('APPLICATION_ENV', 'dev'); // Use: 'dev' or 'prod'

if (MAINTENANCE) {
    echo file_get_contents(__DIR__.'/../app/views/error/maintenance.phtml');
    exit;
}
/**
 * Display all errors when APPLICATION_ENV is development.
 */
if (APPLICATION_ENV == 'development' || APPLICATION_ENV == 'dev') {
    //Debug::enable(); // TODO: Use debug by Symfony instead of own code & Check if better
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('error_reporting', E_ALL);
    error_reporting(E_ALL);
}

$loader = require __DIR__ . '/../vendor/autoload.php';

$kernel = new Kernel(APPLICATION_ENV);
