<?php

/**
 * Contains functions for interacting with data on the Sonic Flow data server.
 */

function getConnectionString() {
	global $config;

	$conn_string = "host=localhost dbname=%s user=%s password=%s";
	$conn_string = sprintf($conn_string,$config["pg_db"],$config["pg_user"],$config["pg_pass"]);

	return $conn_string;
}

/**
 * Gets song matches from the Sonic Flow song ID database.
 *
 * @param search The phrase that is being searched for. Typically contains
 * the song, artist, and/or album.
 *
 * @return An array containing all of the song objects that matched the search
 * phrase.
 */
function getSonicFlowResults($search) {
	$query  = 'SELECT DISTINCT songs.id as id, songs.title as title,artists.name as artist,albums.name as album FROM songs,artists,albums ';
	$query .= 'WHERE songs.albumid = albums.id AND albums.artistid = artists.id AND (';
	$query .= 'songs.title ILIKE $1 OR artists.name ILIKE $1)';
		
	$dbconn = pg_connect(getConnectionString());
	$result = pg_prepare($dbconn,"songs",$query);
	$result = pg_execute($dbconn,"songs",array("%$search%")) or die('Query failed: ' . pg_last_error());
	$results = array();
	while ($line = pg_fetch_array($result, null,PGSQL_ASSOC)) {
		$results[] = new Song($line["id"], $line["title"], $line["artist"], $line["album"]);
	}

	pg_free_result($result);
	pg_close($dbconn);

	return $results;
}

function getQueue() {
	$query  = 'SELECT songs.title as title, artists.name as artist, albums.name as album FROM queue,songs,artists,albums ';
	$query .= 'WHERE queue.songid = songs.id AND songs.albumid = albums.id AND albums.artistid = artists.id';
	
	$dbconn = pg_connect(getConnectionString());
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	$results = array();
	while ($line = pg_fetch_array($result, null,PGSQL_ASSOC)) {
		$results[] = new Song($line["gid"], $line["title"], $line["artist"], $line["album"], $line["arturl"]);
	}

	pg_free_result($result);
	pg_close($dbconn);

	return $results;
}

function addSong($id) {
	$dbconn = pg_connect(getConnectionString());

	$query  = "INSERT INTO queue (id) VALUES ($1)";
	$result = pg_prepare($dbconn,"addSong",$query);
	$result = pg_execute($dbconn,"addSong",array($id)) or die('Query failed: ' . pg_last_error());

	pg_free_result($result);
	pg_close($dbconn);
}

?>
