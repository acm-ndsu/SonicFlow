<?php require_once("config.php") ?>
<?php require_once("assets/includes/sonicflow.php"); ?>
<?php

	/*
	 * This will check whether there is a new song yet.
	 * Returns true if there is a new song; otherwise, false.
	 */
	$id = $_POST['id'];
	$from = $_POST['from'];
	if ($id == "none") {
		echo 0;
	}
	if ($from == "playing") {
		$song = getNext();
		if ($id != $song->id) {
			echo 1;
//			$content = "\t\t\t\t\t<li class=\"songTitle\">$song->title</li>\n"
//				. "\t\t\t\t\t<li id=\"songId\" style=\"display:none\">$song->id</li>\n"
//				. "\t\t\t\t\t<li class=\"songArtist\">$song->artist</li>\n"
//				. "\t\t\t\t\t<li class=\"songAlbum\">$song->album</li>";
//			echo $content;
		} else {
			echo 0;
		}
	} else {
		// Logic for handling queue	
	}
?>
