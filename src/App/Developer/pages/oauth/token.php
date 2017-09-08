<?php

include '_settings.php';

$code = $_GET['code'];

// Request TOKEN from AUTH_CODE
$ch = curl_init('https://account.orbitrondev.org/oauth/token');

curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=authorization_code&code='.$code.'&client_id='.$client_id.'&client_secret='.$client_secret.'&redirect_uri='.$redirect_uri);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$resultFormatted = json_decode($result, true);

curl_close($ch);

if(array_key_exists('error', $resultFormatted)) {
    header('Location: /unauthorized.php?message=Token could not be received');
}

?>
<pre><?php echo $result; ?></pre>
<a href="resource.php?token=<?php echo $resultFormatted['refresh_token']; ?>">GET RESOURCES</a>
<br />
<br />
<hr />
<pre>
<b>_settings.php</b>
<?php echo htmlentities(file_get_contents(__DIR__.'/_settings.php')); ?>
<br />
<b>token.php</b>
<?php echo htmlentities(file_get_contents(__DIR__.'/token.php')); ?>
</pre>
