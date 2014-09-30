#!/usr/bin/php
<?php require('assets/includes/sonicflow.php');

if ($argc < 2) {
	echo "Usage: {$argv[0]} <position>\n\n";
	die;
}

removeSongAtPosition($argv[1], false);

?> 
