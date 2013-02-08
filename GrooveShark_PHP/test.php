<?php
#echo exec("osascript input.scpt");
#
echo applePrompt("this is a test");
#	display dialog "'.$message.'" default answer "'.$default.'"

# echo appleSelect("What to download", Array("Song", "Playlist"));

#run_osascript('display dialog "Download a" buttons {"Song", "Playlist"} default button 2');

function applePrompt($message, $default = ""){
	run_osascript("display dialog \"{$message}\" default button 1 default answer \"{$default}\"");
}

function appleSelect($message, $buttons = Array(), $default = 1){
	if(count($buttons)){
		$buttons = '{"'.implode('","', $buttons).'"}';
	}
	else{
		$buttons = "";
	}
	run_osascript("display dialog \"{$message}\" buttons {$buttons} default button {$default}");
}

function run_osascript($code){
	// use this wrapper to solve "execution error: No user interaction allowed. (-1713)"
	$output = exec("osascript -s s -e 'tell application \"AppleScript Runner\"\n{$code}\nend tell'");
	echo $output;	
}

?>