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
class hikamarketConfigClass extends hikamarketClass {
	protected $toggle = array('config_value' => 'config_namekey');
	public function load() {
		$query = 'SELECT * FROM '.hikamarket::table('config');
		$this->db->setQuery($query);
		$this->values = $this->db->loadObjectList('config_namekey');
		if(!empty($this->values['default_params']->config_value)) {
			$this->values['default_params']->config_value = hikamarket::unserialize(base64_decode($this->values['default_params']->config_value));
		}
	}

	public function set($namekey, $value = null) {
		if(empty($this->values))
			$this->values = array();
		$this->values[$namekey] = new stdClass();
		$this->values[$namekey]->config_value = $value;
		$this->values[$namekey]->config_namekey = $namekey;
		return true;
	}

	public function get($namekey, $default = null) {
		if(isset($this->values[$namekey])){
			if(preg_match('#^(menu_|params_)[0-9]+$#',$namekey) && !empty($this->values[$namekey]->config_value) && is_string($this->values[$namekey]->config_value)) {
				$this->values[$namekey]->config_value = hikamarket::unserialize(base64_decode($this->values[$namekey]->config_value));
			}
			return $this->values[$namekey]->config_value;
		}
		return $default;
	}

	public function save(&$configObject, $default = false) {
		if(empty($this->values)) {
			$this->load();
		}

		$query = 'REPLACE INTO '.hikamarket::table('config').' (config_namekey,config_value'.($default?',config_default':'').') VALUES ';
		$params = array();

		$queryShop = 'REPLACE INTO '.hikamarket::table('shop.config').' (config_namekey,config_value) VALUES ';
		$paramsShop = array();

		if(is_object($configObject)) {
			if(isset($configObject->config_value) && isset($configObject->config_namekey)) {
				$n = new stdClass();
				$n->{$configObject->config_namekey} = $configObject->config_value;
				$configObject = $n;
			} else
				$configObject = get_object_vars($configObject);
		}

		$concatFields = array(
			'updatable_order_statuses',
			'valid_order_statuses',
			'admin_notify_subsale',
			'stats_valid_order_statuses',
			'vendor_email_order_status_notif_statuses',
			'vendor_extra_categories',
			'vendor_show_modules'
		);
		$listConcatFields = array(
			'default_template_id'
		);

		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
		foreach($configObject as $namekey => $value) {
			$shop = false;
			if(substr($namekey, 0, 5) == 'shop.') {
				$shop = true;
				$namekey = substr($namekey, 5);
			}
			if( $namekey == 'default_params' || $namekey == 'vendor_statistics' || preg_match('#^(menu_|params_)[0-9]+$#',$namekey) ) {
				$value = base64_encode(serialize($value));
			}
			if(is_array($value) && in_array($namekey, $concatFields))
				$value = trim(implode(',', $value), ',');
			if(is_array($value) && in_array($namekey, $listConcatFields))
				$value = ',' . trim(implode(',', $value), ',') . ',';
			if(is_array($value))
				continue;

			if(!$shop) {
				if(empty($this->values[$namekey]))
					$this->values[$namekey] = new stdClass();
				$this->values[$namekey]->config_value = $value;
				if( !isset($this->values[$namekey]->config_default) ) {
					$this->values[$namekey]->config_default = $this->values[$namekey]->config_value;
				}
				$params[] = '('.$this->db->Quote(strip_tags($namekey)).','.$this->db->Quote($safeHtmlFilter->clean($value, 'string')).($default?','.$this->db->Quote($this->values[$namekey]->config_default):'').')';
			} else {
				$paramsShop[] = '('.$this->db->Quote(strip_tags($namekey)).','.$this->db->Quote($safeHtmlFilter->clean($value, 'string')).')';
			}
		}

		$ret = false;
		if(!empty($paramsShop)) {
			$queryShop .= implode(',',$paramsShop);
			$this->db->setQuery($queryShop);
			$ret = $this->db->execute();
		}
		if(!empty($params)) {
			$query .= implode(',',$params);
			$this->db->setQuery($query);
			$ret = $this->db->execute();
		}

		return $ret;
	}

	public function reset(){
		$query = 'UPDATE '.hikamarket::table('config').' SET config_value = config_default';
		$this->db->setQuery($query);
		$this->values = $this->db->execute();
	}

	public function vendorget($vendor, $namekey, $default = null) {
		if(is_numeric($vendor)) {
			$vendorClass = hikamarket::get('class.vendor');
			$vendor = $vendorClass->get( (int)$vendor );
		}

		$vendor_acl_default_public_group = (int)$this->get('vendor_acl_default_public_group', 0);

		if(empty($vendor_acl_default_public_group)) {
			if(is_string($vendor->vendor_access) && strpos($vendor->vendor_access, '@') === false)
				return $default;
			if(is_array($vendor->vendor_access)) {
				$a = implode(',', $vendor->vendor_access);
				if(strpos($a, '@') === false)
					return $default;
			}
		}

		$joomla_acl = hikamarket::get('type.joomla_acl');
		$gs = $joomla_acl->getList();
		$groups = array();
		foreach($gs as $g) {
			$groups[$g->id] = $g;
		}
		unset($gs);

		if(is_string($vendor->vendor_access)) {
			$vendor_access = explode(',', trim(strtolower($vendor->vendor_access), ','));
		} else {
			$vendor_access = $vendor->vendor_access;
		}

		if(!empty($vendor_acl_default_public_group)) {
			$vendor_access[] = '@1';
		}

		foreach($vendor_access as $k => $ax) {
			if(substr($ax,0,1) != '@')
				continue;
			$ax_id = (int)substr($ax,1);
			if($ax_id == 0)
				continue;

			$group = (isset($groups[$ax_id])) ? $group = $groups[$ax_id] : null;
			while(!empty($group)) {
				$access = $this->get('vendor_options_acl_'.$group->id, '');
				if(!empty($access)) {
					$access = hikamarket::unserialize($access);
					if(isset($access[$namekey]))
						return $access[$namekey];
				}
				$group = (isset($groups[$group->parent_id])) ? $group = $groups[$group->parent_id] : null;
			}
		}
		return $default;
	}
}
