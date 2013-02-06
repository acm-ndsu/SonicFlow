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
	$songs  = findSongs($search);
	$insertArtist = 'INSERT INTO artists VALUES ($1,$2)';
	$insertAlbum  = 'INSERT INTO albums VALUES ($1,$2,$3,$4)';
	$insertSong   = 'INSERT INTO songs VALUES ($1,$2,$3)';
	$songQuery    = 'SELECT id FROM songs WHERE id = $1';

	$dbconn = pg_connect(getConnectionString());
	$result = pg_prepare($dbconn,'checkSong',$songQuery);
	pg_prepare($dbconn,'artist',$insertArtist);
	pg_prepare($dbconn,'album', $insertAlbum);
	pg_prepare($dbconn,'song',  $insertSong);

	foreach ($songs as $song) {
		$result = pg_execute($dbconn,'checkSong',array($song->id));
		if (!pg_fetch_all($result)) {
			pg_execute($dbconn,'artist',array($song->artistId,$song->artist));
			pg_execute($dbconn,'album',array($song->albumId,$song->album,$song->artistId,$song->arturl));
			pg_execute($dbconn,'song',array($song->id,$song->title,$song->albumId));
		}	
	}	
	return findSongs($search);

}
?>
