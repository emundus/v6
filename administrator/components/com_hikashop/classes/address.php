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
class hikashopAddressClass extends hikashopClass {
	var $tables = array('address');
	var $pkeys = array('address_id');

	function getByUser($user_id) {
		$query = 'SELECT a.* FROM '.hikashop_table('address').' AS a '.
			' WHERE a.address_user_id = '.(int)$user_id.' and a.address_published = 1 '.
			' ORDER BY a.address_default DESC, a.address_id DESC';
		$this->database->setQuery($query);
		$addresses = $this->database->loadObjectList('address_id');
		$type = 'both';

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();
		$app->triggerEvent('onUserAddressesLoad', array( &$addresses, $user_id, $type) );

		return $addresses;
	}

	function isAddressValid(&$addr, $options = array()) {
		if(empty($addr->address_published))
			return false;

		if(!empty($options['user_id'])) {
			if($addr->address_user_id != $options['user_id'])
				return false;
		}


		$null = null;
		$fieldClass = hikashop_get('class.field');
		$field_type = $addr->address_type;
		if(!empty($field_type))
			$field_type .= '_address';

		if(empty($addr->address_state) && !empty($addr->address_country)) {
			$zoneClass = hikashop_get('class.zone');
			$states = $zoneClass->getChildren($addr->address_country);
			if(empty($states)) {
				$addr->address_state = 'no_state_found';
			} else {
				$zones_pulished = $zoneClass->getZones($states, 'zone_published', 'zone_namekey', true);
				if(empty($zones_pulished)) {
					$addr->address_state = 'no_state_found';
				} else {
					$published = false;
					foreach($zones_pulished as $zone_published) {
						if($zone_published)
							$published = true;
					}
					if(!$published) {
						$addr->address_state = 'no_state_found';
					}
				}
			}
		}

		$data = array($field_type => get_object_vars($addr));
		$address = $fieldClass->getFilteredInput($field_type, $null, 'ret', $data, false, 'frontcomp');

		if(empty($address)) {
			return false;
		}
		return true;
	}

	function get($element, $default = null) {
		static $cachedElements = array();
		if($element == 'reset_cache') {
			$cachedElements = array();
			return true;
		}

		if((int)$element == 0)
			return true;

		$app = JFactory::getApplication();
		if((int)$element < 0) {
			$addresses = $app->getUserState(HIKASHOP_COMPONENT.'.addresses', array());
			$i = (int)$element;
			if(isset($addresses[$i]))
				$cachedElements[$element] = hikashop_copy($addresses[$i]);
		} else {
			if(!isset($cachedElements[$element]))
				$cachedElements[$element] = parent::get($element, $default);
		}

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app->triggerEvent('onUserAddressLoad', array( &$cachedElements[$element], $element) );

		if(!is_object($cachedElements[$element]))
			return $cachedElements[$element];

		$copy = new stdClass();
		foreach(get_object_vars($cachedElements[$element]) as $key => $val) {
			$copy->$key = $val;
		}

		return $copy;
	}

