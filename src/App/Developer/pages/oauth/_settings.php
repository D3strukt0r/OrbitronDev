<?php

error_reporting(E_ALL);

$client_id = 'Example OAuth2';
$client_secret = '1a2eac89bf7e8a01342c6fa1c6373cb7a0045e6f4aeac81d923eeacf63b3235a';
$redirect_uri = 'https://developer.orbitrondev.org/oauth/authorized.php';

$message = isset($_GET['message']) ? $_GET['message'] : '';
if(isset($message) && strlen($message) > 0) {
    echo '<p style="color: blue">' . $message . '</p>';
}
