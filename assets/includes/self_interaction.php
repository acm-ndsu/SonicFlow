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
pg_prepare($dbconn,'removeFromQueue', 'DELETE FROM queue WHERE id = $1');
pg_prepare($dbconn,'artLocSong','SELECT location FROM albums WHERE id IN ('
	. 'SELECT albumid FROM songs where id = $1)');


// queries for limiting song requests

// gets the last timestamp of a song request given a song id.
pg_prepare($dbconn, 'getSongRequestTime', 'SELECT lastqueued FROM queuetimes ' .
		'WHERE songid = $1');

// returns 1 in the first row if a song has ever been requested or 0 if it has
// not, given a song id.
pg_prepare($dbconn, 'songWasRequested', 'SELECT COUNT(songid) AS requested ' .
		'FROM queuetimes WHERE songid = $1');

// sets the last queue time of a song to the current time, given a song id.
pg_prepare($dbconn, 'updateSongRequestTime', 'UPDATE queuetimes SET '.
		'lastqueued = $2 WHERE songid = $1');

// inserts a song queue time with the default timestamp of 0, given a song id.
pg_prepare($dbconn, 'addSongRequestTime', 'INSERT INTO queuetimes ' .
		'(songid, lastqueued, uid) VALUES ($1, 0, NULL)');

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
	$query .= 'songs.title ILIKE $1 OR artists.name ILIKE $1) ORDER BY artist, album, title';
	
	$result = pg_prepare($dbconn,"songs",$query);
	$result = pg_execute($dbconn,"songs",array("%$search%")) or die('Query failed: ' . pg_last_error());
	$results = array();
	while ($line = pg_fetch_array($result, null,PGSQL_ASSOC)) {
		$results[] = new Song($line["id"], $line["title"], $line["artist"], $line["album"],'',$line["albumid"],'');
	}

	pg_free_result($result);
	return $results;
}

define('R_SUCCESS', 0);
define('R_SONG_REQUEST_TOO_SOON', 1);
define('R_USER_REQUEST_TOO_SOON', 2);

// Returns whether the song was added
function addSongToQueue($id) {
	global $dbconn;
	$add;
	if (songRequestIsTooSoon($id)) {
		$add = R_SONG_REQUEST_TOO_SOON;
	} else {
		pg_execute($dbconn,"addToQueue",array($id)) or die('Insertion of song with ID: ' . $id . ' has failed!');
		updateSongRequestTime($id, time());
		$add = R_SUCCESS;
	}
	return $add;
}

function removeSongFromQueue($id) {
	global $dbconn;
	pg_execute($dbconn,"removeFromQueue",array($id)) or die('Deletion of song with ID: ' . $id . ' has failed!');
	return 0;
}
function getQueue() {
	global $dbconn;
	$query  = 'SELECT songs.id AS gid, songs.title as title, artists.name as artist, albums.name as album, albums.location as location FROM queue,songs,artists,albums ';
	$query .= 'WHERE queue.songid = songs.id AND songs.albumid = albums.id AND albums.artistid = artists.id ORDER BY queue.id';
	
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	$results = array();
	while ($line = pg_fetch_array($result, null,PGSQL_ASSOC)) {
		$results[] = new Song($line["gid"], $line["title"], $line["artist"], $line["album"], '','',$line["location"]);
	}

	pg_free_result($result);
	return $results;
}

function getNext() {
	// TODO: Make this better by not having while loop.
	global $dbconn;
	$query = 'SELECT queue.id AS queueid, queue.songid AS id,title,artists.name AS artist,albums.name AS album,location FROM queue,songs,artists,albums WHERE queue.songid = songs.id AND songs.albumid = albums.id AND artists.id = albums.artistid ORDER BY queueid LIMIT 1';
	$result = pg_query($query);
	$results = pg_fetch_all($result);
	$record = $results[0];
	return array($record['queueid'],new Song($record['id'],$record['title'],$record['artist'],$record['album'],'','',$record['location']));
}

function getLast() {
	global $dbconn;
	$query = 'SELECT queue.id AS queueid, queue.songid AS id,title,artists.name AS artist,albums.name AS album,location FROM queue,songs,artists,albums WHERE queue.songid = songs.id AND songs.albumid = albums.id AND artists.id = albums.artistid ORDER BY queueid DESC LIMIT 1';
	$result = pg_query($query);
	$results = pg_fetch_all($result);
	$record = $results[0];
	return array($record['queueid'],new Song($record['id'],$record['title'],$record['artist'],$record['album'],'','',$record['location']));
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

function getArtLocFromSong($id) {
	global $dbconn;
	$result = pg_execute($dbconn,'artLocSong',array($id));
	$results = pg_fetch_all($result);
	if (count($results) == 1) {
		return $results[0]['location'];
	} else {
		return 'assets/albumart/default.png'; // TODO: Put this in config?
	}
	
}

/**
 * Checks whether a song has been requested too soon.
 *
 * @param $id The ID of the song to check.
 *
 * @return True if the song was requested too soon ago; false otherwise.
 */
function songRequestIsTooSoon($id) {
	if (!songWasRequested($id)) {
		addSongRequestTime($id);
	}
	$lastRequest = getSongRequestTime($id);
	return (time() - $lastRequest < SONG_REQUEST_LIMIT);
}

/**
 * Executes a prepared statement and returns the result.
 *
 * @param $statement The name of the prepared statment to execute.
 * @param $params The parameters to the prepared statement in an array.
 *
 * @return The results as an array of associative arrays.
 */
function executeStatement($statement, $params) {
	global $dbconn;
	$r = pg_execute($dbconn, $statement, $params);
	$results = pg_fetch_all($r);
	pg_free_result($r);
	return $results;
}

/**
 * Checks when a song was last requested.
 *
 * @param $id The ID of the song to check.
 *
 * @return The timestamp of the last time that the given song was requested.
 */
function getSongRequestTime($id) {
	$results = executeStatement('getSongRequestTime', array($id));
	$time = $results[0]['lastqueued'];
	return (int) $time;
}

/**
 * Checks whether a song has ever been requested.
 *
 * @param $id The ID of the song to check.
 *
 * @return TRUE if the song has ever been requested; otherwise FALSE.
 */
function songWasRequested($id) {
	$results = executeStatement('songWasRequested', array($id));
	$requested = $results[0]['requested'];
	return ($requested == '1');
}

/**
 * Updates the last queue time of a song to the current time.
 *
 * @param $id The ID of the song to update.
 * @param $time The current timestamp.
 */
function updateSongRequestTime($id, $time) {
	executeStatement('updateSongRequestTime', array($id, $time));
}

/**
 * Inserts a song queue time record into the database. The default timestamp of
 * 0 is used for the last time requested, and the user reference is set to NULL.
 * 
 * @param $id The ID of the song to insert a queue time record for.
 */
function addSongRequestTime($id) {
	executeStatement('addSongRequestTime', array($id));
}

?>