	function loadZone(&$addresses, $type = 'name', $display = 'frontcomp') {
		if(empty($this->fields) || $this->loadedFields != $display) {
			$fieldClass = hikashop_get('class.field');
			$fields = $fieldClass->getData($display, 'address');
			$this->fields =& $fields;
			$this->loadedFields = $display;
		} else {
			$fields =& $this->fields;
		}

		if(empty($fields))
			return;

		$namekeys = array();
		foreach($fields as $field) {
			if($field->field_type == 'zone') {
				$namekeys[$field->field_namekey] = $field->field_namekey;
			}
		}

		if(empty($namekeys))
			return;

		$zones = array();
		$quoted_zones = array();
		foreach($addresses as $address) {
			foreach($namekeys as $namekey) {
				if(empty($address->$namekey))
					continue;

				if(is_array($address->$namekey))
					$address->$namekey = reset($address->$namekey);
				$zones[$address->$namekey] = $address->$namekey;
				$quoted_zones[$address->$namekey] = $this->database->Quote($address->$namekey);
			}
		}

		if(empty($zones))
			return;

		if(!in_array($type, array('name', 'object'))) {
			$this->_getParents($zones,$addresses,$namekeys);

			return;
		}

		$query = 'SELECT * FROM '.hikashop_table('zone').' WHERE zone_namekey IN ('.implode(',',$quoted_zones).');';
		$this->database->setQuery($query);
		$zones = $this->database->loadObjectList('zone_namekey');
		if(empty($zones))
			return;

		foreach($addresses as $k => $address) {
			foreach($namekeys as $namekey) {
				if(empty($address->$namekey) || empty($zones[$address->$namekey]))
					continue;

				$addresses[$k]->{$namekey.'_orig'} = $addresses[$k]->$namekey;
				if($type == 'name') {
					$addresses[$k]->{$namekey.'_id'} = $zones[$address->$namekey]->zone_id;
					$addresses[$k]->{$namekey.'_code_2'} = $zones[$address->$namekey]->zone_code_2;
					$addresses[$k]->{$namekey.'_code_3'} = $zones[$address->$namekey]->zone_code_3;
					$addresses[$k]->{$namekey.'_name'} = $zones[$address->$namekey]->zone_name;

					if(is_numeric($zones[$address->$namekey]->zone_name_english)) {
						$addresses[$k]->$namekey = $zones[$address->$namekey]->zone_name;
					} else {
						$addresses[$k]->$namekey = $zones[$address->$namekey]->zone_name_english;
					}

					$addresses[$k]->{$namekey.'_name_english'} = $addresses[$k]->$namekey;
				} else {
					$addresses[$k]->$namekey = $zones[$address->$namekey];
				}
			}
		}
	}

	function displayAddress(&$fields, &$address, $view = 'address', $text = false) {
		$params = new HikaParameter('');
		$params->set('address', $address);

		$js = '';
		$fieldsClass = hikashop_get('class.field');
		$html = '' . hikashop_getLayout($view, 'address_template', $params, $js);
		if(!empty($fields)) {
			foreach($fields as $field) {
				$fieldname = $field->field_namekey;
				if(!empty($address->$fieldname))
					$html = str_replace('{'.$fieldname.'}', $fieldsClass->show($field,$address->$fieldname), $html);
			}
		}

		$html = str_replace("\n\n", "\n", trim(str_replace("\r\n","\n", trim(preg_replace('#{(?:(?!}).)*}#i', '', $html))), "\n"));
		if(!$text) {
			$html = str_replace("\n", "<br/>\n", $html);
		}
		return $html;
	}

	function loadUserAddresses($user_id, $type = '') {
		static $addresses = array();

		if(empty($type))
			$type = 'both';

		if(isset($addresses[$user_id][$type]))
			return $addresses[$user_id][$type];

		if($user_id === 'reset_cache') {
			$addresses = array();
			return true;
		}
		$app = JFactory::getApplication();

		if((int)$user_id == 0) {
			$session_addresses = $app->getUserState(HIKASHOP_COMPONENT.'.addresses', array());
			return hikashop_copy($session_addresses);
		}

		if((int)$user_id == 0)
			return array();

		$filters = array('a.address_user_id = '.(int)$user_id, 'a.address_published = 1');

		if($type != 'both')
			$filters[] = 'a.address_type IN ('. $this->database->Quote($type) . ',' . $this->database->Quote('both') . ',' . $this->database->Quote('') . ')';

		$query = 'SELECT a.* FROM '.hikashop_table('address').' AS a WHERE '.implode(' AND ', $filters).' ORDER BY a.address_default DESC, a.address_id DESC';
		$this->database->setQuery($query);
		$addresses[$user_id][$type] = $this->database->loadObjectList('address_id');


		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app->triggerEvent('onUserAddressesLoad', array( &$addresses[$user_id][$type], $user_id, $type) );

		return $addresses[$user_id][$type];
	}

	public function cleanCaches() {
		$this->get('reset_cache');
		$this->loadUserAddresses('reset_cache');
		return true;
	}

