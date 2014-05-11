<?php

$config_file = file('config.cfg');
foreach ($config_file as $line) {
	$line = trim($line);
	if ($line != '' && $line{0} != '#') {
		list($key, $value) = explode('=', $line, 2);
		$config[$key] = $value;
	}
}


?>
