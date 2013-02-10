<?php require_once("config.php") ?>
<?php require_once("assets/includes/self_interaction.php");
<?php

	/*
	 * This will check whether there is a new song yet.
	 * Returns true if there is a new song; otherwise, false.
	 */

	$id = $_POST['id'];
	$song = getNext();
	echo ($id != $song->id);
?>
