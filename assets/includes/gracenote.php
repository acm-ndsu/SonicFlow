<?php
/**
 * Contains functions for interacting with data from the Gracenote data server.
 */

require('config.php');
require_once("./php-gracenote/Gracenote.class.php");

$gracenoteAPI = new Gracenote\WebAPI\GracenoteWebAPI($config["gn_id"], $config["gn_tag"], $config["gn_user"]);

function getArt($title, $artist, $album,$size="small") {
	global $gracenoteAPI;

	$results = $gracenoteAPI->searchTrack($artist,$title,$album, Gracenote\WEBAPI\GracenoteWebAPI::BEST_MATCH_ONLY);
	$picLoc = $results[0]["album_art_url"];
	$picLoc = str_replace("size=medium","size=$size",$picLoc);
	return $picLoc;
}
?>
