<?php
	$action = $_POST["action"];
	$channel = "Headphone";
	switch ($action) {
	case "vup":
		system("amixer -q set $channel 2+ unmute");
		echo "Volume +2";
		break;
	case "vdown":
		system("amixer -q set $channel 2- unmute");
		echo "Volume -2";
		break;
	case "toggle":
		system("amixer -q set $channel toggle");
		echo "Mute option toggled";
		break;
	case "mute":
		system("amixer -q set $channel mute");
		echo "Volume muted";
		break;
	case "umute":
		system("amixer -q set $channel unmute");
		echo "Volume unmuted";
		break;
	}
?> 