	function _getParents(&$zones, &$addresses, &$fields) {
		$namekeys = array();
		foreach($zones as $zone) {
			$namekeys[] = $this->database->Quote($zone);
		}

		$query = 'SELECT a.* FROM '.hikashop_table('zone_link').' AS a WHERE a.zone_child_namekey IN ('.implode(',',$namekeys).');';
		$this->database->setQuery($query);
		$parents = $this->database->loadObjectList();

		if(empty($parents))
			return;

		$childs = array();
		foreach($parents as $parent) {
			foreach($addresses as $k => $address) {
				foreach($fields as $field) {
					if(!is_array($addresses[$k]->$field)) {
						$addresses[$k]->$field = array($addresses[$k]->$field);
					}

					foreach($addresses[$k]->$field as $value) {
						if($value == $parent->zone_child_namekey && !in_array($parent->zone_parent_namekey,$addresses[$k]->$field)) {
							$values =& $addresses[$k]->$field;
							$values[] = $parent->zone_parent_namekey;
							$childs[$parent->zone_parent_namekey] = $parent->zone_parent_namekey;
						}
					}
				}
			}
		}
		if(!empty($childs)) {
			$this->_getParents($childs,$addresses,$fields);
		}
	}

	public function save(&$addressData, $order_id = 0, $type = 'shipping') {
		$app = JFactory::getApplication();
		$new = empty($addressData->address_id);

		$do = true;
		if(!empty($addressData->address_id) && $addressData->address_id > 0) {
			$oldData = $this->get($addressData->address_id);

			if(!empty($addressData->address_vat) && $oldData->address_vat != $addressData->address_vat && !$this->_checkVat($addressData)) {
				return false;
			}

			$user_id = hikashop_loadUser();
			$allowed = array($user_id => $user_id);
			$checkoutHelper = hikashop_get('helper.checkout');
			$cart = $checkoutHelper->getCart();
			if(!empty($cart->user_id))
				$allowed[$cart->user_id] = $cart->user_id;
			if(!hikashop_isClient('administrator') && (!in_array($oldData->address_user_id, $allowed) || !$oldData->address_published)) {
				$do = false;
			}

			$orderClass = hikashop_get('class.order');
			if($order_id) {
				unset($addressData->address_id);
				$addressData->address_default = 0;
				$addressData->address_type = $type;
				$addressData->address_published = 0;
				$new = true;
			}elseif($oldData->address_published != 0 && $orderClass->addressUsed($addressData->address_id, $order_id, $type)) {
				unset($addressData->address_id);
				$new = true;
				$oldData->address_published = 0;
				parent::save($oldData);
				$this->cleanCaches();
			}
		} elseif(!empty($addressData->address_vat) && !$this->_checkVat($addressData)) {
			return false;
		}

		if(empty($addressData->address_id) && empty($addressData->address_user_id) && empty($order_id))
			return false;

		JPluginHelper::importPlugin('hikashop');
		if($new) {
			if(!empty($addressData->address_user_id)) {
				$query = 'SELECT count(*) as cpt FROM '.hikashop_table('address').' WHERE address_user_id = '.(int)$addressData->address_user_id.' AND address_published = 1 AND address_default = 1';
				if(in_array(@$addressData->address_type, array('shipping', 'billing'))) {
					$query .= ' AND address_type IN (\'\', \'both\', '.$this->database->Quote($addressData->address_type).')';
				}
				$this->database->setQuery($query);
				$ret = $this->database->loadObject();
				if($ret->cpt == 0) {
					$addressData->address_default = 1;
				}
			}

			$app->triggerEvent('onBeforeAddressCreate', array( &$addressData, &$do) );
		} else {
			$app->triggerEvent('onBeforeAddressUpdate', array( &$addressData, &$do) );
		}

		if(!$do)
			return false;

		if(empty($addressData->address_id) || (int)$addressData->address_id > 0) {

			if($new) {
				if((!isset($addressData->address_published) || !empty($addressData->address_published)) && isset($addressData->address_type) && in_array($addressData->address_type, array('', 'both'))) {
					$config = hikashop_config();
					if($config->get('distinguish_new_addresses', 1) && !$config->get('checkout_legacy',0)) {
						$addressData->address_type = 'billing'; 
						$duplicatedAddress = hikashop_copy($addressData);
						$duplicatedAddress->address_type = 'shipping';
						unset($duplicatedAddress->address_id);
						parent::save($duplicatedAddress);
					}
				}
			}

			$status = parent::save($addressData);
		} else {
			$addresses = $app->getUserState(HIKASHOP_COMPONENT.'.addresses', array());
			$addresses[ (int)$addressData->address_id ] = $addressData;
			$app->setUserState(HIKASHOP_COMPONENT.'.addresses', $addresses);
			$status = true;
		}
		if(!$status)
			return false;

		$this->cleanCaches();

		if((!isset($addressData->address_published) || !empty($addressData->address_published)) && !empty($addressData->address_default) && !empty($oldData)) {
			$query = 'UPDATE '.hikashop_table('address').' SET address_default = 0 WHERE address_user_id = '.(int)$oldData->address_user_id.' AND address_id != '.(int)$status;
			if(!empty($type)) {
				$query .= ' AND address_type=' . $this->database->Quote($type);
			}
			$this->database->setQuery($query);
			$this->database->execute();

			if(!empty($type)) {
				$config = hikashop_config();
				if(!$config->get('checkout_legacy',0)) {
					$query = 'SELECT * FROM #__hikashop_address WHERE address_user_id = '.(int)$oldData->address_user_id.' AND address_type IN (\'\', \'both\') AND address_published = 1 AND address_default = 1';
					$this->database->setQuery($query);
					$addresses = $this->database->loadObjectList();
					if(!empty($addresses)) {
						foreach($addresses as $alreadyDefaultAddress) {
							$alreadyDefaultAddress->address_type = 'billing';
							$duplicatedAddress = hikashop_copy($alreadyDefaultAddress);
							$duplicatedAddress->address_type = 'shipping';
							unset($duplicatedAddress->address_id);
							if($type == $alreadyDefaultAddress->address_type)
								$alreadyDefaultAddress->address_default = 0;
							else
								$duplicatedAddress->address_default = 0;
							parent::save($alreadyDefaultAddress);
							parent::save($duplicatedAddress);
						}
					}
				}
			}
		}

		if(!empty($oldData) && (int)$oldData->address_id != (int)$status) {
			$query = 'UPDATE '.hikashop_table('cart').' SET cart_billing_address_id = '.(int)$status.' WHERE user_id = '.(int)$oldData->address_user_id.' AND cart_billing_address_id = '.(int)$oldData->address_id;
			$this->database->setQuery($query);
			$this->database->execute();

			$query = 'UPDATE '.hikashop_table('cart').' SET cart_shipping_address_ids = '.(int)$status.' WHERE user_id = '.(int)$oldData->address_user_id.' AND cart_shipping_address_ids = '.(int)$oldData->address_id;
			$this->database->setQuery($query);
			$this->database->execute();

			if(!empty($app) && !hikashop_isClient('administrator')) {
				$cartClass = hikashop_get('class.cart');
				$cartClass->get('reset_cache');
			}

			if((int)$app->getUserState(HIKASHOP_COMPONENT.'.'.'billing_address', 0) == (int)$oldData->address_id) {
				$app->setUserState(HIKASHOP_COMPONENT.'.'.'billing_address', (int)$status);
			}
			if((int)$app->getUserState(HIKASHOP_COMPONENT.'.'.'shipping_address', 0) == (int)$oldData->address_id) {
				$app->setUserState(HIKASHOP_COMPONENT.'.'.'shipping_address', (int)$status);
			}
		}

		if($new) {
			$addressData->address_id = (int)$status;
			$app->triggerEvent( 'onAfterAddressCreate', array( &$addressData ) );
		} else {
			$app->triggerEvent( 'onAfterAddressUpdate', array( &$addressData ) );
		}

		return $status;
	}

