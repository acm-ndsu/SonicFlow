<?php
/**
 * GrooveShark - Interface to the Grooveshark unofficial web API
 * 
 * PHP Version 5
 *
 * @author Matt Austin <matt@m-ausitn.com>
 */

include("util.class.php");
include("http.class.php");

class GrooveShark 
{
	public $http = array();
	public $country = '';
	public $secretKey = '';
	public $session = '';	
	
	private $communication_token = '';
	private $lastRandomizer = null;
	private $uuid = '';
	
	
	public $options = array(
		'base_domin'     => 'grooveshark.com',
		'getAppData'     => false,
		'proxy'          => false,
		'configDefaults' => array(
			'client'         => 'htmlshark',
			//clientRevision and revToken from: http://static.a.gs-cdn.net/gs/app.js
			'clientRevision' => '20120312',
			'revToken'       => 'reallyHotSauce',
			// tokenKey from from JSQueue.swf /action/JSQueue.as
			// use: http://www.showmycode.com/ and file: http://grooveshark.com/JSQueue.swf
			'tokenKey'       => 'paperPlates'
		),
	);
	
	public function __construct($options = null) 
	{
		// If options were passed in the the constructor merge them into the default options
		if($options){
			$this->options = Util::merge($this->options,$options);
		}

		// Default HTTP options 
		$http_options = array(
			#'proxy' => 'tcp://127.0.0.1:8080',
			#'follow_redirects' => false,
			#'headers' => array('User-Agent' => 'test'),
		);
		
		// Set up proxy server if we need it
		if($this->options['proxy']){
			$http_options['proxy'] = $this->options['proxy'];
		}		
		
		$this->http = new http($http_options);

		// Read new app data form the app.js javascript file
		// That will give us the new clientRevision and revToken if this ever stops working
		if($this->options['getAppData']){
			$this->getAppData();
		}					
		
		$this->getSession();
		$this->communication_token = $this->getCommunicationToken();
				
	}

	public function getSongFromToken($token){
		return $this->send('getSongFromToken', array(
			'token' => $token,
			'country' => $this->country
		));
	}

    /** Search 
     *
     * @param string $search
     * @param string $type (Songs, Albums
     * @return array Array of songs matching search
     */

	public function search($search, $type = 'Songs') {
		$params = array(
			'query' => $search,
			'type' => $type,
			'guts' => 0,
			'ppOverride' => false,
		);
		$data = $this->send('getResultsFromSearch', $params);
		return $data['result'];
	}

	/** Get Song (stream) data by id
     *
     * @param int $artist_id Artist Id
     * @return mixed Song Data
     */
	public function getSongById($song_id) {
		$params = array(
			'songID' => $song_id,
			'mobile' => 'false',
			'prefetch' => 'false',
			'country' => $this->country,		
		);
		$result = $this->send('getStreamKeyFromSongIDEx', $params, 'jsqueue');
		
		// add the download URL to this data. 
		if(isset($result['ip'])){
			$result['url'] = "http://{$result['ip']}/stream.php?streamKey={$result['streamKey']}";
		}
		return $result;
	}

	/** Get song by a url 
     *
     * @param string $url
     * @return mixed Song Data
     */	
	public function getSongByUrl($url){
		// parse the "token" form the url
		if(preg_match('/\w+(?=\?)/', $url, $matches)){
			$token = $matches[0];
			$params = array(
				'token' => $token,
				'country' => $this->country,		
			);
			return $this->send('getSongFromToken', $params);				
		}
		else{
			return false;
		}		
	}

	/** List all songs in an album
     *
     * @param int $album_id Album Id
     * @return array Array of Albums 
     */
	public function getSongsByAlbum($album_id) {
		$params = array(
			'albumID' => $album_id,
			'isVerified' => false,
			'offset' => 0,
		);
		$data = $this->send('albumGetSongs', $params);
		if(isset($data['songs'])){
			return $data['songs'];
		}
		return array();
	}

    /** Get playlist info and songs
     *
     * @param int $playlist_id Playlist Id
     * @return array Array of songs 
     */
	public function getPlaylistByID($playlist_id) {
		$params = array(
			'playlistID' => $playlist_id,
		);
		$data = $this->send('getPlaylistByID', $params);
		return $data;
	}

