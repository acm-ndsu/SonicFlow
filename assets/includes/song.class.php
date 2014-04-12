<?php

/**
 * A POD struct that encapsulates information about a single song. Includes the
 * song name, album, artist, and the Grooveshark song ID.
 */
class Song {
	public $id       = NULL;
	public $title    = NULL;
	public $artist   = NULL;
	public $album    = NULL;
	public $artistId = NULL;
	public $albumId  = NULL;
	public $arturl   = NULL;
	public $track	 = NULL;
	public $popularity	= NULL;
	public $duration = NULL;

	public function __construct($id, $title, $artist, $album, $artistId, $albumId, $arturl, $track, $pop, $duration) {
		$this->id       = $id;
		$this->title    = $title;
		$this->artist   = $artist;
		$this->album    = $album;
		$this->artistId = $artistId;
		$this->albumId  = $albumId;
		$this->arturl   = $arturl;
		$this->track	= $track;
		$this->popularity = $pop;
		$this->duration = $duration;
	}
}

?>
