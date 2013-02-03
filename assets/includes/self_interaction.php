<?php

/**
 * Contains functions for interacting with data on the Sonic Flow data server.
 */
 
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
	// TODO: Actually query the database.
	// This is a sample function that doesn't yet actually do anything.
	$results = array();
	$results[] = new Song(100, "Stein um Stein", "Rammstein", "Reise, Reise");
	$results[] = new Song(101, "Stark", "Ich + Ich", "Vom selben Stern");
	$results[] = new Song(102, "Wave No Flag", "Mono Inc.", "After the War");
	return $results;
}

function getQueue() {
	global $config;

	$conn_string = "host=localhost dbname=%s user=%s password=%s";
	$conn_string = sprintf($conn_string,$config["pg_db"],$config["pg_user"],$config["pg_pass"]);
	$dbconn = pg_connect($conn_string);

	$query = "SELECT queue.id,queue.gid,songs.artist,songs.title,songs.album,queue.arturl FROM queue,songs WHERE queue.gid = songs.gid";
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());

	$results = array();
	while ($line = pg_fetch_array($result, null,PGSQL_ASSOC)) {
		$results[] = new Song($line["gid"], $line["title"], $line["artist"], $line["album"], $line["arturl"]);
	}

	// Free resultset
	pg_free_result($result);

	// Close connection
	pg_close($dbconn);

	return $results;
}
?>
