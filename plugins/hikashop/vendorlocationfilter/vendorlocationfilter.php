<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashopVendorlocationfilter extends JPlugin {

	protected $db = null;

	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		if(!isset($this->params)) {
			$plugin = JPluginHelper::getPlugin('hikashop', 'vendorlocationfilter');
			$this->params = new JRegistry($plugin->params);
		}
	}

	public function init() {
		static $init = null;
		if($init !== null)
			return $init;

		$use_search_module = (int)$this->params->get('use_search_module', 1);
		if(!$use_search_module) {
			$init = false;
			return false;
		}

		$this->db = JFactory::getDBO();

		$init = defined('HIKAMARKET_COMPONENT');
		if(!$init) {
			$filename = rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikamarket'.DS.'helpers'.DS.'helper.php';
			if(file_exists($filename)) {
				include_once($filename);
				$init = defined('HIKAMARKET_COMPONENT');
			}
		}
		return $init;
	}

	private function dbEscape($data) {
		if(empty($this->db))
			$this->db = JFactory::getDBO();
		if(HIKASHOP_J30)
			return $this->db->escape($data, true);
		return $this->db->getEscaped($data, true);
	}

	public function onBeforeProductListingLoad(&$filters, &$order, &$view, &$select, &$select2, &$ON_a, &$ON_b, &$ON_c) {
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator') || !$this->init())
			return;

		if(!empty($filters['current_vendor']))
			return;

		$use_search_module = (int)$this->params->get('use_search_module', 1);
		$empty_is_all = (int)$this->params->get('empty_is_all', 1);
		$hide_for_guest = (int)$this->params->get('hide_for_guest', 0);

		$location_search = null;
		if($use_search_module) {
			$location_search = hikaInput::get()->getVar('location_search', null);
			if($location_search !== null) {
				$app->setUserState(HIKAMARKET_COMPONENT.'.vendor_location_filter.search', $location_search);
			} else {
				$location_search = $app->getUserState(HIKAMARKET_COMPONENT.'.vendor_location_filter.search', null);
			}
		}

		if($empty_is_all && empty($location_search))
			return;

		$filter_mode = $this->params->get('filter_mode', 'zip;city');
		if(empty($filter_mode)) $filter_mode = 'zip;city';
		$filter_mode = explode(';', trim($filter_mode));
		$vendorZipColumn = $this->params->get('vendor_zip_column', 'accepted_zip');
		if(empty($vendorZipColumn)) $vendorZipColumn = 'accepted_zip';
		$vendorZipColumn = $this->db->quoteName($vendorZipColumn);
		$vendorCitiesColumn = $this->params->get('vendor_city_column', 'accepted_cities');
		if(empty($vendorCitiesColumn)) $vendorCitiesColumn = 'accepted_cities';
		$vendorCitiesColumn = $this->db->quoteName($vendorCitiesColumn);

		$user_id = hikashop_loadUser();
		if(empty($user_id) && !$use_search_module) {
			if(!empty($hide_for_guest)) {
				$this->pushEmptyFilter($filters, $filter_mode, $vendorZipColumn, $vendorCitiesColumn);
				$this->mergeFilters($filters);
			}
			return;
		}

		list($userZip, $userCity) = $this->getUserZipCity($user_id);

		if((int)$this->params->get('zipcode_digits', 0))
			$userZip = '' . ((int)$userZip);

		$zipMode = in_array('zip', $filter_mode);
		$cityMode = in_array('city', $filter_mode);

		if($location_search === null && (!$zipMode || empty($userZip)) && (!$cityMode || empty($userCity))) {
			$this->pushEmptyFilter($filters, $filter_mode, $vendorZipColumn, $vendorCitiesColumn);
			$this->mergeFilters($filters);
			return;
		}

		$vendorIds = $this->getVendorsCache($userZip, $userCity, $location_search);
		if(empty($vendorIds)) {
			$vendorIds = $this->getVendors($filter_mode, $userZip, $userCity, $location_search);
			$this->setVendorsCache($vendorIds, $userZip, $userCity, $location_search);
		}

		if(!empty($vendorIds))
			$filters['vendor_location_filter'] = '(hikam_vendor.vendor_id IS NULL OR hikam_vendor.vendor_id IN ('.implode(',',$vendorIds).'))';
		else
			$filters['vendor_location_filter'] = '(hikam_vendor.vendor_id IS NULL)';

		$this->mergeFilters($filters);
	}

	public function onAfterCheckCartQuantities(&$cart, $parent_products) {
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator') || !$this->init())
			return;

		$check_cart = (int)$this->params->get('check_cart', '0');
		if(!$check_cart)
			return;

		$user_id = hikashop_loadUser();
		if(empty($user_id))
			return;

		$use_cache = (int)$this->params->get('use_cache', 1);

		$filter_mode = $this->params->get('filter_mode', 'zip;city');
		if(empty($filter_mode)) $filter_mode = 'zip;city';
		$filter_mode = explode(';', trim($filter_mode));

		$zipMode = in_array('zip', $filter_mode);
		$cityMode = in_array('city', $filter_mode);

		$addr = !empty($cart->shipping_address) ? $cart->shipping_address : @$cart->billing_address;
		if(empty($addr))
			return;

		$userZip = !empty($addr->address_post_code) ? $addr->address_post_code : 0;
		$userCity = !empty($addr->address_city) ? $addr->address_city : '';

		if((int)$this->params->get('zipcode_digits', 0))
			$userZip = '' . ((int)$userZip);

		if(empty($userZip) && empty($userCity))
			return;

		$vendorIds = $this->getVendorsCache($userZip, $userCity, null);
		if(empty($vendorIds)) {
			$vendorIds = $this->getVendors($filter_mode, $userZip, $userCity, null);
			$this->setVendorsCache($vendorIds, $userZip, $userCity, null);
		}

		$already = array();
		foreach($cart->messages as $msg) {
			if(empty($msg['owner']) || $msg['owner'] != 'vendorlocation')
				continue;
			$already[ $msg['product_id'] ] = true;
		}

		foreach($cart->products as $product) {
			$vendor = (int)$product->product_vendor_id;
			if(isset($vendorIds[ $vendor ]))
				continue;

			if(isset($already[$product->product_id]))
				continue;

			if((int)$product->product_parent_id > 0 && $product->product_type == 'variant' && isset($parent_products[ (int)$product->product_parent_id ]))
				$product_name = trim($parent_products[ (int)$product->product_parent_id ]->product_name . ' ' . $product->product_name);
			else
				$product_name = $product->product_name;

			$cart->messages[] = array(
				'msg' => JText::sprintf('PRODUCT_X_NOT_SOLD_IN_YOUR_LOCATION', $product_name),
				'product_id' => $product->product_id,
				'type' => 'notice',
				'owner' => 'vendorlocation'
			);
		}
	}

	private function getUserZipCity($user_id = null) {
		if($user_id === null)
			$user_id = hikashop_loadUser();

		$addressClass = hikashop_get('class.address');
		$addresses = $addressClass->loadUserAddresses($user_id);

		$userZip = 0;
		$userCity = '';
		if(empty($addresses))
			return array($userZip, $userCity);

		$address = reset($addresses);
		if(!empty($address->address_post_code))
			$userZip = $address->address_post_code;
		if(!empty($address->address_city))
			$userCity = $address->address_city;
		return array($userZip, $userCity);
	}

	private function getVendors($filter_mode, $userZip, $userCity, $location_search) {
		$zipMode = in_array('zip', $filter_mode);
		$cityMode = in_array('city', $filter_mode);

		$vendorZipColumn = $this->params->get('vendor_zip_column', 'accepted_zip');
		if(empty($vendorZipColumn)) $vendorZipColumn = 'accepted_zip';
		$vendorZipColumn = $this->db->quoteName($vendorZipColumn);
		$vendorCitiesColumn = $this->params->get('vendor_city_column', 'accepted_cities');
		if(empty($vendorCitiesColumn)) $vendorCitiesColumn = 'accepted_cities';
		$vendorCitiesColumn = $this->db->quoteName($vendorCitiesColumn);

		$sql_filters = array();

		if($zipMode) {
			$f = array();
			$stars = '*';
			if($location_search !== null || !empty($userZip)) {
				if($location_search !== null) {
					$f[] = $vendorZipColumn.' LIKE \'%'.$this->dbEscape($location_search).'%\'';
				} else {
					$f[] = $vendorZipColumn.' LIKE \'%'.$this->dbEscape($userZip).'%\'';
				}
				for($i = strlen($userZip) - 1; $i >= 0; $i--) {
					$z = substr($userZip, 0, $i) . $stars;
					$f[] = $vendorZipColumn.' LIKE \'%'.$this->dbEscape($z).'%\'';
					$stars .= '*';
				}
			}
			unset($stars);

			if(!empty($f))
				$sql_filters[] = '('.implode(') OR (', $f).')';
		}

		if($cityMode && ($location_search !== null || !empty($userCity))) {
			if($location_search !== null) {
				$sql_filters[] = $vendorCitiesColumn.' LIKE \'%'.$this->dbEscape($location_search).'%\'';
			} else {
				$sql_filters[] = $vendorCitiesColumn.' LIKE \'%'.$this->dbEscape($userCity).'%\'';
			}
		}

		$query = 'SELECT vendor_id FROM '.hikamarket::table('vendor').' WHERE ('.implode(') OR (', $sql_filters).')';
		$this->db->setQuery($query);
		try {
			$vendors = $this->db->loadObjectList('vendor_id');
		} catch(Exception $e) {
			$vendors = array();
			hikashop_writeToLog($query . "\r\n\r\n" . $e);
		}

		if(empty($vendors))
			$vendors = array();
		$vendors = array_keys($vendors);

		$vendorIds = array_combine($vendors, $vendors);
		unset($vendors);

		if((int)$this->params->get('force_main_vendor', 1)) {
			$vendorIds[0] = 0;
			$vendorIds[1] = 1;
		}

		if((int)$this->params->get('include_logged_vendor', 1)) {
			$currentVendor = hikamarket::loadVendor();
			if(!empty($currentVendor))
				$vendorIds[$currentVendor] = $currentVendor;
		}

		return $vendorIds;
	}

	private function getVendorsCache($userZip, $userCity, $location_search) {
		$use_cache = (int)$this->params->get('use_cache', 1);
		if(!$use_cache)
			return null;

		$app = JFactory::getApplication();

		$session_userZip = $app->getUserState(HIKAMARKET_COMPONENT.'.vendor_location_filter.cache_user_zip');
		$session_userCity = $app->getUserState(HIKAMARKET_COMPONENT.'.vendor_location_filter.cache_user_city');
		$session_search = $app->getUserState(HIKAMARKET_COMPONENT.'.vendor_location_filter.cache_search');
		if($session_userZip != $userZip || $session_userCity != $userCity || $session_search != $location_search)
			return null;

		return $app->getUserState(HIKAMARKET_COMPONENT.'.vendor_location_filter.vendor_list');
	}

	private function setVendorsCache($vendorIds, $userZip, $userCity, $location_search) {
		$use_cache = (int)$this->params->get('use_cache', 1);
		if(!$use_cache)
			return false;

		$app = JFactory::getApplication();

		$app->setUserState(HIKAMARKET_COMPONENT.'.vendor_location_filter.vendor_list', $vendorIds);
		$app->setUserState(HIKAMARKET_COMPONENT.'.vendor_location_filter.cache_user_zip', $userZip);
		$app->setUserState(HIKAMARKET_COMPONENT.'.vendor_location_filter.cache_user_city', $userCity);
		$app->setUserState(HIKAMARKET_COMPONENT.'.vendor_location_filter.cache_search', $location_search);

		return true;
	}

	private function pushEmptyFilter(&$filters, $filter_mode, $vendorZipColumn, $vendorCitiesColumn) {
		if(in_array('zip', $filter_mode))
			$filters['vendor_zip_filter'] = '(hikam_vendor.'. $vendorZipColumn.' IS NULL OR hikam_vendor.'. $vendorZipColumn.' = \'\' OR hikam_vendor.'. $vendorZipColumn.' = \'*\')';
		if(in_array('city', $filter_mode))
			$filters['vendor_city_filter'] = '(hikam_vendor.'. $vendorCitiesColumn.' IS NULL OR hikam_vendor.'. $vendorCitiesColumn.' = \'\' OR hikam_vendor.'. $vendorCitiesColumn.' = \'*\')';
	}

	private function mergeFilters(&$filters) {
		if(isset($filters['vendor_city_filter']) && isset($filters['vendor_zip_filter'])) {
			$filters['vendor_location_filter'] = '(' . $filters['vendor_city_filter'] . ') OR (' . $filters['vendor_zip_filter'] . ')';
			unset($filters['vendor_city_filter']);
			unset($filters['vendor_zip_filter']);
		}
	}
}
