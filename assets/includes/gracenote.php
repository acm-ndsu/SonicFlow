<?php
/**
 * Contains functions for interacting with data from the Gracenote data server.
 */
require_once('./php-gracenote/Gracenote.class.php');

$gracenoteAPI = new Gracenote\WebAPI\GracenoteWebAPI($config['gn_id'], $config['gn_tag'], $config['gn_user']);

/**
 * Gets the album art from gracenote for the specified artist and album.
 *
 */
function getArtFromSong($song,$size='small') {
	if (isset($song->arturl)) {
		return $song->arturl;
	}
	return getArt($song->artist,$song->album,$song->albumId,$size);
}

function getArt($artistName,$albumName,$albumId,$size='medium') {
	global $gracenoteAPI;	
	$loc = getArtLoc($albumId);
	if ($loc != '') {
		return $loc;
	}	
	$results = $gracenoteAPI->searchTrack($artistName,'',$albumName, Gracenote\WEBAPI\GracenoteWebAPI::BEST_MATCH_ONLY);
	$picLoc = $results[0]['album_art_url'];
	$picLoc = str_replace('size=medium',"size=$size",$picLoc);
	return $picLoc;
}
?>
