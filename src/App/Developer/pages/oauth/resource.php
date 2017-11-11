<?php

include '_settings.php';

$refresh_token = $_GET['token'];

// Request new Access token from Refresh token
$ch = curl_init('https://account.orbitrondev.org/oauth/token');

curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=refresh_token&refresh_token='.$refresh_token.'&client_id='.$client_id.'&client_secret='.$client_secret.'&redirect_uri='.$redirect_uri);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$resultFormatted = json_decode($result, true);
$token = $resultFormatted['access_token'];
$refreshToken = $resultFormatted['refresh_token'];

curl_close($ch);


// Request API
$ch = curl_init('https://account.orbitrondev.org/oauth/resource');

curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'access_token='.$token);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$resultFormatted = json_decode($result, true);

curl_close($ch);


if(array_key_exists('error', $resultFormatted)) {
    header('Location: /unauthorized.php?message=Resource could not be received');
}

?>
<pre><?php var_dump($result); ?></pre>
<a href="resource.php?token=<?php echo $refreshToken; ?>">GET RESOURCES AGAIN</a>
<br />
<br />
<hr />
<pre>
<b>_settings.php</b>
<?php echo htmlentities(file_get_contents(__DIR__.'/_settings.php')); ?>
<br />
<b>resource.php</b>
<?php echo htmlentities(file_get_contents(__DIR__.'/resource.php')); ?>
</pre>
