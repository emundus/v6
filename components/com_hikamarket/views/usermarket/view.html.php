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
class usermarketViewusermarket extends HikamarketView {

	const ctrl = 'user';
	const name = 'HIKA_USERS';
	const icon = 'generic';

	public function display($tpl = null) {
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName();

		global $Itemid;
		$this->url_itemid = '';
		if(!empty($Itemid))
			$this->url_itemid = '&Itemid=' . $Itemid;

		$function = $this->getLayout();
		if(method_exists($this,$function))
			$this->$function();
		parent::display($tpl);
	}

	public function show() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.edit';

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$user_id = hikamarket::getCID('user_id');
		$this->assignRef('user_id', $user_id);

		$this->loadRef(array(
			'userClass' => 'shop.class.user',
			'fieldsClass' => 'shop.class.field',
			'addressShopClass' => 'shop.class.address',
			'addressClass' => 'class.address',
			'currencyHelper' => 'shop.class.currency'
		));

		$user = $this->userClass->get($user_id);
		$this->assignRef('user', $user);

		$this->fieldsClass->addJS($null, $null, $null);

		$fields = array();
		$null = null;
		if($this->config->get('address_show_details', 0)) {
			$fields['address'] = $this->fieldsClass->getFields('display:vendor_user_show=1', $null, 'address');
		} else {
			$fields['address'] = $this->fieldsClass->getFields('field_frontcomp', $null, 'address');
		}
		if(hikashop_level(1)) {
			$fields['user'] = $this->fieldsClass->getFields('display:vendor_user_show=1', $user, 'user');
			$this->fieldsClass->jsToggle($fields['user'], $user, 0);
			foreach($fields['user'] as &$field) {
				$field_display = explode(';', trim($field->field_display, ';'));
				$field->vendor_edit = in_array('vendor_user_edit=1', $field_display);
			}
			unset($field);
		}
		$this->fieldsClass->jsToggle($fields['address'], $null, 0);
		$this->assignRef('fields', $fields);
		$all_addresses = $this->addressShopClass->getByUser($user_id);
		if(!empty($all_addresses))
			$this->addressShopClass->loadZone($all_addresses);
		$this->assignRef('addresses', $all_addresses);

		$this->two_columns = true;
		$this->display_badge = false;
		foreach($all_addresses as $addr) {
			if(in_array($addr->address_type, array('', 'both'))) {
				$this->two_columns = false;
				$this->display_badge = true;
				break;
			}
		}

		$order_list_limit = $this->config->get('customer_order_list_limit', 15);
		$filters = array(
			'order_user_id = '.(int)$user_id
		);
		$order_type = 'sale';

		if($vendor->vendor_id > 1) {
			$order_type = 'subsale';
			$filters[] = 'order_vendor_id = ' . (int)$vendor->vendor_id;
		}

		$query = 'SELECT * FROM ' . hikamarket::table('shop.order') . ' WHERE order_type = '.$db->Quote($order_type).' AND ('.implode(') AND (', $filters).') ORDER BY order_id DESC';
		$db->setQuery($query, 0, $order_list_limit);
		$orders = $db->loadObjectList();
		$this->assignRef('orders', $orders);

		$query = 'SELECT COUNT(order_id) FROM ' . hikamarket::table('shop.order') . ' WHERE order_type='.$db->Quote($order_type).' AND ('.implode(' OR ', $filters).')';
		$db->setQuery($query);
		$order_count = $db->loadResult();
		$this->assignRef('order_count', $order_count);

		$this->toolbar = array(
			'back' => array(
				'icon' => 'back',
				'fa' => 'fa-arrow-circle-left',
				'name' => JText::_('HIKA_BACK'),
				'url' => hikamarket::completeLink('user'.$this->url_itemid)
			),
			'apply' => array(
				'url' => '#apply',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'apply\',\'hikamarket_user_form\');"',
				'icon' => 'apply',
				'fa' => 'fa-check-circle',
				'name' => JText::_('HIKA_APPLY'), 'pos' => 'right',
				'display' => hikamarket::acl('user/edit') && ($vendor->vendor_id <= 1)
			),
			'save' => array(
				'url' => '#save',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'save\',\'hikamarket_user_form\');"',
				'icon' => 'save',
				'fa' => 'fa-save',
				'name' => JText::_('HIKA_SAVE'), 'pos' => 'right',
				'display' => hikamarket::acl('user/edit') && ($vendor->vendor_id <= 1)
			)
		);

