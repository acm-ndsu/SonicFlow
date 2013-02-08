<?php

	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	include("../grooveshark.class.php");

	$music_path = "../assets/music/";

	$gs = new GrooveShark();
#	$url = 'http://grooveshark.com/s/1980/2JS6Dg?src=5';

	# look up the son URL then get the download URL. 
#	$song = $gs->getSongByUrl($url);
	$data = $gs->getSongById('28470323');
	echo "\n" . $data['url'] . "\n";
	#passthru("nvlc -I dummy --play-and-exit " . $data['url'] . '--sout \'#standard{access=http,mux=ogg,dst=localhost:8080}\'');
	# push the file name in the header
#	$filename = "{$song['ArtistName']} - {$song['Name']}.mp3";

#	if (!is_dir($music_path)) {
#		mkdir($music_path, 0700, true);
#	}
#	$filename = str_replace(' ','\ ', $filename);
#	print $filename;

#	print "\n";
#	print "{$data['url']}\n";
#	header("Content-Disposition: attachment; filename={$filename}");
#	passthru("wget -O $music_path/{$filename} {$data['url']}");

?>
