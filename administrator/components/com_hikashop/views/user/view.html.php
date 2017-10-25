<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.2.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class UserViewUser extends hikashopView {
	var $ctrl = 'user';
	var $nameListing = 'USERS';
	var $nameForm = 'HIKA_USER';
	var $icon = 'user';

	public function display($tpl = null) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this, $function))
			$this->$function();
		parent::display($tpl);
	}

	public function listing() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$config =& hikashop_config();
		$this->assignRef('config', $config);

		$this->loadRef(array(
			'toggleClass' => 'helper.toggle',
			'searchType' => 'type.search'
		));

		$manage = hikashop_isAllowed($config->get('acl_user_manage','all'));
		$this->assignRef('manage',$manage);

		hikashop_setTitle(JText::_($this->nameListing), $this->icon, $this->ctrl);

		$cfg = array(
			'table' => 'user',
			'main_key' => 'user_id',
			'order_sql_value' => 'huser.user_id',
			'order_sql_accept' => array('huser.', 'juser.')
		);
		$searchMap = array(
			'huser.user_id',
			'huser.user_email',
			'juser.username',
			'juser.email',
			'juser.name'
		);

		$pageInfo = $this->getPageInfo($cfg['order_sql_value'], 'desc');
		$pageInfo->filter->filter_partner = $app->getUserStateFromRequest($this->paramBase.'.filter_partner', 'filter_partner', '', 'int');

		$filters = array();
		$order = '';


		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass', $fieldsClass);

		$fields = $fieldsClass->getData('backend_listing', 'user', false);
		$this->assignRef('fields',$fields);
		foreach($fields as $field) {
			if($field->field_type == "customtext")
				continue;
			$searchMap[] = 'huser.'.$field->field_namekey;
		}

		$this->processFilters($filters, $order, $searchMap, $cfg['order_sql_accept']);

		$query = ' FROM '.hikashop_table($cfg['table']).' AS huser LEFT JOIN '.hikashop_table('users', false).' AS juser ON huser.user_cms_id = juser.id '.$filters.$order;
		$db->setQuery('SELECT huser.*, juser.* '.$query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);
		$rows = $db->loadObjectList();

		$fieldsClass->handleZoneListing($fields, $rows);
		foreach($rows as $k => $row) {
			if(!empty($row->user_params))
				$rows[$k]->user_params = hikashop_unserialize($row->user_params);
			if(!empty($rows[$k]->name))
				$this->escape($rows[$k]->name);
			if(!empty($rows[$k]->username))
				$this->escape($rows[$k]->username);
			if(!empty($rows[$k]->email))
				$this->escape($rows[$k]->email);
			$this->escape($rows[$k]->user_email);
		}

		if(!empty($pageInfo->search)) {
			$rows = hikashop_search($pageInfo->search, $rows, $cfg['main_key']);
		}
		$this->assignRef('rows', $rows);

		$db->setQuery('SELECT COUNT(*) '.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);

		$this->getPagination();
		$this->getOrdering('huser.user_id', true);

		$acl = ($pageInfo->filter->filter_partner == 1) ? 'acl_affiliates_delete' : 'acl_user_delete';

		$this->toolbar = array(
			array('name' => 'editList', 'display' => $manage),
			array('name' => 'deleteList', 'check' => JText::_('HIKA_VALIDDELETEITEMS'), 'display' => hikashop_isAllowed($config->get($acl,'all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);
		return true;
	}


	public function form() {
		$user_id = hikashop_getCID('user_id');
		$fieldsClass = hikashop_get('class.field');

		$config = hikashop_config();
		$this->assignRef('config', $config);

		$this->loadRef(array(
			'currencyType' => 'type.currency',
			'addressClass' => 'class.address',
			'currencyClass' => 'class.currency',
			'popup' => 'helper.popup',
		));

		$addresses = array();
		$fields = null;
		$rows = array();
		if(!empty($user_id)) {
			$class = hikashop_get('class.user');
			$user = $class->get($user_id,'hikashop',true);
			if(!empty($user)) $class->loadPartnerData($user);
			$fields['user'] = $fieldsClass->getFields('backend',$user,'user','field&task=state');
			$null=array();
			$fieldsClass->addJS($null,$null,$null);
			$fieldsClass->jsToggle($fields['user'],$user,0);

			$addresses = $this->addressClass->getByUser($user_id);
			if(!empty($addresses)) {
				 $this->addressClass->loadZone($addresses,'name','backend');
				$fields['address'] =&  $this->addressClass->fields;
			}

			$db = JFactory::getDBO();
			$filters = array(
				'order_user_id = ' . (int)$user_id
			);
			$query = 'SELECT * FROM '.hikashop_table('order').' WHERE order_type = '.$db->Quote('sale').' AND ('.implode(' OR ',$filters).') ORDER BY order_id DESC';
			$db->setQuery($query);
			$orders = $db->loadObjectList();
			foreach($orders as $order) {
				if($order->order_user_id==$user_id){
					$rows[]=$order;
				}
			}
			$task = 'edit';
		} else {
			$user = new stdClass();
			$task = 'add';
		}
		$this->assignRef('rows',$rows);
		$this->assignRef('user',$user);
		$this->assignRef('fields',$fields);
		$this->assignRef('addresses',$addresses);
		$this->assignRef('fieldsClass',$fieldsClass);

		$pluginClass = hikashop_get('class.plugins');
		$payments = $pluginClass->getMethods('payment');
		$newPayments = array();
		foreach($payments as $payment) {
			$newPayments[$payment->payment_id] = $payment;
		}
		unset($payments);
		$this->assignRef('payments', $newPayments);

		$affiliate_active = false;
		$this->assignRef('affiliate_active', $affiliate_active);

		if(!HIKASHOP_J16) {
			$url_link = JRoute::_('index.php?option=com_users&task=edit&cid[]='.$user->user_cms_id );
			$email_link = hikashop_completeLink('order&task=mail&user_id='.$user_id,true);
		} else {
			$url_link = JRoute::_('index.php?option=com_users&task=user.edit&id='.$user->user_cms_id );
			$email_link = hikashop_completeLink('order&task=mail&user_id='.$user_id,true);
		}
		$history_link = empty($this->user->user_email) ? '' : hikashop_completeLink('email_history&search='.$this->user->user_email);

		$this->toolbar = array(
			array('name' => 'link', 'icon' => 'upload', 'alt' => JText::_('JOOMLA_USER_OPTIONS'), 'url' => $url_link,'display'=>!empty($user->user_cms_id)),
			array('name' => 'popup', 'icon' => 'send', 'alt' => JText::_('HIKA_EMAIL'), 'url' => $email_link,'display'=>!empty($user_id)),
			array('name' => 'link', 'icon' => 'send', 'alt' => JText::_('EMAIL_HISTORY'), 'url' => $history_link,'display'=>!empty($user_id) && hikashop_level(2)),
			'save',
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		$js = '
function updateCustomFeesPanel(active) {
	var el = document.getElementById("custom_fees_panel");
	if(!el) return;
	el.style.display = (active == 1) ? "" : "none";;
}
';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		$order_info = '';
		$order_id = hikaInput::get()->getInt('order_id', 0);
		if(!empty($order_id)) {
			$order_info = '&order_id=' . $order_id;
		}

		hikashop_setTitle(JText::_($this->nameForm), $this->icon, $this->ctrl.'&task='.$task.'&user_id='.$user_id.$order_info);
	}

	public function editaddress() {
		$user_id = hikaInput::get()->getInt('user_id');
		$address_id = hikashop_getCID('address_id');
		$address = new stdClass();
		if(!empty($address_id)){
			$class=hikashop_get('class.address');
			$address = $class->get($address_id);
		}
		$extraFields=array();
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$fieldsClass->skipAddressName=true;
		$extraFields['address'] = $fieldsClass->getFields('backend',$address,'address','user&task=state');
		$this->assignRef('extraFields',$extraFields);
		$this->assignRef('user_id',$user_id);
		$this->assignRef('address',$address);
		$null=array();
		$fieldsClass->addJS($null,$null,$null);
		$fieldsClass->jsToggle($this->extraFields['address'],$address,0);
		$requiredFields = array();
		$validMessages = array();
		$values = array('address'=>$address);
		$fieldsClass->checkFieldsForJS($extraFields,$requiredFields,$validMessages,$values);
		$fieldsClass->addJS($requiredFields,$validMessages,array('address'));
		$cart=hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);
		jimport('joomla.html.parameter');
		$params = new HikaParameter('');
		$this->assignRef('params',$params);
	}

	public function state() {
		$namekey = hikaInput::get()->getCmd('namekey','');
		if(empty($namekey)) {
			echo '<span class="state_no_country">'.JText::_('PLEASE_SELECT_COUNTRY_FIRST').'</span>';
			exit;
		}

		$field_namekey = hikaInput::get()->getCmd('field_namekey', '');
		if(empty($field_namekey))
			$field_namekey = 'address_state';

		$field_id = hikaInput::get()->getCmd('field_id', '');
		if(empty($field_id))
			$field_id = 'address_state';

		$field_type = hikaInput::get()->getCmd('field_type', '');
		if(empty($field_type))
			$field_type = 'address';

		$class = hikashop_get('type.country');
		echo $class->displayStateDropDown($namekey, $field_id, $field_namekey, $field_type);

		exit;
	}

	public function selection() {
		$singleSelection = hikaInput::get()->getVar('single', 0);
		$confirm = hikaInput::get()->getVar('confirm', 1);
		$this->assignRef('singleSelection', $singleSelection);
		$this->assignRef('confirm', $confirm);

		$ctrl = hikaInput::get()->getCmd('ctrl');
		$this->assignRef('ctrl', $ctrl);

		$task = 'useselection';
		$this->assignRef('task', $task);

		$afterParams = array();
		$after = hikaInput::get()->getString('after', '');
		if(!empty($after)) {
			list($ctrl, $task) = explode('|', $after, 2);

			$afterParams = hikaInput::get()->getString('afterParams', '');
			$afterParams = explode(',', $afterParams);
			foreach($afterParams as &$p) {
				$p = explode('|', $p, 2);
				unset($p);
			}
		}
		$this->assignRef('afterParams', $afterParams);

		$this->listing();
	}


	public function useselection() {
		$users = hikaInput::get()->get('cid', array(), 'array');
		$rows = array();
		$data = '';
		$confirm = hikaInput::get()->getVar('confirm', true);
		$singleSelection = hikaInput::get()->getVar('single', false);

		$elemStruct = array(
			'user_email',
			'user_cms_id',
			'name',
			'username',
			'email'
		);

		if(!empty($users)) {
			JArrayHelper::toInteger($users);
			$db = JFactory::getDBO();
			$query = 'SELECT a.*, b.* FROM '.hikashop_table('user').' AS a LEFT JOIN '.hikashop_table('users', false).' AS b ON a.user_cms_id = b.id WHERE a.user_id IN ('.implode(',',$users).')';
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if(!empty($rows)) {
				$data = array();
				foreach($rows as $v) {
					$d = '{id:'.$v->user_id;
					foreach($elemStruct as $s) {
						if($s == 'id')
							continue;
						$d .= ','.$s.':\''. str_replace('"','\'',$v->$s).'\'';
					}
					$data[] = $d.'}';
				}
				if(!$singleSelection)
					$data = '['.implode(',',$data).']';
				else {
					$data = $data[0];
					$rows = $rows[0];
				}
			}
		}
		$this->assignRef('rows', $rows);
		$this->assignRef('data', $data);
		$this->assignRef('confirm', $confirm);
		$this->assignRef('singleSelection', $singleSelection);

		if($confirm == true) {
			$js = 'window.hikashop.ready( function(){window.top.hikashop.submitBox('.$data.');});';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
		}
	}
}
