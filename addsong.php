<?php include('header.html'); ?>
<?php require_once('assets/includes/sonicflow.php'); ?>
<?php 
	if(isset($_POST['id'])) {
		addSongToQueue($_POST['id']);
		echo "Song added!";
	}
?>
		<script type="text/javascript">
			function redirect() {
				window.location = "queue.html";
			}
			setTimeout("redirect()",1500);
		</script>
<?php include('footer.html'); ?>
