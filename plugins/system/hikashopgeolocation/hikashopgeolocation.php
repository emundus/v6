<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgSystemHikashopgeolocation extends JPlugin
{
	public $geolocation;
	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('system', 'hikashopgeolocation');
			$this->params = new JRegistry($plugin->params);
		}
	}

	function onAfterOrderCreate(&$order,&$send_email){
		$app = JFactory::getApplication();
		if (hikashop_isClient('administrator') || !hikashop_level(2))
			return true;

		if(!empty($order->order_id) && !empty($order->order_ip)){
			$order_geoloc = $this->params->get('order',1);
			if($order_geoloc){
				$geo = new stdClass();
				$geo->geolocation_ref_id = $order->order_id;
				$geo->geolocation_type = 'order';
				$geo->geolocation_ip = $order->order_ip;
				$class = hikashop_get('class.geolocation');
				$class->params =& $this->params;
				$class->save($geo);
			}
		}
		return true;
	}

	public function afterInitialise() {
		return $this->onAfterInitialise();
	}

	public function afterRoute() {
		return $this->onAfterRoute();
	}

	public function onAfterInitialise() {
		$app = JFactory::getApplication();

		$admin = false;
		if(version_compare(JVERSION,'4.0','>=') && $app->isClient('administrator'))
			$admin = true;
		if(version_compare(JVERSION,'4.0','<') && $app->isAdmin())
			$admin = true;
		if($admin)
			return;

		if(!$this->params->get('after_init', 1))
			return;

		$this->process();
	}

	public function onAfterRoute() {
		$app = JFactory::getApplication();
		$admin = false;
		if(version_compare(JVERSION,'4.0','>=') && $app->isClient('administrator'))
			$admin = true;
		if(version_compare(JVERSION,'4.0','<') && $app->isAdmin())
			$admin = true;
		if($admin)
			return;

		if($this->params->get('after_init', 1))
			return;

		$this->process();
	}

	function process(){
		$app = JFactory::getApplication();
		$admin = false;
		if(version_compare(JVERSION,'4.0','>=') && $app->isClient('administrator'))
			$admin = true;
		if(version_compare(JVERSION,'4.0','<') && $app->isAdmin())
			$admin = true;
		if ($admin) return true;
		$zone = 0;
		$components  = $this->params->get('components','all');

		if($components=='all' || in_array($_REQUEST['option'],explode(',',$components))){
			$blocked_zones = $this->params->get('blocked_zones','');
			$authorized_zones = $this->params->get('authorized_zones','');

			if(!empty($blocked_zones) || !empty($authorized_zones)){
				if(!defined('DS'))
					define('DS', DIRECTORY_SEPARATOR);
				if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return true;

				if(!hikashop_level(2)) return true;
				$zone = $this->getZone();
				if(!empty($zone)){
					$zoneClass = hikashop_get('class.zone');
					$parents = $zoneClass->getZoneParents($zone);
					$db = JFactory::getDBO();
					$zones = array();
					foreach($parents as $parent){
						$zones[] = $db->Quote($parent);
					}
					$db->setQuery('SELECT zone_id FROM '.hikashop_table('zone').' WHERE zone_namekey IN ('.implode(',',$zones).')');
					$zones = $db->loadColumn();

					$ok = false;
					if(!empty($authorized_zones)){
						$authorized_zones = explode(',',$authorized_zones);
						$valid_zones = array_intersect($zones,$authorized_zones);
						if(!empty($valid_zones)){
							$ok=true;
						}
					}elseif(!empty($blocked_zones)){
						$ok=true;
						$blocked_zones = explode(',',$blocked_zones);
						$invalid_zones = array_intersect($zones,$blocked_zones);
						if(!empty($invalid_zones)){
							$ok=false;
						}
					}
					if(!$ok){
						$name = 'hikashopgeolocation_restricted.php';
						$path = JPATH_THEMES.DS.$app->getTemplate().DS.'system'.DS.$name;
						if(!file_exists($path)){
							$path = JPATH_PLUGINS .DS.'system'.DS.'hikashopgeolocation'.DS.$name;
							if(!file_exists($path)){
								exit;
							}
						}

						require($path);
					}
				}
			}
		}

		$set_default_currency  = $this->params->get('set_default_currency', 0);
		if(!empty($set_default_currency)) {
			$currency = $app->getUserState('com_hikashop.currency_id', 0);
			if(empty($currency)){
				if(empty($zone)){
					if(!defined('DS'))
						define('DS', DIRECTORY_SEPARATOR);
					if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return true;
					if(!hikashop_level(2)) return true;
					$zone = $this->getZone();
				}
				$config =& hikashop_config();
				$toSetCurrency = (int)$config->get('main_currency',1);
				if(!empty($zone)){
					$zoneClass = hikashop_get('class.zone');
					$zone_currency_id = $zoneClass->getZoneCurrency($zone);
					$currencyClass = hikashop_get('class.currency');

					if(empty($zone_currency_id) && !empty($this->geolocation->currencyCode)){
						$zone_currency_id = $this->geolocation->currencyCode;
					}

					if(!empty($zone_currency_id)){
						$currencyData = $currencyClass->get($zone_currency_id);
						if(!empty($currencyData) && ($currencyData->currency_published || $currencyData->currency_displayed)){
							$toSetCurrency = $currencyData->currency_id;
						}
					}
				}

				$app->setUserState( HIKASHOP_COMPONENT.'.currency_id',$toSetCurrency);
			}
		}

		$zone_id = (int)$app->getUserState('com_hikashop.zone_id',0);
		if(empty($zone_id) && empty($zone)) {
			if(!defined('DS'))
				define('DS', DIRECTORY_SEPARATOR);
			if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return true;
			if(!hikashop_level(2)) return true;
			$zone = $this->getZone();
		}
		$geo_zone_id = (int)$app->getUserState('com_hikashop.geoloc_zone_id',0);
		if(empty($geo_zone_id) && !empty($zone))
			$app->setUserState('com_hikashop.geoloc_zone_id', (int)$zone);

		return true;
	}

	function getZone(){
		$app = JFactory::getApplication();
		$zone = (int)$app->getUserState(HIKASHOP_COMPONENT.'.zone_id',0);
		if(!empty($zone))
			return $zone;

		$geoClass = hikashop_get('class.geolocation');
		$this->geolocation = $geoClass->getIPLocation(hikashop_getIP());
		if(empty($this->geolocation) || empty($this->geolocation->countryCode))
			return $zone;

		$geolocation_country_code = $this->geolocation->countryCode;
		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM '.hikashop_table('zone').' WHERE zone_code_2 ='.$db->Quote($geolocation_country_code).' AND zone_type=\'country\'  AND zone_published=1');
		$zones = $db->loadObjectList();
		if(empty($zones)) {
			$states = array();
			$countries = array();
			foreach($zones as $zone){
				if($zone->zone_type=='state'){
					$states[]=$zone;
				}else{
					$countries[]=$zone;
				}
			}
			if(!empty($states)) {
				if(empty($countries)){
					$zone = $states[0]->zone_id;
				}else{
					$child_namekeys=array();
					foreach($states as $state){
						$child_namekeys[]=$db->Quote($state->zone_namekey);
					}
					$parent_namekeys=array();
					foreach($countries as $country){
						$parent_namekeys[]=$db->Quote($country->zone_namekey);
					}
					$db->setQuery('SELECT zone_child_namekey FROM '.hikashop_table('zone_link').' WHERE zone_parent_namekey IN ('.implode(',',$parent_namekeys).') AND zone_child_namekey IN ('.implode(',',$child_namekeys).')');
					$link = $db->loadResult();
					if(empty($link)){
						$zone = $countries[0]->zone_id;
					}else{
						foreach($states as $state){
							if($state->zone_namekey==$link){
								$zone = $state->zone_id;
							}
						}
					}
				}
			} elseif(!empty($countries[0])) {
				$zone = $countries[0]->zone_id;
			} else {
				hikashop_writeToLog('No zone found for the country code '.$geolocation_country_code);
			}
		}
		if(empty($zone)){
			$db->setQuery('SELECT zone_id FROM '.hikashop_table('zone').' WHERE zone_code_2='.$db->Quote($geolocation_country_code).' AND zone_published=1');
			$zone = $db->loadResult();
		}
		if(!empty($zone)){
			$app->setUserState( HIKASHOP_COMPONENT.'.zone_id', (int)$zone);
			$app->setUserState( HIKASHOP_COMPONENT.'.geoloc_zone_id', (int)$zone);
		}

		return (int)$zone;
	}
}
