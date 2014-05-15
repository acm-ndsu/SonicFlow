<?php

/*
 * Contains functions that are related to querying the tinysong
 * database for Grooveshark song IDs.
 */

//set up error handling
ini_set("log_errors", 1);
ini_set("error_log", "/tmp/php-error.log");


function findSongs($search) {
	global $config;
	$songs = array();
	$outputValue = -1;
	$returnValue = -1;
	exec("python /var/www/SonicFlow/assets/includes/pygs/search.py \"" . addslashes($search) . "\"", $returnValue,$outputValue);
	//$outputValue = implode($outputValue);
	
	$data = json_decode(implode($returnValue), true);


//test to see if the return is an array
	if(true)
	{
		//send output value to log
		error_log("Search results:\n" . implode($returnValue));
	}


	foreach ($data as $s) {
		$songs[] = new Song($s['SongID'], $s['Name'], $s['ArtistName'], $s['AlbumName'], $s['ArtistID'], $s['AlbumID'], $s['CoverArtFilename'], $s['TrackNum'], $s['Popularity'], $s['EstimateDuration']);
	}
	return $songs;

}


?>
