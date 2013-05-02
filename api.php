<?php
	require_once('assets/includes/sonicflow.php');

	$action = $_POST["action"];
	$search = $_POST["search"];
	$channel = "Headphone";

	if (is_null($action)) {
		$action="none";
	}


	$result = "{\"action\":$action,\"result\":{";

	switch ($action) {
	case "vup":
		system("amixer -q set $channel 2+ unmute");
		$result = $result."\"change\":\"+2\"";
		break;
	case "vdown":
		system("amixer -q set $channel 2- unmute");
		$result = $result."\"change\":\"-2\"";
		break;
	case "toggle":
		system("amixer -q set $channel toggle");
		$result = $result."\"change\":\"toggle\"";
		break;
	case "mute":
		system("amixer -q set $channel mute");
		$result = $result."\"change\":\"mute\"";
		break;
	case "umute":
		system("amixer -q set $channel unmute");
		$result = $result."\"change\":\"unmute\"";
		break;
	case "search":
		$searchResults = getSonicFlowResults($search);
		if (count($searchResults) == 0) {
			$provider = "grooveshark";
			$searchResults = getGroovesharkResults($search);
		}
		$numResults = count($searchResults);

		$result = $result . "\"size\":".$numResults.",\"provider\":".$providerName."\",results\":{";

		if (is_null($searchResults)) {
			break;
		}

		foreach ($searchResults as $s) {
			$result = $result . "\"id:\"".$s->id.",\"title:\"".$s->title.",\"artist\"".$s->artist.",\"album\"".$s->album;
		}
		$result = $result . "}";
		break;
	}

	$result = $result."}}";

	echo $result;
?>

