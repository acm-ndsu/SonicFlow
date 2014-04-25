<?php

/**
 * Contains functions for interacting with data on the Sonic Flow data server.
 */


$dbconn = pg_connect(getConnectionString());
pg_prepare($dbconn,'songCheck',  'SELECT id FROM songs   WHERE id = $1');
pg_prepare($dbconn,'artistCheck','SELECT id FROM artists WHERE id = $1');
pg_prepare($dbconn,'albumCheck', 'SELECT id FROM albums  WHERE id = $1');
pg_prepare($dbconn,'artLocation','SELECT location FROM albums WHERE id = $1');
pg_prepare($dbconn,'addSong',    'INSERT INTO songs   VALUES ($1,$2,$3,$4,$5,$6)');
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
    $matchText = "songs.title || ' ' || artists.name || ' ' || albums.name";
    $query  = 'SELECT DISTINCT songs.id as id, songs.title as title,artists.name as artist,albums.name as album, artists.id AS artistid, songs.track AS track, songs.duration as duration, songs.popularity as popularity, albums.id as albumid, ts_rank_cd(to_tsvector(' . $matchText . '), query) AS rank FROM songs,artists,albums, to_tsquery($1) query ';
    $query .= 'WHERE songs.albumid = albums.id AND albums.artistid = artists.id AND ' . $matchText . ' @@ query ORDER BY rank DESC LIMIT 256';

    $search = str_replace(' ', ' | ', $search);
	$result = pg_prepare($dbconn,"songs",$query);
	$result = pg_execute($dbconn,"songs",array("%$search%")) or die('Query failed: ' . pg_last_error());
	$results = array();
	while ($line = pg_fetch_array($result, null,PGSQL_ASSOC)) {
		$results[] = new Song($line["id"], $line["title"], $line["artist"], $line["album"],$line['artistid'],$line["albumid"],'', $line['track'], $line['popularity'], $line['duration']);
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

/*
 * Gets the current songs from the queue table.
 *
 * @returns mixed array of Song objects that are currently in the queue.
 */
function getQueue() {
	global $dbconn;
	$query  = 'SELECT songs.id AS gid, songs.title as title, artists.name as artist, albums.name as album, '
			. 'albums.location as location, songs.track, songs.popularity, songs.duration FROM queue,songs,artists,albums '
			. 'WHERE queue.songid = songs.id AND songs.albumid = albums.id AND albums.artistid = artists.id ORDER BY queue.id';
	
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	$results = array();
	while ($line = pg_fetch_array($result, null,PGSQL_ASSOC)) {
		$results[] = new Song($line["gid"], $line["title"], $line["artist"], $line["album"], '','',$line["location"], $line['track'], $line['popularity'], $line['duration']);
	}

	pg_free_result($result);
	return $results;
}

/*
 * Gets the song that is at the front of the queue.
 *
 * @returns array The first element of the array is the queue id of the song; the second is a
 * 	Song object for the song at the front of the queue.
 */
function getNext() {
	global $dbconn;
	$query = 'SELECT queue.id AS queueid, queue.songid AS id,title,artists.id AS artist_id,albums.id AS album_id,songs.track,songs.popularity,songs.duration,artists.name AS artist,'
			. 'albums.name AS album,location FROM queue,songs,artists,albums '
			. 'WHERE queue.songid = songs.id AND songs.albumid = albums.id AND '
			. 'artists.id = albums.artistid ORDER BY queueid LIMIT 1';
	$result = pg_query($query);
	$results = pg_fetch_all($result);
	$record = $results[0];
	return array($record['queueid'],new Song($record['id'],$record['title'],$record['artist'],
			$record['album'],$record['artist_id'],$record['album_id'],$record['location'], $record['track'], $record['popularity'], $record['duration']));
}

/*
 * Gets the song that is at the back of the queue.
 *
 * @returns array The first element of the array is the queue id of the song; the second is a
 * 	Song object for the song at the back of the queue.
 */
function getLast() {
	global $dbconn;
	$query = 'SELECT queue.id AS queueid, queue.songid AS id,title,albums.id AS albums_id,artists.id AS artist_id, artists.name AS artist,'
			. 'albums.id AS album_id, albums.name AS album,songs.duration AS duration,songs.popularity AS popularity,songs.track AS track,'
            . 'location FROM queue,songs,artists,albums '
			. 'WHERE queue.songid = songs.id AND songs.albumid = albums.id AND artists.id = albums.artistid ORDER BY queueid DESC LIMIT 1';
	$result = pg_query($query);
	$results = pg_fetch_all($result);
	$record = $results[0];
	return array($record['queueid'],new Song($record['id'],$record['title'],$record['artist'],
			$record['album'],$record['artist_id'],$record['album_id'],$record['location'], $record['track'], $record['popularity'], $record['duration']));
}

/*
 * Adds a song to the database with the specified fieds.
 *
 */
function addSong($id,$title,$albumId,$track,$pop,$duration) {
	global $dbconn;
	pg_execute($dbconn,"addSong",array($id,$title,$albumId,$track,$pop,(int) $duration)) or die('Query failed: ' . pg_last_error());
}

/*
 * Adds an artist to the database with the specified fields.
 *
 */
function addArtist($id,$name) {
	global $dbconn;
	pg_execute($dbconn,"addArtist",array($id,$name)) or die('Query failed: ' . pg_last_error());
}

/*
 * Adds an album to the database with the specified fields.
 *
 */
function addAlbum($id,$name,$artistId,$artLoc,$artUrl) {
	global $dbconn;
    $file_headers = get_headers($artUrl);
    
    if($file_headers[0] == 'HTTP/1.1 404 Not Found' || strpos($artUrl, '70_album.png') !== false) {
		$artLoc = 'assets/albumart/default.png';
	}
    
	pg_execute($dbconn,"addAlbum",array($id,$name,$artistId,$artLoc)) or die('Query failed: ' . pg_last_error());
}

/*
 * Checks whether the song with the specified Id is in the database.
 * @return boolean true if the song is in the database; otherwise, false.
 */
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

/*
 * Checks whether the album with the specified Id is in the database.
 * @return boolean true if the album is in the database; otherwise, false.
 */
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

/*
 * Checks whether the artist with the specified Id is in the database.
 * @return boolean true if the artist is in the database; otherwise, false.
 */
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

/*
 * Gets the album art location for the album with the specified Id. 
 * @param integer the Id of the album for which to get the location.
 */
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

/*
 * Gets the location of the album art, depending on the specified song Id.
 * @param integer the Id of the song for which to find the album art location.
 */
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
