#!/usr/bin/php
<?php require('assets/includes/sonicflow.php');

$q = getQueue();
while (!empty($q)) {
	removeSongAtPosition(0, false);
}

?> 
