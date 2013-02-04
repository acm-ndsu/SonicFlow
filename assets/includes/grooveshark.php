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
	return findSongs($search);

}
?>
