<?php
class Util{
	
	# Recursivaly merge two arrays.
	function merge($arr1, $arr2 = null) {
		$args = func_get_args();
		$r = (array)current($args);
		while (($arg = next($args)) !== false) {
			foreach ((array)$arg as $key => $val)	 {
				if (is_array($val) && isset($r[$key]) && is_array($r[$key])) {
					$r[$key] = Util::merge($r[$key], $val);
				} elseif (is_int($key)) {
					$r[] = $val;
				} else {
					$r[$key] = $val;
				}
			}
		}
		return $r;
	}

	# Make a random UUID
	function makeUUID(){
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
		        mt_rand( 0, 0x0fff ) | 0x4000,
		        mt_rand( 0, 0x3fff ) | 0x8000,
		        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );
	}

}	
	
?>