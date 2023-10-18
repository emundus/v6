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
class OrderstatusViewOrderstatus extends hikashopView
{
	var $type = '';
	var $ctrl = 'orderstatus';
	var $nameListing = 'HIKA_ORDERSTATUSES';
	var $nameForm = 'HIKA_ORDERSTATUS';
	var $icon = 'tasks';
	var $triggerView = true;

	public function display($tpl = null) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function))
			$this->$function();
		parent::display($tpl);
	}

	public function listing() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$config = hikashop_config();

		$this->loadRef(array(
			'toggleHelper' => 'helper.toggle'
		));

		$pageInfo = $this->getPageInfo('o.orderstatus_ordering');

		$filters = array();
		$order = '';
		$searchMap = array('o.orderstatus_id', 'o.orderstatus_name', 'o.orderstatus_namekey', 'o.orderstatus_description');

		$this->processFilters($filters, $order, $searchMap);
		$query = ' FROM ' . hikashop_table('orderstatus').' AS o ' . $filters . $order;
		$this->getPageInfoTotal($query, '*');
		$db->setQuery('SELECT o.*' . $query, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $db->loadObjectList();

		if(!empty($pageInfo->search)) {
			$rows = hikashop_search($pageInfo->search, $rows, array('orderstatus_id', 'orderstatus_namekey', 'orderstatus_published', 'orderstatus_ordering', 'orderstatus_email_params', 'orderstatus_links_params'));
		}


		$orderstatusClass = hikashop_get('class.orderstatus');
		$this->orderStatuses = $orderstatusClass->getList();
		$this->colors = true;
		foreach($this->orderStatuses as $status) {
			if(!empty($status->orderstatus_color)) {
				$this->colors = true;
				break;
			}
		}

		$this->assignRef('rows', $rows);

		$orderstatus_columns = array(
			'created' => array(
				'text' => JText::_('CREATED'),
				'title' => JText::_('DEFAULT_ORDER_STATUS'),
				'description' => JText::_('CREATED_DESC'),
				'key' => 'order_created_status',
				'default' => 'created',
				'type' => 'radio'
			),
			'unpaid' => array(
				'text' => JText::_('UNPAID'),
				'title' => JText::_('UNPAID_ORDER_STATUSES'),
				'description' => JText::_('UNPAID_DESC'),
				'key' => 'order_unpaid_statuses',
				'default' => 'created',
				'type' => 'toggle'
			),
			'cancellable' => array(
				'text' => JText::_('CANCELLABLE'),
				'title' => JText::_('CANCELLABLE_ORDER_STATUS'),
				'description' => JText::_('CANCELLABLE_DESC'),
				'key' => 'cancellable_order_status',
				'default' => '',
				'type' => 'toggle'
			),
			'cancelled' => array(
				'text' => JText::_('CANCELLED'),
				'title' => JText::_('CANCELLED_ORDER_STATUS'),
				'description' => JText::_('CANCELLED_DESC'),
				'key' => 'cancelled_order_status',
				'default' => '',
				'type' => 'toggle'
			),
			'capture' => array(
				'text' => JText::_('CAPTURE'),
				'title' => JText::_('PAYMENT_CAPTURE_ORDER_STATUS'),
				'description' => JText::_('CAPTURE_DESC'),
				'key' => 'payment_capture_order_status',
				'default' => '',
				'type' => 'toggle'
			),
			'confirmed' => array(
				'text' => JText::_('CONFIRMED'),
				'title' => JText::_('CONFIRMED_ORDER_STATUS'),
				'description' => JText::_('CONFIRMED_DESC'),
				'key' => 'order_confirmed_status',
				'default' => 'confirmed,shipped',
				'type' => 'radio'
			),
			'invoice' => array(
				'text' => JText::_('INVOICE'),
				'title' => JText::_('INVOICE_ORDER_STATUSES'),
				'description' => JText::_('INVOICE_DESC'),
				'key' => 'invoice_order_statuses',
				'default' => 'confirmed,shipped',
				'type' => 'toggle'
			),
		);

		if(hikashop_level(1)){
			$orderstatus_columns['print'] = array(
				'text' => JText::_('PRINT_INVOICE'),
				'title' => JText::_('PRINT_ORDER_STATUSES'),
				'description' => JText::_('PRINT_DESC'),
				'key' => 'print_invoice_statuses',
				'default' => 'confirmed,shipped,refunded',
				'type' => 'toggle'
			);
			$orderstatus_columns['contact'] = array(
				'text' => JText::_('CONTACT_BUTTON'),
				'title' => JText::_('CONTACT_BUTTON_ON_ORDERS'),
				'description' => JText::_('CONTACT_BUTTON_DESC'),
				'key' => 'contact_button_orders',
				'default' => 'created,confirmed,shipped,refunded,pending,cancelled',
				'type' => 'toggle'
			);
		}
		$orderstatus_columns['download'] = array(
			'text' => JText::_('DOWNLOAD'),
			'title' => JText::_('ORDER_STATUS_FOR_DOWNLOAD'),
			'description' => JText::_('DOWNLOAD_DESC'),
			'key' => 'order_status_for_download',
			'default' => 'confirmed,shipped',
			'type' => 'toggle'
		);


		if(!$config->get('legacy_widgets',0)){
			$orderstatus_columns['statistics'] = array(
				'text' => JText::_('HIKA_STATISTICS'),
				'title' => JText::_('STATISTICS_ORDER_STATUS'),
				'description' => JText::_('STATISTICS_DESC'),
				'key' => 'stats_valid_order_statuses',
				'default' => 'confirmed,shipped',
				'type' => 'toggle'
			);
		}


		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();
		$app->triggerEvent('onOrderStatusListingLoad', array(&$orderstatus_columns, &$rows));

		$this->assignRef('orderstatus_columns', $orderstatus_columns);

		if(version_compare(PHP_VERSION, '5.2.0', '>=')) {
			$columns = array_fill_keys(array_keys($orderstatus_columns), false);
		} else {
			$keys = array_keys($orderstatus_columns);
			$columns = array_combine($keys, array_fill(0, count($keys), false));
		}

		foreach($orderstatus_columns as $key => $column){
			if(empty($column['trigger']))
				$orderstatus_columns[$key]['trigger'] = 'fct.configstatus';
		}

		foreach($rows as &$row) {
			$row->columns = $columns;
			foreach($orderstatus_columns as $key => $column) {
				if(!empty($column['key']) && in_array($row->orderstatus_namekey, explode(',', $config->get($column['key'], $column['default']))))
					$row->columns[$key] = true;
				if(empty($column['key']) && in_array($row->orderstatus_namekey, explode(',', $column['default'])))
					$row->columns[$key] = true;
			}
		}
		unset($row);

		$this->getPagination();
		$this->getOrdering('o.orderstatus_ordering', true);

		hikashop_setTitle(JText::_($this->nameListing), $this->icon, $this->ctrl);

		$manage = array(
			'edit' => hikashop_isAllowed($config->get('acl_orderstatus_manage','all')),
		);
		$this->assignRef('manage', $manage['edit']);

		$this->toolbar = array(
			array('name' => 'addNew', 'display' => $manage['edit']),
			array('name' => 'editList', 'display' => $manage['edit']),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		hikashop_loadJslib('tooltip');
	}

	public function form() {
		$element_id = hikashop_getCID();

		$element = hikaInput::get()->getRaw('fail');
		if(empty($element)) {
			$orderstatusClass = hikashop_get('class.orderstatus');
			$element =  $orderstatusClass->get($element_id);
		}
		$this->assignRef('element', $element);

		$this->loadRef(array(
			'editor' => 'helper.editor',
			'joomlaAcl' => 'type.joomla_acl',
			'colorType' => 'type.color',
		));
		$this->editor->name = 'orderstatus_description';
		$this->editor->content = @$element->orderstatus_description;

		$title = JText::_($this->nameForm);
		if(!empty($element->orderstatus_name))
			$title .= ': '.$element->orderstatus_name;
		hikashop_setTitle($title, $this->icon, $this->ctrl);

		$this->toolbar = array(
			'save-group',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);
	}
}
