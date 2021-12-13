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
class vendormarketViewVendormarket extends hikamarketView {

	const ctrl = 'vendor';
	const name = 'HIKA_VENDORS';
	const icon = 'user-tie';

	public function display($tpl = null, $params = null) {
		$this->params =& $params;
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName();
		$fct = $this->getLayout();
		if(method_exists($this, $fct) && $this->$fct($params) === false)
			return false;
		parent::display($tpl);
	}

	public function listing($tpl = null, $mainVendor = false) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		hikamarket::setTitle(JText::_(self::name), self::icon, self::ctrl);

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$shopConfig = hikamarket::config(false);
		$main_currency = (int)$shopConfig->get('main_currency', 1);

		$invoice_statuses = explode(',', $config->get('valid_order_statuses', 'confirmed,shipped'));
		foreach($invoice_statuses as &$invoice_status) {
			$invoice_status = $db->Quote($invoice_status);
		}
		unset($invoice_status);

		$vendorOrderType = 'subsale';

		$this->loadRef(array(
			'toggleClass' => 'helper.toggle',
			'currencyClass' => 'shop.class.currency'
		));

		$cfg = array(
			'table' => 'vendor',
			'main_key' => 'vendor_id',
			'order_sql_value' => 'a.vendor_id'
		);

		$manage = true;
		$this->assignRef('manage', $manage);

		$page_filters = array(
			'type' => 0,
			'vendors_unpaid' => 0
		);

		$pageInfo = $this->getPageInfo($cfg['order_sql_value'], 'asc', $page_filters);

		$filters = array();
		$searchMap = array(
			'vendor_name'
		);
		$order = '';
		$join = '';
		$extra_select = '';
		$group = '';

		if(!$mainVendor)
			$filters[] = 'a.vendor_id > 1';

		if($this->pageInfo->filter->vendors_unpaid == 1) {
			$extra_select = ', COUNT(o.order_id) ';
			$join .= ' INNER JOIN '.hikamarket::table('shop.order').' AS o ON o.order_vendor_id = a.vendor_id ';
			$filters[] = 'o.order_vendor_paid = 0';
			$filters[] = 'NOT(o.order_vendor_price = 0)';
			$filters[] = '(o.order_type = '.$db->Quote($vendorOrderType).' AND o.order_status IN ('.implode(',',$invoice_statuses).')) OR o.order_type = '.$db->Quote('vendorrefund');
			$group = ' GROUP BY a.vendor_id ';
		}

		$orderingAccept = array(
			'a.vendor_id',
			'a.vendor_name',
			'a.vendor_published',
			'a.vendor_email',
		);
		$this->processFilters($filters, $order, $searchMap, $orderingAccept);

		$query = 'FROM '.hikamarket::table($cfg['table']).' AS a '.$join.$filters.$group.$order;
		$db->setQuery('SELECT a.* '.$extra_select.$query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);

		$rows = $db->loadObjectList();
		$this->assignRef('rows', $rows);