	public function frontSaveForm($task = '') {
		$fieldsClass = hikashop_get('class.field');
		$data = hikaInput::get()->get('data', array(), 'array');
		$ret = array();

		$user_id = hikashop_loadUser(false);

		if(empty($task) && !empty($data['address'])) {
			$type = '';
			if(!empty($data['address']['address_id'])) {
				$oldData = $this->get($data['address']['address_id']);
				if(!empty($oldData->address_type))
					$type = $oldData->address_type.'_';
			}elseif(!empty($data['address']['address_type']) && in_array($data['address']['address_type'], array('billing','shipping')))
				$type = $data['address']['address_type'].'_';
			$type .= 'address';

			$formdata = array($type => $data['address']);
			$null = null;
			$address = $fieldsClass->getFilteredInput($type, $null, 'ret', $formdata, false, 'frontcomp');

			if(empty($address))
				return array('id' => false, 'error' => $fieldsClass->messages);

			$address_id = 0;
			if(!empty($data['address']['address_id'])) {
				$address->address_id = (int)$data['address']['address_id'];
				$address_id = (int)$address_id;
			}
			if(!empty($data['address']['address_user_id']))
				$address->address_user_id = (int)$data['address']['address_user_id'];
			else
				$address->address_user_id = hikashop_loadUser(false);

			if(empty($address->address_id) && !empty($data['address']['address_type']))
				$address->address_type = $data['address']['address_type'];
			$ret = $this->save($address);

			if($address_id != $ret)
				return array('id' => $ret, 'previous_id' => $address_id);

			return $ret;
		}

		$currentTask = 'billing_address';
		if( (empty($task) || $task == $currentTask) && !empty($data[$currentTask])) {
			$oldAddress = null;
			$billing_address = $fieldsClass->getInput(array($currentTask, 'address'), $oldAddress);

			if(!empty($billing_address)) {
				$billing_address->address_user_id = $user_id;
				$id = (int)@$billing_address->address_id;

				$result = $this->save($billing_address, 0, 'billing');
				if(!$result)
					return false;

				$r = new stdClass();
				$r->id = $result;
				$r->previous_id = $id;
				$ret[$currentTask] = $r;
			}
		}

		$same_address = hikaInput::get()->getString('same_address');
		$currentTask = 'shipping_address';
		if( (empty($task) || $task == $currentTask) && !empty($data[$currentTask]) && $same_address != 'yes') {
			$oldAddress = null;
			$shipping_address = $fieldsClass->getInput(array($currentTask, 'address'), $oldAddress);

			if(!empty($shipping_address)) {
				$shipping_address->address_user_id = $user_id;
				$id = (int)@$shipping_address->address_id;

				$result = $this->save($shipping_address, 0, 'shipping');
				if(!$result)
					return false;

				$r = new stdClass();
				$r->id = $result;
				$r->previous_id = $id;
				$ret[$currentTask] = $r;
			}
		}

		return $ret;
	}

