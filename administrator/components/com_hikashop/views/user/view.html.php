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

class UserViewUser extends hikashopView {
	var $ctrl = 'user';
	var $nameListing = 'CUSTOMERS';
	var $nameForm = 'CUSTOMER';
	var $icon = 'user';
	var $triggerView = true;
	var $extraData = null;

	public function display($tpl = null) {
		$this->extraData = new stdClass();

		if(!empty($_REQUEST['filter_partner']))
			$this->nameListing = 'PARTNERS';
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

		$this->handleListingPartner($pageInfo, $filters);

		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass', $fieldsClass);

		$fields = $fieldsClass->getData('backend_listing', 'user', false);
		$this->assignRef('fields',$fields);
		foreach($fields as $field) {
			if($field->field_type == "customtext")
				continue;
			$searchMap[] = 'huser.'.$field->field_namekey;
		}


		$extrafilters = array();
		$tables = array();
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$select = 'huser.*, juser.*';
		$app->triggerEvent('onBeforeUserListing', array($this->paramBase, &$extrafilters, &$pageInfo, &$filters, &$tables, &$searchMap, &$select));
		$this->assignRef('extrafilters', $extrafilters);


		$this->processFilters($filters, $order, $searchMap, $cfg['order_sql_accept']);

		$query = ' FROM '.hikashop_table($cfg['table']).' AS huser LEFT JOIN '.hikashop_table('users', false).' AS juser ON huser.user_cms_id = juser.id '.implode(' ', $tables). $filters.$order;
		$db->setQuery('SELECT '.$select.' '.$query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);
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
			array('name' => 'popup', 'icon' => 'cogs', 'title' => JText::_('HIKASHOP_ACTIONS'), 'alt' => JText::_('HIKASHOP_ACTIONS'), 'url' => hikashop_completeLink('user&task=batch&tmpl=component'), 'width' => $config->get('actions_popup_width','1024'), 'height' => $config->get('actions_popup_height','520'), 'check' => true, 'display' => $manage),
			array('name' => 'editList', 'display' => $manage),
			array('name' => 'deleteList', 'check' => JText::_('HIKA_VALIDDELETEITEMS'), 'display' => hikashop_isAllowed($config->get($acl,'all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		if($this->manage) {
			$massactionClass = hikashop_get('class.massaction');
			$massactionClass->addActionButtons($this->toolbar, 'user');
		}
		return true;
	}

	protected function handleListingPartner($pageInfo, &$filters) {

		$partner = hikashop_get('type.user_partner');
		$this->assignRef('partner', $partner);

		$affiliate_plugin = JPluginHelper::getPlugin('system', 'hikashopaffiliate');
		$affiliate_active = (!empty($affiliate_plugin));
		$this->assignRef('affiliate_active', $affiliate_active);

		if(empty($pageInfo->filter->filter_partner))
			return;


		if($pageInfo->filter->filter_partner != 1) {
			$filters[] = 'huser.user_partner_activated = 0';
			return;
		}

		$filters[] = 'huser.user_partner_activated = 1';

		$db = JFactory::getDBO();
		$config = hikashop_config();
		try {
			$db->setQuery('DROP TABLE IF EXISTS '.hikashop_table('click_view').', '.hikashop_table('sale_view').', '.hikashop_table('lead_view'));
			$db->execute();

			$query = 'CREATE OR REPLACE VIEW '.hikashop_table('click_view').' AS SELECT a.user_id, SUM(b.click_partner_price) AS click_price FROM '.hikashop_table('user').' AS a LEFT JOIN '.hikashop_table('click').' AS b ON a.user_id=b.click_partner_id AND (CASE WHEN a.user_currency_id=0 THEN '.hikashop_getCurrency().' ELSE a.user_currency_id END)=b.click_partner_currency_id WHERE a.user_partner_activated=1 AND b.click_partner_paid=0 GROUP BY b.click_partner_id;';
			$db->setQuery($query);
			$db->execute();

			$partner_valid_status_list = explode(',', $config->get('partner_valid_status','confirmed,shipped'));
			foreach($partner_valid_status_list as $k => $partner_valid_status) {
				$partner_valid_status_list[$k] = $db->Quote($partner_valid_status);
			}
			$query = 'CREATE OR REPLACE VIEW '.hikashop_table('sale_view').' AS '.
				' SELECT a.user_id, SUM(b.order_partner_price) AS sale_price '.
				' FROM '.hikashop_table('user').' AS a '.
				' LEFT JOIN '.hikashop_table('order').' AS b ON a.user_id=b.order_partner_id AND (CASE WHEN a.user_currency_id=0 THEN '.hikashop_getCurrency().' ELSE a.user_currency_id END)=b.order_partner_currency_id '.
				' WHERE a.user_partner_activated=1 AND b.order_partner_paid=0 AND b.order_type=\'sale\' AND b.order_status IN ('.implode(',',$partner_valid_status_list).')'.
				' GROUP BY b.order_partner_id;';
			$db->setQuery($query);
			$db->execute();

			$query = 'CREATE OR REPLACE VIEW '.hikashop_table('lead_view').' AS SELECT a.user_id, SUM(b.user_partner_price) AS lead_price '.
				' FROM '.hikashop_table('user').' AS a '.
				' LEFT JOIN '.hikashop_table('user').' AS b ON a.user_id=b.user_partner_id AND (CASE WHEN a.user_currency_id=0 THEN '.hikashop_getCurrency().' ELSE a.user_currency_id END)=b.user_partner_currency_id '.
				' WHERE a.user_partner_activated=1 AND b.user_partner_paid=0 '.
				' GROUP BY b.user_partner_id;';
			$db->setQuery($query);
			$db->execute();

			$db->setQuery('UPDATE '.hikashop_table('user').' SET user_unpaid_amount=0');
			$db->execute();

			$query = 'UPDATE '.hikashop_table('user').' AS a JOIN '.hikashop_table('click_view').' AS b ON a.user_id=b.user_id '.
				' SET a.user_unpaid_amount=b.click_price '.
				' WHERE a.user_partner_activated=1';
			$db->setQuery($query);
			$db->execute();

			$query = 'UPDATE '.hikashop_table('user').' AS a JOIN '.hikashop_table('sale_view').' AS b ON a.user_id=b.user_id '.
				' SET a.user_unpaid_amount=a.user_unpaid_amount+b.sale_price '.
				' WHERE a.user_partner_activated=1';
			$db->setQuery($query);
			$db->execute();

			$query = 'UPDATE '.hikashop_table('user').' AS a JOIN '.hikashop_table('lead_view').' AS b ON a.user_id=b.user_id '.
				' SET a.user_unpaid_amount=a.user_unpaid_amount+b.lead_price '.
				' WHERE a.user_partner_activated=1';
			$db->setQuery($query);
			$db->execute();

			$db->setQuery('DROP VIEW IF EXISTS '.hikashop_table('click_view').', '.hikashop_table('sale_view').', '.hikashop_table('lead_view'));
			$db->execute();
		} catch(Exception $e) {

		}

		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper', $currencyClass);
	}

	public function sales() {
		$this->paramBase.= '_sales';
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'b.order_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database = JFactory::getDBO();
		$config =& hikashop_config();
		$partner_valid_status_list=explode(',',$config->get('partner_valid_status','confirmed,shipped'));
		foreach($partner_valid_status_list as $k => $partner_valid_status){
			$partner_valid_status_list[$k]= $database->Quote($partner_valid_status);
		}
		$filters = array(
			'b.order_type='.$database->Quote('sale'),
			'b.order_partner_id='.hikashop_getCID('user_id'),
			'b.order_partner_paid=0',
			'b.order_status IN ('.implode(',',$partner_valid_status_list).')'
		);

		$searchMap = array('c.id','c.username','c.name','a.user_email','b.order_user_id','b.order_id','b.order_full_price','b.order_number');

		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped(HikaStringHelper::strtolower(trim($pageInfo->search)),true).'%\'';
			$filter = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
			$filters[] =  $filter;
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}

		$query = ' FROM '.hikashop_table('order').' AS b LEFT JOIN '.hikashop_table('user').' AS a ON b.order_user_id=a.user_id LEFT JOIN '.hikashop_table('users',false).' AS c ON a.user_cms_id=c.id '.$filters.$order;
		$database->setQuery('SELECT a.*,b.*,c.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'order_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
	}

	public function clicks() {
		$this->paramBase.='_clicks';
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.click_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();

		$filters = array('a.click_partner_paid=0');

		$user_id = hikashop_getCID('user_id');
		if(!empty($user_id)){
			$filters[] = 'a.click_partner_id='.$user_id;
		}
		$this->assignRef('user_id',$user_id);

		$searchMap = array('a.click_ip','a.click_referer','a.click_partner_id','a.click_id','b.user_email');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$query = ' FROM '.hikashop_table('click').' AS a';
		$query .= ' JOIN '.hikashop_table('user').' AS b ON a.click_partner_id = b.user_id';
		if(!empty($filters)) $query .= ' WHERE '. implode(' AND ',$filters);


		$database->setQuery('SELECT a.*, b.user_email, b.user_currency_id '.$query.$order,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'click_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);

		$this->toolbar = array(
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form'),
			'dashboard'
		);

		hikashop_setTitle(JText::_('CLICKS'),'mouse-pointer',$this->ctrl.'&task=clicks&user_id='.$user_id);
	}

	public function leads() {
		$this->paramBase = 'leads';
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getData('backend_listing','user',false);
		$this->assignRef('fields',$fields);
		$this->assignRef('fieldsClass',$fieldsClass);
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.user_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();
		$user_id = hikashop_getCID('user_id');
		$userClass = hikashop_get('class.user');
		$user = $userClass->get($user_id);
		$this->assignRef('user',$user);
		$filters = array('a.user_partner_id='.$user_id,'a.user_partner_paid=0');

		$searchMap = array('a.user_id','a.user_email','b.username','b.email','b.name');
		foreach($fields as $field){
			$searchMap[]='a.'.$field->field_namekey;
		}
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}

		$query = ' FROM '.hikashop_table('user').' AS a LEFT JOIN '.hikashop_table('users',false).' AS b ON a.user_cms_id=b.id '.$filters.$order;
		$database->setQuery('SELECT a.*,b.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		$fieldsClass->handleZoneListing($fields,$rows);
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'user_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
	}

	public function pay() {
		$user = null;
		$user_id = hikashop_getCID('user_id');
		if(!empty($user_id)){
			$userClass = hikashop_get('class.user');
			$user = $userClass->get($user_id);
			if(!empty($user))
				$userClass->loadPartnerData($user);
		}
		$this->assignRef('user',$user);

		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper', $currencyHelper);

		$method = 'paypal';
		$this->assignRef('method', $method);
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
			'nameboxType' => 'type.namebox',
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

			$addresses['billing'] = $this->addressClass->loadUserAddresses($user_id, 'billing');
			if(!empty($addresses['billing'])) {
				$this->addressClass->loadZone($addresses['billing'],'name','backend');
				$fields['address'] =&  $this->addressClass->fields;
			}
			$addresses['shipping'] = $this->addressClass->loadUserAddresses($user_id, 'shipping');
			if(!empty($addresses['shipping'])) {
				$this->addressClass->loadZone($addresses['shipping'],'name','backend');
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
		$pluginClass = hikashop_get('class.plugins');
		$plugin = JPluginHelper::getPlugin('system', 'hikashopaffiliate');
		if(!empty($plugin)) {
			$affiliate_active = true;
		}
		$this->assignRef('affiliate_active', $affiliate_active);

		$url_link = JRoute::_('index.php?option=com_users&task=user.edit&id='.$user->user_cms_id );
		$email_link = 'index.php?option=com_hikashop&ctrl=order&task=mail&tmpl=component&user_id='.$user_id;
		$history_link = empty($this->user->user_email) ? '' : hikashop_completeLink('email_history&search='.$this->user->user_email);

		$this->toolbar = array(
			array('name' => 'link', 'icon' => 'upload', 'alt' => JText::_('JOOMLA_USER_OPTIONS'), 'url' => $url_link,'display'=>!empty($user->user_cms_id)),
			array('name' => 'popup', 'icon' => 'send', 'alt' => JText::_('HIKA_EMAIL'), 'url' => $email_link,'display'=>!empty($user_id)),
			array('name' => 'link', 'icon' => 'send', 'alt' => JText::_('EMAIL_HISTORY'), 'url' => $history_link,'display'=>!empty($user_id) && hikashop_level(2)),
			array('name' => 'group', 'buttons' => array( 'apply', 'save')),
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		$js = '
function updateCustomFeesPanel(active) {
	var el = document.getElementById("custom_fees_panel");
	if(!el) return;
	el.style.display = (active == 1) ? "" : "none";
}
';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		$order_info = '';
		$order_id = hikaInput::get()->getInt('order_id', 0);
		if(!empty($order_id)) {
			$order_info = '&order_id=' . $order_id;
		}

		hikashop_loadJslib('tooltip');
		hikashop_setTitle(JText::_($this->nameForm), $this->icon, $this->ctrl.'&task='.$task.'&user_id='.$user_id.$order_info);
	}

	public function editaddress() {
		$user_id = hikaInput::get()->getInt('user_id');
		$address_id = hikashop_getCID('address_id');
		$address = new stdClass();
		if(!empty($address_id)){
			$class=hikashop_get('class.address');
			$address = $class->get($address_id);
		}else{
			$type = hikaInput::get()->getCmd('type');
			if(in_array($type, array('billing', '', 'both','shipping')))
				$address->address_type = $type;
		}
		$extraFields=array();
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$fieldsClass->skipAddressName=true;
		$field_type = 'address';
		if(!empty($address->address_type))
			$field_type = $address->address_type.'_'.$field_type;
		$extraFields['address'] = $fieldsClass->getFields('backend',$address,$field_type,'user&task=state');
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

		$id = hikaInput::get()->getInt('state_field_id', 0);
		$field_options = '';
		if($id){
			$class = hikashop_get('class.field');
			$field = $class->get($id);
			$field_options = $field->field_options;
		}

		$class = hikashop_get('type.country');
		echo $class->displayStateDropDown($namekey, $field_id, $field_namekey, $field_type, '', $field_options);

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
			hikashop_toInteger($users);
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
