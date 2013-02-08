<?php
	include("../grooveshark.class.php");
	$music_path = "songs";

	$gs = new GrooveShark();
	$search = $argv[1];

	#$artists = $gs->search($search, 'Artists');
	#print_r($artists);

	$songs = $gs->search($search);
	print_r($songs);
	
	