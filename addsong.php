<?php

$BANNED_SONGS = array(41961821, 41884141);
include('tags.php');
?>
<?php include('header.html'); ?>
<?php require_once('assets/includes/sonicflow.php'); ?>
<?php 
	
	if(isset($_POST['id'])) {
		if (in_array($_POST['id'], $BANNED_SONGS)) {
			echo 'This song has been banned. Please stop.';
		} else {
			$id = $_POST['id'];
			$added = addSongToQueue($id);
			unset($_POST['id']);
			if ($added == R_SUCCESS) {
				echo "Song added!";
			} else if ($added == R_SONG_REQUEST_TOO_SOON) {
				$timeSince = time() - getSongRequestTime($id);
				$t = ceil((SONG_REQUEST_LIMIT - $timeSince) / 60);
				$s = ($t != 1) ? 's' : '';
				echo "Song requested too soon. It can be requested " .
						"again in $t minute$s";
			}
		}
	}
?>
		<script type="text/javascript">
			function redirect() {
				window.location = "queue.html";
			}
			setTimeout("redirect()",5000);
		</script>
<?php include('footer.html'); ?>
