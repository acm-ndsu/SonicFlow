<?php

/*
 * Contains functions that are related to querying the tinysong
 * database for Grooveshark song IDs.
 */
function findSongs($search) {
	global $config;
	$url  = 'http://tinysong.com/s/';
//	echo $search . "<br />\n";
	$search = urlencode($search);
	$url .= $search . '?format=json&limit=15&key=';
//	echo $url . '<br />\n';
	$url .= $config['ts_key'];
	$curl = curl_init();
	curl_setopt($curl,CURLOPT_URL,$url);
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);
	$data = curl_exec($curl);
	$arr = json_decode($data,TRUE);
	$songs = array();
	foreach ($arr as $song) {
		$songs[] = new Song($song['SongID'],$song['SongName'],$song['ArtistName'],$song['AlbumName'],$song['ArtistID'],$song['AlbumID'],getArt($song['ArtistName'],$song['AlbumName'],$song['AlbumID']));
	}
	return $songs;
}

?>