		$pathway = $app->getPathway();
		$items = $pathway->getPathway();
		if(!count($items)) {
			$pathway->addItem(JText::_('VENDOR_ACCOUNT'), hikamarket::completeLink('vendor'.$this->url_itemid));
		}
		$pathway->addItem(JText::_('CUSTOMERS'), hikamarket::completeLink('user&task=listing'.$this->url_itemid));

		if(!empty($user->name))
			$itemName = $user->name;
		else
			$itemName = JText::_('HIKAM_GUEST_USER');
		$pathway->addItem($itemName, hikamarket::completeLink('user&task=listing'.$this->url_itemid));
	}

	public function listing() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$fieldsClass = hikamarket::get('shop.class.field');

		$fields = $fieldsClass->getData('display:vendor_user_listing=1', 'user', false);
		$this->assignRef('fields', $fields);
		$singleSelection = hikaInput::get()->getInt('single', 0);
		$confirm = hikaInput::get()->getInt('confirm', 1);

		$manage = hikamarket::acl('user/edit') || hikamarket::acl('user/show');
		$this->assignRef('manage', $manage);

		$elemStruct = array(
			'user_email',
			'user_cms_id',
			'name',
			'username',
			'email'
		);

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->assignRef('Itemid', $Itemid);

		$cfg = array(
			'table' => 'shop.user',
			'main_key' => 'user_id',
			'order_sql_value' => 'hkuser.user_id'
		);

		$pageInfo = $this->getPageInfo($cfg['order_sql_value']);

		$filters = array();
		$oder = '';
		$searchMap = array(
			'hkuser.user_id',
			'hkuser.user_email',
			'juser.username',
			'juser.email',
			'juser.name'
		);
		foreach($fields as $field) {
			$searchMap[] = 'hkuser.'.$field->field_namekey;
		}

		$this->processFilters($filters, $order, $searchMap, array('juser.', 'hkuser.'));

		$customerVendorJoin = '';
		if($vendor->vendor_id > 1)
			$customerVendorJoin = ' INNER JOIN '.hikamarket::table('customer_vendor').' AS cv ON hkuser.user_id = cv.customer_id AND cv.vendor_id = '.$vendor->vendor_id . ' ';

		$query = ' FROM '.hikamarket::table('user','shop').' AS hkuser ' . $customerVendorJoin .
			' LEFT JOIN '.hikamarket::table('users',false).' AS juser ON hkuser.user_cms_id = juser.id '.$filters.$order;
		$db->setQuery('SELECT hkuser.*,juser.* '.$query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);
		$rows = $db->loadObjectList();

		$fieldsClass->handleZoneListing($fields, $rows);
		foreach($rows as $k => $row) {
			if(!empty($row->user_params)) {
				$rows[$k]->user_params = hikamarket::unserialize($row->user_params);
			}
		}

		$db->setQuery('SELECT COUNT(*) '.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);

		$this->getPagination();

		$this->assignRef('rows', $rows);
		$this->assignRef('singleSelection', $singleSelection);
		$this->assignRef('confirm', $confirm);
		$this->assignRef('elemStruct', $elemStruct);
		$this->assignRef('pageInfo', $pageInfo);
		$this->assignRef('fieldsClass', $fieldsClass);
		$this->assignRef('fields', $fields);

		$this->toolbar = array(
			array('icon' => 'back', 'fa' => 'fa-arrow-circle-left', 'name' => JText::_('HIKA_BACK'), 'url' => hikamarket::completeLink('vendor'.$this->url_itemid))
		);

		$pathway = $app->getPathway();
		$items = $pathway->getPathway();
		if(!count($items)) {
			$pathway->addItem(JText::_('VENDOR_ACCOUNT'), hikamarket::completeLink('vendor'.$this->url_itemid));
		}
		$pathway->addItem(JText::_('CUSTOMERS'), hikamarket::completeLink('user&task=listing'.$this->url_itemid));
	}

	public function state() {
		$namekey = hikaInput::get()->getCmd('namekey','');
		if(!headers_sent()){
			header('Content-Type:text/html; charset=utf-8');
		}
		if(!empty($namekey)){
			$field_namekey = hikaInput::get()->getString('field_namekey', '');
			if(empty($field_namekey))
				$field_namekey = 'address_state';

			$field_id = hikaInput::get()->getString('field_id', '');
			if(empty($field_id))
				$field_id = 'address_state';

			$field_type = hikaInput::get()->getString('field_type', '');
			if(empty($field_type))
				$field_type = 'address';

			$class = hikamarket::get('shop.type.country');
			echo $class->displayStateDropDown($namekey, $field_id, $field_namekey, $field_type);
		}
		exit;
	}

	public function show_address() {
		$this->ajax = true;
		$this->address();
	}

	public function address() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.edit';

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$tmpl = hikaInput::get()->getCmd('tmpl', '');
		$this->ajax = in_array($tmpl, array('component', 'ajax', 'raw'));

		$address_id = hikamarket::getCID('address_id');
		$this->loadRef(array(
			'fieldsClass' => 'shop.class.field',
			'addressShopClass' => 'shop.class.address',
			'addressClass' => 'class.address'
		));

		$user_id = hikaInput::get()->getInt('user_id');
		$this->assignRef('user_id', $user_id);

		$edit = false;
		if(hikaInput::get()->getVar('edition', false) === true && hikamarket::acl('user/edit/address'))
			$edit = true;
		$this->assignRef('edit', $edit);

		$address = $this->addressClass->get($address_id);
		$this->assignRef('address', $address);

		if(@$address->address_user_id != $user_id) {
			$address = new stdClass();
			$address->address_user_id = $user_id;
			$address->address_id = $address_id;
		}

		if(!empty($address) && !empty($address->address_type)) {
			$type = $address->address_type;
		} else if(!empty($this->params->type)) {
			$type = $this->params->type;
		} else {
			$type = hikaInput::get()->getCmd('address_type', '');
			if(empty($type))
				$type = hikaInput::get()->getCmd('subtask', 'billing');
			if(substr($type, -8) == '_address')
				$type = substr($type, 0, -8);
		}
		if(!in_array($type, array('billing','shipping')))
			$type = 'billing';
		$this->assignRef('type', $type);

		$field_type = 'address';
		$shopVersion = $shopConfig->get('version', '1.0.0');
		if(version_compare($shopVersion, '4.2.0', '>='))
			$field_type = $type.'_address';

		$null = null;
		if(!$edit) {
			$fieldMode = 'field_frontcomp';
			if($this->config->get('address_show_details', 0)) {
				$fieldMode = 'display:vendor_user_show=1';
			}
			$fields = array(
				'address' => $this->fieldsClass->getFields($fieldMode, $null, $field_type)
			);
		} else {
			$extra_fields_show = $this->fieldsClass->getFields('display:vendor_user_show=1', $null, $field_type);
			$extra_fields_edit = $this->fieldsClass->getFields('display:vendor_user_edit=1', $null, $field_type);
			$all_fields = array();
			foreach($extra_fields_show as $fieldname => $field) {
				$all_fields[$field->field_ordering] = $field;
				$all_fields[$field->field_ordering]->fieldname = $fieldname;
			}
			unset($extra_fields_show);
			foreach($extra_fields_edit as $fieldname => $field) {
				if(!isset($all_fields[$field->field_ordering])) {
					$all_fields[$field->field_ordering] = $field;
					$all_fields[$field->field_ordering]->fieldname = $fieldname;
				}
				$all_fields[$field->field_ordering]->vendor_edit = true;
			}
			unset($extra_fields_edit);
			ksort($all_fields);
			$fields = array('address' => array());
			foreach($all_fields as $field) {
				$fieldname = $field->fieldname;
				$fields['address'][$fieldname] = $field;
			}
			unset($all_fields);
		}
		$this->assignRef('fields', $fields);

		$this->fieldsClass->jsToggle($fields['address'], $null, 0);

		$all_addresses = $this->addressShopClass->getByUser($user_id);
		if(!empty($all_addresses))
			$this->addressShopClass->loadZone($all_addresses);
		$this->assignRef('addresses', $all_addresses);
		$this->two_columns = true;
		$this->display_badge = false;
		foreach($all_addresses as $addr) {
			if(in_array($addr->address_type, array('', 'both'))) {
				$this->two_columns = false;
				$this->display_badge = true;
				break;
			}
		}
	}
}
