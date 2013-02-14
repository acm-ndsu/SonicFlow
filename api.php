<?php
	$action = $_POST["action"];
	switch ($action) {
	case "vup":
		system("amixer -q set Master 2- unmute");
		echo "Volume -2";
		break;
	case "vdown":
		system("amixer -q set Master 2+ unmute");
		echo "Volume +2";
		break;
	case "toggle":
		system("amixer -q set Master toggle");
		echo "Mute option toggled";
		break;
	case "mute":
		system("amixer -q set Master mute");
		echo "Volume muted";
		break;
	case "umute":
		system("amixer -q set Master unmute");
		echo "Volume unmuted";
		break;
	}
?> 
