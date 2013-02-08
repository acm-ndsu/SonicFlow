<?php
include("http.class.php");

// Default HTTP options 
$http_options = array(
	#'proxy' => 'tcp://127.0.0.1:8080',
	#'follow_redirects' => false,
	#'headers' => array('User-Agent' => 'test'),
);

$http = new http($http_options);
$url = "http://express.dominos.com/power/store/8130/coupon/9171";
$data = $http->get($url);

print_r(json_decode($data));

?>