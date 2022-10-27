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
class characteristicmarketViewcharacteristicmarket extends hikamarketView {

	protected $ctrl = 'characteristic';
	protected $icon = 'characteristic';
	protected $triggerView = true;

	public function display($tpl = null, $params = array()) {
		$this->params =& $params;
		$fct = $this->getLayout();
		if(method_exists($this, $fct)) {
			if($this->$fct() === false)
				return;
		}
		parent::display($tpl);
	}

	public function listing($tpl = null) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.listing';

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$toggleClass = hikamarket::get('helper.toggle');
		$this->assignRef('toggleClass', $toggleClass);

		$manage = hikamarket::acl('characteristic/edit') || hikamarket::acl('characteristic/show');
		$this->assignRef('manage', $manage);

		$show_vendor = hikamarket::level(1);
		$this->assignRef('show_vendor', $show_vendor);

		$characteristic_action_delete = hikamarket::acl('characteristic/delete');
		$characteristic_actions = $characteristic_action_delete;
		$this->assignRef('characteristic_action_delete', $characteristic_action_delete);
		$this->assignRef('characteristic_actions', $characteristic_actions);

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->assignRef('Itemid', $Itemid);

		$cfg = array(
			'table' => 'shop.characteristic',
			'main_key' => 'characteristic_id',
			'order_sql_value' => 'characteristic.characteristic_id'
		);


		$pageInfo = $this->getPageInfo($cfg['order_sql_value']);
		$pageInfo->filter->vendors = $app->getUserStateFromRequest($this->paramBase.'.filter_vendors', 'filter_vendors', -1, 'int');

		$filters = array(
			'characteristic.characteristic_parent_id = 0'
		);
		$searchMap = array(
			'characteristic.characteristic_value',
			'characteristic.characteristic_alias',
			'characteristic.characteristic_id'
		);
		$order = '';

		if($vendor->vendor_id > 1) {
			$filters[] = 'characteristic.characteristic_vendor_id IN (0, ' . (int)$vendor->vendor_id.')';
		} else {
			$vendorType = hikamarket::get('type.filter_vendor');
			$this->assignRef('vendorType', $vendorType);
			if($pageInfo->filter->vendors >= 0) {
				if($pageInfo->filter->vendors > 1)
					$filters[] = 'characteristic.characteristic_vendor_id = '.(int)$pageInfo->filter->vendors;
				else
					$filters[] = 'characteristic.characteristic_vendor_id <= 1';
			}
		}

		$this->processFilters($filters, $order, $searchMap);

		$query = 'FROM '.hikamarket::table($cfg['table']).' AS characteristic '.$filters.$order;
		$db->setQuery('SELECT * '.$query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);

		$rows = $db->loadObjectList();
		$this->assignRef('characteristics', $rows);

		$db->setQuery('SELECT COUNT(*) '.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);

		if(!empty($rows)) {
			$characteristic_ids = array();
			$vendor_ids = array();

			foreach($rows as $row) {
				$characteristic_ids[] = (int)$row->characteristic_id;
				$vendor_ids[ (int)$row->characteristic_vendor_id ] = (int)$row->characteristic_vendor_id;
			}

			$vendors = array();
			if(!empty($vendor_ids)) {
				$query = 'SELECT vendor_id, vendor_name FROM ' . hikamarket::table('vendor') . ' WHERE vendor_id IN ('.implode(',', $vendor_ids).')';
				$db->setQuery($query);
				$vendors = $db->loadObjectList('vendor_id');
			}

			$filters = array(
				'characteristic_parent_id IN ('.implode(',', $characteristic_ids).')'
			);
			if($vendor->vendor_id > 1) {
				$filters[] = 'characteristic_vendor_id IN (0, ' . (int)$vendor->vendor_id.')';
			}
			$query = 'SELECT characteristic_parent_id, COUNT(*) as counter FROM ' . hikamarket::table('shop.characteristic') .
					' WHERE '.implode(' AND ', $filters).' GROUP BY characteristic_parent_id';
			$db->setQuery($query);
			$value_counter = $db->loadObjectList('characteristic_parent_id');

			$vendor_filter = '';
			$vendor_join = '';
			if($vendor->vendor_id > 1) {
				$vendor_filter = ' AND p.product_vendor_id = ' . (int)$vendor->vendor_id;
				$vendor_join = ' INNER JOIN ' . hikamarket::table('shop.product') . ' AS p ON v.variant_product_id = p.product_id ';
			}
			$query = 'SELECT v.variant_characteristic_id, COUNT(v.variant_product_id) as counter '.
					' FROM ' . hikamarket::table('shop.variant') . ' AS v ' . $vendor_join .
					' WHERE variant_characteristic_id IN ('.implode(',', $characteristic_ids).')' . $vendor_filter .
					' GROUP BY variant_characteristic_id';
			$db->setQuery($query);
			$used_counter = $db->loadObjectList('variant_characteristic_id');

			foreach($rows as &$row) {
				$i = (int)$row->characteristic_id;
				$row->vendor = '';
				if((int)$row->characteristic_vendor_id > 0 && isset($vendors[(int)$row->characteristic_vendor_id]))
					$row->vendor = $vendors[(int)$row->characteristic_vendor_id]->vendor_name;
				$row->counter = 0;
				$row->used = 0;
				if(isset($value_counter[$i]))
					$row->counter = (int)$value_counter[$i]->counter;
				if(isset($used_counter[$i]))
					$row->used = (int)$used_counter[$i]->counter;
			}
			unset($row);
		}