	public function getCurrentUserAddress($type = '', $user = null) {
		static $cache = array();

		if($type == 'reset_cache') {
			$cache = array();
			return;
		}

		$app = JFactory::getApplication();
		$user_id = 0;
		if(!empty($user))
			$user_id = is_object($user) ? (int)$user->user_id : (int)$user;
		if(empty($user) || empty($user_id))
			$user_id = hikashop_loadUser(false);

		if(empty($type) || !in_array($type, array('billing', 'shipping'))) {
			$config =& hikashop_config();
			$type = $config->get('tax_zone_type', 'shipping');
		}
		if(empty($cache[$type]))
			$cache[$type] = array();

		if(isset($cache[$type][$user_id]))
			return $cache[$type][$user_id];

		$user_address = 0;

		if(!empty($user_id)) {
			$cartClass = hikashop_get('class.cart');
			$cart = $cartClass->get(0);
			if($type == 'shipping' && !empty($cart))
				$user_address = (int)$cart->cart_shipping_address_ids;
			if(($type == 'billing' || empty($shipping_address)) && !empty($cart))
				$user_address = (int)$cart->cart_billing_address_id;
		}

		if(empty($user_address))
			$user_address = (int)$app->getUserState(HIKASHOP_COMPONENT.'.'.$type.'_address', 0);
		if(empty($user_address) && $type == 'shipping')
			$user_address = (int)$app->getUserState(HIKASHOP_COMPONENT.'.'.'billing_address', 0);

		if(empty($user_address) && !empty($user_id)) {
			$addresses = $this->loadUserAddresses((int)$user_id);
			if(!empty($addresses) && is_array($addresses)) {
				$address = reset($addresses);
				$user_address = (int)$address->address_id;
			}
		}

		if(empty($user_id) && !is_numeric($user_address)) {
			return $user_address;
		}

		if(empty($user_address) && !empty($user_id)) {
			$query = 'SELECT address_id FROM '.hikashop_table('address').
				' WHERE address_user_id = '.(int)$user_id.' AND address_published = 1'.
				' ORDER BY address_default DESC, address_id DESC';
			$this->db->setQuery($query, 0, 1);
			$user_address = (int)$this->db->loadResult();
		}

		$cache[$type][$user_id] = $user_address;

		return $cache[$type][$user_id];
	}

