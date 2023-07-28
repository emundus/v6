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
class OrderViewOrder extends hikashopView {
	var $ctrl= 'order';
	var $nameListing = 'ORDERS';
	var $nameForm = 'HIKASHOP_ORDER';
	var $icon = 'order';
	var $triggerView = true;

	public function display($tpl = null, $params = array()) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		$this->view_params = $params;
		if(method_exists($this,$function))
			$this->$function();
		parent::display($tpl);
	}

	public function listing() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$user_id = hikashop_loadUser(false);

		$config = hikashop_config();
		$this->assignRef('config', $config);
		global $Itemid;
		$this->Itemid = $Itemid;

		$menus	= $app->getMenu();
		$menu	= $menus->getActive();
		$show_page_heading = true;
		$params = null;
		if(!empty($menu) && method_exists($menu, 'getParams')) {
			$params = $menu->getParams();
			$show_page_heading = $params->get('show_page_heading');
		}
		if(is_null($show_page_heading)) {
			$com_menus = JComponentHelper::getParams('com_menus');
			if(!empty($com_menus))
				$show_page_heading = $com_menus->get('show_page_heading');
		}
		if(!empty($menu) && method_exists($menu, 'getParams') && $menu->link == 'index.php?option=com_hikashop&view=order&layout=listing') {
			if($show_page_heading)
				$this->title = $params->get('page_heading');
			$title = $params->get('page_title');
			if(empty($title))
				$title = $menu->title;
			hikashop_setPageTitle($title);

			$robots = $params->get('robots');
			if (!$robots) {
				$jconfig = JFactory::getConfig();
				$robots = $jconfig->get('robots', '');
			}
			if($robots) {
				$doc = JFactory::getDocument();
				$doc->setMetadata('robots', $robots);
			}
		} else {
			if($show_page_heading)
				$this->title = JText::_('ORDERS');
			hikashop_setPageTitle('ORDERS');
			$pathway = $app->getPathway();
			$pathway->addItem(JText::_('ORDERS'), hikashop_completeLink('order&Itemid='.$Itemid));

			$this->toolbar = array(
				'back' => array(
					'icon' => 'back',
					'name' => JText::_('HIKA_BACK'),
					'url' => hikashop_completeLink('user&task=cpanel&Itemid='.$Itemid),
					'fa' => array('html' => '<i class="fas fa-arrow-circle-left"></i>')
				)
			);
		}

		$this->loadRef(array(
			'currencyClass' => 'class.currency',
			'cartHelper' => 'helper.cart',
			'dropdownHelper' => 'helper.dropdown',
			'toolbarHelper' => 'helper.toolbar',
			'popupHelper' => 'helper.popup',
		));

		$params = new HikaParameter();
		$params->set('show_quantity_field', 0);
		$this->assignRef('params', $params);


		$extraFilters = array('order_status' => $config->get('orders_listing_default_status',''), 'order_range' => $config->get('orders_listing_default_range','last 6 months'));
		$pageInfo = $this->getPageInfo('hk_order.order_created', 'desc', $extraFilters);

		$this->full_ordering = $pageInfo->filter->order->value . ' ' . strtolower($pageInfo->filter->order->dir);

		$filters = array(
			'hk_order.order_type = ' . $db->Quote('sale'),
			'hk_order.order_user_id = ' . (int)$user_id
		);

		if(!empty($pageInfo->filter->order_status) && $pageInfo->filter->order_status != 'all') {
			$filters['hk_order.order_status'] = 'hk_order.order_status = '.$db->Quote($pageInfo->filter->order_status);
		}

		$end = 0;
		$start = 0;
		switch($pageInfo->filter->order_range) {
			case 'last 30 days':
				$start = time() - 2592000;
				break;
			case 'last 6 months':
				$start = mktime(0, 0, 0, date('n')-6, 1, date('y'));
				break;
			case '':
				break;
			default:
				if(!is_numeric($pageInfo->filter->order_range)) {
					$pageInfo->filter->order_range = '';
					break;
				}
				$start = hikashop_getTime($pageInfo->filter->order_range.'-01-01 00:00:00');
				$end = hikashop_getTime(($pageInfo->filter->order_range+1).'-01-01 00:00:00');
				break;
		}
		if($start)
			$filters['hk_order.order_created.start'] = 'hk_order.order_created >= '.(int)$start;
		if($end)
			$filters['hk_order.order_created.end'] = 'hk_order.order_created < '.(int)$end;

		$order = '';
		$searchMap = array(
			'hk_order.order_id',
			'hk_order.order_invoice_id',
			'hk_order.order_number',
			'hk_order.order_invoice_number',
		);

		if(!empty($pageInfo->search)) {
			$searchMap[] = 'hk_order_product.order_product_name';
			$searchMap[] = 'hk_order_product.order_product_code';
		}

		$orderingAccept = array(
			'hk_order.'
		);
		JPluginHelper::importPlugin('hikashop');
		$app->triggerEvent('onBeforeFrontendOrderListing', array($this->paramBase, &$pageInfo, &$filters, &$searchMap));
		$this->processFilters($filters, $order, $searchMap, $orderingAccept);

		if(!empty($pageInfo->search)) {
			$db->setQuery('SELECT hk_order.order_id FROM ' . hikashop_table('order') . ' AS hk_order LEFT JOIN ' . hikashop_table('order_product') . ' AS hk_order_product ON hk_order.order_id = hk_order_product.order_id ' . $filters);
			$rows = $db->loadObjectList('order_id');
			if(count($rows))
				$filters = 'WHERE hk_order.order_id IN ('.implode(',',array_keys($rows)).')';
			else
				$filters = 'WHERE 1=0';
		}

		$query = ' FROM ' . hikashop_table('order') . ' AS hk_order ' . $filters . $order;
		$this->getPageInfoTotal($query, '*');
		$db->setQuery('SELECT hk_order.*' . $query, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $db->loadObjectList('order_id');

		if(!empty($pageInfo->search)) {
			$rows = hikashop_search($pageInfo->search, $rows, 'order_id');
		}

		$address_data = array();
		$address_html = array();
		if(!empty($rows)) {
			foreach($rows as $row) {
				if((empty($row->order_shipping_method) && empty($row->order_shipping_id)) || empty($row->order_shipping_address_id))
					continue;
				$address_data[(int)$row->order_shipping_address_id] = (int)$row->order_shipping_address_id;
			}
		}
		if(!empty($address_data)) {
			$query = ' SELECT * FROM ' . hikashop_table('address') . ' WHERE address_id IN (' . implode(',', $address_data) . ')';
			$db->setQuery($query);
			$address_data = $db->loadObjectList('address_id');

			$addressClass = hikashop_get('class.address');
			$addressClass->loadZone($address_data, 'name','frontcomp');
			$fields = $addressClass->fields;

			foreach($address_data as $k => $v) {
				$address_html[$k] = $addressClass->displayAddress($fields, $v, 'address');
			}
		}
		$this->address_data = $address_data;
		$this->address_html = $address_html;

		$this->action_column = false;

		if(hikashop_level(1) && $config->get('allow_payment_button', 1)) {
			$unpaid_statuses = explode(',', $config->get('order_unpaid_statuses', 'created'));
			if(!empty($rows)) {
				foreach($rows as &$order) {
					if(in_array($order->order_status, $unpaid_statuses)) {
						$order->show_payment_button = true;
						$this->action_column = true;
					}
				}
			}
			unset($order);

			$payment_change = $config->get('allow_payment_change', 1);
			$this->assignRef('payment_change', $payment_change);

			$pluginsPayment = hikashop_get('type.plugins');
			$pluginsPayment->type = 'payment';
			$this->assignRef('paymentPluginsType', $pluginsPayment);

			$paymentClass = hikashop_get('class.payment');
			$this->assignRef('paymentClass', $paymentClass);
		}
		$print_statuses = explode(',', trim($config->get('print_invoice_statuses', 'confirmed,shipped,refunded'), ', '));
		if(hikashop_level(1) && !empty($rows)) {
			foreach($rows as &$order) {
				if(in_array($order->order_status, $print_statuses)) {
					$order->show_print_button = true;
					$this->action_column = true;
				}
			}
			unset($order);
		}
		$contact_statuses = explode(',', trim($config->get('contact_button_orders', 'created,confirmed,shipped,refunded,pending,cancelled'), ', '));
		if(hikashop_level(1) && !empty($rows)) {
			foreach($rows as &$order) {
				if(in_array($order->order_status, $contact_statuses)) {
					$order->show_contact_button = true;
					$this->action_column = true;
				}
			}
			unset($order);
		}

		$cancellable_order_status = explode(',', trim($config->get('cancellable_order_status', ''), ', '));
		if(!empty($cancellable_order_status) && !empty($rows)) {
			foreach($rows as &$order) {
				if(in_array($order->order_status, $cancellable_order_status)) {
					$order->show_cancel_button = true;
					$this->action_column = true;
				}
			}
			unset($order);
		}

		if($config->get('allow_reorder', 0)) {
			$this->action_column = true;
		}

		if(!empty($rows)) {
			$first_row = reset($rows);
			$this->order_products( (int)$first_row->order_id );
		}

		$this->assignRef('rows', $rows);

		$this->getPagination();
		$this->getOrdering('hk_order.order_id', true);

		$leftFilters = array();
		$this->assignRef('leftFilters', $leftFilters);
		$rightFilters = array();
		$rightFilters['order_range_text'] = JText::sprintf('X_ORDERS_DONE', $this->pageInfo->elements->total);
		$rightFilters['order_range'] = hikashop_get('type.order_range');
		$rightFilters['separator'] = '<br/>';
		$rightFilters['order_status'] = hikashop_get('type.order_status');
		$this->assignRef('rightFilters', $rightFilters);

		$category = hikashop_get('type.categorysub');
		$category->type = 'status';
		$category->load(true);
		$this->assignRef('order_statuses',$category);
		$cart = hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		hikashop_loadJslib('tooltip');
	}

	public function pay() {
		$order_id = hikashop_getCID('order_id');

		$orderClass = hikashop_get('class.order');
		$order = $orderClass->loadFullOrder($order_id);
		$this->assignRef('order', $order);

		$pluginsPayment = hikashop_get('type.plugins');
		$pluginsPayment->type = 'payment';
		$pluginsPayment->order = $this->order;
		$pluginsPayment->preload(false);
		$this->assignRef('paymentPluginType', $pluginsPayment);

		$this->currencyClass = hikashop_get('class.currency');

		hikashop_get('helper.checkout');
		$checkoutHelper = hikashopCheckoutHelper::get();
		$this->assignRef('checkoutHelper', $checkoutHelper);

		$new_payment_method = hikaInput::get()->getVar('new_payment_method', null);
		$paymentMethod = null;

		if(!empty($new_payment_method)) {
			$payment_method = explode('_', $new_payment_method);
			$payment_id = array_pop($payment_method);
			$payment_method = implode('_', $payment_method);

			$methods = $pluginsPayment->methods['payment'][(string)$order->order_id];
			$found = false;
			foreach($methods as $method) {
				if($method->payment_id != $payment_id || $method->payment_type != $payment_method)
					continue;
				$found = $method;
				break;
			}
			if(!$found) {
				$new_payment_method = null;
				$payment_id = null;
				$payment_method = null;
			}

			if(!empty($payment_method)) {
				$paymentPlugin = hikashop_import('hikashoppayment', $payment_method);
				if( method_exists($paymentPlugin, 'needCC') ) {
					$paymentClass = hikashop_get('class.payment');
					$paymentMethod = $paymentClass->get($payment_id);
					$needCC = $paymentPlugin->needCC($paymentMethod);
				}
			}
		}
		$this->assignRef('new_payment_method', $new_payment_method);
		$this->assignRef('paymentMethod', $paymentMethod);

		hikashop_setPageTitle('PAY_NOW');
	}


	public function show() {
		$type = 'order';

		$order =& $this->_order($type);

		$config = hikashop_config();
		$this->assignRef('config', $config);

		$download_time_limit = $config->get('download_time_limit',0);
		$this->assignRef('download_time_limit', $download_time_limit);

		$download_number_limit = $config->get('download_number_limit',0);
		$this->assignRef('download_number_limit', $download_number_limit);

		$order_status_for_download = $config->get('order_status_for_download','confirmed,shipped');
		$order_status_download_ok = (in_array($order->order_status, explode(',',$order_status_for_download)));
		$this->assignRef('order_status_download_ok', $order_status_download_ok);

		$products = array();
		if(!empty($order->products) && hikashop_level(1)) {
			$products_ids = array();
			$productClass = hikashop_get('class.product');
			foreach($order->products as $item) {
				if($item->product_id)
					$products_ids[] = $item->product_id;
			}
			if(count($products_ids)){
				$productClass->getProducts($products_ids);
				$products =& $productClass->all_products;
			}
		}
		$this->assignRef('products',$products);

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid)) {
			$url_itemid = '&Itemid=' . $Itemid;
		}
		$this->assignRef('url_itemid', $url_itemid);
		$toolbar_array = array();

		$unpaid_statuses = explode(',', $config->get('order_unpaid_statuses', 'created'));
		if(hikashop_level(1) && $config->get('allow_payment_button', 1) && in_array($this->element->order_status, $unpaid_statuses) && bccomp(sprintf('%F',$this->element->order_full_price), 0, 5) > 0) {
			$url = 'order&task=pay&order_id='.$this->element->order_id.$url_itemid;
			$url .= ($config->get('allow_payment_change', 1)) ? '&select_payment=1' : '';
			$token = hikaInput::get()->getVar('order_token');
			if(!empty($token))
				$url .= '&order_token='.urlencode($token);
			$url = hikashop_completeLink($url);
			if($config->get('force_ssl',0) && strpos('https://',$url) === false)
				$url = str_replace('http://','https://', $url);
			$pay = array(
				'icon' => 'pay',
				'name' => JText::_('PAY_NOW'),
				'url' => $url,
				'fa' => array('html' => '<i class="far fa-credit-card"></i>')
			);
			$toolbar_array['pay'] = $pay;
		}
		if($this->invoice_type == 'order') {
			$print_statuses = explode(',', $this->config->get('print_invoice_statuses', 'confirmed,shipped,refunded'));
			if(hikashop_level(1) && in_array($this->element->order_status, $print_statuses)) {
				$url = 'order&task=invoice&order_id='.$this->element->order_id.$url_itemid;
				$token = hikaInput::get()->getVar('order_token');
				if(!empty($token))
					$url .= '&order_token='.urlencode($token);
				$toolbar_array['invoice'] = array(
					'icon' => 'print',
					'name' => JText::_('PRINT_INVOICE'),
					'url' => hikashop_completeLink($url,true),
					'popup' => array(
						'id' => 'hikashop_print_cart',
						'width' => 760,
						'height' => 480
					),
					'fa' => array('html' => '<i class="fas fa-print"></i>')
				);
			}
			$contact_statuses = explode(',', trim($config->get('contact_button_orders', 'created,confirmed,shipped,refunded,pending,cancelled'), ','));
			if(hikashop_level(1) && in_array($this->element->order_status, $contact_statuses)) {
				$url = 'order&task=contact&order_id='.$this->element->order_id.$url_itemid;
				$token = hikaInput::get()->getVar('order_token');
				if(!empty($token))
					$url .= '&order_token='.urlencode($token);
				$toolbar_array['contact'] = array(
					'icon' => 'email',
					'name' => JText::_('CONTACT_US_ABOUT_YOUR_ORDER'),
					'url' => hikashop_completeLink($url),
					'fa' => array('html' => '<i class="fas fa-envelope"></i>')
				);
			}

			$user = JFactory::getUser();
			if(!$user->guest) {
				$redirect = hikaInput::get()->getString('cancel_redirect');
				$url = hikaInput::get()->getString('cancel_url');
				if(!empty($redirect) || !empty($url)) {
					$back = array(
						'icon' => 'back',
						'name' => JText::_('HIKA_BACK'),
						'javascript' =>  "submitbutton('cancel'); return false;",
						'fa' => array('html' => '<i class="fas fa-arrow-circle-left"></i>')
					);
					$toolbar_array['back'] = $back;
				}
			}
		}

		if(count($toolbar_array))
			$this->toolbar = $toolbar_array;


		$app = JFactory::getApplication();
		$menus	= $app->getMenu();
		$menu	= $menus->getActive();
		$pathway = $app->getPathway();
		if(empty($menu) || $menu->link != 'index.php?option=com_hikashop&view=order&layout=listing')
			$pathway->addItem(JText::_('ORDERS'), hikashop_completeLink('order&Itemid='.$Itemid));
		$title = JText::_('HIKASHOP_ORDER').':'.$this->element->order_number;
		$pathway->addItem($title, hikashop_completeLink('order&task=show&order_id=' . $this->element->order_id . '&Itemid=' . $Itemid));
		hikashop_setPageTitle($title);

		if(!empty($menu) && method_exists($menu, 'getParams')) {
			$params = $menu->getParams();
			$robots = $params->get('robots');
			if (!$robots) {
				$jconfig = JFactory::getConfig();
				$robots = $jconfig->get('robots', '');
			}
			if($robots) {
				$doc = JFactory::getDocument();
				$doc->setMetadata('robots', $robots);
			}
		}
		if($this->invoice_type == 'order' || empty($this->element->order_invoice_number))
			$this->title = JText::_('HIKASHOP_ORDER').': '.$this->element->order_number;
		else
			$this->title = JText::_('INVOICE').': '.$this->element->order_invoice_number;
	}


	public function contact() {
		$user = hikashop_loadUser(true);
		$this->assignRef('element',$user);
		if(empty($user->id)) {
			$userClass = hikashop_get('class.user');
			$this->privacy = $userClass->getPrivacyConsentSettings('contact');
		}

		$doc = JFactory::getDocument();
		$app = JFactory::getApplication();
		$order_id = (int)hikashop_getCID('order_id');
		$config =& hikashop_config();
		$this->assignRef('config',$config);

		$menu = $app->getMenu()->getActive();
		if(!empty($menu) && method_exists($menu, 'getParams') && $menu->getParams()->get('show_page_heading'))
			$this->title = $menu->getParams()->get('page_heading');
		if(!empty($menu) && method_exists($menu, 'getParams')) {
			$params = $menu->getParams();
			$robots = $params->get('robots');
			if (!$robots) {
				$jconfig = JFactory::getConfig();
				$robots = $jconfig->get('robots', '');
			}
			if($robots) {
				$doc = JFactory::getDocument();
				$doc->setMetadata('robots', $robots);
			}
		}

		$element = null;
		if(!empty($order_id)) {
			$orderClass = hikashop_get('class.order');
			$element = $orderClass->loadFullOrder($order_id);
		}

		if(hikashop_level(1)){
			$fieldsClass = hikashop_get('class.field');
			$this->assignRef('fieldsClass',$fieldsClass);
			$contactFields = $fieldsClass->getFields('frontcomp',$element,'contact','checkout&task=state');
			$null=array();
			$fieldsClass->addJS($null,$null,$null);
			$fieldsClass->jsToggle($contactFields,$element,0);
			$extraFields = array('contact'=>&$contactFields);
			$requiredFields = array();
			$validMessages = array();
			$values = array('contact'=>$element);
			$fieldsClass->checkFieldsForJS($extraFields,$requiredFields,$validMessages,$values);
			$fieldsClass->addJS($requiredFields,$validMessages,array('contact'));
			$this->assignRef('contactFields',$contactFields);
		}

		$this->assignRef('order',$element);

		$js = "
function checkFields(){
	var send = true;
	var name = document.getElementById('hikashop_contact_name');
	var fields = [];
	if(name != null){
		if(name.value == ''){
			name.className = name.className.replace('invalid','') + ' invalid';
			send = false;
			fields.push('".JText::_('HIKA_USER_NAME',true)."');
		}else{
			name.className=name.className.replace('invalid','');
		}
	}
	var email = document.getElementById('hikashop_contact_email');
	if(email != null){
		if(email.value == ''){
			email.className = email.className.replace('invalid','') + ' invalid';
			send = false;
			fields.push('".JText::_('HIKA_EMAIL',true)."');
		}else{
			email.value = email.value.replace(/ /g,\"\");
			var filter = /^([a-z0-9_'&\.\-\+])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,14})+$/i;
			if(!email || !filter.test(email.value)){
				email.className = email.className.replace('invalid','') + ' invalid';
				send = false;
				fields.push('".JText::_('HIKA_EMAIL',true)."');
			}else{
				email.className=email.className.replace('invalid','');
			}
		}
	}
	var altbody = document.getElementById('hikashop_contact_altbody');
	if(altbody != null){
		if(altbody.value == ''){
			altbody.className = altbody.className.replace('invalid','') + ' invalid';
			send = false;
			fields.push('".JText::_('ADDITIONAL_INFORMATION',true)."');
		}else{
			altbody.className = altbody.className.replace('invalid','');
		}
	}


	var consent = document.getElementById('hikashop_contact_consent');
	if(consent != null){
		var consentarea = document.getElementById('hikashop_contact_value_consent');
		if(!consent.checked){
			consentarea.className = name.className.replace('invalid','') + ' invalid';
			send = false;
			fields.push('".JText::_('PLG_CONTENT_CONFIRMCONSENT_CONSENTBOX_LABEL',true)."');
		}else{
			consentarea.className=name.className.replace('invalid','');
		}
	}

	if(!hikashopCheckChangeForm('contact','hikashop_order_contact_form')){
		send = false;
	}

	if(send == true){
		document.getElementById('toolbar').innerHTML='<img src=\"".HIKASHOP_IMAGES."spinner.gif\"/>';
		return true;
	}
	alert('".addslashes(JText::sprintf('PLEASE_FILL_THE_FIELDS',''))."'+ fields.join(', '));
	return false;
}
window.hikashop.ready(function(){
	var name = document.getElementById('hikashop_contact_name');
	if(name != null){
		name.onclick=function(){
			name.className=name.className.replace('invalid','');
		}
	}
	var email = document.getElementById('hikashop_contact_email');
	if(email != null){
		email.onclick=function(){
			email.className=email.className.replace('invalid','');
		}
	}
	var altbody = document.getElementById('hikashop_contact_altbody');
	if(altbody != null){
		altbody.onclick=function(){
			altbody.className=altbody.className.replace('invalid','');
		}
	}
	var consent = document.getElementById('hikashop_contact_value_consent');
	if(consent != null){
		consent.onclick=function(){
			consent.className=altbody.className.replace('invalid','');
		}
	}
});
		";
		$doc->addScriptDeclaration($js);
	}

	public function invoice() {
		$type = 'invoice';
		$this->setLayout('show');
		$order =& $this->_order($type);
		$js = "window.hikashop.ready( function() {setTimeout(function(){window.focus();window.print();setTimeout(function(){hikashop.closeBox();}, 1000);},1000);});";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid)) {
			$url_itemid = '&Itemid=' . $Itemid;
		}
		$this->assignRef('url_itemid', $url_itemid);
	}

	public function order_products($order_id = null) {
		if($order_id === null)
			$order_id = hikashop_getCID();

		$this->config = hikashop_config();

		$this->loadRef(array(
			'imageHelper' => 'helper.image',
			'currencyClass' => 'class.currency',
		));
		$orderClass = hikashop_get('class.order');
		$this->row = $orderClass->loadFullOrder($order_id, true);

		$product_ids = array();
		foreach($this->row->products as $product) {
			$product_ids[(int)$product->product_id] = (int)$product->product_id;
		}

		$this->products = array();
		if(count($product_ids)) {
			$db = JFactory::getDBO();
			$db->setQuery('SELECT * FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',', $product_ids). ')');
			$this->products = $db->loadObjectList('product_id');

			$parent_ids = array();
			$productClass = hikashop_get('class.product');
			foreach($this->products as $k => $product) {
				if(!empty($product->product_parent_id))
					$parent_ids[$product->product_id] = (int)$product->product_parent_id;
				else
					$productClass->addAlias($this->products[$k]);
			}
			if(count($parent_ids)) {
				$db->setQuery('SELECT * FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',', $parent_ids). ')');
				$parents = $db->loadObjectList('product_id');
				foreach($parent_ids as $variant_id => $parent_id){
					if(!isset($parents[$parent_id]))
						continue;
					$productClass->addAlias($parents[$parent_id]);
					$this->products[$variant_id]->product_alias = $parents[$parent_id]->product_alias;
					$this->products[$variant_id]->product_canonical = $parents[$parent_id]->product_canonical;
				}
			}
		}
	}

	protected function &_order($type) {
		$order_id = hikashop_getCID('order_id');
		$app = JFactory::getApplication();
		if(empty($order_id)){
			$order_id = $app->getUserState('com_hikashop.order_id');
		}
		if(!empty($order_id)){
			$orderClass = hikashop_get('class.order');
			$order = $orderClass->loadFullOrder($order_id,true);
		}
		if(empty($order)){
			$app->redirect(hikashop_completeLink('order&task=listing',false,true));
		}
		$config =& hikashop_config();
		$this->assignRef('config',$config);
		$store = str_replace(array("\r\n","\n","\r"),array('<br/>','<br/>','<br/>'),$config->get('store_address',''));
		if(JText::_($store)!=$store){
			$store = JText::_($store);
		}

		$this->loadRef(array(
			'currencyHelper' => 'class.currency',
			'popup' => 'helper.popup',
			'dropdownHelper' => 'helper.dropdown',
			'toolbarHelper' => 'helper.toolbar',
		));

		if(!empty($order->order_payment_id)){
			$pluginsPayment = hikashop_get('type.plugins');
			$pluginsPayment->type='payment';
			$this->assignRef('payment',$pluginsPayment);
		}
		if(!empty($order->order_shipping_id)){
			$pluginsShipping = hikashop_get('type.plugins');
			$pluginsShipping->type='shipping';
			$this->assignRef('shipping',$pluginsShipping);

			$shippingClass = hikashop_get('class.shipping');
			$this->assignRef('shippingClass', $shippingClass);

			if(empty($order->order_shipping_method)) {
				$shippings_data = array();
				$shipping_ids = explode(';', $order->order_shipping_id);
				foreach($shipping_ids as $key) {
					$shipping_data = '';
					list($k, $w) = explode('@', $key);
					$shipping_id = $k;
					if(isset($order->shippings[$shipping_id])) {
						$shipping = $order->shippings[$shipping_id];
						$shipping_data = $shipping->shipping_name;
					} else {
						foreach($order->products as $order_product) {
							if($order_product->order_product_shipping_id == $key) {
								if(!is_numeric($order_product->order_product_shipping_id)) {
									$shipping_name = $this->getShippingName($order_product->order_product_shipping_method, $shipping_id);
									$shipping_data = $shipping_name;
								} else {
									$shipping_method_data = $this->shippingClass->get($shipping_id);
									$shipping_data = $shipping_method_data->shipping_name;
								}
								break;
							}
						}
						if(empty($shipping_data))
							$shipping_data = '[ ' . $key . ' ]';
					}
					$shippings_data[] = $shipping_data;
				}
				$order->order_shipping_method = $shippings_data;
			}
		}

		$products = array();
		if(!empty($order->products)) {
			$product_ids = array();
			foreach($order->products as $k => $v) {
				if(empty($v->product_id))
					continue;
				$product_ids[ (int)$v->product_id ] = (int)$v->product_id;
			}

			if(!empty($product_ids)) {
				$query = 'SELECT * FROM ' . hikashop_table('product') . ' as p WHERE p.product_id IN (' . implode(',', $product_ids) . ')';
				$db = JFactory::getDBO();
				$db->setQuery($query);
				$products = $db->loadObjectList('product_id');

				$productClass = hikashop_get('class.product');
				foreach($products as &$product) {
					$productClass->addAlias($product);
				}
				unset($product);
			}
		}
		$image_address_path = trim((string)$config->get('image_address_path'));
		if (!empty($image_address_path))
			$image_address_path = hikashop_cleanURL($image_address_path);

		$img_style_css = strip_tags(trim((string)$config->get('img_style_css')));

		$this->assignRef('image_address_path',$image_address_path);
		$this->assignRef('img_style_css',$img_style_css);
		$this->assignRef('products', $products);
		$this->assignRef('store_address',$store);
		$this->assignRef('element',$order);
		$this->assignRef('order',$order);
		$this->assignRef('invoice_type',$type);
		$display_type = 'frontcomp';
		$this->assignRef('display_type',$display_type);
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		if(is_string($order->order_shipping_method))
			$currentShipping = hikashop_import('hikashopshipping',$order->order_shipping_method);
		else
			$currentShipping = hikashop_import('hikashopshipping', reset($order->order_shipping_method));
		$this->assignRef('currentShipping',$currentShipping);
		$fields = array();
		if(hikashop_level(2)){
			$fields['entry'] = $fieldsClass->getFields('frontcomp',$order,'entry');
			$fields['item'] = $fieldsClass->getFields('frontcomp',$order,'item');

			if($type=='invoice')
				$fields['order'] = $fieldsClass->getFields('display:invoice=1',$order,'order');
			else
				$fields['order'] = $fieldsClass->getFields('display:front_order=1',$order,'order');
		}
		$this->assignRef('fields',$fields);
		return $order;
	}

	public function getShippingName($shipping_method, $shipping_id) {
		$shipping_name = $shipping_method . ' ' . $shipping_id;
		if(strpos($shipping_id, '-') !== false) {
			$shipping_ids = explode('-', $shipping_id, 2);
			$shipping = $this->shippingClass->get($shipping_ids[0]);
			if(!empty($shipping->shipping_params) && is_string($shipping->shipping_params))
				$shipping->shipping_params = hikashop_unserialize($shipping->shipping_params);
			$shippingMethod = hikashop_import('hikashopshipping', $shipping_method);
			$methods = $shippingMethod->shippingMethods($shipping);

			if(isset($methods[$shipping_id])){
				$shipping_name = $shipping->shipping_name.' - '.$methods[$shipping_id];
			}else{
				$shipping_name = $shipping_id;
			}
		}
		return $shipping_name;
	}
}
