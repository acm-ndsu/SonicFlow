<?php
/**
 * Contains functions for getting and interacting with data from the
 * Grooveshark data server.
 */

 /**
 * Gets song matches by querying the Grooveshark API for matches.
 *
 * @param search The phrase that is being searched for. Typically contains
 * the song, artist, and/or album.
 *
 * @return An array containing all of the song objects that matched the search
 * phrase.
 */
function getGroovesharkResults($search) {
	// TODO: Actually query the database.
	// This is a sample function that doesn't yet actually do anything.
	$results = array();
	$results[] = new Song(382349, "Dalai Lama", "Rammstein", "Reise, Reise");
	$results[] = new Song(3245, "Wo Bist Du?", "Rammstein", "Rosenrot");
	$results[] = new Song(25356, "Links 2,3,4", "Rammstein", "Mutter");
	return $results;
}
?>
