<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikamarketVendorlocationfilter extends JPlugin {

	protected $db = null;

	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		if(!isset($this->params)) {
			$plugin = JPluginHelper::getPlugin('hikamarket', 'vendorlocationfilter');
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
		$init = true;
		return true;
	}

	private function dbEscape($data) {
		if(empty($this->db))
			$this->db = JFactory::getDBO();
		if(HIKASHOP_J30)
			return $this->db->escape($data, true);
		return $this->db->getEscaped($data, true);
	}

	public function onBeforeVendorListingDisplay(&$view, &$params) {
		$app = JFactory::getApplication();
		if(hikamarket::isAdmin() || !$this->init())
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
			if(empty($location_search))
				$location_search = null;
		}

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
				$this->pushEmptyFilter($params, $filter_mode, $vendorZipColumn, $vendorCitiesColumn);
				$this->mergeFilters($params);
			}
			return;
		}

		$addressClass = hikashop_get('class.address');
		if(!empty($user_id))
			$addresses = $addressClass->loadUserAddresses($user_id);
		$userZip = 0;
		$userCity = '';
		if(!empty($addresses)) {
			$address = reset($addresses);
			if(!empty($address->address_post_code))
				$userZip = trim($address->address_post_code);
			if(!empty($address->address_city))
				$userCity = trim($address->address_city);
		}

		if((int)$this->params->get('zipcode_digits', 0))
			$userZip = '' . ((int)$userZip);

		$zipMode = in_array('zip', $filter_mode);
		$cityMode = in_array('city', $filter_mode);

		if($location_search === null && (!$zipMode || empty($userZip)) && (!$cityMode || empty($userCity))) {
			$this->pushEmptyFilter($params, $filter_mode, $vendorZipColumn, $vendorCitiesColumn);
			$this->mergeFilters($params);
			return;
		}

		if(in_array('zip', $filter_mode)) {
			$f = array();
			$stars = '*';
			if($location_search !== null || !empty($userZip)) {
				if($location_search !== null) {
					$f[] = 'vendor.'.$vendorZipColumn.' LIKE \'%'.$this->dbEscape($location_search).'%\'';
				} else {
					$f[] = 'vendor.'.$vendorZipColumn.' LIKE \'%'.$this->dbEscape($userZip).'%\'';
				}
				if(!empty($userZip)) {
					for($i = strlen($userZip) - 1; $i > 1; $i--) {
						$z = substr($userZip, 0, $i) . $stars;
						$f[] = 'vendor.'.$vendorZipColumn.' LIKE \'%'.$this->dbEscape($z).'%\'';
						$stars .= '*';
					}
				}
			}
			unset($stars);

			if(!empty($f))
				$params['filter']['zip_filter'] = '('.implode(') OR (', $f).')';
		}

		if(in_array('city', $filter_mode) && ($location_search !== null || !empty($userCity))) {
			if($location_search !== null) {
				$params['filter']['city_filter'] = 'vendor.'.$vendorCitiesColumn.' LIKE \'%'.$this->dbEscape($location_search).'%\'';
			} else {
				$params['filter']['city_filter'] = 'vendor.'.$vendorCitiesColumn.' LIKE \'%'.$this->dbEscape($userCity).'%\'';
			}
		}

		$this->mergeFilters($params);
	}

	private function pushEmptyFilter(&$params, $filter_mode, $vendorZipColumn, $vendorCitiesColumn) {
		if(in_array('zip', $filter_mode))
			$params['filter']['hide_zip_guest'] = '(vendor.'. $vendorZipColumn.' IS NULL OR vendor.'. $vendorZipColumn.' = \'\' OR vendor.'. $vendorZipColumn.' = \'*\')';
		if(in_array('city', $filter_mode))
			$params['filter']['hide_city_guest'] = '(vendor.'. $vendorCitiesColumn.' IS NULL OR vendor.'. $vendorCitiesColumn.' = \'\' OR vendor.'. $vendorCitiesColumn.' = \'*\')';
	}

	private function mergeFilters(&$params) {
		if(isset($params['filter']['zip_filter']) && isset($params['filter']['city_filter'])) {
			$params['filter']['location_filter'] = '(' . $params['filter']['zip_filter'] . ') OR (' . $params['filter']['city_filter'] . ')';
			unset($params['filter']['zip_filter']);
			unset($params['filter']['city_filter']);
		}
	}
}
