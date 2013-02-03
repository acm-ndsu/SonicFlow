<?php

/**
 * A POD struct that encapsulates information about a single song. Includes the 
 * song name, album, artist, and the Grooveshark song ID.
 */
class Song {
	public $id = NULL;
	public $name = NULL;
	public $artist = NULL;
	public $album = NULL;
	
	public function __construct($id, $name, $artist, $album) {
		$this->id = $id;
		$this->name = $name;
		$this->artist = $artist;
		$this->album = $album;
	}
}

?>