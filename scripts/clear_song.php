#!/usr/bin/php
<?php include('assets/includes/sonicflow.php');

if ($argc < 2) {
	echo "Usage: {$argv[0]} <position>\n\n";
	die;
}

removeSongAtPosition($argv[1]);

?> 
