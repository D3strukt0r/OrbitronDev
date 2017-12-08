<?php

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
define('DEV_ONLY_INTERNAL', false);

if (MAINTENANCE) {
    echo file_get_contents(__DIR__.'/../templates/error/maintenance.phtml');
    exit;
}

$loader = require __DIR__ . '/../vendor/autoload.php';

$kernel = new Kernel(Kernel::ENVIRONMENT_DEVELOPMENT);
