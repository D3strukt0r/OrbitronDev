<?php

/**
 * Force https if not accessing over localhost
 */
//if ($_SERVER['HTTP_HOST'] != 'localhost') {
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }
    if(strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
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
    /*if (strpos($_SERVER['HTTP_HOST'], 'www.') === false) {
        header('Location: https://www.' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }*/
//}

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

define('EXEC_START', microtime(true));
define('MAINTENANCE', false);
define('APPLICATION_ENV', 'development');

if (MAINTENANCE) {
    ?>
    <html>
        <head>
            <title>Maintenance</title>
            <meta name="viewport"
                  content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
            <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"
                  type="text/css" />
            <style type="text/css">
                hr {
                    margin-top: 20px;
                    margin-bottom: 20px;
                    border: 0;
                    border-top: 1px solid #eee;
                }

                .col-center {
                    float: none;
                    margin: 0 auto;
                }

                .error-message {
                    margin-top: 50px;
                }

                body {
                    background-color: darkgray;
                }
            </style>
        </head>
        <body>
            <div class="col-sm-6 col-lg-6 col-center error-message">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <b>The website is currently under maintenance</b>
                    </div>
                    <div class="panel-body">
                        <p>We are acutally improving our website.</p>
                        <p>Please be patient</p>
                        <hr />
                        <i>Normally we wont take long please just wait a minute and try it then again. If this page is
                           persistant, please contact an Administrator.</i>
                    </div>
                </div>
            </div>
        </body>
    </html>
    <?php
    exit;
}
/**
 * Display all errors when APPLICATION_ENV is development.
 */
if (APPLICATION_ENV == 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('error_reporting', E_ALL);
    error_reporting(E_ALL);
}

$loader = require __DIR__ . '/../vendor/autoload.php';

$kernel = new Kernel(APPLICATION_ENV);
