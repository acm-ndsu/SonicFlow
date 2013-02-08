<?php

	$filename = "JSQueue.swf";
	$url = "http://grooveshark.com/JSQueue.swf";
	$js_file = "http://static.a.gs-cdn.net/gs/app.js";
	
	echo("____________________\n");
	
	#get the file from grooveshark
	file_put_contents($filename, file_get_contents($url));
	
	# fun swfdump
	$dump = `swfdump -a $filename 2>&1 | grep "findpropstrict <q>\[public\]com.grooveshark.jsQueue::Service$" -B 5 `;
	
	unlink($filename);
	
	if(preg_match('/pushstring "(.*?)"/s', $dump, $matches)){
		$tokenKey = end($matches);
		echo "Token Key: $tokenKey\n";
	}
	else{
		echo "Could not find Token Key";
	}

	$data = file_get_contents($js_file);
	preg_match("/client:\"(?<client>\w+)\".*?clientRevision:\"(?<clientRevision>\d+)\".*?revToken:\"(?<revToken>\w+)\"/s", $data, $matches);
	$client = $matches['client'];
	$clientRevision = $matches['clientRevision'];
	$revToken = $matches['revToken'];
	
	print "Client: $client\nClient Revision: $clientRevision\nRev Token: $revToken\n";

?>