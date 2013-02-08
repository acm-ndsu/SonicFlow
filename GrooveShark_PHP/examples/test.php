<?php
include("../grooveshark.class.php");

$gs = new GrooveShark(array(
/* an example of the options you can pass in.
	'proxy' => "tcp://127.0.0.1:8080",
	'configDefaults' => array(
		'client'         => 'htmlshark',
		'clientRevision' => '20110606',
		'revToken'       => 'backToTheScienceLab',
		'tokenKey'       => 'bewareOfBearsharktopus'
	),*/
));

// if they change the clientRevision or revToken this will try to find it from app.js.
#$gs->getAppData();

#$url = 'http://grooveshark.com/#/s/Cookies+With+A+Smile/3EXEk7?src=5';
#$song = $gs->getSongByUrl($url);
#print_r($gs->getSongFromToken("3EXEk7"));
#$data = $gs->getSongById($song['SongID']);
#$data = $gs->search('Cookies With A Smile');

$data = $gs->getPlaylistByID(65715998);


print_r($data['Name']);
/*	$zip = new ZipArchive();

	$playlist_id = '57895619';
	$filename = $playlist_id . '.zip';
	
	if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
	    exit("cannot create <$filename>\n");
	}
	
	#$zip->addFromString('test.txt' , 'test');
	
	get_mem();
	
	#$zip->close();
	
	function get_mem(){
		$size = memory_get_usage(true);
		$unit=array('b','kb','mb','gb','tb','pb');
		$size = @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
		echo "Mem: $size\n"; // 123 kb
	}
*/

