<?php include('header.html'); ?>
<?php require_once('assets/includes/sonicflow.php'); ?>
<?php 
	if(isset($_POST['id'])) {
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
?>
		<script type="text/javascript">
			function redirect() {
				window.location = "queue.html";
			}
			setTimeout("redirect()",5000);
		</script>
<?php include('footer.html'); ?>
