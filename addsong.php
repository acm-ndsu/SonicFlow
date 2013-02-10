<?php include('header.html'); ?>
<?php require_once('assets/includes/sonicflow.php'); ?>
<?php 
	if(isset($_POST['id'])) {
		addSongToQueue($_POST['id']);
		unset($_POST['id']);
		echo "Song added!";
	}
	$currentQueue = getQueue(); 
	if (empty($currentQueue)) {
		echo "<div class=\"emptyQueue\">\n";
		echo "\tThe queue is currently empty.\n";
		echo "</div>\n";
	}
?>
		<div class="queue">
			<ul class="songs">
<?php
			foreach ($currentQueue as $s) {
?>
			<li>
				<img class="albumArt" src="<?php echo $s->arturl; ?>" alt="Song Album" />
				<div class="songTitle"><?php echo $s->title; ?></div>
				<div class="songArtist"><?php echo $s->artist ?></div>
				<div class="songAlbum"><?php echo $s->album; ?></div>
			</li>
<?php
			}
?>
	
			</ul>
		</div>
		<script type="text/javascript">
			function redirect() {
				window.location = "queue.html";
			}
			setTimeout("redirect()",1500);
		</script>
<?php include('footer.html'); ?>
