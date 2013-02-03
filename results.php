<?php include('library.php'); ?>
<?php include('header.html'); ?>
<?php
	$provider = $_GET['provider'];
	
	$results = getSongResults($provider);
?>
<?php include('footer.html'); ?>
