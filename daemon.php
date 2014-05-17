<?php

	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	#nclude("GrooveShark_PHP/grooveshark.class.php");
	include("assets/includes/sonicflow.php");
	
	#$gs = new GrooveShark();
	$failcount = 0;
	echo("Daemon started.\n");
	while (true) {
		try {
			$next   = getNext();
			if ($next[0] == '') {
				$failcount = 0;
				sleep(2);
			} else {
				$id = $next[0];
				$song = $next[1];
				$songId = $song->id;
				if ($song != null)  {
					$failcount = 0;
					if ($next[2] != CACHE_IN_PROGRESS) {
						$cmd = "mplayer -cache 8192 /var/www/SonicFlow/assets/songs/" . $id . ".mp3";
						passthru($cmd);
						removeSongFromQueue($id);
					}
				} else {
					echo "\n\nFailed to retrieve URL for $song->title by $song->artist!\n\n";
					$failcount++;
					if ($failcount >= 3) {
						fixBadId($id);
						$failcount = 0;
					}
				}
				sleep(2);
		
			}
		} catch (Exception $e) { 
			echo "\n\nException happened! with $song->title by $song->artist\n\n";
			sleep(2);
		}
	}

?>
