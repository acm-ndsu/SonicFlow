<?php
	include("../grooveshark.class.php");
	$music_path = "songs";
	// remove memry limits 
	ini_set('memory_limit', -1);

	$gs = new GrooveShark();
	$playlist_id = $argv[1];

	$playlist = $gs->getPlaylistByID($playlist_id);
	$songs = $playlist['Songs'];

	echo "Downloading playlist: {$playlist['Name']}\n";


	$filename = $playlist_id . '.zip';
	$zip = new ZipArchive();
	# open or create a zip file by the playlist id number

	foreach ($songs as $song) {
		if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
		    exit("cannot create <$filename>\n");
		}
		$songs_info = $gs->getSongById($song['SongID']);
		$file_name = $song['Name'].".mp3";
		passthru("wget -O \"$music_path/{$file_name}\" {$songs_info['url']}");
		echo "Downloading file: {$file_name} from url: {$songs_info['url']} \n";
		#$zip->addFromString($file_name , file_get_contents($songs_info['url']));		
		#$zip->close();
	}
	echo "Created file: {$filename}\ndone.\n";


	function get_mem(){
		$size = memory_get_usage(true);
		$unit=array('b','kb','mb','gb','tb','pb');
		$size = @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
		echo "Mem: $size\n"; // 123 kb
	}