	function delete(&$elements, $order = false) {
		$elements = (int)$elements;

		JPluginHelper::importPlugin( 'hikashop' );
		$app = JFactory::getApplication();
		$do=true;
		$app->triggerEvent( 'onBeforeAddressDelete', array( & $elements, & $do) );
		if(!$do){
			return false;
		}

		$fieldClass = hikashop_get('class.field');
		$array = array(&$elements);
		$fieldClass->handleBeforeDelete($array, 'address');

		$data = $this->get($elements);
		$orderClass = hikashop_get('class.order');
		$status = true;
		if($orderClass->addressUsed($elements)){
			if(!$order){
				$address=new stdClass();
				$address->address_id = $elements;
				$address->address_published=0;
				$status = parent::save($address);
				$app = JFactory::getApplication();
				if(hikashop_isClient('administrator')){
					$app->enqueueMessage(JText::_('ADDRESS_UNPUBLISHED_CAUSE_USED_IN_ORDER'));
				}
			}
		}else{

			if(!$order || (isset($data->address_published) && !$data->address_published)){
				$status = parent::delete($elements);
			}
		}
		if($status){
			if(!empty($data->address_default)) {
				$query = 'SELECT MIN(address_id) as address_id, MAX(address_default) as address_default FROM '.hikashop_table('address').' WHERE address_user_id = ' . (int)$data->address_user_id . ' AND address_published = 1 AND address_type = ' . $this->database->Quote($data->address_type);
				$this->database->setQuery($query);
				$ret = $this->database->loadObject();

				if(!empty($ret) && (int)$ret->address_default == 0) {
					$address=new stdClass();
					$address->address_id = (int)$ret->address_id;
					$address->address_default = 1;
					parent::save($address);
				}
			}

			$fieldClass->handleAfterDelete($elements, 'address');

			$app->triggerEvent( 'onAfterAddressDelete', array( & $elements ) );
		}
		return $status;
	}

	function _checkVat(&$vatData) {
		$vatHelper = hikashop_get('helper.vat');
		if(!$vatHelper->isValid($vatData)) {
			$this->message = @$vatHelper->message;
			return false;
		}
		return true;
	}

	public function maxiFormat($address, $fields = null, $nlbr = false) {
		static $templateClassicalMode = true;

		$config = hikashop_config();
		$tpl = $config->get('address_template', '');
		if(!empty($tpl)) {
			$templateAddress = $tpl;
		} else {
			$params = new HikaParameter('');
			$params->set('address', $address);
			$js = null;
			$app = JFactory::getApplication();
			$view = hikashop_isClient('administrator') ? 'order' : 'address';
			$templateAddress = hikashop_getLayout($view, 'address_template', $params, $js);
		}

		$ret = ''.$templateAddress;
		if($templateClassicalMode) {
			if(!empty($fields)) {
				if(empty($this->fieldsClass))
					$this->fieldsClass = hikashop_get('class.field');

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
		$config = hikashop_config();
		$ret = $config->get('mini_address_format', '');
		if(empty($ret))
			$ret = '{address_lastname} {address_firstname} - {address_street}, {address_state} ({address_country})';
		if(!empty($format))
			$ret = $format;
		if(!empty($fields)) {
			if(empty($this->fieldsClass))
				$this->fieldsClass = hikashop_get('class.field');

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
}
