<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);

$client_id = 'Oauth_Test';
$client_secret = '$2y$11$vU17IE6KTzW9n';
$redirect_uri = 'https://developer.orbitrondev.org/oauth/authorized.php';

$message = @$_GET['message'];
if(isset($message) && strlen($message) > 0) {
    echo '<p style="color: blue">' . $message . '</p>';
}