		$db->setQuery('SELECT COUNT(a.vendor_id) '.$query);
		if($this->pageInfo->filter->vendors_unpaid == 1) {
			$query = 'FROM '.hikamarket::table($cfg['table']).' AS a '.$join.$filters;
			$db->setQuery('SELECT COUNT(DISTINCT a.vendor_id) '.$query);
		}
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);

		$ids = array();
		$vendorsFilter = '';
		if(!empty($rows)){
			foreach($rows as $row) {
				$ids[] = $row->vendor_id;
			}
			$vendorsFilter = 'AND t.vendor_id IN ('.implode(',',$ids).') ';
		}
		$query = 'SELECT t.vendor_id AS vendor_id, COUNT(t.order_transaction_paid) AS `number_unpaid`, t.order_transaction_currency_id AS `currency_id`, SUM(t.order_transaction_price) AS `price_unpaid` '.
			' FROM '.hikamarket::table('order_transaction').' AS t '.
			' WHERE t.order_transaction_paid = 0 AND t.order_transaction_valid > 0 AND t.order_transaction_price != 0.0 '.$vendorsFilter.
			' GROUP BY t.vendor_id, t.order_transaction_currency_id';
		$db->setQuery($query);
		$orders = $db->loadObjectList();
		foreach($rows as &$row) {
			$row->number_unpaid = 0;
			$row->price_unpaid = 0.0;
			if(empty($row->price_full))
				$row->price_full = 0.0;
			if(!empty($orders)) {
				foreach($orders as $order) {
					if((int)$order->vendor_id != (int)$row->vendor_id)
						continue;

					$row->number_unpaid += (int)$order->number_unpaid;

					if((int)$row->vendor_currency_id == 0)
						$row->vendor_currency_id = $main_currency;
					if((int)$order->currency_id == 0)
						$order->currency_id = $main_currency;

					if(!empty($order->price_unpaid)) {
						$row->price_unpaid += $this->currencyClass->convertUniquePrice((float)$order->price_unpaid, (int)$order->currency_id, (int)$row->vendor_currency_id);
					}
					if(!empty($order->price_full)) {
						$row->price_full += $this->currencyClass->convertUniquePrice((float)$order->price_full, (int)$order->currency_id, (int)$row->vendor_currency_id);
					}
				}
			}
			unset($row);
		}

		$this->toolbar = array(
			'pay' => array('name' => 'custom', 'icon' => 'pay', 'alt' => JText::_('PAY_VENDOR'), 'task' => 'pay', 'display' => $manage),
			'reports' => array('name' => 'custom', 'icon' => 'reports', 'alt' => JText::_('HIKAM_REPORTS'), 'task' => 'reports', 'display' => $manage),
			'|',
			array('name' => 'publishList', 'display' => $manage),
			array('name' => 'unpublishList', 'display' => $manage),
			array('name' => 'addNew', 'display' => $manage),
			array('name' => 'editList', 'display' => $manage),
			array('name' => 'deleteList', 'display' => $manage),
			'|',
			array('name' => 'pophelp', 'target' => 'vendor'),
			'dashboard'
		);

		$this->getPagination();

		$this->getOrdering('a.ordering', !$pageInfo->filter->type);
	}

	public function selection($tpl = null){
		$this->paramBase .= '.vendor_selection';
		$this->listing($tpl, true);

		$elemStruct = array(
			'vendor_name',
			'vendor_email'
		);
		$this->assignRef('elemStruct', $elemStruct);

		$singleSelection = hikaInput::get()->getVar('single', false);
		$this->assignRef('singleSelection', $singleSelection);
	}

	public function useselection() {
		$selection = hikaInput::get()->get('cid', array(), 'array');
		$rows = array();
		$data = '';

		$elemStruct = array(
			'vendor_name',
			'vendor_email'
		);

		if(!empty($selection)) {
			hikamarket::toInteger($selection);
			$db = JFactory::getDBO();
			$query = 'SELECT a.* FROM '.hikamarket::table('vendor').' AS a  WHERE a.vendor_id IN ('.implode(',',$selection).')';
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if(!empty($rows)) {
				$data = array();
				foreach($rows as $v) {
					$d = '{id:'.$v->user_id;
					foreach($elemStruct as $s) {
						if($s == 'id')
							continue;
						$d .= ','.$s.':"'. str_replace('"', '\"', $v->$s).'"';
					}
					$data[] = $d.'}';
				}
				$data = '['.implode(',', $data).']';
			}
		}
		$this->assignRef('rows', $rows);
		$this->assignRef('data', $data);

		$confirm = hikaInput::get()->getBool('confirm', true);
		$this->assignRef('confirm', $confirm);
		if($confirm) {
			$js = 'window.addEvent("domready", function(){window.top.hikamarket.submitBox('.$data.');});';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
		}
	}

	public function form($params = null) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		hikamarket::loadJslib('tooltip');

		$title = JText::_('HIKA_VENDOR');
		$ctrl = '';
		$cancelUrl = urlencode(base64_encode(hikamarket::completeLink('vendor')));
		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$formMode = true;
		$embbed = true;
		$vendor = null;
		$vendor_admin = null;
		$editor = null;
		$task = 'add';
		$users = array();
		$products = array();
		$products_count = 0;
		$orders = array();
		$orders_count = 0;
		$invoices = array();
		$invoices_count = 0;
		$fees = array();

		if($params != null && $params->get('configPanelIntegration', false))
			$formMode = false;

		$vendor_limit_vendors = $config->get('vendor_limit_orders_display', 10);
		$vendor_limit_products = $config->get('vendor_limit_products_display', 10);

		$vendor_id = 1;
		if($formMode) {
			$vendor_id = hikamarket::getCID();
			if($vendor_id <= 1)
				$vendor_id = 0;
		}

		$failVendor = hikaInput::get()->getVar('fail[vendor]', null);
		if(!empty($failVendor)) {
			$vendor = $failVendor;
			$vendor_id = $failVendor->vendor_id;
			if(empty($vendor->vendor_id))
				unset($vendor->vendor_id);
		} else if(!empty($vendor_id) || !$formMode) {
			$vendorClass = hikamarket::get('class.vendor');
			$vendor = $vendorClass->get($vendor_id);
		}

		if(empty($vendor) && $vendor_id == 1) {
			$e = $db->Quote('');
			$query = 'INSERT IGNORE INTO `'.hikamarket::table('vendor').'` '.
'(`vendor_id`,`vendor_admin_id`,`vendor_published`,`vendor_name`,`vendor_email`,`vendor_currency_id`,`vendor_description`,`vendor_access`,`vendor_shippings`,`vendor_params`) VALUES '.
'(1,0,1,'.$e.','.$e.',0,'.$e.','.$db->Quote('*').','.$e.','.$e.')';
			$db->setQuery($query);
			$db->execute();

			$app->enqueueMessage(JText::_('MAIN_VENDOR_RESTORED'), 'error');

			$vendorClass->get(false);
			$vendor = $vendorClass->get($vendor_id);
		}

		if(!empty($vendor)) {
			$task = 'edit&cid[]='.$vendor_id;
			$title = JText::sprintf('VENDOR_EDIT', $vendor->vendor_name);
			$cancelUrl = urlencode(base64_encode(hikamarket::completeLink('vendor&task=edit&cid[]='.$vendor_id)));
		} else {
			$vendor = new stdClass();
		}

		$ctrl .= '&task='.$task;
		$this->assignRef('task', $task);

		hikamarket::loadJslib('otree');

		$this->loadRef(array(
			'imageHelper' => 'shop.helper.image',
			'uploaderType' => 'shop.type.uploader',
			'popup' => 'shop.helper.popup',
			'currencyHelper' => 'shop.class.currency',
			'currencyType' => 'shop.type.currency',
			'categoryType' => 'type.shop_category',
			'marketaclType' => 'type.market_acl',
			'fieldsClass' => 'shop.class.field',
			'joomlaAcl' => 'type.joomla_acl',
			'nameboxType' => 'type.namebox'
		));

		if(hikamarket::level(1)) {
			$feeClass = hikamarket::get('class.fee');
			$this->assignRef('feeClass', $feeClass);
		}

		$vendor_acl = array();
		$vendor_group = array();
		$accesses = explode(',', @$vendor->vendor_access);
		foreach($accesses as $access) {
			if(substr($access, 0, 1) == '@') {
				$vendor_group[] = substr($access, 1);
			} else {
				$vendor_acl[] = $access;
			}
		}
		$vendor->vendor_acl = implode(',', $vendor_acl);
		$vendor->vendor_group = implode(',', $vendor_group);

		if(!empty($vendor->vendor_zone_id)) {
			$query = 'SELECT zone_namekey FROM ' . hikamarket::table('shop.zone') . ' WHERE zone_id = ' . (int)$vendor->vendor_zone_id;
			$db->setQuery($query);
			$vendor->vendor_zone_namekey = $db->loadResult();
		}

		if( !empty($vendor->vendor_admin_id) && (int)$vendor->vendor_admin_id > 0) {
			$userClass = hikamarket::get('shop.class.user');
			$vendor_admin = $userClass->get($vendor->vendor_admin_id);
		}

		$editor = hikamarket::get('shop.helper.editor');
		$editor->name = 'vendor_description';
		$editor->content = @$vendor->vendor_description;
		$editor->height = 250;

		$product_template = null;
		if(!empty($vendor->vendor_template_id) && (int)$vendor->vendor_template_id > 0) {
			$query = 'SELECT * FROM '.hikamarket::table('shop.product').' AS a WHERE a.product_type = \'template\' AND a.product_id = ' . (int)$vendor->vendor_template_id;
			$db->setQuery($query);
			$product_template = $db->loadObject();
		}
		$this->assignRef('product_template', $product_template);


		$extraFields = array(
			'vendor' => $this->fieldsClass->getFields('backend', $vendor, 'plg.hikamarket.vendor', 'user&task=state') // Call control "user" in hikashop backend part.
		);
		$this->assignRef('extraFields', $extraFields);
		$this->assignRef('vendor', $vendor);

		$null = array();
		$this->fieldsClass->addJS($null, $null, $null);
		$this->fieldsClass->jsToggle($this->extraFields['vendor'], $vendor, 0);

		if($vendor_id > 0) {
			$query = 'SELECT a.*,b.* FROM '.hikamarket::table('user','shop').' AS a LEFT JOIN '.hikamarket::table('users',false).' AS b ON a.user_cms_id = b.id '.
					'WHERE a.user_vendor_id = ' . (int)$vendor_id . ' ORDER BY a.user_id';
			$db->setQuery($query);
			$users = $db->loadObjectList('user_id');

			$query = 'SELECT hku.*, vu.user_access as `user_vendor_access`, ju.* '.
					' FROM '.hikamarket::table('user','shop').' AS hku '.
					' INNER JOIN '.hikamarket::table('vendor_user').' AS vu ON hku.user_id = vu.user_id ' .
					' LEFT JOIN '.hikamarket::table('users',false).' AS ju ON hku.user_cms_id = ju.id '.
					' WHERE vu.vendor_id = ' . (int)$this->vendor->vendor_id . ' ORDER BY hku.user_id';
			$db->setQuery($query);
			$o_users = $db->loadObjectList('user_id');

			$users = array_merge($users, $o_users);
			unset($o_users);
		}
		$this->assignRef('users', $users);

		if($vendor_id > 1) {
			$query = 'SELECT a.*, c.* FROM '.hikamarket::table('shop.order').' AS a INNER JOIN '.hikamarket::table('shop.user').' AS c ON a.order_user_id = c.user_id '.
					'WHERE a.order_vendor_id = ' . $vendor_id . ' AND a.order_type = \'subsale\' ORDER BY a.order_id DESC';
			$db->setQuery($query, 0, $vendor_limit_vendors);
			$orders = $db->loadObjectList();

			$query = 'SELECT COUNT(*) FROM '.hikamarket::table('shop.order').' AS a WHERE a.order_vendor_id = ' . $vendor_id . ' AND a.order_type = \'subsale\'';
			$db->setQuery($query);
			$orders_count = $db->loadResult();
		}
		$this->assignRef('orders', $orders);
		$this->assignRef('orders_count', $orders_count);

		if($vendor_id > 1) {
			$query = 'SELECT a.* FROM '.hikamarket::table('shop.order').' AS a '.
					'WHERE a.order_vendor_id = ' . $vendor_id . ' AND (a.order_type = \'vendorpayment\' OR (a.order_type = \'sale\' AND a.order_id = a.order_vendor_paid)) ORDER BY a.order_id DESC';
			$db->setQuery($query, 0, $vendor_limit_vendors);
			$invoices = $db->loadObjectList();

			$query = 'SELECT COUNT(*) FROM '.hikamarket::table('shop.order').' AS a '.
					'WHERE a.order_vendor_id = ' . $vendor_id . ' AND (a.order_type = \'vendorpayment\' OR (a.order_type = \'sale\' AND a.order_id = a.order_vendor_paid))';
			$db->setQuery($query);
			$invoices_count = $db->loadresult();
		}
		$this->assignRef('invoices', $invoices);
		$this->assignRef('invoices_count', $invoices_count);

		if($vendor_id > 1) {
			$query = 'SELECT * FROM '.hikamarket::table('shop.product').' AS a WHERE a.product_vendor_id = ' . $vendor_id . ' ORDER BY a.product_id DESC';
			$db->setQuery($query, 0, $vendor_limit_products);
			$products = $db->loadObjectList();

			$query = 'SELECT COUNT(*) FROM '.hikamarket::table('shop.product').' AS a WHERE a.product_vendor_id = ' . $vendor_id . '';
			$db->setQuery($query);
			$products_count = $db->loadResult();
		}
		$this->assignRef('products', $products);
		$this->assignRef('products_count', $products_count);

		if($vendor_id > 1 && hikamarket::level(1)) {
			$fees = $feeClass->getVendor($vendor_id);
		}
		$this->assignRef('fees', $fees);

		if(!empty($vendor->vendor_zone_id)) {
			$query = 'SELECT * FROM '.hikamarket::table('shop.zone').' WHERE zone_id = ' . (int)$vendor->vendor_zone_id;
			$db->setQuery($query);
			$vendor->zone = $db->loadObject();
		}

		$this->assignRef('embbed', $embbed);
		$this->assignRef('vendor', $vendor);
		$this->assignRef('vendor_id', $vendor_id);
		$this->assignRef('vendor_admin', $vendor_admin);
		$this->assignRef('editor', $editor);
		$this->assignRef('cancelUrl', $cancelUrl);

		if($formMode) {
			hikamarket::setTitle($title, self::icon, self::ctrl.$ctrl);
			$this->toolbar = array(
				'pay' => array('name' => 'Link', 'icon' => 'pay', 'alt' => JText::_('PAY_VENDOR'), 'url' => hikamarket::completeLink('vendor&task=pay&cid[]='.$vendor_id)),
				'reports' => array('name' => 'Link', 'icon' => 'reports', 'alt' => JText::_('HIKAM_REPORTS'), 'url' => hikamarket::completeLink('vendor&task=reports&cid[]='.$vendor_id)),
				'|',
				'save',
				'apply',
				array('name' => 'hikacancel', 'url' => hikamarket::completeLink('vendor')),
				'|',
				array('name' => 'pophelp', 'target' => self::ctrl.'-form')
			);

			if($config->get('market_mode', 'fee') == 'commission') {
				$this->toolbar['pay'] = array(
					'name' => 'Link',
					'icon' => 'invoice',
					'alt' => JText::_('GENERATE_INVOICE'),
					'url' => hikamarket::completeLink('vendor&task=geninvoice&cid[]='.$vendor_id)
				);
			}
		}
	}

	public function fees($params = null) {
		if($params == null || !$params->get('configPanelIntegration', false))
			return false;

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$this->loadRef(array(
			'currencyHelper' => 'shop.class.currency',
			'currencyType' => 'shop.type.currency',
			'feeClass' => 'class.fee',
			'joomlaAclType' => 'type.joomla_acl'
		));

		$formRoot = 'config';
		$this->assignRef('formRoot', $formRoot);

		$fees = $this->feeClass->getConfig();
		$this->assignRef('fees', $fees);

		$fees_show_groups = true;
		$this->assignRef('fees_show_groups', $fees_show_groups);
	}

	public function products($tpl = null) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$cancelUrl = urlencode(base64_encode(hikamarket::completeLink('vendor')));
		$this->paramBase .= '.products';

		$vendor_id = hikamarket::getCID();
		if( $vendor_id > 0 )
			$cancelUrl = urlencode(base64_encode(hikamarket::completeLink('vendor&task=edit&cid[]='.$vendor_id)));

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$toggleClass = hikamarket::get('helper.toggle');
		$this->assignRef('toggleClass', $toggleClass);

		$filterType = $app->getUserStateFromRequest($this->paramBase.'.filter_type', 'filter_type', 0, 'int');

		$cfg = array(
			'table' => 'shop.product',
			'main_key' => 'product_id',
			'order_sql_value' => 'a.product_id'
		);

		$pageInfo = new stdClass();
		$filters = array();

		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($this->paramBase.".filter_order", 'filter_order', $cfg['order_sql_value'], 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($this->paramBase.".filter_order_Dir", 'filter_order_Dir', 'asc', 'word');

		$pageInfo->limit = new stdClass();
		$pageInfo->limit->value = $app->getUserStateFromRequest($this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value))
			$pageInfo->limit->value = 500;
		if(hikaInput::get()->getVar('search') != $app->getUserState($this->paramBase.".search")) {
			$app->setUserState($this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		} else {
			$pageInfo->limit->start = $app->getUserStateFromRequest($this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}

		$pageInfo->search = HikaStringHelper::strtolower($app->getUserStateFromRequest($this->paramBase.".search", 'search', '', 'string'));
		$this->assignRef('pageInfo', $pageInfo);

		$filters = array();
		$searchMap = array(
			'product_name',
			'product_code',
			'product_id'
		);

		if(!empty($pageInfo->search)) {
			$searchVal = '\'%' . $db->escape(HikaStringHelper::strtolower($pageInfo->search), true) . '%\'';
			$filters[] = '(' . implode(' LIKE '.$searchVal.' OR ',$searchMap).' LIKE '.$searchVal . ')';
		}
		if(!empty($filters)) {
			$filters = ' WHERE a.product_vendor_id = '.$vendor_id.' AND ' . implode(' AND ', $filters);
		} else {
			$filters = ' WHERE a.product_vendor_id = '.$vendor_id;
		}

		$order = '';
		if(!empty($pageInfo->filter->order->value)) {
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$query = 'FROM '.hikamarket::table($cfg['table']).' AS a '.$filters.$order;
		$db->setQuery('SELECT * '.$query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);

		$rows = $db->loadObjectList();
		if(!empty($pageInfo->search)) {
			$rows = hikamarket::search($pageInfo->search, $rows, $cfg['main_key']);
		}
		$this->assignRef('products', $rows);

		$db->setQuery('SELECT COUNT(*) '.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);

		$this->assignRef('vendor_id', $vendor_id);
		$this->assignRef('cancelUrl', $cancelUrl);

		$manager = true;

		hikamarket::setTitle(JText::_(self::name), self::icon, self::ctrl.$ctrl);
		$this->toolbar = array(
			array('name' => 'hikacancel', 'url' => hikamarket::completeLink('vendor&task=edit&cid[]='.$vendor_id)),
			'|',
			array('name' => 'pophelp', 'target' => self::ctrl.'-form')
		);

		jimport('joomla.html.pagination');
		if($pageInfo->limit->value == 500)
			$pageInfo->limit->value = 100;
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
		$this->assignRef('pagination', $pagination);

		$doOrdering = !$filterType;
		$this->assignRef('doOrdering', $doOrdering);
		if($doOrdering) {
			$ordering = new stdClass();
			$ordering->ordering = false;
			$ordering->orderUp = 'orderup';
			$ordering->orderDown = 'orderdown';
			$ordering->reverse = false;
			if($pageInfo->filter->order->value == 'a.ordering') {
				$ordering->ordering = true;
				if($pageInfo->filter->order->dir == 'desc') {
					$ordering->orderUp = 'orderdown';
					$ordering->orderDown = 'orderup';
					$ordering->reverse = true;
				}
			}
			$this->assignRef('ordering', $ordering);
		}
	}

	public function pay($tpl = null) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$cancelUrl = urlencode(base64_encode(hikamarket::completeLink('vendor')));
		$this->paramBase .= '.pay';

		$vendor_id = hikamarket::getCID();
		$vendor_ids = hikaInput::get()->get('cid', array(), 'array');
		if(!empty($vendor_ids) && count($vendor_ids) > 1) {
			$vendor_id = $vendor_ids;
			hikamarket::toInteger($vendor_id);
		}

		if(empty($vendor_id)) {
			$app->redirect(hikamarket::completeLink('vendor'));
			return false;
		}
		$this->assignRef('vendor_id', $vendor_id);

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$feeMode = ($this->config->get('market_mode', 'fee') == 'fee');
		$this->assignRef('feeMode', $feeMode);

		$valid_statuses = explode(',', $config->get('valid_order_statuses', 'confirmed,shipped'));
		foreach($valid_statuses as &$status) {
			$status = $db->Quote($status);
		}

		$this->loadRef(array(
			'vendorClass' => 'class.vendor',
			'toggleHelper' => 'helper.toggle',
			'currencyHelper' => 'shop.class.currency',
			'paymentMethods' => 'type.paymentmethods',
			'popup' => 'shop.helper.popup',
		));

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$this->assignRef('pageInfo', $pageInfo);

		if(!is_array($vendor_id)) {
			$vendor = $this->vendorClass->get($vendor_id);
			$this->assignRef('vendor', $vendor);

			$title = JText::sprintf('VENDOR_PAY', $vendor->vendor_name);
			$cancelUrl = urlencode(base64_encode(hikamarket::completeLink('vendor&task=pay&cid[]='.$vendor_id)));
			$ctrl = '&task=pay&cid[]='.$vendor_id;

			$this->assignRef('cancelUrl', $cancelUrl);

			$query = 'SELECT t.*, o.*, u.* '.
				' FROM '.hikamarket::table('order_transaction').' AS t '.
				' LEFT JOIN '.hikamarket::table('shop.order').' AS o ON o.order_type = '.$db->Quote('subsale').' AND o.order_parent_id = t.order_id AND o.order_vendor_id = '.$vendor_id . ' '.
				' LEFT JOIN '.hikamarket::table('shop.user').' AS u ON o.order_user_id = u.user_id '.
				' WHERE t.order_transaction_paid = 0 AND t.order_transaction_valid > 0 AND t.order_transaction_price != 0.0 AND t.vendor_id = '.$vendor_id.''.
				' ORDER BY t.order_transaction_created DESC, o.order_invoice_created DESC, o.order_created DESC, o.order_id DESC';
			$db->setQuery($query);
			$orders = $db->loadObjectList();
			$this->assignRef('orders', $orders);
		} else {
			$query = 'SELECT v.* FROM ' . hikamarket::table('vendor') . ' AS v WHERE vendor_id IN ('.implode(',', $vendor_id).')';
			$db->setQuery($query);
			$vendors = $db->loadObjectList('vendor_id');
			$this->assignRef('vendors', $vendors);

			$vendor_names = array();
			foreach($vendors as &$v) {
				$vendor_names[] = $v->vendor_name;
				$v->nb_orders = 0;
				$v->total_vendor_price = 0.0;
				$v->total_full_price = 0.0;
			}
			unset($v);
			$vendor_names = implode(', ', array_slice($vendor_names, 0, 5));

			$title = JText::sprintf('VENDOR_PAY', $vendor_names);

			$pageInfo->filter->filter_start = $app->getUserStateFromRequest($this->paramBase.'.filter_start', 'filter_start', '', 'string');
			$pageInfo->filter->filter_end = $app->getUserStateFromRequest($this->paramBase.'.filter_end', 'filter_end', '', 'string');

			$filters = array(
				'vendor_id' => 't.vendor_id IN ('.implode(',', $vendor_id).')',
				'transaction_price' => 't.order_transaction_price != 0',
				'transaction_paid' => 't.order_transaction_paid = 0',
				'transaction_valid' => 't.order_transaction_valid > 0'
			);
			$date_filters = '';
			if(!empty($pageInfo->filter->filter_start)) {
				$parts = explode(' ', $pageInfo->filter->filter_start);
				$parts = explode('-', $parts[0]);
				$start = hikamarket::getTime(mktime(0, 0, 0, $parts[1], $parts[2], $parts[0]));

				$filters['date_start'] = 't.order_transaction_created >= ' . (int)$start;
			}
			if(!empty($pageInfo->filter->filter_end)) {
				$parts = explode(' ', $pageInfo->filter->filter_end);
				$parts = explode('-', $parts[0]);
				$end = hikamarket::getTime(mktime(23, 59, 59, $parts[1], $parts[2], $parts[0]));

				$filters['date_end'] = 't.order_transaction_created <= ' . (int)$end;
			}

			$query = 'SELECT t.vendor_id, t.order_transaction_currency_id, COUNT(t.order_transaction_id) AS `nb_orders`, SUM(t.order_transaction_price) AS `total_vendor_price`, SUM(o.order_full_price) AS `total_full_price` '.
					' FROM '.hikamarket::table('order_transaction').' AS t '.
					' LEFT JOIN '.hikamarket::table('shop.order').' AS o ON t.order_id = o.order_parent_id AND t.vendor_id = o.order_vendor_id '.
					' WHERE ('.implode(') AND (', $filters) .')'.
					' GROUP BY t.vendor_id, t.order_transaction_currency_id';
			$db->setQuery($query);
			$transactions = $db->loadObjectList();
			foreach($transactions as $transaction) {
				if(empty($vendors[ (int)$transaction->vendor_id ]))
					continue;
				$vendors[ (int)$transaction->vendor_id ]->nb_orders += (int)$transaction->nb_orders;

				$vendor_currency_id = (int)$vendors[ (int)$transaction->vendor_id ]->vendor_currency_id;
				if($vendor_currency_id == (int)$transaction->order_transaction_currency_id) {
					$vendors[ (int)$transaction->vendor_id ]->total_vendor_price += (float)hikamarket::toFloat($transaction->total_vendor_price);
					$vendors[ (int)$transaction->vendor_id ]->total_full_price += (float)hikamarket::toFloat($transaction->total_full_price);
				} else {
					$order->total_vendor_price = (float)hikamarket::toFloat($order->total_vendor_price);
					$order->total_full_price = (float)hikamarket::toFloat($order->total_full_price);

					$vendors[ (int)$transaction->vendor_id ]->total_vendor_price += $this->currencyHelper->convertUniquePrice($transaction->total_vendor_price, (int)$transaction->order_transaction_currency_id, $vendor_currency_id);
					$vendors[ (int)$transaction->vendor_id ]->total_full_price += $this->currencyHelper->convertUniquePrice($transaction->total_full_price, (int)$transaction->order_transaction_currency_id, $vendor_currency_id);

					if(empty($vendors[ (int)$transaction->vendor_id ]->currencies))
						$vendors[ (int)$transaction->vendor_id ]->currencies = array();
					$vendors[ (int)$transaction->vendor_id ]->currencies[(int)$transaction->order_transaction_currency_id] = array(
						'vendor' => $transaction->total_vendor_price,
						'full' => $transaction->total_full_price
					);
				}
			}
		}

		hikamarket::setTitle($title, self::icon, self::ctrl.$ctrl);
		$this->toolbar = array(
			'pay' => array('name' => 'custom', 'icon' => 'pay', 'alt' => JText::_('PAY_VENDOR'), 'task' => 'dopay'),
			array('name' => 'hikacancel', 'url' => (is_int($vendor_id) ? hikamarket::completeLink('vendor&task=edit&cid[]='.$vendor_id) : hikamarket::completeLink('vendor&task=listing')) ),
			'|',
			array('name' => 'pophelp', 'target' => self::ctrl.'-pay')
		);
		if(!$feeMode) {
			$this->toolbar['pay'] = array(
				'name' => 'custom',
				'icon' => 'invoice',
				'alt' => JText::_('GENERATE_INVOICE'),
				'task' => 'dogeninvoice'
			);
		}
	}

	public function payreport($tpl = null) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';

		$order_ids = hikaInput::get()->get('cid', array(), 'array');
		hikamarket::toInteger($order_ids);

		$this->loadRef(array(
			'popup' => 'shop.helper.popup',
			'currencyClass' => 'shop.class.currency'
		));

		$shopConfig = hikamarket::config(false);
		$created_status = $shopConfig->get('order_created_status', 'created');
		$this->assignRef('created_status', $created_status);

		$order_types = array('vendorpayment', 'sale');
		foreach($order_types as &$order_type) {
			$order_type = $db->Quote($order_type);
		}
		unset($order_type);

		$query = 'SELECT v.*, o.* FROM ' . hikamarket::table('shop.order') . ' AS o '.
				' INNER JOIN ' . hikamarket::table('vendor') . ' AS v ON o.order_vendor_id = v.vendor_id '.
				' WHERE o.order_type IN ('.implode(',', $order_types).') AND o.order_id IN ('.implode(',', $order_ids).')';
		$db->setQuery($query);
		$orders = $db->loadObjectList('order_id');
		$this->assignRef('orders', $orders);

		$cancelUrl = urlencode(base64_encode(hikamarket::completeLink('vendor&task=pay&report=1&cid='.implode('&cid=', $order_ids))));
		$this->assignRef('cancelUrl', $cancelUrl);

		hikamarket::setTitle(JText::_('VENDOR_PAY_REPORT'), self::icon, self::ctrl.$ctrl.'&task=pay&report=1&cid[]=' . implode('&cid[]=', $order_ids));
		$this->toolbar = array(
			array('name' => 'hikacancel', 'url' => hikamarket::completeLink('vendor&task=listing')),
			'|',
			array('name' => 'pophelp', 'target' => self::ctrl.'-pay')
		);
	}

	public function paymanual($tpl = null) {
		$app = JFactory::getApplication();
		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);

		$vendor_id = hikamarket::getCID('vendor_id');
		$order_id = hikaInput::get()->getInt('order_id', 0);


		$this->loadRef(array(
			'vendorClass' => 'class.vendor',
			'orderClass' => 'class.order',
			'currencyClass' => 'shop.class.currency',
		));

		$order = $this->orderClass->getRaw($order_id);
		$this->assignRef('order', $order);

		$payment_method = hikaInput::get()->getString('payment_method', 'manual');
		$this->assignRef('payment_method', $payment_method);

		$vendor = $this->vendorClass->get($vendor_id);
		$this->assignRef('vendor', $vendor);

		if(($order->order_vendor_id != $vendor->vendor_id) || $order->order_type != 'vendorpayment') {
			$app->enqueueMessage(JText::_('INVALID_DATA'), 'error');
			return false;
		}

		$created_status = $shopConfig->get('order_created_status', 'created');
		$confirmed_status = $shopConfig->get('order_confirmed_status', 'confirmed');
		if($order->order_status == $confirmed_status) {
			$app->enqueueMessage(JText::_('HIKAM_ORDER_ALREADY_PAID'));
			return false;
		}

		$this->assignRef('confirmed_status', $confirmed_status);

		if($payment_method == 'paypal') {
			$lang = JFactory::getLanguage();
			$locale = strtolower(substr($lang->get('tag'), 0, 2));

			$notify_url = HIKASHOP_LIVE.'index.php?option=com_hikamarket&ctrl=vendor&task=vendorpaynotify&mode=paypal&order_id='.$order->order_id.'&tmpl=component&lang='.$locale;
			$return_url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikamarket&ctrl=vendor&task=paymanual&order_id='.$order->order_id.'&vendor_id='.$vendor->vendor_id.'&return=1&tmpl=component';
			$cancel_url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikamarket&ctrl=vendor&task=paymanual&order_id='.$order->order_id.'&vendor_id='.$vendor->vendor_id.'&return=1&error=1&tmpl=component';

			$this->return_url = 'https://www.paypal.com/cgi-bin/webscr';

			$currencies = null;
			$currencies = $this->currencyClass->getCurrencies($order->order_currency_id, $currencies);
			$currency = $currencies[$order->order_currency_id];

			if($currency->currency_locale['int_frac_digits'] > 2)
				$currency->currency_locale['int_frac_digits'] = 2;

			$this->vars = array(
				'cmd' => '_ext-enter',
				'redirect_cmd' => '_cart',
				'upload' => '1',
				'business' => $vendor->vendor_params->paypal_email,
				'receiver_email' => $vendor->vendor_params->paypal_email,
				'invoice' => $order->order_id,
				'currency_code' => $currency->currency_code,
				'return' => $return_url,
				'notify_url' => $notify_url,
				'cancel_return' => $cancel_url,
				'undefined_quantity' => '0',
				'test_ipn' => '0',
				'no_shipping' => '1',
				'no_note' => '1',
				'charset' => 'utf-8',
				'rm' => '0',
				'bn' => 'ObsidevHikaMarket_Cart_WPS',
				'amount_1' => round($order->order_full_price, (int)$currency->currency_locale['int_frac_digits']),
				'item_name_1' => JText::sprintf('VENDOR_ORDER_PAYMENT', $vendor->vendor_name, $order->order_number)
			);
		}
	}

	public function searchfields() {
		$db = JFactory::getDBO();
		if(!HIKASHOP_J30) {
			$columnTable = $db->getTableFields(hikamarket::table('vendor'));
			$columns = reset($columnTable);
		} else {
			$columns = $db->getTableColumns(hikamarket::table('vendor'));
		}

		$rows = array_keys($columns);
		$selected = hikaInput::get()->getString('values', '');
		$selected_values = explode(',', $selected);
		$new_rows = array();

		foreach($rows as $id => $row) {
			$obj = new stdClass();
			$obj->namekey = $row;
			if(in_array($row, $selected_values))
				$obj->selected = true;
			$new_rows[] = $obj;
		}

		$this->assignRef('rows',$new_rows);
		$controlName = hikaInput::get()->getString('control', 'params');
		$this->assignRef('controlName', $controlName);
	}

	public function delete() {
		$db = JFactory::getDBO();

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$toggleClass = hikamarket::get('helper.toggle');
		$this->assignRef('toggleClass', $toggleClass);

		$cid = hikaInput::get()->post->get('cid', array(), 'array');
		hikamarket::toInteger($cid);

		$query = 'SELECT v.* FROM ' . hikamarket::table('vendor') . ' AS v '.
				' WHERE v.vendor_id IN ('.implode(',', $cid) . ') AND v.vendor_id > 1';
		$db->setQuery($query);
		$vendors = $db->loadObjectList('vendor_id');
		$this->assignRef('vendors', $vendors);

		$vendor_ids = array_keys($vendors);
		$this->assignRef('vendor_ids', $vendor_ids);

		$query = 'SELECT p.product_vendor_id, count(p.product_id) as value FROM ' . hikamarket::table('shop.product') . ' AS p WHERE p.product_vendor_id IN ('.implode(',', $vendor_ids) . ')';
		$db->setQuery($query);
		$data = $db->loadObjectList('product_vendor_id');
		foreach($data as $k => $d) {
			if((int)$k > 0)
				$vendors[(int)$k]->products = $d->value;
		}

		$query = 'SELECT o.order_vendor_id, count(o.order_id) as value FROM ' . hikamarket::table('shop.order') . ' AS o WHERE o.order_vendor_id IN ('.implode(',', $vendor_ids) . ') AND o.order_type = '.$db->Quote('subsale');
		$db->setQuery($query);
		$data = $db->loadObjectList('order_vendor_id');
		foreach($data as $k => $d) {
			if((int)$k > 0)
				$vendors[(int)$k]->orders = $d->value;
		}

		$query = 'SELECT u.user_vendor_id, count(u.user_id) as value FROM ' . hikamarket::table('shop.user') . ' AS u WHERE u.user_vendor_id IN ('.implode(',', $vendor_ids) . ')';
		$db->setQuery($query);
		$data = $db->loadObjectList('user_vendor_id');
		foreach($data as $k => $d) {
			if((int)$k > 0)
				$vendors[(int)$k]->users = $d->value;
		}

		hikamarket::toInteger($vendor_ids);
		sort($vendor_ids);
		$confirm_value = md5(implode(';', $vendor_ids));
		$this->assignRef('confirm_value', $confirm_value);

		hikamarket::setTitle(JText::_('DELETE_VENDORS'), self::icon, self::ctrl);
		$this->toolbar = array(
			'remove' => array('name' => 'custom', 'icon' => 'delete', 'alt' => JText::_('HIKA_DELETE'), 'task' => 'remove'),
			'hikacancel',
			'|',
			array('name' => 'pophelp', 'target' => self::ctrl.'-form')
		);
	}

	public function reports() {
		$app = JFactory::getApplication();
		$config = hikamarket::config();

		$vendor_id = hikamarket::getCID();
		$vendorClass = hikamarket::get('class.vendor');

		$vendor = $vendorClass->get($vendor_id);
		if(empty($vendor)) {
			$app->enqueueMessage(JText::_('VENDOR_DOES_NOT_EXIST'), 'error');
			$app->redirect( hikamarket::completeLink('vendor&task=listing', false, true) );
		}

		$statisticsClass = hikamarket::get('class.statistics');
		$statistics = $statisticsClass->getVendor($vendor);

		$vendor_statistics = $config->get('vendor_statistics', null);
		if(!empty($vendor_statistics)) {
			foreach($statistics as $key => &$stat) {
				$stat['published'] = false;
			}
			unset($stat);

			$vendor_statistics = hikamarket::unserialize(base64_decode($vendor_statistics));
			foreach($vendor_statistics as $key => $stat_conf) {
				if(!isset($statistics[$key]))
					continue;

				if(isset($stat_conf['container']))
					$statistics[$key]['container'] = (int)$stat_conf['container'];
				if(isset($stat_conf['slot']))
					$statistics[$key]['slot'] = (int)$stat_conf['slot'];
				if(isset($stat_conf['order']))
					$statistics[$key]['order'] = (int)$stat_conf['order'];
				if(isset($stat_conf['published']))
					$statistics[$key]['published'] = $stat_conf['published'];
				if(!empty($stat_conf['vars'])) {
					foreach($stat_conf['vars'] as $k => $v)
						$statistics[$key]['vars'][$k] = $v;
				}
			}

			uasort($statistics, array($this, 'sortStats'));
		}

		$statistic_slots = array();
		if(!empty($statistics)) {
			foreach($statistics as $key => &$stat) {
				if(isset($stat['published']) && empty($stat['published']))
					continue;

				$stat['key'] = $key;
				if(empty($stat['slot']))
					$stat['slot'] = 0;
				if(!isset($statistic_slots[ (int)$stat['slot'] ]))
					$statistic_slots[ (int)$stat['slot'] ] = array();

				$order = @$stat['order'] * 100;
				if(isset($statistic_slots[ $stat['slot'] ][ $order ])) {
					for($i = 1; $i < 100; $i++) {
						if(!isset($statistic_slots[ (int)$stat['slot'] ][ $order + $i ])) {
							$order += $i;
							break;
						}
					}
				}

				$statistic_slots[ (int)$stat['slot'] ][$order] =& $stat;
			}
			unset($stat);
		}

		$this->assignRef('statistics', $statistics);
		$this->assignRef('statisticsClass', $statisticsClass);
		$this->assignRef('statistic_slots', $statistic_slots);

		$toolbar_icon = 'chart-bar';

		hikamarket::setTitle(JText::sprintf('VENDOR_REPORTS', $vendor->vendor_name), $toolbar_icon, self::ctrl.'&task=reports&cid='.$vendor_id);
		$this->toolbar = array(
			array('name' => 'hikacancel', 'url' => hikamarket::completeLink('vendor&task=edit&cid='.$vendor_id)),
			'|',
			array('name' => 'pophelp', 'target' => self::ctrl.'-form'),
			'dashboard'
		);
	}

	protected function sortStats($a, $b) {
		if($a['order'] == $b['order'])
			return 0;
		return ($a['order'] < $b['order']) ? -1 : 1;
	}
}
