<?php require_once("config.php") ?>
<?php require_once("assets/includes/sonicflow.php"); ?>
<?php

	/*
	 * This will check whether there is a new song yet.
	 * Returns true if there is a new song; otherwise, false.
	 * If this is being called from the now playing page, then it
	 *   will return the new song id if there is a new song; otherwise, 0.
	 */
	$id_front = $_POST['id_front'];
	$id_back  = $_POST['id_back'];
	$from     = $_POST['from'];
	if ($from == "queue") {
		$song_front = getNext();
		$song_back  = getLast();
		$song_front = $song_front[1];
		$song_back  = $song_back[1];
		echo ($id_front != $song_front->id || $id_back != $song_back->id);
	} else {
		$song_front = getNext();
		$song_front = $song_front[1];
		if ($id_front != $song_front->id) {
			$json = getSongJson($song_front);
			echo $json;
		} else {
			echo 0;
		}
	}

	function getSongJson($song) {
		$arr = array(
			'id' => $song->id,
			'title' => $song->title,
			'artist' => $song->artist,
			'album' => $song->album,
			'arturl' => $song->arturl
			);
		return json_encode($arr);
	}
?>
