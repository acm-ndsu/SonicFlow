<?php

	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	include("GrooveShark_PHP/grooveshark.class.php");
	include("assets/includes/sonicflow.php");
	
	$gs = new GrooveShark();
	$failcount = 0;
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
				$data = $gs->getSongById($songId);
				if ($data != null)  {
					$failcount = 0;
					passthru("python assets/include/pygs/play.py {$data['title']} {$songId}";
					removeSongFromQueue($id);
				} else {
					echo "\n\nFailed to retrieve URL for $song->title by $song->artist!\n\n";
					$failcount++;
					if ($failcount >= 3) {
						fixBadId($id);
						$failcount = 0;
					}
					sleep(2);
				}
		
			}
		} catch (Exception $e) { 
			echo "\n\nException happened! with $song->title by $song->artist\n\n";
			$gs = new GrooveShark();
			sleep(2);
		}
	}

?>
