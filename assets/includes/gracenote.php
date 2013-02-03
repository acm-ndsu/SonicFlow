<?php
/**
 * Contains functions for interacting with data from the Gracenote data server.
 */

require('config.php');
require_once("./php-gracenote/Gracenote.class.php");

$gracenoteAPI = new Gracenote\WebAPI\GracenoteWebAPI($config["gn_id"], $config["gn_tag"], $config["gn_user"]);

/**
 * Gets the album art from gracenote for the specified artist and album.
 *
 */
function getArt($artist, $album,$size="small") {
	global $gracenoteAPI;

	$results = $gracenoteAPI->searchTrack($artist,"",$album, Gracenote\WEBAPI\GracenoteWebAPI::BEST_MATCH_ONLY);
	$picLoc = $results[0]["album_art_url"];
	$picLoc = str_replace("size=medium","size=$size",$picLoc);
	return $picLoc;
}
?>
