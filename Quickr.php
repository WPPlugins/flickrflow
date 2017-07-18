<?php


/**
 * Quickr
 *
 * A simple class to acces the flickr API
 * 
 * @author Pieter-Jan Volders
 */
class Quickr
{
	private $api_key;
	private $mysql;
	private $total_api_reqs;
	
	
	public function __construct($api_key) {
		$this->api_key = $api_key;
	}
		
	private function apiGetArray($params) {
		$params['api_key'] 	= $this->api_key;
		$params['format'] 	= 'php_serial';

		$count 		= 0;
		$rsp_array['stat'] = 'nog doen';
		while (isset($rsp_array) and ($rsp_array['stat'] != 'ok') and ($count < 5)) {
			$url 		= "https://api.flickr.com/services/rest/?".implode('&', $this->encodeParams($params));
			$rsp 		= file_get_contents($url);
			$rsp_array	= unserialize($rsp);
			$count++;
			$this->total_api_reqs++;
		}
		
		$resp = unserialize($rsp);
		if ($resp['stat']=='ok') {
			return $resp;
		} else {
			return false;
		}
		
	}
	
	/**
	 * Tries to find a user id 
	 *
	 * @author your name
	 * @param $something username, email or fid
	 * @return return type
	 */
	public function findUser($something) {
		$emailregex = "[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?";
		$params = array( 'format'	=> 'php_serial' );
		
		if ( preg_match('/^\d*@N\d*$/', $something) )
		{ // Het is een NSID
			$params['method'] = 'flickr.people.getInfo';
			$params['user_id'] = $something ;
			if ( $this->apiGetArray($params) )
				$fid = $something;
			else
				$fid = false;
		
		} elseif ( preg_match("/$emailregex/", $something) )
		{ // Het is een Email
			$params['method'] = 'flickr.people.findByEmail';
			$params['find_email'] = $something ;
			if ( $info = $this->apiGetArray($params) )
				$fid = $info['user']['nsid'];
			else
				$fid = false;
			
		} else 
		{ // Het is een Username
			$params['method'] = 'flickr.people.findByUsername';
			$params['username'] = $something ;
			if ( $info = $this->apiGetArray($params) )
				$fid = $info['user']['nsid'];
			else
				$fid = false;
		
		}
		return $fid;
	}	
	
	public function getPublicPhotos($userid, $aantal=100, $page=1) {
	
		$params = array(
			'method'	=> 'flickr.people.getPublicPhotos',
			'user_id'	=> $userid,
			'format'	=> 'php_serial',
			'extras'	=> 'tags, url_sq, url_s, url_m, date_taken',
			'per_page'	=> $aantal,
			'page'		=> $page
		);
	
		$photos = $this->apiGetArray($params);
		return $photos['photos']['photo'];
			
	}
		
		
	private function encodeParams($params) {
		
		foreach ($params as $k => $v){
			$encoded_params[] = urlencode($k).'='.urlencode($v);
		}
		
		return $encoded_params;
	
	}	
	
	/**
	 * Check the api key by returning the one I love
	 *
	 * @author PJ Volders
	 * @return return boolean	
	 */
	public function check()
	{
		$params = array(
			'method' 	=> 'flickr.test.echo',
			'ilove' 	=> 'elke'
		);
		
		if ( ($info = $this->apiGetArray($params)) && ($info['ilove']['_content'] == 'elke') )
		{
			return true;
		} else {
			return false;
		}
	}
	
}


?>