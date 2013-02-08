<?php

	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	include("GrooveShark_PHP/grooveshark.class.php");
	include("assets/includes/sonicflow.php");

	$gs = new GrooveShark();
	while (true) {
		$next   = getNext();
		if ($next == '') {
			sleep(2);
		} else {
			$id     = $next[0];
			$songId = $next[1];
			$data = $gs->getSongById($songId);
			passthru('wget ' . $data['url'] . ' -O - | mplayer -cache 8192 -af volnorm=2:1.0 -');
			removeSongFromQueue($id);
		}
	}

?>
