<?php

include '_settings.php';

$code = $_GET['code'];

?>
<pre>Authorization Code: <?php echo $code; ?></pre>
<a href="token.php?code=<?php echo $code; ?>">GET TOKEN</a>
<br />
<br />
<hr />
<pre>
<b>_settings.php</b>
<?php echo htmlentities(file_get_contents(__DIR__.'/_settings.php')); ?>
<br />
<b>authorized.php</b>
<?php echo htmlentities(file_get_contents(__DIR__.'/authorized.php')); ?>
</pre>
