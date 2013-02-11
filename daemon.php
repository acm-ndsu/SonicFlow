<?php

	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	include("GrooveShark_PHP/grooveshark.class.php");
	include("assets/includes/sonicflow.php");

	$gs = new GrooveShark();
	while (true) {
		try {
			$next   = getNext();
			if ($next == '') {
				sleep(2);
			} else {
				$id = $next[0];
				$song = $next[1];
				$songId = $song->id;
				$data = $gs->getSongById($songId);
				passthru('wget ' . $data['url'] . ' -O - | mplayer -cache 8192 -af volnorm=2:1.0 -');
				removeSongFromQueue($id);
		
			}
		} catch (Exception $e) { 
			echo "\nError happened!\n";
			echo "\n$e\n";
		}
	}

?>
