<?php
date_default_timezone_set('America/Los_Angeles');
class http {

	public $cookies = array();
	public $last_headers = array();

	public $options = array(
		'http'=>array(
			'max_redirects' => 1,
			'ignore_errors'=>1,
			'method'=>"GET",
		),
		'other' => array(
			'follow_redirects' => true,
		),
	);

	public $default_headers = array(
		#'Host'             => 'www.facebook.com',
		'User-Agent'       => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0.1) Gecko/20100101 Firefox/4.0.1',
		'Accept'           => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Language'  => 'en-us,en;q=0.5',		
		'Accept-Charset'   => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
		'Content-Type'     => 'application/json; charset=UTF-8',
		'Pragma'           => 'no-cache',
		'Cache-Control'    => 'no-cache'
	);

	public function __construct($options = null) 
	{
		if(isset($options['proxy'])){
			$this->options = Util::merge($this->options, array(
				'http'=>array(
					'proxy' => $options['proxy'], 
					'request_fulluri' => true,
				)				
			));
		}
		
		if($options){
			$this->options['other'] = Util::merge($this->options['other'],$options);
		}
		if(isset($options['headers'])){
			$this->default_headers = Util::merge($this->default_headers,$options['headers']);
		}
	}
	
	public function get($url){
		return $this->do_request($url, $this->options);
	}
	
	public function post($url, $content = ''){
		$options = Util::merge($this->options, array(
			'http'=>array(
				'method'=>"POST",
				'content' => $content,
			)				
		));
		return $this->do_request($url, $options);
	}


	/* HTTP Helpers */
	public function set_user_agent($ua){
		$this->default_headers['User-Agent'] = $ua;
	}

	public function add_header($key, $value){
		$this->default_headers[$key] = $value;
	}	

	public function get_cookie($key){
		return $this->cookies[$key]['value'];
	}

	public function get_cookies(){
		return $this->cookies;
	}
	
	public function set_cookie($key, $value){
		$this->cookies[$key] = $value;
	}

	
	/* internal stuff */	

	private function do_request($url, $options){
		$options['http']['header'] = $this->build_headers();
		stream_context_get_default($options);
		
		$data = file_get_contents($url);
		if($http_response_header){		
			$response_headers = $this->parse_headers($http_response_header);
			if(isset($response_headers["Location"])){
				#$response_headers["Location"];
				if($this->options['other']['follow_redirects']){
					$data = $this->get($response_headers["Location"]);
				}
			}			
		}
		return $data;				
	}
	
	private function parse_headers($headers){
		foreach ($headers as $header) {
			if (stripos($header, ": ") === false) {
				$http_response = $header;
			}
			else{
				$parts = explode(": ",$header);
				
				#some headers may have another : in it. we only care about the first one. 
				$key = array_shift($parts);
				$value = join(": ", $parts);
				
				if(isset($response_headers[$key])){
					// if we already have a key make the first item an array 
					if(is_string($response_headers[$key])){
						$response_headers[$key] = Array($response_headers[$key]);
					}
					// push all other items to the array
					$response_headers[$key][] = $value;
				}
				else{
					// new item so just add key/value
					$response_headers[$key] = $value;
				}				
			}
		}
		if(isset($response_headers["Set-Cookie"])){
			$cookies = $response_headers["Set-Cookie"]; 		
			if(is_array($cookies)){
				foreach( $cookies as $cookie ) {			
					$this->parse_cookies($cookie);
				}
			}
			else{
				$this->parse_cookies($cookies);
			}
		}
		$this->last_headers = $response_headers;
		return $response_headers;
	}

	public function build_headers($content = null){
		$headers = $this->default_headers;
		
		if($this->cookies) $headers['Cookie'] = $this->build_cookies();
		if($content) $headers['Content-Length'] = strlen($content);
		
		foreach($headers as $name=>$value){
			$head[] = "{$name}: {$value}";
		}
		
		return trim( implode( "\r\n", $head ) );
	}

	private function build_cookies(){
		foreach($this->cookies as $name=>$value){
			$value = $value['value'];
			$cookie[] = "{$name}={$value}";
		} 
        if( count( $cookie ) > 0 ) {
                return trim( implode( '; ', $cookie ) );
        }
	}

	private function parse_cookies($header) {
		$csplit = explode( ';', $header );
		$cdata = array();
		foreach( $csplit as $data ) {
			$cinfo = explode( '=', $data );
			
			// todo clean up and use cookie class
			
			$cinfo[0] = trim( $cinfo[0] );			
			if(in_array($cinfo[0], array('secure', 'httponly'))) $cinfo[1] = true;
			if( $cinfo[0] == 'expires' ) $cinfo[1] = strtotime( $cinfo[1] );
			
			if( in_array( $cinfo[0], array('domain', 'expires', 'path', 'secure', 'comment', 'httponly' ) ) ) {
				$cdata[trim( $cinfo[0] )] = $cinfo[1];
			}
			else{
				$cdata['name'] = trim( $cinfo[0] );
				$cdata['value'] = trim( $cinfo[1] );
			}
			$cdata['data'] = $data;
		}
		$this->cookies[$cdata['name']] = $cdata;
		return $this->cookies;
	}
	
}

class cookie {
	public $value = "";
	public $expires = "";
	public $domain = "";
	public $path = "";
	public $name = "";
	public $secure = false;

	public function set_value($key,$value) {
		switch (strtolower($key)) {
			case "expires":
				$this->expires = $value;
				return;
			case "domain":
				$this->domain = $value;
				return;
			case "path":
				$this->path = $value;
				return;
			case "secure":
				$this->secure = ($value == true);
				return;
		}
		if ($this->name == "" && $this->value == "") {
			$this->name = $key;
			$this->value = $value;
		}
	}
}