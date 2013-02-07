<?php

/**
 * Contains functions for interacting with data on the Sonic Flow data server.
 */


$dbconn = pg_connect(getConnectionString());
pg_prepare($dbconn,'songCheck',  'SELECT id FROM songs   WHERE id = $1');
pg_prepare($dbconn,'artistCheck','SELECT id FROM artists WHERE id = $1');
pg_prepare($dbconn,'albumCheck', 'SELECT id FROM albums  WHERE id = $1');
pg_prepare($dbconn,'artLocation','SELECT location FROM albums WHERE id = $1');
pg_prepare($dbconn,'addSong',    'INSERT INTO songs   VALUES ($1,$2,$3)');
pg_prepare($dbconn,'addArtist',  'INSERT INTO artists VALUES ($1,$2)');
pg_prepare($dbconn,'addAlbum',   'INSERT INTO albums  VALUES ($1,$2,$3,$4)');
pg_prepare($dbconn,'addToQueue', 'INSERT INTO queue (songid) VALUES ($1)');

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
	global $dbconn;
	$query  = 'SELECT DISTINCT songs.id as id, songs.title as title,artists.name as artist,albums.name as album, albums.id as albumid FROM songs,artists,albums ';
	$query .= 'WHERE songs.albumid = albums.id AND albums.artistid = artists.id AND (';
	$query .= 'songs.title ILIKE $1 OR artists.name ILIKE $1)';
	
	$result = pg_prepare($dbconn,"songs",$query);
	$result = pg_execute($dbconn,"songs",array("%$search%")) or die('Query failed: ' . pg_last_error());
	$results = array();
	while ($line = pg_fetch_array($result, null,PGSQL_ASSOC)) {
		$results[] = new Song($line["id"], $line["title"], $line["artist"], $line["album"],'',$line["albumid"],'');
	}

	pg_free_result($result);
	return $results;
}

function addSongToQueue($id) {
	global $dbconn;
	pg_execute($dbconn,"addToQueue",array($id)) or die('Insertion of song with ID: ' . $id . ' has failed!');
	return 0;
}

function getQueue() {
	global $dbconn;
	$query  = 'SELECT songs.id AS gid, songs.title as title, artists.name as artist, albums.name as album, albums.location as location FROM queue,songs,artists,albums ';
	$query .= 'WHERE queue.songid = songs.id AND songs.albumid = albums.id AND albums.artistid = artists.id';
	
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	$results = array();
	while ($line = pg_fetch_array($result, null,PGSQL_ASSOC)) {
		$results[] = new Song($line["gid"], $line["title"], $line["artist"], $line["album"], '','',$line["location"]);
	}

	pg_free_result($result);
	return $results;
}

function addSong($id,$title,$albumId) {
	global $dbconn;
	pg_execute($dbconn,"addSong",array($id,$title,$albumId)) or die('Query failed: ' . pg_last_error());
}

function addArtist($id,$name) {
	global $dbconn;
	pg_execute($dbconn,"addArtist",array($id,$name)) or die('Query failed: ' . pg_last_error());
}

function addAlbum($id,$name,$artistId,$artLoc,$artUrl) {
	global $dbconn;
	if (strlen($artUrl) < 10) {
		$artLoc = 'assets/albumart/default.png';
	} else {
		file_put_contents($artLoc,file_get_contents($artUrl)); 
	}	
	pg_execute($dbconn,"addAlbum",array($id,$name,$artistId,$artLoc)) or die('Query failed: ' . pg_last_error());
}

function songIsInDb($id) {
	global $dbconn;
	$result = pg_execute($dbconn,'songCheck',array($id));
	$found = false;
	if (pg_fetch_all($result)) {
		$found = true;
	}
	pg_free_result($result);
	return $found;
}

function albumIsInDb($id) {
	global $dbconn;
	$result = pg_execute($dbconn,'albumCheck',array($id));
	$found = false;
	if (pg_fetch_all($result)) {
		$found = true;
	}
	pg_free_result($result);
	return $found;
}

function artistIsInDb($id) {
	global $dbconn;
	$result = pg_execute($dbconn,'artistCheck',array($id));
	$found = false;
	if (pg_fetch_all($result)) {
		$found = true;
	}
	pg_free_result($result);
	return $found;
}

function getArtLoc($id) {
	global $dbconn;
	$result = pg_execute($dbconn,'artLocation',array($id));
	$location = '';
	while ($line = pg_fetch_array($result,null,PGSQL_ASSOC)) {
		 $location = $line['location'];
	}

	pg_free_result($result);
	return $location;
}

?>
