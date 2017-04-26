<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class addressViewAddress extends HikaShopView {
	public function display($tpl = null, $params = null) {
		if(empty($params))
			$params = new HikaParameter('');
		$this->assignRef('params', $params);

		$function = $this->getLayout();
		if(method_exists($this, $function))
			$this->$function();
		parent::display($tpl);
	}

	public function listing() {
		$config = hikashop_config();
		$this->assignRef('config', $config);

		$user_id = hikashop_loadUser();
		$this->assignRef('user_id', $user_id);

		$addresses = array();
		$fields = null;

		if(!empty($user_id)) {
			$addressClass = hikashop_get('class.address');
			$addresses = $addressClass->getByUser($user_id);
			if(!empty($addresses)) {
				$addressClass->loadZone($addresses);
				$fields =& $addressClass->fields;
			}
		}
		$this->assignRef('fields', $fields);
		$this->assignRef('addresses', $addresses);

		$this->loadRef(array(
			'fieldClass' => 'class.field',
			'popupHelper' => 'helper.popup',
		));
		$this->fieldsClass =& $this->fieldClass;
		$this->popup =& $this->popupHelper;

		$this->type = 'user';
		$this->show_new_btn = false;
		$this->use_popup = (int)$config->get('user_address_legacy_popup', 0);
		$this->address_selector = (int)$config->get('user_address_selector', 0);

		hikashop_setPageTitle('ADDRESSES');
	}

	public function show() {
		$app = JFactory::getApplication();
		$this->assignRef('app', $app);

		$config = hikashop_config();
		$this->assignRef('config', $config);

		if(!empty($this->params->type)) {
			$type = $this->params->type;
		} else {
			$type = JRequest::getCmd('address_type', '');
			if(empty($type))
				$type = JRequest::getCmd('subtask', 'billing');
			if(substr($type, -8) == '_address')
				$type = substr($type, 0, -8);
		}

		if(!empty($this->params->address_id))
			$address_id = (int)$this->params->address_id;
		else
			$address_id = hikashop_getCID();

		if(!empty($this->params->fieldset_id))
			$fieldset_id = $this->params->fieldset_id;
		else
			$fieldset_id = JRequest::getVar('fid', '');

		$this->assignRef('type', $type);
		$this->assignRef('address_id', $address_id);
		$this->assignRef('fieldset_id', $fieldset_id);

		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass', $fieldsClass);

		$edit = (JRequest::getVar('edition', false) === true);
		if(isset($this->params->edit))
			$edit = $this->params->edit;
		$this->assignRef('edit', $edit);

		$user_id = hikashop_loadUser();
		$addressClass = hikashop_get('class.address');

		if(!empty($address_id)) {
			$address = $addressClass->get($address_id);
			if($address->address_user_id != $user_id) {
				$address = new stdClass();
				$address_id = 0;
			}
			if(!$edit) {
				$addresses = array(&$address);
				$addressClass->loadZone($addresses);
				$userAddresses = $addressClass->loadUserAddresses($user_id);
				$this->assignRef('addresses', $userAddresses);
			}
		} else {
			$address = @$_SESSION['hikashop_address_data'];
			if(empty($address)) {
				$address = new stdClass();
				$userCMS = JFactory::getUser();
				if(!$userCMS->guest) {
					$name = $userCMS->get('name');
					$pos = strpos($name, ' ');
					if($pos !== false) {
						$address->address_firstname = substr($name, 0, $pos);
						$name = substr($name, $pos + 1);
					}
					$address->address_lastname = $name;
				}
			}
			if($edit) {
				$userAddresses = $addressClass->loadUserAddresses($user_id);
				$this->assignRef('addresses', $userAddresses);
			}
		}
		$this->assignRef('address', $address);

		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->assignRef('url_itemid', $url_itemid);

		$extraFields = array(
			'address' => $fieldsClass->getFields('frontcomp' ,$address, 'address', 'checkout&task=state'.$url_itemid)
		);
		$this->assignRef('fields', $extraFields['address']);

		$init_js = '';
		$this->assignRef('init_js', $init_js);

		static $jsInit = array();
		if(empty($jsInit[$type])) {
			$jsInit[$type] = array();
			$null = array();
			$fieldsClass->addJS($null,$null,$null);

			foreach($extraFields['address'] as &$p) {
				$p->field_table = $type.'_address';
			}
			unset($p);
			$fieldsClass->jsToggle($extraFields['address'], $address, 0);
		}

		if(empty($jsInit[$type][$edit])) {
			if($edit) {
				$parents = $fieldsClass->getParents($extraFields['address']);
				if(!empty($parents)) {
					$p = reset($parents);
					$p->type = $type.'_address';
				} else {
					$p = new stdClass();
					$p->type = $type.'_address';
					$parents = array($p);
				}
				$init_js = $fieldsClass->initJSToggle($parents, $address, 0);
			} else {
				$requiredFields = array();
				$validMessages = array();
				$values = array('address' => $address);
				$fieldsClass->checkFieldsForJS($extraFields, $requiredFields, $validMessages, $values);
				$fieldsClass->addJS($requiredFields, $validMessages, array('address'));
			}
		}
		$jsInit[$type][$edit] = true;
	}

	public function form() {
		$user_id = hikashop_loadUser();
		$this->assignRef('user_id', $user_id);

		$address_id = hikashop_getCID('address_id');

		$tmpl = JRequest::getString('tmpl', '');
		$this->assignRef('tmpl', $tmpl);

		$address = JRequest::getVar('fail');
		if(empty($address)) {
			$address = new stdClass();
			if(!empty($address_id)) {
				$addressClass = hikashop_get('class.address');
				$address = $addressClass->get($address_id);
				if($address->address_user_id != $user_id) {
					$address = new stdClass();
					$address_id = 0;
				}
			} else {
				$userCMS = JFactory::getUser();
				if(!$userCMS->guest){
					$name = $userCMS->get('name');
					$pos = strpos($name,' ');
					if($pos!==false){
						$address->address_firstname = substr($name,0,$pos);
						$name = substr($name,$pos+1);
					}
					$address->address_lastname = $name;
				}
			}
		}

		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass', $fieldsClass);
		$fieldsClass->skipAddressName = true;

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;

		$extraFields = array(
			'address' => $fieldsClass->getFields('frontcomp', $address, 'address', 'checkout&task=state'.$url_itemid)
		);
		$this->assignRef('extraFields',$extraFields);

		$null = array();
		$fieldsClass->addJS($null,$null,$null);
		$fieldsClass->jsToggle($this->extraFields['address'], $address, 0);

		$this->assignRef('address', $address);

		$module = hikashop_get('helper.module');
		$module->initialize($this);

		$requiredFields = array();
		$validMessages = array();
		$values = array('address' => $address);
		$fieldsClass->checkFieldsForJS($extraFields, $requiredFields, $validMessages, $values);
		$fieldsClass->addJS($requiredFields, $validMessages, array('address'));

		$cart = hikashop_get('helper.cart');
		$this->assignRef('cart', $cart);
	}

	public function select() {
		$config = hikashop_config();
		$this->assignRef('config', $config);

		$fieldClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass', $fieldClass);
		$this->assignRef('fieldClass', $fieldClass);

		$this->fields = $this->params->fields;
		$this->addresses = $this->params->addresses;

		if(isset($this->params->type))
			$this->type = $this->params->type;
		else
			$this->type = 'user';

		if(isset($this->params->show_new_btn))
			$this->show_new_btn = $this->params->show_new_btn;
		else
			$this->show_new_btn = true;

		if(isset($this->params->address_selector) && is_int($this->params->address_selector)) {
			$this->address_selector = (int)$this->params->address_selector;
		} else {
			$this->address_selector = (int)$config->get('user_address_selector', 0);
		}

		$this->fieldset_id = 'hikashop_'.$this->type.'_address_zone';
	}
}
