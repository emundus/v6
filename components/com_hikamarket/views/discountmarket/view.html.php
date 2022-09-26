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
class discountmarketViewdiscountmarket extends hikamarketView {

	protected $ctrl = 'discount';
	protected $icon = 'discount';

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

		$currencyClass = hikamarket::get('shop.class.currency');
		$this->assignRef('currencyClass', $currencyClass);

		$manage = hikamarket::acl('discount/edit') || hikamarket::acl('discount/show');
		$this->assignRef('manage', $manage);

		$discount_action_publish = hikamarket::acl('discount/edit/published');
		$discount_action_delete = hikamarket::acl('discount/delete');
		$discount_actions = $discount_action_publish || $discount_action_delete;
		$this->assignRef('discount_action_publish', $discount_action_publish);
		$this->assignRef('discount_action_delete', $discount_action_delete);
		$this->assignRef('discount_actions', $discount_actions);

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->assignRef('Itemid', $Itemid);

		$cfg = array(
			'table' => 'shop.discount',
			'main_key' => 'discount_id',
			'order_sql_value' => 'discount.discount_id'
		);
		$default_sort_dir = 'asc';

		$listing_filters = array(
			'vendors' => -1,
			'published' => -1,
			'type' => '',
		);

		$pageInfo = $this->getPageInfo($cfg['order_sql_value'], $default_sort_dir, $listing_filters);

		$filters = array();
		$searchMap = array(
			'discount.discount_code',
			'discount.discount_id'
		);
		$order = '';

		if($vendor->vendor_id > 1) {
			$filters[] = 'discount.discount_target_vendor = ' . (int)$vendor->vendor_id;
		} else {
			$vendorType = hikamarket::get('type.filter_vendor');
			$this->assignRef('vendorType', $vendorType);
			if($pageInfo->filter->vendors >= 0) {
				if($pageInfo->filter->vendors > 1)
					$filters[] = 'discount.discount_target_vendor = '.(int)$pageInfo->filter->vendors;
				else
					$filters[] = 'discount.discount_target_vendor <= 1';
			}
		}
		if(!empty($pageInfo->filter->type) && in_array($pageInfo->filter->type, array('discount', 'coupon'))) {
			$filters['discount_type'] = 'discount.discount_type = ' . $db->Quote($pageInfo->filter->type);
		}
		if($pageInfo->filter->published >= 0) {
			$filters['published'] = 'discount.discount_published = ' . ($pageInfo->filter->published ? '1' : '0');
		}

		$this->processFilters($filters, $order, $searchMap);

		$query = 'FROM '.hikamarket::table($cfg['table']).' AS discount '.$filters.$order;
		$db->setQuery('SELECT * '.$query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);

		$rows = $db->loadObjectList();
		$this->assignRef('discounts', $rows);

		$db->setQuery('SELECT COUNT(*) '.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);

		if($pageInfo->elements->page) {
			$productIds = array();
			$categoryIds = array();
			$zoneNamekeys = array();

			foreach($rows as $row) {
				if(!empty($row->discount_product_id)) {
					if(strpos($row->discount_product_id, ',') === false)
						$productIds[] = (int)$row->discount_product_id;
					else
						$productIds = array_merge($productIds, explode(',', trim($row->discount_product_id, ',')));
				}
				if(!empty($row->discount_category_id)) {
					if(strpos($row->discount_category_id, ',') === false)
						$categoryIds[] = (int)$row->discount_category_id;
					else
						$categoryIds = array_merge($categoryIds, explode(',', trim($row->discount_category_id, ',')));
				}
				if(!empty($row->discount_zone_id)) {
					if(strpos($row->discount_zone_id, ',') === false)
						$zoneNamekeys[] = $row->discount_zone_id;
					else
						$zoneNamekeys = explode(',', trim($row->discount_zone_id, ','));
				}
			}

			if(!empty($productIds)) {
				hikamarket::toInteger($productIds);
				$query = 'SELECT * FROM '.hikamarket::table('shop.product').' WHERE product_id IN ('.implode(',',$productIds).')';
				$db->setQuery($query);
				$products = $db->loadObjectList();
				foreach($rows as &$row) {
					if(empty($row->discount_product_id))
						continue;

					$pid = explode(',', trim($row->discount_product_id, ','));
					$row->product_name = array();
					foreach($products as $product) {
						if(in_array($product->product_id, $pid))
							$row->product_name[] = $product->product_name;
					}
					if(!empty($row->product_name)) {
						$row->product_name = implode(', ', $row->product_name);
					} else
						$row->product_name = JText::_('PRODUCT_NOT_FOUND');
				}
				unset($row);
			}

			if(!empty($categoryIds)) {
				hikamarket::toInteger($categoryIds);
				$query = 'SELECT * FROM '.hikamarket::table('shop.category').' WHERE category_id IN ('.implode(',',$categoryIds).')';
				$db->setQuery($query);
				$categories = $db->loadObjectList();
				foreach($rows as &$row){
					if(empty($row->discount_category_id))
						continue;

					$pid = explode(',', trim($row->discount_category_id, ','));
					$row->category_name = array();
					foreach($categories as $category) {
						if(in_array($category->category_id, $pid))
							$row->category_name[] = $category->category_name;
					}
					if(!empty($row->category_name)) {
						$row->category_name = implode(', ', $row->category_name);
					} else
						$row->category_name = JText::_('CATEGORY_NOT_FOUND');
				}
				unset($row);
			}

			if(!empty($zoneNamekeys)) {
				foreach($zoneNamekeys as &$zoneNamekey) {
					$zoneNamekey = $db->Quote($zoneNamekey);
				}
				unset($zoneNamekey);
				$query = 'SELECT * FROM '.hikamarket::table('shop.zone').' WHERE zone_namekey IN ('.implode(',',$zoneNamekeys).')';
				$db->setQuery($query);
				$zones = $db->loadObjectList();
				foreach($rows as &$row){
					if(empty($row->discount_zone_id))
						continue;

					$pid = explode(',', trim($row->discount_zone_id, ','));
					$row->zone_name_english = array();
					foreach($zones as $zone) {
						if(in_array($zone->zone_namekey, $pid))
							$row->zone_name_english[] = $zone->zone_name_english;
					}
					if(!empty($row->zone_name_english)) {
						$row->zone_name_english = implode(', ', $row->zone_name_english);
					} else
						$row->zone_name_english = JText::_('ZONE_NOT_FOUND');
				}
				unset($row);
			}
		}
		$text_asc = JText::_('ASCENDING');
		$text_desc = JText::_('DESCENDING');
		$ordering_values = array(
			'discount.discount_id' => JText::_('SORT_ID'),
			'discount.discount_code' => JText::_('SORT_NAME'),
			'discount.discount_type' => JText::_('SORT_TYPE'),
			'discount.discount_start' => JText::_('SORT_START_DATE'),
			'discount.discount_end' => JText::_('SORT_END_DATE'),
		);
		$this->ordering_values = array();
		foreach($ordering_values as $k => $v) {
			$this->ordering_values[$k.' asc'] = $v . ' ' .$text_asc;
			$this->ordering_values[$k.' desc'] = $v . ' ' .$text_desc;
		}
		$this->full_ordering = $this->pageInfo->filter->order->value . ' ' . strtolower($this->pageInfo->filter->order->dir);

