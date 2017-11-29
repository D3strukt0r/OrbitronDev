<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);

$client_id = 'OrbitronDev OAuth Guide';
$client_secret = 'b7841351296b348e35d3';
$redirect_uri = 'https://developer.orbitrondev.org/oauth/authorized.php';

$message = isset($_GET['message']) ? $_GET['message'] : '';
if(isset($message) && strlen($message) > 0) {
    echo '<p style="color: blue">' . $message . '</p>';
}
