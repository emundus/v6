<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class hikashopGeolocationInc{
	var $errors = array();
	var $service = 'api.ipinfodb.com';
	var $version = 'v3';
	var $apiKey = '';
	var $timeout = 10;

	function setKey($key){
		if(!empty($key)) $this->apiKey = $key;
	}
	function setTimeout($key){
		if(!empty($key)) $this->timeout = $key;
	}

	function getError(){
		return implode("\n", $this->errors);
	}

	function getCountry($host){
		return $this->getResult($host, 'ip-country');
	}

	function getCity($host){
		return $this->getResult($host, 'ip-city');
	}

	function getResult($host, $name){
		$ip = @gethostbyname($host);

		if(preg_match('/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/', $ip)){
			return $this->curlRequest($ip);
		}

		$this->errors[] = '"' . $host . '" is not a valid IP address or hostname.';
		return;
	}
	function curlRequest($ip) {
		$qs = 'http://' . $this->service . '/' . $this->version . '/ip-country/' . '?ip=' . $ip . '&format=json&key=' . $this->apiKey;
		$app = JFactory::getApplication();
		if(!function_exists('curl_init')){
			$app->enqueueMessage('The HikaShop Geolocation plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}
		if(!function_exists('json_decode')){
			$app->enqueueMessage('The HikaShop Geolocation plugin can only work with PHP 5.2 at least. Please ask your web hosting to update your PHP version','error');
			return false;
		}
		if (!isset($this->curl)) {
			$this->curl = curl_init();
			curl_setopt ($this->curl, CURLOPT_FAILONERROR, TRUE);
			if (@ini_get('open_basedir') == '' && @ini_get('safe_mode' == 'Off')) {
				curl_setopt ($this->curl, CURLOPT_FOLLOWLOCATION, TRUE);
			}
			curl_setopt ($this->curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt ($this->curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
			curl_setopt ($this->curl, CURLOPT_TIMEOUT, $this->timeout);
		}

		curl_setopt ($this->curl, CURLOPT_URL, $qs);

		$json = curl_exec($this->curl);

		if(curl_errno($this->curl) || $json === FALSE) {
			$err = curl_error($this->curl);
			$app->enqueueMessage('cURL failed. Error: ' . $err);
		}

		$response = json_decode($json);

		if (!empty($response->statusCode) && $response->statusCode != 'OK') {
			$app->enqueueMessage('API returned error: ' . $response->statusMessage);
		}

		return $response;
	}
}
