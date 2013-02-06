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
	$songs  = findSongs($search);
	foreach ($songs as $song) {
		if (!songIsInDb($song->id)) {		
			if (!albumIsInDb($song->albumId)) {
				if (!artistIsInDb($song->artistId)) {
					addArtist($song->artistId,$song->artist);
				}
				$location = 'assets/albumart/' . $song->albumId . '.jpg';
				addAlbum($song->albumId,$song->album,$song->artistId,$location,$song->arturl);
			}
			addSong($song->id,$song->title,$song->albumId);
		}
	}
	return $songs;
}

?>
