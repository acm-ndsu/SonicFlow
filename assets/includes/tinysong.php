<?php

/*
 * Contains functions that are related to querying the tinysong
 * database for Grooveshark song IDs.
 */

function findSongs($search) {
	global $config;
	$url  = "http://tinysong.com/s/";
	$search = rawurlencode(utf8_encode($search));
	$url .= $search . "?format=json&limit=5&key=" . $config["ts_key"];
	$curl = curl_init();
	curl_setopt($curl,CURLOPT_URL,$url);
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);
	$data = curl_exec($curl);
	$arr = json_decode($data,TRUE);
	$songs = array();
	foreach ($arr as $song) {
		$songs[] = new Song($song["SongID"],$song["SongName"],$song["ArtistName"],$song["AlbumName"]);
	}
	return $songs;
}

?>