		$this->toolbar = array(
			array(
				'icon' => 'back',
				'fa' => 'fa-arrow-circle-left',
				'name' => JText::_('HIKA_BACK'), 'url' => hikamarket::completeLink('vendor')
			),
			array(
				'icon' => 'new',
				'fa' => 'fa-plus-circle',
				'name' => JText::_('HIKA_NEW'),
				'url' => hikamarket::completeLink('discount&task=add'),
				'pos' => 'right',
				'display' => hikamarket::acl('discount/add')
			)
		);

		$this->getPagination();
	}

	public function show() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.edit';

		hikamarket::loadJslib('tooltip');

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$toggleClass = hikamarket::get('helper.toggle');
		$this->assignRef('toggleClass', $toggleClass);
		$popup = hikamarket::get('shop.helper.popup');
		$this->assignRef('popup', $popup);

		$discount_id = hikamarket::getCID('discount_id');
		$this->loadRef(array(
			'discountClass' => 'shop.class.discount',
			'productClass' => 'shop.class.product',
			'categoryClass' => 'shop.class.category',
			'currencyClass' => 'shop.class.currency',
			'vendorClass' => 'class.vendor',
			'categoryType' => 'type.shop_category',
			'nameboxType' => 'type.namebox',
			'currencyType' => 'shop.type.currency',
			'radioType' => 'shop.type.radio',
		));

		$main_currency = $shopConfig->get('main_currency',1);
		$this->assignRef('main_currency_id', $main_currency);

		$discount = $this->discountClass->get($discount_id);
		$this->assignRef('discount', $discount);

		if(hikashop_level(1)) {
			$rootCategory = $this->vendorClass->getRootCategory($vendor);
			$this->assignRef('rootCategory', $rootCategory);

			$this->loadRef(array(
				'categoryType' => 'type.shop_category',
				'productsType' => 'type.products'
			));
		}

		if(hikashop_level(2)) {
			hikamarket::loadJslib('otree');
			$joomlaAcl = hikamarket::get('type.joomla_acl');
			$this->assignRef('joomlaAcl', $joomlaAcl);
		}

		$this->toolbar = array(
			'back' => array(
				'icon' => 'back',
				'fa' => 'fa-arrow-circle-left',
				'name' => JText::_('HIKA_BACK'),
				'url' => hikamarket::completeLink('discount')
			),
			'apply' => array(
				'url' => '#apply',
				'fa' => 'fa-check-circle',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'apply\',\'hikamarket_discount_form\');"',
				'icon' => 'apply',
				'name' => JText::_('HIKA_APPLY'), 'pos' => 'right',
				'display' => hikamarket::acl('discount/edit')
			),
			'save' => array(
				'url' => '#save',
				'fa' => 'fa-save',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'save\',\'hikamarket_discount_form\');"',
				'icon' => 'save',
				'name' => JText::_('HIKA_SAVE'), 'pos' => 'right',
				'display' => hikamarket::acl('discount/edit')
			)
		);
	}
}
