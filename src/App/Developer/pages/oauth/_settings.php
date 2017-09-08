<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);

$client_id = 'oauth2_test';
$client_secret = 'd32d89dzh2379dzh23fzhd23p98hzfr23tfz2t90phzq4tgh4398qzh49qzg';
$redirect_uri = 'https://developer.orbitrondev.org/oauth/authorized.php';

$message = @$_GET['message'];
if(isset($message) && strlen($message) > 0) {
    echo '<p style="color: blue">' . $message . '</p>';
}
