<?php

/**
 * Utility
 * @type Class (Library)
 * @name 'Utility'
 * @description: Utility methods
 * 
 * Developed by: Mohamed Asif
 * Date: 19/05/2021
 * Email: mohamedasif18@gmail.com
 */

class Utility {
	
	/**
	 * Time Now
	 *
	 * @name timenow
	 */
	public function timenow() {
		return date('Y-m-d H:i:s', now());
	}

	/**
	 * Update JSON
	 *
	 * @name $existJsonString, $arrayElements
	 * @type String, array
	 */
	public function updateJSON($existJsonString, $arrayElements) {
		if (!is_string ($existJsonString)) return json_encode($arrayElements);
		$existJsonArray = json_decode($existJsonString, true);
		foreach ($arrayElements as $key => $value) {
			$existJsonArray[$key] = $value;
		}
		return json_encode($existJsonArray);
	}

	/**
	 * Generate Random String
	 * @param $key
	 * @type String
	 */
	function generateRandomString($key = '') {
		$randomString = uniqid(rand(),1);
		$randomString = strip_tags(stripslashes($randomString));
		$randomString = str_replace(".", "", $randomString);
		$randomString = strrev(str_replace("/", "", $randomString));
		$randomString = trim(substr($randomString, 0, 7));
		$randomString = $key.$randomString;
		return $randomString;
	}

	/**
	 * Format Bytes
	 * @param $size, $precision
	 * @type Number, Number
	 */
	function formatBytes($size, $precision = 2) {
		if (!is_numeric($size) || $size <= 0) return $size;

		$base = log($size, 1024);
		$suffixes = array('', 'K', 'M', 'G', 'T');   
		return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
	}

	/**
	 * Tiny Int to Boolean
	 * @param $tinyIntvalue
	 * @type TinyInt
	 */
	function parseTinyIntToBoolean($tinyIntvalue) {
		return ((INT)$tinyIntvalue === 1);
	}
}
?>
