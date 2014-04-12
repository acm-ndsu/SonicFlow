<?php

/*
 * Contains functions that are related to querying the tinysong
 * database for Grooveshark song IDs.
 */
function findSongs($search) {
	global $config;
	$songs = array();
	$data = json_decode(shell_exec("python /var/www/SonicFlow/assets/includes/pygs/search.py \"" . addslashes($search) . "\""), true);
	foreach ($data as $s) {
		$songs[] = new Song($s['SongID'], $s['Name'], $s['ArtistName'], $s['AlbumName'], $s['ArtistID'], $s['AlbumID'], $s['CoverArtFilename'], $s['TrackNum'], $s['Popularity'], $s['EstimateDuration']);
	}
	return $songs;

}


?>
