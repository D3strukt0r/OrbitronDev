<?php

include '_settings.php';

?>
<a href="https://account.orbitrondev.org/oauth/authorize?client_id=<?php echo $client_id; ?>&response_type=code&redirect_uri=<?php echo $redirect_uri; ?>&state=authorized">AUTHORIZE</a>
<br />
<br />
<hr />
<pre>
<b>_settings.php</b>
<?php echo htmlentities(file_get_contents(__DIR__.'/_settings.php')); ?>
<br />
<b>unauthorized.php</b>
<?php echo htmlentities(file_get_contents(__DIR__.'/unauthorized.php')); ?>
</pre>
