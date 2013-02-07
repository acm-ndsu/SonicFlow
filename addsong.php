<?php
	require_once('assets/includes/sonicflow.php');
	/*
	 * This  provides the functionality to verify that a user has not
	 * added a song too recently or the song in question hasn't been
	 * queued too recently. It then adds the song to the queue if the
	 * above conditions are satisfied.
	 */

	$id  = $_GET['id'];

	// check that user hasn't added song recently (2  min)
	// check that song hasn't been added recently (30 min)

	addSongToQueue($id);
	echo "Song added.";
?>