		$this->toolbar = array(
			array(
				'icon' => 'back',
				'fa' => 'fa-arrow-circle-left',
				'name' => JText::_('HIKA_BACK'), 'url' => hikamarket::completeLink('vendor')),
			array(
				'icon' => 'new',
				'fa' => 'fa-plus-circle',
				'name' => JText::_('HIKA_NEW'),
				'url' => hikamarket::completeLink('characteristic&task=add'),
				'pos' => 'right',
				'display' => hikamarket::acl('characteristic/add')
			)
		);

		$this->getPagination();
	}

	public function show() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.edit';

		if(HIKASHOP_J40)
			JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'left'));
		else
			JHTML::_('behavior.tooltip');

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$show_vendor = hikamarket::level(1);
		$this->assignRef('show_vendor', $show_vendor);

		$this->loadRef(array(
			'characteristicClass' => 'class.characteristic',
			'toggleClass' => 'helper.toggle',
			'nameboxType' => 'type.namebox',
			'characteristicdisplayType' => 'shop.type.characteristicdisplay'
		));

		$cid = hikamarket::getCID('characteristic_id');

		$characteristic = $this->characteristicClass->get($cid);
		$this->assignRef('characteristic', $characteristic);

		$characteristic_action_delete = hikamarket::acl('characteristic/values/delete');
		$this->assignRef('characteristic_action_delete', $characteristic_action_delete);
		$characteristic_actions = $characteristic_action_delete;
		$this->assignRef('characteristic_actions', $characteristic_actions);

		$characteristic_ordering = hikamarket::acl('characteristic/values/ordering');
		$this->assignRef('characteristic_ordering', $characteristic_ordering);

		$acl_edit_value = hikamarket::acl('characteristic/values/edit');
		$this->assignRef('acl_edit_value', $acl_edit_value);

		$multi_language = false;
		$this->assignRef('multi_language', $multi_language);

		$editable_characteristic = true;
		$editable_characteristic = (empty($characteristic) || $vendor->vendor_id <= 1 || $characteristic->characteristic_vendor_id == $vendor->vendor_id);
		$this->assignRef('editable_characteristic', $editable_characteristic);


		$used_counter = 0;
		if(!empty($characteristic)) {
			$vendor_filter = '';
			$vendor_join = '';
			if($vendor->vendor_id > 1) {
				$vendor_filter = ' AND p.product_vendor_id = ' . (int)$vendor->vendor_id;
				$vendor_join = ' INNER JOIN ' . hikamarket::table('shop.product') . ' AS p ON v.variant_product_id = p.product_id ';
			}
			$query = 'SELECT COUNT(v.variant_product_id) as counter '.
					' FROM ' . hikamarket::table('shop.variant') . ' AS v ' . $vendor_join .
					' WHERE variant_characteristic_id = ' . (int)$cid . $vendor_filter .
					' GROUP BY variant_characteristic_id';
			$db->setQuery($query);
			$used_counter = $db->loadResult('counter');
		} else
			$characteristic = new stdClass();
		$this->assignRef('used_counter', $used_counter);

		$rows = array();

		$pageInfo = $this->getPageInfo('characteristic.characteristic_id');
		$pageInfo->limit->start = 0;
		$pageInfo->limit->value = 500;

		$pageInfo->filter->vendors = $app->getUserStateFromRequest($this->paramBase.'.filter_vendors', 'filter_vendors', -1, 'int');

		$filters = array(
			'characteristic.characteristic_parent_id = ' . (int)$cid
		);
		$searchMap = array(
			'characteristic.characteristic_value',
			'characteristic.characteristic_alias',
			'characteristic.characteristic_id'
		);
		$order = '';

		if((int)$cid > 0) {
			if($vendor->vendor_id > 1) {
				$filters[] = 'characteristic.characteristic_vendor_id IN (0, ' . (int)$vendor->vendor_id . ')';
			} else {
				$vendorType = hikamarket::get('type.filter_vendor');
				$this->assignRef('vendorType', $vendorType);
				if($pageInfo->filter->vendors >= 0) {
					if($pageInfo->filter->vendors > 1)
						$filters[] = 'characteristic.characteristic_vendor_id = '.(int)$pageInfo->filter->vendors;
					else
						$filters[] = 'characteristic.characteristic_vendor_id <= 1';
				}
			}

			$this->processFilters($filters, $order, $searchMap);

			$query = 'FROM '.hikamarket::table('shop.characteristic').' AS characteristic '.$filters.$order;
			$db->setQuery('SELECT * '.$query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);

			$rows = $db->loadObjectList();

			$db->setQuery('SELECT COUNT(*) '.$query);
			$pageInfo->elements = new stdClass();
			$pageInfo->elements->total = $db->loadResult();
			$pageInfo->elements->page = count($rows);
		}

		$characteristic->values = $rows;


		if(!empty($rows)) {
			$characteristic_ids = array();
			$vendor_ids = array();

			foreach($rows as $row) {
				$characteristic_ids[] = (int)$row->characteristic_id;
				$vendor_ids[ (int)$row->characteristic_vendor_id ] = (int)$row->characteristic_vendor_id;
			}

			$vendors = array();
			if(!empty($vendor_ids)) {
				$query = 'SELECT vendor_id, vendor_name FROM ' . hikamarket::table('vendor') . ' WHERE vendor_id IN ('.implode(',', $vendor_ids).')';
				$db->setQuery($query);
				$vendors = $db->loadObjectList('vendor_id');
			}

			$vendor_filter = '';
			if($vendor->vendor_id > 1)
				$vendor_filter = ' AND p.product_vendor_id = ' . (int)$vendor->vendor_id;
			$query = 'SELECT v.variant_characteristic_id, COUNT(v.variant_product_id) as counter '.
					' FROM ' . hikamarket::table('shop.variant') . ' AS v INNER JOIN ' . hikamarket::table('shop.product') . ' AS p ON v.variant_product_id = p.product_id '.
					' WHERE variant_characteristic_id IN ('.implode(',', $characteristic_ids).') AND p.product_type = ' . $db->Quote('variant') . $vendor_filter .
					' GROUP BY variant_characteristic_id';
			$db->setQuery($query);
			$used_counter = $db->loadObjectList('variant_characteristic_id');

			foreach($rows as &$row) {
				$i = (int)$row->characteristic_id;
				$row->vendor = '';
				if(isset($vendors[$row->characteristic_vendor_id]))
					$row->vendor = $vendors[$row->characteristic_vendor_id]->vendor_name;
				$row->used = 0;
				if(isset($used_counter[$i]))
					$row->used = (int)$used_counter[$i]->counter;
			}
			unset($row);
		}

		$this->toolbar = array(
			'back' => array(
				'icon' => 'back',
				'fa' => 'fa-arrow-circle-left',
				'name' => JText::_('HIKA_BACK'),
				'url' => hikamarket::completeLink('characteristic')
			),
			'apply' => array(
				'url' => '#apply',
				'fa' => 'fa-check-circle',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'apply\',\'adminForm\');"',
				'icon' => 'apply',
				'name' => JText::_('HIKA_APPLY'), 'pos' => 'right',
				'display' => hikamarket::acl('characteristic/edit')
			),
			'save' => array(
				'url' => '#save',
				'fa' => 'fa-save',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'save\',\'adminForm\');"',
				'icon' => 'save',
				'name' => JText::_('HIKA_SAVE'), 'pos' => 'right',
				'display' => hikamarket::acl('characteristic/edit')
			)
		);
	}
}
