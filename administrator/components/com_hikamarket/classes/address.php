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
class hikamarketAddressClass extends hikamarketClass {

	protected $tables = array('shop.address');
	protected $pkeys = array('address_id');
	protected $toggle = array('address_published' => 'address_id');

	public function frontSaveForm($user_id, $area = '') {
		$fieldsClass = hikamarket::get('shop.class.field');
		$data = hikaInput::get()->get('data', array(), 'array');

		$ret = false;

		$currentTask = 'user_address';
		if(!empty($data[$currentTask])) {
			$oldAddress = null;
			$user_address = $fieldsClass->getFilteredInput(array($currentTask, 'address'), $oldAddress, false, 'data', false, $area);

			if(!empty($user_address)) {
				$user_address->address_user_id = $user_id;
				$id = (int)@$user_address->address_id;

				$result = $this->save($user_address);
				if($result) {
					$ret = new stdClass();
					$ret->id = $result;
					$ret->previous_id = $id;
				}
			}
		}

		return $ret;
	}

	public function save(&$addressData, $type = null) {
		$app = JFactory::getApplication();
		if(!hikamarket::isAdmin()) {
			$vendor_id = hikamarket::loadVendor(false, false);
			if($vendor_id > 1) return false;
			if(!hikamarket::acl('user/edit/address')) return false;
		}

		$addrClass = hikamarket::get('shop.class.address');

		if($type === null && !empty($addressData->address_type))
			$type = $addressData->address_type;
		if($type === null && !empty($addressData->address_id)) {
			$old = $addrClass->get($addressData->address_id);
			$type = @$old->address_type;
		}

		$new = true;
		if(!empty($addressData->address_id)) {
			$new = false;
			$oldData = $this->get($addressData->address_id);

			if(!empty($addressData->address_vat) && $oldData->address_vat != $addressData->address_vat) {
				if(!$addrClass->_checkVat($addressData))
					return false;
			}

			$orderClass = hikamarket::get('shop.class.order');
			if(!empty($addressData->address_id) && !empty($oldData->address_published) && $orderClass->addressUsed($addressData->address_id)) {
				unset($addressData->address_id);
				$new = true;
				$oldData->address_published = 0;
				parent::save($oldData);
			}
		} elseif(!empty($addressData->address_vat)) {
			if(!$addrClass->_checkVat($addressData))
				return false;
		}

		if(empty($addressData->address_id) && empty($addressData->address_user_id))
			return false;

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikamarket');
		$do = true;
		if($new) {
			if(!empty($addressData->address_user_id)) {
				$query = 'SELECT count(*) as cpt FROM '.hikamarket::table('shop.address').' WHERE address_user_id = '.(int)$addressData->address_user_id.' AND address_published = 1 AND address_default = 1';
				$this->db->setQuery($query);
				$ret = $this->db->loadObject();
				if($ret->cpt == 0)
					$addressData->address_default = 1;
			}

			$app->triggerEvent('onBeforeAddressCreate', array(&$addressData, &$do));
		} else {
			$app->triggerEvent('onBeforeAddressUpdate', array(&$addressData, &$do));
		}

		if(!$do)
			return false;

		$status = parent::save($addressData);
		if(!$status)
			return false;

		if(!empty($addressData->address_default) && !empty($oldData->address_id)) {
			$query = 'UPDATE '.hikamarket::table('shop.address').' SET address_default = 0 '.
				' WHERE address_user_id = '.(int)$oldData->address_user_id.' AND address_id != '.(int)$oldData->address_id;
			if(!empty($type))
				$query .= ' AND address_type = ' . $this->db->Quote($type);
			$this->db->setQuery($query);
			$this->db->execute();

			$shopConfig = hikamarket::config(false);
			if(!empty($type) && !$shopConfig->get('checkout_legacy', 0)) {
				$query = 'SELECT * FROM '.hikamarket::table('shop.address').' WHERE address_user_id = '.(int)$oldData->address_user_id.' AND address_type IN (\'\', \'both\') AND address_published = 1 AND address_default = 1';
				$this->db->setQuery($query);
				$defaultAddresses = $this->db->loadObjectList();
				if(!empty($defaultAddresses)) {
					foreach($defaultAddresses as $defaultAddress) {
						$defaultAddress->address_type = 'billing';
						$duplicate = hikashop_copy($defaultAddress);
						$duplicate->address_type = 'shipping';
						unset($duplicate->address_id);
						if($type == $defaultAddress->address_type)
							$defaultAddress->address_default = 0;
						else
							$duplicate->address_default = 0;
						parent::save($defaultAddress);
						parent::save($duplicate);
					}
				}
			}
		}


		if($new)
			$app->triggerEvent('onAfterAddressCreate', array(&$addressData));
		else
			$app->triggerEvent('onAfterAddressUpdate', array(&$addressData));

		return $status;
	}