    /** List songs in playlist
     *
     * @param int $playlist_id Playlist Id
     * @return array Array of songs 
     */
	public function playlistGetSongs($playlist_id) {
		$params = array(
			'playlistID' => $playlist_id,
		);
		$data = $this->send('playlistGetSongs', $params);
		return $data['Songs'];
	}

	public function send($method = '', $params = null, $client =null, $secure = false){
		$query = array(
			'header' => array(
				'client' => ($client) ? $client : $this->options['configDefaults']['client'],
				'clientRevision' => $this->options['configDefaults']['clientRevision'],
				'session' => $this->session,
				'privacy' => 0,
				'country' => $this->country,
				'uuid' => ($this->uuid) ? $this->uuid:Util::makeUUID(),
			),
			'method' => $method
		);

		// we need to send our own token for every request after the communication_token request
		if(($this->communication_token) && !array_key_exists('token', $query['header'])){
			$lastRandomizer = $this->makeNewRandomizer();	
	
			// TODO some functions require one key and some the other 
			// this method should be changed to pass this in as an option with each call. 
			
			if($method == 'getStreamKeyFromSongIDEx'){
				$token = $this->options['configDefaults']['tokenKey'];
			}else{
				$token = $this->options['configDefaults']['revToken'];
			}	
			// Build the "magic" hash
			$query['header']['token'] = $lastRandomizer.sha1(
				$method.":".
				$this->communication_token.":".
				$token.":".
				$lastRandomizer
			);
		}

		// add parameters if they are passed
		if( $params !== null ) {
			$query['parameters'] = $params;
		}

		$protocol = ($secure) ? 'https://':'http://';
		$url = $protocol . $this->options['base_domin'] ."/more.php?". $method;
		$content = json_encode($query);
		
		// Post the data to the server
		$data = $this->http->post($url, $content);	
		$result = json_decode($data, true);
		
		// Pass up any errors
		if(isset($result['fault'])){
			throw new Exception($result['fault']['message']);
		}
		
		// Return the result fromt he response
		return $result['result'];
	}
	
	private function getSession(){
	
		// Call to the base domain to get a PHP session.  While
		// we are there why not read some JS varibles from the page. 
		// We need country for example. 
	
		$data = $this->http->get("http://{$this->options['base_domin']}/");
		$this->session = $this->http->get_cookie('PHPSESSID');
		$this->secretKey = md5($this->session);
		
		preg_match("/gsConfig = (.*?);/", $data, $matches);
		$result = json_decode($matches[1], true);
		$this->country = $result['country'];		
	}

	public function getCommunicationToken(){
		if($this->secretKey){
			$this->uuid = Util::makeUUID();
			return $this->send('getCommunicationToken', array(
				'secretKey' => $this->secretKey
			),null, true);
		}
		else{
		 	/* oops we dont have key yet.. we either did not call get session yet
		 	*  or something went wrong */
		}
	}
	
	public function getAppData(){
		
		/* TODO:  
		*	this function should also get the tokenKey from the swf
		*	http://grooveshark.com/JSQueue.swf
		* 
		*  Note: 
		*	This can be done at runtime without decompiling using a preloader from mm.conf 
		* 	and something like MixingLoom or SWFRETools for AOP. 
		* 
		* 	Create an air app that will preload this swf and find these varribles.
		* 	This should avoide any legal issues with decompiling.
		* 
		* 	This will all be part of the AS lib for grooveshark along with the AIR app.  
		*/
		
		$data = $this->http->get("http://static.a.gs-cdn.net/gs/app.js");
		preg_match("/client:\"(?<client>\w+)\".*?clientRevision:\"(?<clientRevision>\d+)\".*?revToken:\"(?<revToken>\w+)\"/s", $data, $matches);
		if(isset($matches)){
			$this->configDefaults = Array(
				'client' => $matches['client'],
				'clientRevision' => $matches['clientRevision'],
				'revToken' => $matches['revToken']
			);
			return $this->configDefaults;
		}
		return false;
	}

	private function makeNewRandomizer(){		
		// This is just a random string, but make sure we never send the same token twice in a row.
		$rand = sprintf("%06x",mt_rand(0,0xffffff));
		if($rand !== $this->lastRandomizer){
			$this->lastRandomizer = $rand;
			return $rand;
		}else{
			return $this->makeNewRandomizer();
		}
	}
		
}

