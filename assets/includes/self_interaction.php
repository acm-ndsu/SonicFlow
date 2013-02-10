<?php

/**
 * Contains functions for interacting with data on the Sonic Flow data server.
 */


$dbconn = pg_connect(getConnectionString());
$db->prepare('songCheck', 'SELECT id FROM songs WHERE id = $1');
$db->prepare('artistCheck', 'SELECT id FROM artists WHERE id = $1');
$db->prepare('albumCheck', 'SELECT id FROM albums WHERE id = $1');
$db->prepare('artLocation', 'SELECT location FROM albums WHERE id = $1');
$db->prepare('addSong', 'INSERT INTO songs VALUES ($1,$2,$3)');
$db->prepare('addArtist', 'INSERT INTO artists VALUES ($1,$2)');
$db->prepare('addAlbum', 'INSERT INTO albums VALUES ($1,$2,$3,$4)');
$db->prepare('addToQueue', 'INSERT INTO queue (songid) VALUES ($1)');
$db->prepare('removeFromQueue', 'DELETE FROM queue WHERE id = $1');
$db->prepare('songs', 'SELECT DISTINCT songs.id as id, songs.title as title,artists.name as artist,albums.name as album, albums.id as albumid FROM songs,artists,albums WHERE songs.albumid = albums.id AND albums.artistid = artists.id AND (songs.title ILIKE $1 OR artists.name ILIKE $1) ORDER BY artist, album, title');
$db->prepare('getQueue', 'SELECT songs.id AS gid, songs.title as title, artists.name as artist, albums.name as album, albums.location as location FROM queue,songs,artists,albums WHERE queue.songid = songs.id AND songs.albumid = albums.id AND albums.artistid = artists.id ORDER BY queue.id');
$db->prepare('getNext', 'SELECT id,songid FROM queue ORDER BY id LIMIT 1');

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
	global $db;
	$db->execute('songs', array("%$search%"));
	$results = $db->getResults();
	$songs = array();
	if (count($results) >= 1) {
		foreach ($results as $s) {
			$songs[] = new Song($s["id"], $s["title"], $s["artist"], $s["album"], '', $s["albumid"], '');
		}
	}
	return $songs;
}

function addSongToQueue($id) {
	global $db;
	$db->execute("addToQueue", array($id));
}

function removeSongFromQueue($id) {
	global $db;
	$db->execute("removeFromQueue", array($id));
}

function getQueue() {
	global $db;
	$db->execute('getQueue', array());
	$results = $db->getResults();
	$songs = array();
	if (count($results) > 0) {
		foreach ($results as $s) {
			$songs[] = new Song($s['gid'], $s['title'], $s['artist'], $s['album'], '', '', $s['location']);
		}
	}
	return $songs;
}

function getNext() {
	global $db;
	$db->execute('getNext');
	$results = $db->getResults();
	return array($results[0]['id'], $results[0]['songid']);
}

function addSong($id,$title,$albumId) {
	global $db;
	$db->execute('addSong', array($id, $title, $albumId));
}

function addArtist($id, $name) {
	global $db;
	$db->execute("addArtist", array($id, $name));
}

function addAlbum($id, $name, $artistId, $artLoc, $artUrl) {
	global $db;
	if (strlen($artUrl) < 10) {
		$artLoc = 'assets/albumart/default.png';
	} else {
		file_put_contents($artLoc, file_get_contents($artUrl)); 
	}
	$db->execute("addAlbum", array($id, $name, $artistId, $artLoc));
}

function songIsInDb($id) {
	global $db;
	$db->execute('songCheck', array($id));
	return count($db->getResults() >= 1);
}

function albumIsInDb($id) {
	global $db;
	$db->execute('albumCheck', array($id));
	return count($db->getResults() >= 1);
}

function artistIsInDb($id) {
	global $db;
	$db->execute('artistCheck', array($id));
	return count($db->getResults() >= 1);
}

function getArtLoc($id) {
	global $db;
	$db->execute('artLocation', array($id));
	$results = $db->getResults();
	$location = $results[0]['location'];
	return $location;
}

?>
