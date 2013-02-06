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
		if (!itemIsInDb('songs',$song->id)) {
		{
			if (!itemIsInDb('albums',$song->albumId)) {
				if (!itemsIsInDb('artists',$song->artistId)) {
					addArtist($song->artistId);
				}
				$location = 'assets/albumart/' . $song->albumId . '.jpg';
				addAlbum($song->albumId,$song->albumName,$song->artistId,$location,$song->arturl);
			}
			addSong($song->id,$song->title,$song->albumId);
		}
	}	
	pg_free_result($result);

	$songs;
}

?>
