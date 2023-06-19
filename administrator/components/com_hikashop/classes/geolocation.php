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
class hikashopGeolocationClass extends hikashopClass {
	var $tables = array('geolocation');
	var $pkeys = array('geolocation_id');

	function save(&$element) {
		if(empty($element->geolocation_id) || !empty($element->geolocation_ip)) {
			if(empty($element->geolocation_ip)) {
				return false;
			}

			$location = $this->getIPLocation($element->geolocation_ip);
			if(empty($location)) {
				return false;
			}

			$element->geolocation_latitude = @$location->latitude;
			$element->geolocation_longitude = @$location->longitude;
			$element->geolocation_postal_code = @$location->zipPostalCode;
			$element->geolocation_country = @$location->countryName;
			$element->geolocation_country_code = @$location->countryCode;
			$element->geolocation_state = @$location->regionName;
			$element->geolocation_state_code = @$location->RegionCode;
			$element->geolocation_city = @$location->city;
			$element->geolocation_created = time();
		}
		return parent::save($element);
	}

	function getIPLocation($ip){
		$plugin = JPluginHelper::getPlugin('system', 'hikashopgeolocation');
		if(empty($plugin) || empty($plugin->params)) return false;
		jimport('joomla.html.parameter');
		$this->params = new HikaParameter( $plugin->params );

		if(!empty($_SERVER["HTTP_CF_IPCOUNTRY"])) {
			$geoClass = hikashop_get('inc.geoplugin');
			$geoClass->countryCode = $_SERVER["HTTP_CF_IPCOUNTRY"];
			if(!empty($geoClass->countryCode) && $geoClass->countryCode == 'UK') {
				$geoClass->countryCode = 'GB';
			}
		}

		$service = $this->params->get('geoloc_service','both');
		switch($service) {
			case 'ipinfodb':
				return $this->ipinfodb($ip);
			case 'geoplugin':
				return $this->geoplugin($ip);
			case 'both':
			default:
				$result = $this->geoplugin($ip);
				if(empty($result)){
					$result = $this->ipinfodb($ip);
				}
				break;
		}
		return $result;
	}

	function geoplugin($ip){
		$geoClass = hikashop_get('inc.geoplugin');
		$timeout = $this->params->get('geoloc_timeout', 2);
		if(!empty($timeout))
			$geoClass->timeout = $timeout;
		$geoClass->locate($ip);
		if(!empty($geoClass->countryCode) && $geoClass->countryCode == 'UK') {
			$geoClass->countryCode = 'GB';
		}

		if(empty($geoClass->countryCode))
			return false;

		return $geoClass;
	}

	function ipinfodb($ip){
		$geoClass = hikashop_get('inc.geolocation');
		$api_key = $this->params->get('geoloc_api_key', '');
		if(empty($api_key))
			return false;
		$timeout = $this->params->get('geoloc_timeout', 2);
		if(!empty($timeout))
			$geoClass->setTimeout($timeout);

		$geoClass->setKey($api_key);
		$locations = $geoClass->getCountry($ip);
		if(!empty($locations->countryCode) && $locations->countryCode == 'UK') {
			$locations->countryCode = 'GB';
		}
		return $locations;
	}
}
