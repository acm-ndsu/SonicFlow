<?php

	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	
	include("../grooveshark.class.php");

	$gs = new GrooveShark();
	$url = 'http://grooveshark.com/s/1980/2JS6Dg?src=5';

	# look up the son URL then get the download URL. 
	$song = $gs->getSongByUrl($url);
	$data = $gs->getSongById($song['SongID']);

	# push the file name in the header
	$filename = "{$song['ArtistName']} - {$song['Name']}.mp3";
	header("Content-Disposition: attachment; filename={$filename}");
#	passthru("wget -qO- {$data['url']}");


?>