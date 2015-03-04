#!/usr/bin/php
<?php require('assets/includes/sonicflow.php');
do {
	$q = getQueue();
	print_r($q);
	removeSongAtPosition(0, false);
} while (!empty($q));

?> 