	public function delete(&$address) {
		$app = JFactory::getApplication();
		if(!hikamarket::isAdmin()) {
			$vendor_id = hikamarket::loadVendor(false, false);
			if($vendor_id > 1) return false;
			if(!hikamarket::acl('user/edit/address')) return false;
		}

		$address_id = 0;
		if(is_object($address))
			$address_id = (int)$address->address_id;
		else
			$address_id = (int)$address;

		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app->triggerEvent('onBeforeAddressDelete', array(&$address_id, &$do));
		if(!$do)
			return false;

		$orderClass = hikamarket::get('shop.class.order');
		if($orderClass->addressUsed($address_id)) {
			$addr = new stdClass();
			$addr->address_id = $address_id;
			$addr->address_published = 0;
			$status = parent::save($addr);
		} else {
			$status = parent::delete($address_id);
		}
		if($status)
			$app->triggerEvent('onAfterAddressDelete', array(&$address_id));
		return $status;
	}

	public function maxiFormat($address, $fields = null, $nlbr = false) {
		static $templateAddress = null;
		static $templateClassicalMode = true;
		if($templateAddress === null) {
			$config = hikamarket::config();
			$tpl = $config->get('address_template', '');
			if(!empty($tpl)) {
				$templateAddress = $tpl;
			} else {
				$params = null;
				$js = null;
				$templateAddress = hikamarket::getLayout('shop.address', 'address_template', $params, $js);
			}
		}

		$ret = ''.$templateAddress;
		if($templateClassicalMode) {
			if(!empty($fields)) {
				if(empty($this->fieldsClass))
					$this->fieldsClass = hikamarket::get('shop.class.field');

				foreach($fields as $field){
					$fieldname = $field->field_namekey;
					$ret = str_replace('{'.$fieldname.'}', $this->fieldsClass->show($field, @$address->$fieldname), $ret);
				}
			} elseif(!empty($address)) {
				foreach($address as $k => $v) {
					if(is_string($v))
						$ret = str_replace('{' . $k . '}', $v, $ret);
				}
			}
			$ret = str_replace(array("\r\n\r\n","\n\n","\r\r"),array("\r\n","\n","\r"), trim(preg_replace('#{(?:(?!}).)*}#i','',$ret)));
		} else {

		}

		if($nlbr)
			$ret = str_replace(array("\r\n","\r","\n"), '<br/>', $ret);
		return $ret;
	}

	public function miniFormat($address, $fields = null, $format = '') {
		$shopConfig = hikamarket::config(false);
		$ret = $shopConfig->get('mini_address_format', '');
		if(empty($ret))
			$ret = '{address_lastname} {address_firstname} - {address_city}, {address_state} ({address_country})';
		if(!empty($format))
			$ret = $format;
		if(!empty($fields)) {
			if(empty($this->fieldsClass))
				$this->fieldsClass = hikamarket::get('shop.class.field');

			foreach($fields as $field) {
				$fieldname = $field->field_namekey;
				$ret = str_replace('{'.$fieldname.'}', $this->fieldsClass->show($field, @$address->$fieldname), $ret);
			}
		} else {
			foreach($address as $k => $v) {
				if(is_string($v))
					$ret = str_replace('{' . $k . '}', $v, $ret);
			}
		}
		$ret = preg_replace('#{[-_a-zA-Z0-9]+}#iU', '', $ret);
		return $ret;
	}

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		if(empty($options['url_params']))
			return $ret;

		if(isset($options['url_params']['USER_ID']) && (int)$options['url_params']['USER_ID'] > 0) {
			$query = 'SELECT * FROM ' . hikamarket::table('shop.address').
				' WHERE address_published = 1 AND address_user_id = ' . (int)$options['url_params']['USER_ID'];

			if(!empty($options['url_params']['ADDR_TYPE']))
				$query .= ' AND address_type IN (\'\', '.$this->db->Quote($options['url_params']['ADDR_TYPE']).')';

			$this->db->setQuery($query);
			$addresses = $this->db->loadObjectList('address_id');
		}

		if(!empty($value)) {

		}

		if(!empty($addresses)) {
			$fields = null;
			$config = hikamarket::config();
			if(empty($this->fieldsClass))
				$this->fieldsClass = hikamarket::get('shop.class.field');
			$null = null;
			if($config->get('address_show_details', 0)) {
				$fields = $this->fieldsClass->getFields('display:vendor_user_show=1', $null, 'address');
			} else {
				$fields = $this->fieldsClass->getFields('field_frontcomp', $null, 'address');
			}

			if(empty($this->shopAddressClass))
				$this->shopAddressClass = hikamarket::get('shop.class.address');
			$this->shopAddressClass->loadZone($addresses);

			foreach($addresses as $k => $v) {
				$v->address_mini_format = $this->miniFormat($v, $fields);

				$ret[0][$k] = $v;
			}
			asort($ret[0]);
		}
		unset($addresses);

		return $ret;
	}
}
