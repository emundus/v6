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
class ordermarketViewordermarket extends HikamarketView {

	protected $ctrl = 'order';
	protected $icon = 'order';
	protected $triggerView = true;

	public function display($tpl = null, $params = array()) {
		$this->params =& $params;

		global $Itemid;
		$this->url_itemid = '';
		if(!empty($Itemid))
			$this->url_itemid = '&Itemid=' . $Itemid;

		$fct = $this->getLayout();
		if(method_exists($this, $fct) && $this->$fct($tpl) === false)
			return false;
		parent::display($tpl);
	}

	public function listing($tpl = null) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.listing';

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid='&Itemid='.$Itemid;
		$this->assignRef('Itemid', $Itemid);

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$config = hikamarket::config();
		$this->assignRef('config', $config);
		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$this->loadRef(array(
			'toggleClass' => 'helper.toggle',
			'paymentType' => 'type.paymentmethods',
			'orderStatusType' => 'type.order_status',
			'addressClass' => 'class.address',
			'currencyHelper' => 'shop.class.currency',
			'shopAddressClass' => 'shop.class.address',
			'fieldClass' => 'shop.class.field',
			'popup' => 'shop.helper.popup',
			'searchType' => 'type.search',
		));

		$filterType = $app->getUserStateFromRequest($this->paramBase.'.filter_type', 'filter_type', 0, 'int');

		$cfg = array(
			'table' => 'shop.order',
			'main_key' => 'order_id',
			'order_sql_value' => 'hkorder.order_id'
		);

		$pageInfo = $this->getPageInfo($cfg['order_sql_value'], 'desc');
		$pageInfo->filter->filter_status = $app->getUserStateFromRequest($this->paramBase.'.filter_status', 'filter_status', '', 'string');
		$pageInfo->filter->filter_payment = $app->getUserStateFromRequest($this->paramBase.'.filter_payment', 'filter_payment', '', 'string');
		$pageInfo->filter->filter_user = $app->getUserStateFromRequest($this->paramBase.'.filter_user', 'filter_user', '', 'string');

		$filters = array();
		$searchMap = array(
			'hkorder.order_id',
			'hkorder.order_number',
			'hkuser.user_email'
		);
		$orderingAccept = array('hkorder.','hkuser.');
		$order = '';

		$fields = array();
		if(hikashop_level(2)) {
			$null = null;
			$fields =  $this->fieldClass->getFields('display:vendor_order_listing=1', $null, 'order');
			foreach($fields as $field) {
				if($field->field_type == 'customtext')
					continue;
				$searchMap[] = 'hkorder.'.$field->field_namekey;
			}
		}
		$this->assignRef('fields', $fields);

		if(!empty($pageInfo->filter->filter_status))
			$filters['order_status'] = 'hkorder.order_status = ' . $db->Quote($pageInfo->filter->filter_status);
		if(!empty($pageInfo->filter->filter_payment))
			$filters['order_payment_id'] = 'hkorder.order_payment_id = ' . (int)$pageInfo->filter->filter_payment;
		if(!empty($pageInfo->filter->filter_user) && (int)$pageInfo->filter->filter_user > 0)
			$filters['order_user_id'] = 'hkorder.order_user_id = ' . (int)$pageInfo->filter->filter_user;

		if($vendor->vendor_id > 1) {
			$filters['order_vendor_id'] = 'hkorder.order_vendor_id = ' . $vendor->vendor_id;
			$filters['order_type'] = 'hkorder.order_type IN (' . $db->Quote('subsale') . ',' . $db->Quote('sale') . ')';
		} else {
			$filters['order_vendor_id'] = '(hkorder.order_vendor_id = 0 OR hkorder.order_vendor_id = 1)';
			$filters['order_type'] = 'hkorder.order_type = ' . $db->Quote('sale');
		}

		$extrafilters = array();
		$joins = array();
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikamarket');
		$app->triggerEvent('onBeforeOrderListing', array($this->paramBase, &$extrafilters, &$pageInfo, &$filters, &$joins, &$searchMap));
		$this->assignRef('extrafilters', $extrafilters);

		$this->processFilters($filters, $order, $searchMap, $orderingAccept);

		$query = 'FROM '.hikamarket::table($cfg['table']).' AS hkorder '.
			' LEFT JOIN '.hikamarket::table('shop.user').' AS hkuser ON hkorder.order_user_id = hkuser.user_id '.
			implode(' ', $joins).' '.$filters.$order;
		$db->setQuery('SELECT hkorder.*, hkuser.* '.$query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);

		if(empty($pageInfo->search)) {
			$query = 'FROM '.hikamarket::table($cfg['table']).' AS hkorder '.$filters;
		}

		$orders = $db->loadObjectList('order_id');

		$db->setQuery('SELECT COUNT(*) '.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($orders);

		$this->assignRef('orders', $orders);

		if(hikashop_level(2)) {
			$this->fieldClass->handleZoneListing($fields, $orders);
		}

		$this->display_shipping = (bool)$shopConfig->get('force_shipping', 1);

		$addresses = null;
		$address_fields = null;
		$payments = array();
		if(!empty($orders)) {
			$query = 'SELECT DISTINCT a.* '.
				' FROM ' . hikamarket::table('shop.address') . ' AS a '.
				' INNER JOIN ' . hikamarket::table('shop.order') . ' AS o ON (a.address_id = o.order_billing_address_id OR a.address_id = o.order_shipping_address_id) ' .
				' WHERE o.order_id IN (' . implode(',', array_keys($orders)) . ')';
			$db->setQuery($query);
			$addresses = $db->loadObjectList('address_id');

			$this->shopAddressClass->loadZone($addresses);

			$shopPluginClass = hikamarket::get('shop.class.plugins');
			$paymentMethods = $shopPluginClass->getMethods('payment');
			foreach($paymentMethods as $payment) {
				$payments[$payment->payment_id] = $payment;
			}

			foreach($orders as &$order) {
				$order->shipping_name = null;
				if(empty($order->order_shipping_method) && empty($order->order_shipping_id))
					continue;

				$this->display_shipping = true;

				if(!empty($order->order_shipping_method)) {
					if(!is_numeric($order->order_shipping_id))
						$order->shipping_name = $this->getShippingName($order->order_shipping_method, $order->order_shipping_id);
					else
						$order->shipping_name = $this->getShippingName(null, $order->order_shipping_id);
				} else {
					$order->shipping_name = array();
					$shipping_ids = explode(';', $order->order_shipping_id);
					foreach($shipping_ids as $shipping_id) {
						$order->shipping_name[] = $this->getShippingName(null, $shipping_id);
					}
					if(count($order->shipping_name) == 1)
						$order->shipping_name = reset($order->shipping_name);
				}
			}
			unset($order);
		}
		$this->assignRef('addresses', $addresses);
		$this->assignRef('address_fields', $address_fields);
		$this->assignRef('payments', $payments);

		$this->order_stats = null;
		if($config->get('display_order_statistics', 0)) {
			if($vendor->vendor_id > 1) {
				$query = 'SELECT o.order_status, COUNT(o.order_id) as `total` FROM '.hikamarket::table('shop.order').' AS o WHERE o.order_type = \'subsale\' AND o.order_vendor_id = '.(int)$vendor->vendor_id.' GROUP BY o.order_status';
			} else {
				$query = 'SELECT o.order_status, COUNT(o.order_id) as `total` FROM '.hikamarket::table('shop.order').' AS o WHERE o.order_type = \'sale\' GROUP BY o.order_status';
			}
			$db->setQuery($query);
			$this->order_stats = $db->loadObjectList('order_status');
			ksort($this->order_stats);
		}

		$text_asc = JText::_('ASCENDING');
		$text_desc = JText::_('DESCENDING');
		$ordering_values = array(
			'hkorder.order_id' => JText::_('SORT_ID'),
			'hkorder.order_invoice_id' => JText::_('SORT_INVOICE'),
			'hkorder.order_user_id' => JText::_('SORT_USER'),
			'hkorder.order_created' => JText::_('SORT_CREATION'),
			'hkorder.order_modified' => JText::_('SORT_MODIFICATION'),
			'hkorder.order_full_price' => JText::_('SORT_PRICE'),
		);
		$this->ordering_values = array();
		foreach($ordering_values as $k => $v) {
			$this->ordering_values[$k.' asc'] = $v . ' ' .$text_asc;
			$this->ordering_values[$k.' desc'] = $v . ' ' .$text_desc;
		}
		$this->full_ordering = $this->pageInfo->filter->order->value . ' ' . strtolower($this->pageInfo->filter->order->dir);

		$this->toolbar = array(
			'back' => array(
				'icon' => 'back',
				'fa' => 'fa-arrow-circle-left',
				'name' => JText::_('HIKA_BACK'),
				'url' => hikamarket::completeLink('vendor'.$this->url_itemid)
			),
			'report' => array(
				'icon' => 'report',
				'fa' => 'fa-bar-chart fa-chart-bar',
				'name' => JText::_('HIKA_EXPORT'),
				'url' => hikamarket::completeLink('order&task=export'.$this->url_itemid),
				'pos' => 'right',
				'acl' => hikamarket::acl('order/export')
			),
			'new' => array(
				'icon' => 'new',
				'fa' => 'fa-plus-circle',
				'name' => JText::_('HIKA_NEW'),
				'url' => hikamarket::completeLink('order&task=create'.$this->url_itemid),
				'pos' => 'right',
				'display' => ($vendor->vendor_id <= 1) || $config->get('vendor_edit_order', 0),
				'acl' => hikamarket::acl('order/add')
			),
			'request' => array(
				'icon' => 'pay',
				'fa' => 'fa-money fa-money-bill-alt',
				'name' => JText::_('HIKAM_PAYMENT_REQUEST'),
				'url' => hikamarket::completeLink('order&task=request'.$this->url_itemid),
				'pos' => 'right',
				'display' => ($vendor->vendor_id > 1),
				'acl' => hikamarket::acl('order/request')
			),
			'payments' => array(
				'icon' => 'pay',
				'fa' => 'fa-list-alt',
				'name' => JText::_('HIKAM_PAYMENT_LISTING'),
				'url' => hikamarket::completeLink('order&task=payments'.$this->url_itemid),
				'pos' => 'right',
				'display' => ($vendor->vendor_id > 1),
				'acl' => hikamarket::acl('order/payments')
			)
		);

		$pathway = $app->getPathway();
		$items = $pathway->getPathway();
		if(!count($items)) {
			$pathway->addItem(JText::_('VENDOR_ACCOUNT'), hikamarket::completeLink('vendor'.$this->url_itemid));
		}
		$pathway->addItem(JText::_('ORDERS'), hikamarket::completeLink('order'.$this->url_itemid));

		$this->getPagination();

		$this->getOrdering('hkorder.ordering', !$filterType);
	}

	public function show($tpl = null, $toolbar = true) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$order_id = hikamarket::getCID('order_id', true);

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$config = hikamarket::config();
		$this->assignRef('config', $config);
		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$edit = hikaInput::get()->getCmd('task','') == 'edit';
		$this->assignRef('edit', $edit);

		$address_mode = hikaInput::get()->getInt('address_mode', 0);
		$this->assignRef('address_mode', $address_mode);

		hikamarket::loadJslib('tooltip');

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid='&Itemid='.$Itemid;
		$this->assignRef('Itemid', $Itemid);

		$orderClass = hikamarket::get('shop.class.order');
		$order = $orderClass->loadFullOrder($order_id, true, false);
		if(!isset($order->hikamarket))
			$order->hikamarket = new stdClass();

		if(!empty($order) && $order->order_vendor_id != $vendor->vendor_id && ($vendor->vendor_id > 1 || ($order->order_vendor_id > 1 && !hikamarket::acl('order/show/vendors')))) {
			$order = null;
			$app->enqueueMessage(JText::_('ORDER_ACCESS_FORBIDDEN'));
			$app->redirect(hikamarket::completeLink('order'.$this->url_itemid));
			return false;
		}

		$editable_order = ($order->order_type === 'sale') && ($vendor->vendor_id <= 1);
		if($order->order_type === 'sale' && $vendor->vendor_id > 1 && (int)$order->order_vendor_id == $vendor->vendor_id && (int)$config->get('vendor_edit_order', 0) == 1) {
			$editable_order = hikamarket::isEditableOrder($order_id, $vendor->vendor_id);
		}
		$this->assignRef('editable_order', $editable_order);

		if(empty($order->customer)) {
			$userClass = hikamarket::get('shop.class.user');
			$order->customer = $userClass->get($order->order_user_id);
		}

		if($order->order_type == 'sale') {
			$filters = array(
				'type' => 'o.order_type IN (' . $db->Quote('subsale') . ', ' . $db->Quote('vendorrefund') . ')',
				'parent' => 'order_parent_id = ' . (int)$order->order_id
			);
			$query = 'SELECT o.*, v.* FROM ' . hikamarket::table('shop.order') . ' AS o ' .
				' LEFT JOIN ' . hikamarket::table('vendor') . ' AS v ON o.order_vendor_id = v.vendor_id '.
				' WHERE (' . implode(') AND (', $filters) . ') ' .
				' ORDER BY v.vendor_id ASC, o.order_id ASC';
			$db->setQuery($query);
			$order->hikamarket->children = $db->loadObjectList('order_id');

			$refunds = false;
			foreach($order->hikamarket->children as $d) {
				if($d->order_type !== 'vendorrefund') {
					$refunds = true;
					break;
				}
			}
			if($refunds) {
				foreach($order->hikamarket->children as &$d) {
					if($d->order_type !== 'subsale')
						continue;
					$m = false;
					$total = (float)hikamarket::toFloat($d->order_vendor_price);
					foreach($order->hikamarket->children as $o) {
						if($o->order_type == 'vendorrefund' && $o->order_vendor_id == $d->order_vendor_id) {
							$total += (float)hikamarket::toFloat($o->order_vendor_price);
							$m = true;
						}
					}
					if($m)
						$d->order_vendor_price_with_refunds = $total;
				}
				unset($d);
			}
		}

		if($order->order_type == 'subsale' && (int)$vendor->vendor_id <= 1) {
			$vendorClass = hikamarket::get('class.vendor');
			$order->hikamarket->vendor = $vendorClass->get( (int)$order->order_vendor_id );

			$query = 'SELECT order_number, order_invoice_number FROM ' . hikamarket::table('shop.order') . ' WHERE order_id = ' . (int)$order->order_parent_id;
			$db->setQuery($query);
			$order->hikamarket->parent = $db->loadObject();
		}

		if(!empty($order->products)) {
			$product_ids = array();

			foreach($order->products as &$product) {
				$product_ids[(int)$product->product_id] = (int)$product->product_id;

				$product->order_product_price = (float)hikamarket::toFloat($product->order_product_price);
				$product->order_product_tax = (float)hikamarket::toFloat($product->order_product_tax);

				$product->tax_rate = 0;
				if($product->order_product_tax > 0 && $product->order_product_price != 0)
					$product->tax_rate = $product->order_product_tax / $product->order_product_price;
			}
			unset($product);

			if($order->order_type == 'sale') {
				$query = 'SELECT hkop.*, hko.order_vendor_id, hmv.vendor_name, hmv.vendor_id '.
					' FROM ' . hikamarket::table('shop.order_product') . ' as hkop '.
					' INNER JOIN ' . hikamarket::table('shop.order'). ' AS hko ON hkop.order_id = hko.order_id '.
					' LEFT JOIN ' . hikamarket::table('vendor'). ' AS hmv ON hmv.vendor_id = hko.order_vendor_id '.
					' WHERE hko.order_type = \'subsale\' AND hko.order_parent_id = '. (int)$order->order_id .
					' ORDER BY hko.order_id DESC';
				$db->setQuery($query);
				$vendorProducts = $db->loadObjectList();

				foreach($order->products as &$product) {
					$product->vendor_data = array();
					foreach($vendorProducts as $vendorProduct) {
						if((int)$vendorProduct->order_product_parent_id == $product->order_product_id) {
							$product->vendor_data[] = $vendorProduct;
						}
					}
				}
				unset($product);

			} elseif($order->order_type == 'subsale') {
				$filters = array(
					'f.file_ref_id IN ('.implode(',', $product_ids).')',
					'f.file_type = '.$db->quote('file')
				);
				$query = 'SELECT d.*, f.* '.
					' FROM '.hikamarket::table('shop.file').' AS f '.
					' INNER JOIN '.hikamarket::table('shop.download').' AS d ON d.order_id = ' . (int)$order->order_parent_id . ' AND f.file_id = d.file_id '.
					' WHERE (' . implode(') AND (',$filters).')'.
					' ORDER BY f.file_ref_id ASC, f.file_ordering ASC, d.file_pos ASC';
				$db->setQuery($query);
				$files = $db->loadObjectList();

				foreach($files as $file) {
					foreach($order->products as &$product) {
						if(empty($product->files))
							continue;

						foreach($product->files as &$f) {
							if((int)$f->file_id != (int)$file->file_id)
								continue;
							$f->download_number = $file->download_number;
							$file->done = true;
							break;
						}

						if($file->done)
							break;
					}
					unset($product);
				}
			}
		}

		$this->assignRef('order', $order);

		$rootCategory = 0;
		$vendorCategories = 0;
		$vendorClass = hikamarket::get('class.vendor');
		$rootCategory = $vendorClass->getRootCategory($vendor);
		$extra_categories = $vendorClass->getExtraCategories($vendor);
		if(!empty($extra_categories))
			$vendorCategories = array_merge(array($rootCategory), $extra_categories);
		if(empty($rootCategory))
			$rootCategory = 1;
		if(empty($vendorCategories))
			$vendorCategories = $rootCategory;
		$this->assignRef('rootCategory', $rootCategory);
		$this->assignRef('vendorCategories', $vendorCategories);

		$this->loadRef(array(
			'toggleClass' => 'helper.toogle',
			'currencyHelper' => 'shop.class.currency',
			'payment' => 'shop.type.plugins',
			'shipping' => 'shop.type.plugins',
			'shippingClass' => 'shop.class.shipping',
			'paymentClass' => 'shop.class.payment',
			'fieldsClass' => 'shop.class.field',
			'addressClass' => 'class.address',
			'shopAddressClass' => 'shop.class.address',
			'popup' => 'shop.helper.popup',
			'order_status' => 'type.order_status',
			'imageHelper' => 'shop.helper.image',
			'dropdownHelper' => 'shop.helper.dropdown',
			'nameboxType' => 'type.namebox',
			'ratesType' => 'type.rates',
		));
		$this->payment->type = 'payment';
		$this->shipping->type = 'shipping';

		$fields = array();
		if(!empty($order_id)) {
			$order->order_discount_price = (float)hikamarket::toFloat(@$order->order_discount_price);
			$order->order_discount_tax = (float)hikamarket::toFloat(@$order->order_discount_tax);
			$order->order_discount_tax_rate = 0;
			if(empty($order->order_discount_tax_namekey))
				$order->order_discount_tax_namekey = '';

			$order->order_payment_price = (float)hikamarket::toFloat(@$order->order_payment_price);
			$order->order_payment_tax = (float)hikamarket::toFloat(@$order->order_payment_tax);
			$order->order_payment_tax_rate = 0;
			if(empty($order->order_payment_tax_namekey))
				$order->order_payment_tax_namekey = '';

			$order->order_shipping_price = (float)hikamarket::toFloat(@$order->order_shipping_price);
			$order->order_shipping_tax = (float)hikamarket::toFloat(@$order->order_shipping_tax);
			$order->order_shipping_tax_rate = 0;
			if(empty($order->order_shipping_tax_namekey))
				$order->order_shipping_tax_namekey = '';

			$order->currency = $this->currencyHelper->get( (int)$order->order_currency_id );

			$order->fields = $this->fieldsClass->getData('backend','address');
			if(hikashop_level(2)) {
				$fields['order'] = $this->fieldsClass->getFields('display:vendor_order_show=1', $order, 'order');
				$null = null;
				$fields['entry'] = $this->fieldsClass->getFields('display:vendor_order_show=1', $null, 'entry');
				$fields['item'] = $this->fieldsClass->getFields('display:vendor_order_show=1', $null, 'item');
			}

			$query = 'SELECT * FROM '.hikamarket::table('shop.history').' WHERE history_order_id = '.(int)$order_id.' ORDER BY history_created DESC';
			$db->setQuery($query);
			$order->history = $db->loadObjectList();

			if(!empty($order->order_tax_info)) {
				foreach($order->order_tax_info as $k => $v) {
					if(isset($v->tax_amount_for_coupon) && empty($order->order_discount_tax_namekey)) {
						$order->order_discount_tax_namekey = $k;
					}
					if(isset($v->tax_amount_for_payment) && empty($order->order_payment_tax_namekey)) {
						$order->order_payment_tax_namekey = $k;
					}
					if(isset($v->tax_amount_for_shipping) && empty($order->order_shipping_tax_namekey)) {
						$order->order_shipping_tax_namekey = $k;
					}
				}
			}
			if(!empty($order->order_discount_tax) && ($order->order_discount_price - $order->order_discount_tax) != 0)
				$order->order_discount_tax_rate = $order->order_discount_tax / ($order->order_discount_price - $order->order_discount_tax);
			if(!empty($order->order_payment_tax) && ($order->order_payment_price - $order->order_payment_tax) != 0)
				$order->order_payment_tax_rate = $order->order_payment_tax / ($order->order_payment_price - $order->order_payment_tax);
			if(!empty($order->order_shipping_tax) && ($order->order_shipping_price - $order->order_shipping_tax) != 0)
				$order->order_shipping_tax_rate = $order->order_shipping_tax / ($order->order_shipping_price - $order->order_shipping_tax);

			if(!empty($order->order_payment_id)) {
				if($order->order_type == 'subsale' && substr($order->order_payment_method, 0, 7) == 'market-')
					$order->order_payment_method = substr($order->order_payment_method, 7);

				$order->payment_name = $order->order_payment_method . ' - ' . $order->order_payment_id;

				$paymentMethod = $this->paymentClass->get( (int)$order->order_payment_id );
				if(!empty($paymentMethod->payment_name))
					$order->payment_name = $paymentMethod->payment_name;
			}

			$order->shipping_name = null;
			if(!empty($order->order_shipping_method)) {
				if($order->order_type == 'subsale' && substr($order->order_shipping_method, 0, 7) == 'market-')
					$order->order_shipping_method = substr($order->order_shipping_method, 7);

				if(!is_numeric($order->order_shipping_id))
					$order->shipping_name = $this->getShippingName($order->order_shipping_method, $order->order_shipping_id);
				else
					$order->shipping_name = $this->getShippingName(null, $order->order_shipping_id);
			} else if(!empty($order->order_shipping_id)) {
				$order->shipping_name = array();
				$shipping_ids = explode(';', $order->order_shipping_id);
				foreach($shipping_ids as $shipping_id) {
					$order->shipping_name[] = $this->getShippingName(null, $shipping_id);
				}
				if(count($order->shipping_name) == 1)
					$order->shipping_name = reset($order->shipping_name);
			}

			if(!empty($order->order_vendor_params) && is_string($order->order_vendor_params))
				$order->order_vendor_params = hikamarket::unserialize($order->order_vendor_params);

			if((int)$order->order_vendor_paid > 0) {
				$query = 'SELECT * '.
					' FROM ' . hikamarket::table('shop.order') .
					' WHERE order_parent_id = ' . $order->order_parent_id . ' AND order_type = ' . $db->Quote('vendorrefund');
				$db->setQuery($query);
				$order->refunds = $db->loadObjectList();

				foreach($order->refunds as $refund) {
					if($refund->order_vendor_paid == 0)
						$order->current_order_status = $refund->order_status;
				}
			}

			$query = 'SELECT DISTINCT a.* '.
				' FROM ' . hikamarket::table('shop.address') . ' AS a '.
				' INNER JOIN ' . hikamarket::table('shop.order') . ' AS o ON (a.address_id = o.order_billing_address_id OR a.address_id = o.order_shipping_address_id) ' .
				' WHERE o.order_id = '.(int)$order->order_id;
			$db->setQuery($query);
			$addresses = $db->loadObjectList('address_id');
			$this->assignRef('addresses', $addresses);
			$address_fields = null;
			$this->assignRef('address_fields', $address_fields);

			$this->shopAddressClass->loadZone($addresses);

			$bundles = array();
			foreach($order->products as $p) {
				if(empty($p->bundle))
					continue;
				foreach($p->bundle as $b) {
					$bundles[(int)$b->product_id] = (int)$b->product_id;
				}
			}
			if(!empty($bundles)) {
				$filters = array(
					'a.file_ref_id IN ('.implode(',', $bundles).')',
					'a.file_type = \'product\''
				);
				$query = 'SELECT a.* FROM '.hikamarket::table('shop.file').' AS a WHERE '.implode(' AND ',$filters).' ORDER BY file_ref_id ASC, file_ordering ASC';
				$db->setQuery($query);
				$images = $db->loadObjectList();
				if(!empty($images)) {
					foreach($order->products as &$p) {
						if(empty($p->bundle))
							continue;
						foreach($p->bundle as &$b) {
							foreach($images as $image) {
								if($b->product_id != $image->file_ref_id)
									continue;
								if(empty($b->images))
									$b->images = array();
								$b->images[] = $image;
							}
						}
						unset($b);
					}
					unset($p);
				}
			}
		}
		$this->assignRef('fields',$fields);

		if(empty($order->order_shipping_method) && !empty($order->shippings)) {
			$this->loadRef(array(
				'warehouseClass' => 'class.warehouse'
			));

			$order->shipping_data = array();
			$shipping_ids = explode(';', $order->order_shipping_id);
			$order->warehouses = array();
			foreach($shipping_ids as $key) {
				$shipping_data = '';
				list($k, $w) = explode('@', $key);
				$shipping_id = $k;
				if(isset($order->shippings[$shipping_id])) {
					$shipping = $order->shippings[$shipping_id];
					$shipping_data = $shipping->shipping_name;
				}
				if(empty($shipping_data))
					$shipping_data = $this->getShippingName(null, $shipping_id, false);

				if(empty($shipping_data)) {
					foreach($order->products as $order_product) {
						if($order_product->order_product_shipping_id != $key)
							continue;

						if(!is_numeric($order_product->order_product_shipping_id)) {
							$shipping_data = $this->getShippingName($order_product->order_product_shipping_method, $shipping_id);
						} else {
							$shipping_method_data = $this->shippingClass->get($shipping_id);
							$shipping_data = $shipping_method_data->shipping_name;
						}
						break;
					}
				}
				if(empty($shipping_data))
					$shipping_data = '[ ' . $key . ' ]';
				if(isset($order->order_shipping_params->prices[$key])) {
					$price_params = $order->order_shipping_params->prices[$key];
					if($this->shopConfig->get('price_with_tax'))
						$shipping_data .= ' (' . $this->currencyHelper->format($price_params->price_with_tax, $order->order_currency_id) . ')';
					else
						$shipping_data .= ' (' . $this->currencyHelper->format($price_params->price_with_tax - @$price_params->tax, $order->order_currency_id) . ')';
				}
				$order->shipping_data[] = $shipping_data;

				$order->warehouses[$key] = $this->warehouseClass->get($key);
			}
		}

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikamarket');
		JPluginHelper::importPlugin('hikashoppayment');
		JPluginHelper::importPlugin('hikashopshipping');
		$app->triggerEvent('onMarketOrderEditionLoading', array(&$order) );
		$app->triggerEvent('onHistoryDisplay', array(&$order->history) );

		if(class_exists('JDispatcher'))
			$this->dispatcher = JDispatcher::getInstance();

		if($toolbar) {
			hikamarket::setPageTitle(JText::sprintf('HIKAM_ORDER', $order->order_number));

			$this->toolbar = array(
				'back' => array(
					'icon' => 'back',
					'fa' => 'fa-arrow-circle-left',
					'name' => JText::_('HIKA_BACK'), 'url' => hikamarket::completeLink('order'.$this->url_itemid)
				),
				'order-status' => array(
					'icon' => 'order-status',
					'fa' => 'fa-tasks',
					'name' => JText::_('HIKAM_EDIT_ORDER_STATUS'),
					'url' => hikamarket::completeLink('order&task=status&cid='.(int)$order->order_id.$this->url_itemid, true),
					'popup' => array('id' => 'hikamarket_order_status_popup', 'width' => 640, 'height' => 300),
					'linkattribs' => ' onclick="if(window.orderMgr.editOrderStatus) return window.orderMgr.editOrderStatus(this); window.hikashop.openBox(this); return false;"',
					'pos' => 'right',
					'acl' => hikamarket::acl('order/edit/general')
				),
				'email' => array(
					'icon' => 'email',
					'fa' => 'fa-envelope',
					'name' => JText::_('HIKA_EMAIL'),
					'url' => hikamarket::completeLink('order&task=mail&cid='.(int)$order->order_id.$this->url_itemid, true),
					'popup' => array('id' => 'hikamarket_order_mail_popup', 'width' => 800, 'height' => 600),
					'pos' => 'right',
					'acl' => hikamarket::acl('order/edit/mail')
				),
				'invoice' => array(
					'icon' => 'invoice',
					'fa' => 'fa-book',
					'name' => JText::_('INVOICE'),
					'url' => hikamarket::completeLink('order&task=invoice&type=full&cid='.(int)$order->order_id.$this->url_itemid, true),
					'popup' => array('id' => 'hikamarket_order_invoice_popup', 'width' => 640, 'height' => 480),
					'pos' => 'right',
					'acl' => hikamarket::acl('order/show/invoice')
				),
				'shipping-invoice' => array(
					'icon' => 'shipping-invoice',
					'fa' => 'fa-truck fa-shipping-fast',
					'name' => JText::_('SHIPPING_INVOICE'),
					'url' => hikamarket::completeLink('order&task=invoice&type=shipping&cid='.(int)$order->order_id.$this->url_itemid, true),
					'popup' => array('id' => 'hikamarket_order_shippinginvoice_popup', 'width' => 640, 'height' => 480),
					'pos' => 'right',
					'acl' => hikamarket::acl('order/show/shippinginvoice')
				)
			);

			$pathway = $app->getPathway();
			$items = $pathway->getPathway();
			if(!count($items)) {
				$pathway->addItem(JText::_('VENDOR_ACCOUNT'), hikamarket::completeLink('vendor'.$this->url_itemid));
			}
			$pathway->addItem(JText::_('ORDERS'), hikamarket::completeLink('order&task=listing'.$this->url_itemid));

			$pathway->addItem(JText::sprintf('HIKAM_ORDER', $order->order_number), hikamarket::completeLink('order&task=show&cid='.$order->order_id.$this->url_itemid));
		}
	}

	public function show_vendor($tpl = null) {
		$this->show($tpl, true);
	}

	public function invoice() {
		$app = JFactory::getApplication();

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$order_id = hikamarket::getCID('order_id');

		$type = hikaInput::get()->getWord('type');
		$this->assignRef('invoice_type', $type);

		$nobutton = true;
		$this->assignRef('nobutton', $nobutton);

		$display_type = 'frontcomp';
		$this->assignRef('display_type', $display_type);

		$currencyClass = hikamarket::get('shop.class.currency');
		$this->assignRef('currencyHelper', $currencyClass);

		$orderClass = hikamarket::get('shop.class.order');
		$order = $orderClass->loadFullOrder($order_id, true, false);
		if(!empty($order) && $order->order_vendor_id != $vendor->vendor_id && ($vendor->vendor_id > 1 || ($order->order_vendor_id > 1 && !hikamarket::acl('order/show/vendors')))) {
			$order = null;
			$app->enqueueMessage(JText::_('ORDER_ACCESS_FORBIDDEN'));
			$app->redirect(hikamarket::completeLink('order'.$this->url_itemid));
			return false;
		}

		$fieldsClass = hikamarket::get('shop.class.field');
		$this->assignRef('fieldsClass', $fieldsClass);

		$fields = array();
		if(hikashop_level(2)) {
			$null = null;
			if($this->invoice_type == 'shipping') {
				$fields['item'] = $fieldsClass->getFields('display:vendor_order_shipping_invoice=1', $null, 'item');
				$fields['order'] = $fieldsClass->getFields('display:vendor_order_shipping_invoice=1', $null, 'order');
			} else {
				$fields['item'] = $fieldsClass->getFields('display:vendor_order_invoice=1', $null, 'item');
				$fields['order'] = $fieldsClass->getFields('display:vendor_order_invoice=1', $null, 'order');
			}
		}

		$vendorFields = $vendor;
		$extraFields = array(
			'vendor' => $fieldsClass->getFields('frontcomp', $vendorFields, 'plg.hikamarket.vendor')
		);
		$this->assignRef('extraFields', $extraFields);
		$this->assignRef('vendorFields', $vendorFields);

		$store = str_replace(
			array("\r\n","\n","\r"),
			array('<br/>','<br/>','<br/>'),
			$shopConfig->get('store_address','')
		);
		$this->assignRef('store_address', $store);
		$this->assignRef('element', $order);
		$this->assignRef('order', $order);
		$this->assignRef('fields', $fields);

		if(substr($order->order_shipping_method, 0, 7) == 'market-')
			$order->order_shipping_method = substr($order->order_shipping_method, 7);
		if(substr($order->order_payment_method, 0, 7) == 'market-')
			$order->order_payment_method = substr($order->order_payment_method, 7);

		if(!empty($order->order_payment_id)) {
			$pluginsPayment = hikamarket::get('shop.type.plugins');
			$pluginsPayment->type = 'payment';
			$this->assignRef('payment', $pluginsPayment);
		}
		if(!empty($order->order_shipping_id)) {
			$pluginsShipping = hikamarket::get('shop.type.plugins');
			$pluginsShipping->type = 'shipping';
			$this->assignRef('shipping', $pluginsShipping);

			if(empty($order->order_shipping_method)) {
				$shippingClass = hikamarket::get('shop.class.shipping');
				$this->assignRef('shippingClass', $shippingClass);

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
	}

	public function status() {
		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$this->loadRef(array(
			'orderClass' => 'shop.class.order',
			'order_status' => 'type.order_status',
		));

		$order_id = hikamarket::getCID('order_id');
		$order = $this->orderClass->loadFullOrder($order_id, true, false);
		$this->assignRef('order', $order);

		$order_status_filters = array();
		if($order->order_type == 'subsale' && (int)$order->order_vendor_paid > 0 && $config->get('filter_orderstatus_paid_order', 1)) {
			$valid_order_statuses = explode(',', $config->get('valid_order_statuses', 'confirmed,shipped'));
			if(in_array($order->order_status, $valid_order_statuses)) {
				$order_status_filters = $valid_order_statuses;
			} else {
				$order_status_filters = array($order->order_status);
			}
		}
		$this->assignRef('order_status_filters', $order_status_filters);

		$this->toolbar = array(
			'back' => array(
				'icon' => 'back',
				'fa' => 'fa-arrow-circle-left',
				'name' => JText::_('HIKA_CANCEL'),
				'url' => '#cancel',
				'linkattribs' => 'onclick="return window.parent.hikamarket.closeBox();"',
			),
			'save' => array(
				'icon' => 'apply',
				'fa' => 'fa-check-circle',
				'url' => '#apply',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'save\',\'adminForm\');"',
				'name' => JText::_('HIKA_OK'), 'pos' => 'right'
			)
		);
	}

	public function showblock($tpl = null) {
		$block = hikaInput::get()->getString('block', null);
		$blocks = array(
			'customer', 'edit_customer', 'details', 'general', 'history', 'products',
			'coupon', 'edit_coupon', 'payment', 'edit_payment', 'shipping', 'edit_shipping', 'fields', 'edit_fields',
			'product', 'edit_product', 'billingaddress', 'edit_billingaddress', 'shippingaddress', 'edit_shippingaddress',
			'vendors',
		);
		if(!in_array($block, $blocks))
			return false;

		$translate_edit = array('coupon' => 'edit_coupon', 'payment' => 'edit_payment', 'shipping' => 'edit_shipping');
		if(isset($translate_edit[$block]) && hikaInput::get()->getInt('blocksubmitted', 0) === 1)
			$block = $translate_edit[$block];

		$addresses_blocks = array(
			'show' => array('billingaddress' => 'billing', 'shippingaddress' => 'shipping'),
			'edit' => array('edit_billingaddress' => 'billing', 'edit_shippingaddress' => 'shipping'),
		);
		if(isset($addresses_blocks['show'][ $block ])) {
			$this->block_show_address = $addresses_blocks['show'][ $block ];
			$block = 'address';
		}
		if(isset($addresses_blocks['edit'][ $block ])) {
			$this->block_edit_address = $addresses_blocks['edit'][ $block ];
			$block = 'edit_address';

			$this->edit_address_mode = hikaInput::get()->getCmd('address_mode', '');
		}

		$this->show($tpl, false);

		$this->ajax = true;

		if(in_array($block, array('edit_product', 'product'))) {
			$this->product = null;
			$this->pid = hikaInput::get()->getInt('pid', 0);
			if($this->pid == 0 && !hikamarket::acl('order/edit/products'))
				return false;

			foreach($this->order->products as $k => $v) {
				if((int)$v->order_product_id == $this->pid)
					$this->product = $v;
			}
			if($this->pid > 0 && empty($this->product))
				return false;
		}

		if($block == 'edit_address') {
			if($this->block_edit_address == 'billing') {
				if(!empty($this->order->order_billing_address_id))
					$this->order->billing_address = $this->shopAddressClass->get($this->order->order_billing_address_id);
				$f = (isset($this->order->billing_fields) ? $this->order->billing_fields : $this->order->fields);
				$this->fieldsClass->prepareFields($f, $this->order->billing_address, 'address', 'checkout&task=state');
			}
			if($this->block_edit_address == 'shipping') {
				if(!empty($this->order->order_shipping_address_id))
					$this->order->shipping_address = $this->shopAddressClass->get($this->order->order_shipping_address_id);
				$f = (isset($this->order->shipping_fields) ? $this->order->shipping_fields : $this->order->fields);
				$this->fieldsClass->prepareFields($f, $this->order->shipping_address, 'address', 'checkout&task=state');
			}
		}

		$this->setLayout('show_block_' . $block);
		echo $this->loadTemplate();

		$orderClass = hikamarket::get('class.order');
		$events = $orderClass->getEvents();
		if(!empty($events)) {
			echo "\r\n".'<script type="text/javascript">'."\r\n";
			foreach($events as $k => $v) {
				echo 'window.Oby.fireAjax("'.$k.'", '.json_encode($v).');' . "\r\n";
			}
			echo '</script>';
		}

		return false;
	}

	public function history() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$order_id = hikaInput::get()->getInt('order_id', 0);
		$cid = hikamarket::getCID('cid');

		$query = 'SELECT * FROM ' . hikamarket::table('shop.history') . ' WHERE history_id = ' . (int)$cid . ' AND history_order_id = ' . (int)$order_id;
		$db->setQuery($query, 0, 1);
		$history = $db->loadObject();
		$this->assignRef('history', $history);

		if(empty($history))
			return;

		$histories = array( &$history );
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikamarket');
		JPluginHelper::importPlugin('hikashoppayment');
		JPluginHelper::importPlugin('hikashopshipping');
		$app->triggerEvent('onHistoryDisplay', array( &$histories ));

		$history->user = null;

		if(!empty($history->history_user_id) && (int)$history->history_user_id > 0) {
			$userClass = hikamarket::get('shop.class.user');
			$history->user = $userClass->get( (int)$history->history_user_id );
			unset($history->user->password);
		}

		if(!empty($history->history_data) && substr($history->history_data, 0, 1) == '{') {
			$d = json_decode($history->history_data, true);
			if(!empty($d))
				$history->history_data = $d;
		}
	}

	public function export_show() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.listing';

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid='&Itemid='.$Itemid;
		$this->assignRef('Itemid', $Itemid);

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$this->loadRef(array(
			'toggleClass' => 'helper.toggle',
			'currencyHelper' => 'shop.class.currency',
			'paymentType' => 'shop.type.payment',
			'orderStatusType' => 'type.order_status'
		));

		$pageInfo = new stdClass();
		$pageInfo->search = HikaStringHelper::strtolower($app->getUserStateFromRequest($this->paramBase.'.search', 'search', '', 'string'));
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->filter_status = $app->getUserStateFromRequest($this->paramBase.'.filter_status', 'filter_status', '', 'string');
		$pageInfo->filter->filter_payment = $app->getUserStateFromRequest($this->paramBase.'.filter_payment', 'filter_payment', '', 'string');
		$pageInfo->filter->filter_startdate = $app->getUserStateFromRequest($this->paramBase.'.filter_startdate', 'filter_startdate', '', 'string');
		$pageInfo->filter->filter_enddate = $app->getUserStateFromRequest($this->paramBase.'.filter_enddate', 'filter_enddate', '', 'string');
		$this->assignRef('pageInfo', $pageInfo);

		$this->toolbar = array(
			array(
				'icon' => 'back',
				'fa' => 'fa-arrow-circle-left',
				'name' => JText::_('HIKA_BACK'),
				'url' => hikamarket::completeLink('order'.$this->url_itemid)
			),
			array(
				'url' => '#export',
				'fa' => 'fa-file-export',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'export\',\'hikamarket_order_export_form\');"',
				'icon' => 'report',
				'name' => JText::_('HIKA_EXPORT'), 'pos' => 'right'
			)
		);
	}

	public function export() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.listing';

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid='&Itemid='.$Itemid;
		$this->assignRef('Itemid', $Itemid);

		$this->loadRef(array(
			'export' => 'shop.helper.spreadsheet'
		));

		$pageInfo = new stdClass();
		$pageInfo->search = HikaStringHelper::strtolower($app->getUserStateFromRequest($this->paramBase.'.search', 'search', '', 'string'));
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->filter_status = $app->getUserStateFromRequest($this->paramBase.'.filter_status', 'filter_status', '', 'string');
		$pageInfo->filter->filter_payment = $app->getUserStateFromRequest($this->paramBase.'.filter_payment', 'filter_payment', '', 'string');
		$pageInfo->filter->filter_startdate = $app->getUserStateFromRequest($this->paramBase.'.filter_startdate', 'filter_startdate', '', 'string');
		$pageInfo->filter->filter_enddate = $app->getUserStateFromRequest($this->paramBase.'.filter_enddate', 'filter_enddate', '', 'string');
		$this->assignRef('pageInfo', $pageInfo);

		$formData = hikaInput::get()->get('data', array(), 'array');
		$export_format = strtolower(@$formData['export']['format']);
		if(empty($export_format) || !in_array($export_format, array('csv', 'xls'))) {
			$export_format = 'csv';
		}
		$this->assignRef('export_format', $export_format);

		$cfg = array(
			'table' => 'shop.order',
			'main_key' => 'order_id',
			'order_sql_value' => 'hkorder.order_id'
		);

		$filters = array();
		$searchMap = array(
			'hkorder.order_id',
			'hkorder.order_user_id',
			'hkorder.order_full_price',
			'hkorder.order_number',
			'hkuser.user_email',
			'juser.username',
			'juser.name'
		);
		$orderingAccept = array('hkorder.','hkuser.');
		$order = ' ORDER BY hkorder.order_id';

		if(!empty($pageInfo->filter->filter_status))
			$filters['order_status'] = 'hkorder.order_status = ' . $db->Quote($pageInfo->filter->filter_status);

		if(!empty($pageInfo->filter->filter_payment))
			$filters['order_payment_method'] = 'hkorder.order_payment_id = ' . $db->Quote($pageInfo->filter->filter_payment);

		if($vendor->vendor_id > 1) {
			$filters['vendor_id'] = 'hkorder.order_vendor_id = ' . $vendor->vendor_id;
			$filters['order_type'] = 'hkorder.order_type = ' . $db->Quote('subsale');
		} else {
			$filters['vendor_id'] = '(hkorder.order_vendor_id = 0 OR hkorder.order_vendor_id = 1)';
			$filters['order_type'] = 'hkorder.order_type = ' . $db->Quote('sale');
		}

		if(!empty($pageInfo->filter->filter_enddate)) {
			$filter_end = explode('-', $pageInfo->filter->filter_enddate);
			$noHourDay = explode(' ', $filter_end[2]);
			$filter_end[2] = $noHourDay[0];
			$filter_end = mktime(23, 59, 59, $filter_end[1], $filter_end[2], $filter_end[0]);
		}

		if(!empty($pageInfo->filter->filter_startdate)) {
			$filter_start = explode('-',$pageInfo->filter->filter_startdate);
			$noHourDay = explode(' ',$filter_start[2]);
			$filter_start[2] = $noHourDay[0];
			$filter_start = mktime(0, 0, 0, $filter_start[1], $filter_start[2], $filter_start[0]);

			if(!empty($pageInfo->filter->filter_enddate)) {
				$filters['order_created'] = 'hkorder.order_created > '.$filter_start. ' AND hkorder.order_created < '.$filter_end;
			} else {
				$filters['order_created'] = 'hkorder.order_created > '.$filter_start;
			}
		} else if(!empty($pageInfo->filter->filter_enddate)) {
			$filters['order_created'] = 'hkorder.order_created < '.$filter_end;
		}

		$select = '';
		$from = '';

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikamarket');
		$app->triggerEvent('onBeforeOrderExportQuery', array(&$select, &$from, &$filters, &$order, &$searchMap, &$orderingAccept) );

		$this->processFilters($filters, $order, $searchMap, $orderingAccept);

		$query = 'FROM '.hikamarket::table($cfg['table']).' AS hkorder '.
			'LEFT JOIN '.hikamarket::table('shop.user').' AS hkuser ON hkorder.order_user_id = hkuser.user_id '.
			'LEFT JOIN '.hikamarket::table('joomla.users').' AS juser ON hkuser.user_cms_id = juser.id '.
			$from.' '.$filters.' '.$order;
		if(!empty($select) && substr($select, 0, 1) != ',')
			$select = ','.$select;
		$db->setQuery('SELECT hkorder.*, hkuser.*, juser.name, juser.username '.$select.$query);

		$rows = $db->loadObjectList('order_id');
		if(empty($rows)) {
			$app->enqueueMessage(JText::_('HIKAM_NOTHING_TO_EXPORT'), 'error');
			$app->redirect(hikamarket::completeLink('order&task=export'.$url_itemid, false, true));
			return false;
		}


		$addressIds = array();

		foreach($rows as &$row) {
			$row->products = array();
			$addressIds[$row->order_shipping_address_id] = $row->order_shipping_address_id;
			$addressIds[$row->order_billing_address_id] = $row->order_billing_address_id;
		}
		unset($row);

		if(!empty($addressIds)) {
			$db->setQuery('SELECT * FROM '.hikamarket::table('shop.address').' WHERE address_id IN ('.implode(',',$addressIds).')');
			$addresses = $db->loadObjectList('address_id');

			if(!empty($addresses)) {
				$zoneNamekeys = array();
				foreach($addresses as $address) {
					$zoneNamekeys[$address->address_country] = $db->Quote($address->address_country);
					$zoneNamekeys[$address->address_state] = $db->Quote($address->address_state);
				}

				if(!empty($zoneNamekeys)) {
					$db->setQuery('SELECT zone_namekey,zone_name FROM '.hikamarket::table('shop.zone').' WHERE zone_namekey IN ('.implode(',',$zoneNamekeys).')');
					$zones = $db->loadObjectList('zone_namekey');
					if(!empty($zones)) {
						foreach($addresses as &$address) {
							if(!empty($zones[$address->address_country]))
								$address->address_country = $zones[$address->address_country]->zone_name;
							if(!empty($zones[$address->address_state]))
								$address->address_state = $zones[$address->address_state]->zone_name;
						}
						unset($address);
					}
				}

				$fields = array_keys(get_object_vars(reset($addresses)));
				foreach($rows as $k => $row) {
					if(!empty($addresses[$row->order_shipping_address_id])) {
						foreach($addresses[$row->order_shipping_address_id] as $key => $val) {
							$key = 'shipping_'.$key;
							$rows[$k]->$key = $val;
						}
					} else {
						foreach($fields as $field){
							$key = 'shipping_'.$field;
							$rows[$k]->$key = '';
						}
					}

					if(!empty($addresses[$row->order_billing_address_id])) {
						foreach($addresses[$row->order_billing_address_id] as $key => $val) {
							$key = 'billing_'.$key;
							$rows[$k]->$key = $val;
						}
					} else {
						foreach($fields as $field) {
							$key = 'billing_'.$field;
							$rows[$k]->$key = '';
						}
					}
				}
			}
		}

		$orderIds = array_keys($rows);
		$db->setQuery('SELECT * FROM '.hikamarket::table('shop.order_product').' WHERE order_id IN ('.implode(',', $orderIds).')');
		$products = $db->loadObjectList();

		foreach($products as $product) {
			$order =& $rows[$product->order_id];
			$order->products[] = $product;
			if(!isset($order->order_full_tax)) {
				$order->order_full_tax = 0;
			}
			$order->order_full_tax += round($product->order_product_quantity * $product->order_product_tax, 2);
		}
		foreach($rows as $k => $row) {
			$rows[$k]->order_full_tax += $row->order_shipping_tax - $row->order_discount_tax;
		}

		$view =& $this;
		$app->triggerEvent('onBeforeOrderExport', array(&$rows, &$view) );
		$this->assignRef('orders', $rows);
	}

	public function create() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.create';

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->assignRef('Itemid', $Itemid);

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$config = hikamarket::config();
		$this->assignRef('config', $config);
		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$this->loadRef(array(
			'currencyHelper' => 'shop.class.currency',
			'nameboxType' => 'type.namebox',
			'currencyType' => 'shop.type.currency',
		));

		$this->main_currency = $shopConfig->get('main_currency');

		hikamarket::setPageTitle(JText::_('HIKAM_ORDER_CREATION'));

		$this->toolbar = array(
			'back' => array(
				'icon' => 'back',
				'fa' => 'fa-arrow-circle-left',
				'name' => JText::_('HIKA_BACK'),
				'url' => hikamarket::completeLink('order'.$url_itemid)
			),
			'create' => array(
				'icon' => 'new',
				'fa' => 'fa-plus-circle',
				'url' => '#create',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'add\',\'hikamarket_order_create_form\');"',
				'name' => JText::_('HIKAM_CREATE_ORDER'), 'pos' => 'right'
			)
		);
	}

	public function request() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.request';

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->assignRef('Itemid', $Itemid);

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$config = hikamarket::config();
		$this->assignRef('config', $config);
		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$this->loadRef(array(
			'vendorClass' => 'class.vendor',
			'currencyHelper' => 'shop.class.currency'
		));

		$data = $this->vendorClass->getUnpaidOrders($vendor);
		$this->assignRef('data', $data);

		$total = new stdClass();
		$total->count = 0;
		$total->value = 0;
		$total->currency = (int)$vendor->vendor_currency_id;
		if(empty($total->currency))
			$total->currency = hikashop_getCurrency();

		foreach($data as $d) {
			$total->count += (int)$d->count;

			if($total->currency == $d->currency)
				$total->value += hikamarket::toFloat( (int)$d->value );
			else
				$total->value += $this->currencyHelper->convertUniquePrice((float)hikamarket::toFloat($d->value), (int)$d->currency, (int)$total->currency);
		}

		$this->assignRef('total', $total);

		$min_value = hikamarket::toFloat($config->get('min_value_payment_request', 0.0));
		if($min_value > 0.0) {
			$main_currency = (int)$shopConfig->get('main_currency', 1);
			if($total->currency != $main_currency) {
				$min_value = $this->currencyHelper->convertUniquePrice($min_value, $main_currency, $total->currency);
			}
		}
		$this->assignRef('min_value', $min_value);

		hikamarket::setPageTitle(JText::_('HIKAM_PAYMENT_REQUEST'));

		$this->toolbar = array(
			array(
				'icon' => 'back',
				'fa' => 'fa-arrow-circle-left',
				'name' => JText::_('HIKA_BACK'),
				'url' => hikamarket::completeLink('order'.$this->url_itemid)
			),
			array(
				'url' => '#request',
				'fa' => 'fa-money fa-money-bill-alt',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'request\',\'hikamarket_order_request_form\');"',
				'icon' => 'pay',
				'name' => JText::_('HIKAM_DO_PAYMENT_REQUEST'), 'pos' => 'right',
				'display' => ($this->total->value != 0) && ($this->total->value > $min_value)
			)
		);
	}

	public function mail() {
		$order_id = hikashop_getCID('order_id');

		$config = hikamarket::config();
		$this->assignRef('config', $config);

		$orderClass = hikamarket::get('shop.class.order');
		$this->loadRef(array(
			'radioType' => 'shop.type.radio',
		));

		$formData = hikaInput::get()->get('data', array(), 'array');
		$params = array();
		if(!empty($formData['mail']['params'])) {
			foreach($formData['mail']['params'] as $k => $v) {
				if(is_numeric($v))
					$v = (int)$v;
				if(is_string($v))
					$v = strip_tags($v);
				$params[$k] = $v;
			}
		}

		$order = $orderClass->get($order_id);
		$order->url_itemid = '';
		$orderClass->loadOrderNotification($order, 'market.user_order_notification', $params);
		$this->assignRef('element', $order);

		$order->mail->dst_email = $order->customer->user_email;
		$order->mail->dst_name = (!empty($order->customer->name)) ? $order->customer->name : '';

		$this->toolbar = array(
			'send' => array(
				'icon' => 'email',
				'fa' => 'fa-envelope',
				'url' => '#send',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'sendmail\',\'hikamarket_mail_form\');"',
				'name' => JText::_('SEND_EMAIL'), 'pos' => 'right'
			)
		);
	}

	public function previewmail() {
		$this->mail();
	}


	public function payments($tpl = null) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName().'.payments';

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid='&Itemid='.$Itemid;
		$this->assignRef('Itemid', $Itemid);

		$vendor = hikamarket::loadVendor(true, false);
		$this->assignRef('vendor', $vendor);

		$config = hikamarket::config();
		$this->assignRef('config', $config);
		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);

		$this->loadRef(array(
			'orderStatusType' => 'type.order_status',
			'addressClass' => 'class.address',
			'currencyHelper' => 'shop.class.currency',
			'shopAddressClass' => 'shop.class.address',
		));

		$filterType = $app->getUserStateFromRequest($this->paramBase.'.filter_type', 'filter_type', 0, 'int');

		$cfg = array(
			'table' => 'shop.order',
			'main_key' => 'order_id',
			'order_sql_value' => 'hkorder.order_id'
		);

		$pageInfo = $this->getPageInfo($cfg['order_sql_value'], 'desc');
		$pageInfo->filter->filter_status = $app->getUserStateFromRequest($this->paramBase.'.filter_status', 'filter_status', '', 'string');
		$pageInfo->filter->filter_payment = $app->getUserStateFromRequest($this->paramBase.'.filter_payment', 'filter_payment', '', 'string');
		$pageInfo->filter->filter_user = $app->getUserStateFromRequest($this->paramBase.'.filter_user', 'filter_user', '', 'string');

		$filters = array();
		$searchMap = array(
			'hkorder.order_id',
			'hkorder.order_number',
		);
		$orderingAccept = array('hkorder.');
		$order = '';

		$fields = array();
		if(hikashop_level(2)) {
		}
		$this->assignRef('fields', $fields);

		if(!empty($pageInfo->filter->filter_status))
			$filters['order_status'] = 'hkorder.order_status = ' . $db->Quote($pageInfo->filter->filter_status);

		if($vendor->vendor_id > 1) {
			$filters['order_vendor_id'] = 'hkorder.order_vendor_id = ' . $vendor->vendor_id;
			$filters['order_type'] = 'hkorder.order_type = ' . $db->Quote('vendorpayment');
		} else {
			$filters['order_vendor_id'] = '(hkorder.order_vendor_id = 0 OR hkorder.order_vendor_id = 1)';
			$filters['order_type'] = 'hkorder.order_type = ' . $db->Quote('vendorpayment');
		}

		$extrafilters = array();
		$joins = array();
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikamarket');
		$app->triggerEvent('onBeforeVendorPaymentListing', array($this->paramBase, &$extrafilters, &$pageInfo, &$filters, &$joins, &$searchMap));
		$this->assignRef('extrafilters', $extrafilters);

		$this->processFilters($filters, $order, $searchMap, $orderingAccept);

		$query = 'FROM '.hikamarket::table($cfg['table']).' AS hkorder '.
			implode(' ', $joins).' '.$filters.$order;
		$db->setQuery('SELECT hkorder.* '.$query, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);

		if(empty($pageInfo->search)) {
			$query = 'FROM '.hikamarket::table($cfg['table']).' AS hkorder '.$filters;
		}

		$orders = $db->loadObjectList('order_id');

		$db->setQuery('SELECT COUNT(*) '.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($orders);

		$order_ids = array_keys($orders);
		$transaction_stats = array();
		$transaction_old_stats = array();
		if(!empty($order_ids)) {
			$query = 'SELECT hkot.order_transaction_paid AS order_id, COUNT(hkot.order_transaction_id) AS counter FROM '.hikamarket::table('order_transaction').' AS hkot '.
				'WHERE hkot.order_transaction_paid IN ('.implode(',', $order_ids).') GROUP BY hkot.order_transaction_paid';
			$db->setQuery($query);
			$transaction_stats = $db->loadObjectList('order_id');

			$query = 'SELECT hko.order_vendor_paid AS order_id, COUNT(hko.order_id) AS counter FROM '.hikamarket::table('shop.order').' AS hko '.
				'WHERE hko.order_type = '.$db->quote('subsale').' AND hko.order_vendor_paid IN ('.implode(',', $order_ids).') GROUP BY hko.order_vendor_paid';
			$db->setQuery($query);
			$transaction_old_stats = $db->loadObjectList('order_id');
		}

		foreach($orders as &$o) {
			$o->counter = 0;
			if(isset($transaction_stats[(int)$o->order_id]))
				$o->counter += (int)$transaction_stats[(int)$o->order_id]->counter;
			if(isset($transaction_old_stats[(int)$o->order_id]))
				$o->counter += (int)$transaction_old_stats[(int)$o->order_id]->counter;
		}
		unset($o);
		unset($transaction_stats);
		unset($transaction_old_stats);

		$this->assignRef('orders', $orders);

		$text_asc = JText::_('ASCENDING');
		$text_desc = JText::_('DESCENDING');
		$ordering_values = array(
			'hkorder.order_id' => JText::_('SORT_ID'),
			'hkorder.order_created' => JText::_('SORT_CREATION'),
			'hkorder.order_modified' => JText::_('SORT_MODIFICATION'),
			'hkorder.order_full_price' => JText::_('SORT_PRICE'),
		);
		$this->ordering_values = array();
		foreach($ordering_values as $k => $v) {
			$this->ordering_values[$k.' asc'] = $v . ' ' .$text_asc;
			$this->ordering_values[$k.' desc'] = $v . ' ' .$text_desc;
		}
		$this->full_ordering = $this->pageInfo->filter->order->value . ' ' . strtolower($this->pageInfo->filter->order->dir);

		$this->toolbar = array(
			'back' => array(
				'icon' => 'back',
				'fa' => 'fa-arrow-circle-left',
				'name' => JText::_('HIKA_BACK'),
				'url' => hikamarket::completeLink('vendor'.$this->url_itemid)
			),
		);

		$this->getPagination();

		$this->getOrdering('hkorder.ordering', !$filterType);
	}

	protected function getShippingName($shipping_method, $shipping_id, $default_return = true) {
		static $cache = array();

		if(strpos($shipping_id, '@') !== false)
			list($shipping_id, $warehouse) = explode('@', $shipping_id, 2);

		$key = md5($shipping_method . '##' . $shipping_id);
		if(isset($cache[$key]))
			return $cache[$key];

		$shipping_name = false;
		if($default_return)
			$shipping_name = $shipping_method . ' ' . $shipping_id;

		if(strpos($shipping_id, '-') !== false) {
			if(empty($this->shippingClass))
				$this->shippingClass = hikamarket::get('shop.class.shipping');
			$shipping_ids = explode('-', $shipping_id, 2);
			$shipping = $this->shippingClass->get($shipping_ids[0]);
			if(!empty($shipping->shipping_params) && is_string($shipping->shipping_params))
				$shipping->shipping_params = hikamarket::unserialize($shipping->shipping_params);

			if(empty($shipping_method) && !empty($shipping))
				$shipping_method = $shipping->shipping_type;

			$shippingMethod = hikamarket::import('hikashopshipping', $shipping_method);
			$methods = array();
			if(!empty($shippingMethod))
				$methods = $shippingMethod->shippingMethods($shipping);
			if(isset($methods[$shipping_id]))
				$shipping_name = $shipping->shipping_name.' - '.$methods[$shipping_id];

			$cache[$key] = $shipping_name;
		} else if($shipping_method === null && !empty($shipping_id)) {
			if(empty($this->shippingClass))
				$this->shippingClass = hikamarket::get('shop.class.shipping');
			$shipping = $this->shippingClass->get($shipping_id);
			$shipping_name = $shipping->shipping_name;
			$cache[$key] = $shipping_name;
		}
		return $shipping_name;
	}


	public function show_general() {
		$this->show($tpl, false);
	}

	public function show_history() {
		$this->show($tpl, false);
	}

	public function edit_additional() {
		$vendor = hikamarket::loadVendor(true, false);
		if($vendor->vendor_id != 0 && $vendor->vendor_id != 1) {
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_EDIT')));
		}

		$this->show($tpl, false);

		if(hikashop_level(2)) {
			$this->fields['order'] = $this->fieldsClass->getFields('display:vendor_order_edit=1', $order, 'order');
			$null = null;
			$this->fields['entry'] = $this->fieldsClass->getFields('display:vendor_order_edit=1', $null, 'entry');
			$this->fields['item'] = $this->fieldsClass->getFields('display:vendor_order_edit=1', $null, 'item');
		}

		$this->toolbar = array(
			array(
				'url' => '#save',
				'fa' => 'fa-save',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'save\',\'hikamarket_order_additional_form\');"',
				'icon' => 'save',
				'name' => JText::_('HIKA_SAVE'), 'pos' => 'right'
			)
		);

		$ratesType = hikamarket::get('type.rates');
		$this->assignRef('ratesType',$ratesType);

		$pluginsPayment = hikamarket::get('shop.type.plugins');
		$pluginsPayment->type = 'payment';
		$this->assignRef('paymentPlugins', $pluginsPayment);

		$pluginsShipping = hikamarket::get('shop.type.plugins');
		$pluginsShipping->type = 'shipping';
		$this->assignRef('shippingPlugins', $pluginsShipping);
	}

	public function show_additional() {
		$task = hikaInput::get()->getCmd('task', '');
		if($task == 'save') {
			$html = '<html><body><script type="text/javascript">'."\r\n".
				'window.parent.hikamarket.submitFct();'."\r\n".
				'</script></body></html>';
			die($html);
		}
		$this->show($tpl, false);
	}

	public function show_shipping_address() {
		$address_type = 'shipping';
		$this->assignRef('type', $address_type);
		$this->show($tpl, false);

		if($this->edit) {
			if(!empty($this->order->order_shipping_address_id)) {
				$addressClass = hikamarket::get('shop.class.address');
				$this->order->shipping_address = $addressClass->get($this->order->order_shipping_address_id);
			}
			$f = (isset($this->order->shipping_fields) ? $this->order->shipping_fields : $this->order->fields);
			$this->fieldsClass->prepareFields($f, $this->order->shipping_address, 'address', 'checkout&task=state');
		}

		$this->setLayout('show_address');
	}

	public function show_billing_address() {
		$address_type = 'billing';
		$this->assignRef('type', $address_type);
		$this->show($tpl, false);

		if($this->edit) {
			if(!empty($this->order->order_billing_address_id)) {
				$addressClass = hikamarket::get('shop.class.address');
				$this->order->billing_address = $addressClass->get($this->order->order_billing_address_id);
			}
			$f = (isset($this->order->billing_fields) ? $this->order->billing_fields : $this->order->fields);
			$this->fieldsClass->prepareFields($f, $this->order->billing_address, 'address', 'checkout&task=state');
		}

		$this->setLayout('show_address');
	}

	public function show_products() {
		$task = hikaInput::get()->getCmd('task', '');
		if($task == 'save') {
			$html = '<html><body><script type="text/javascript">'."\r\n".
				'window.parent.hikamarket.submitFct();'."\r\n".
				'</script></body></html>';
			die($html);
		}
		$this->show($tpl, false);
	}

	public function edit_products() {
		$vendor = hikamarket::loadVendor(true, false);
		if($vendor->vendor_id != 0 && $vendor->vendor_id != 1) {
			return hikamarket::deny('order', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_ORDER_EDIT')));
		}
		$this->assignRef('vendor', $vendor);

		$config = hikamarket::config();
		$this->assignRef('config', $config);
		$shopConfig = hikamarket::config(false);
		$this->assignRef('shopConfig', $shopConfig);
		$db = JFactory::getDBO();

		$productClass = hikamarket::get('shop.class.product');
		$fieldsClass = hikamarket::get('shop.class.field');
		$this->assignRef('fieldsClass', $fieldsClass);

		$order_id = hikaInput::get()->getInt('order_id');
		$order_product_id = hikaInput::get()->getInt('order_product_id', 0);

		$this->toolbar = array(
			array(
				'url' => '#save',
				'fa' => 'fa-save',
				'linkattribs' => 'onclick="return window.hikamarket.submitform(\'save\',\'hikamarket_order_product_form\');"',
				'icon' => 'save',
				'name' => JText::_('HIKA_SAVE'), 'pos' => 'right'
			)
		);

		$orderClass = hikamarket::get('shop.class.order');
		$order = $orderClass->get($order_id);
		$originalProduct = new stdClass();

		if(!empty($order_product_id)) {
			$orderProductClass = hikamarket::get('shop.class.order_product');
			$orderProduct = $orderProductClass->get($order_product_id);
			if(empty($orderProduct) || $orderProduct->order_id != $order_id) {
				$orderProduct = new stdClass();
				$orderProduct->order_id = $order_id;
			}
			if(!empty($orderProduct->product_id)) {
				$originalProduct = $productClass->get($orderProduct->product_id);
			}
		} else {
			$orderProduct = new stdClass();
			$orderProduct->order_id = $order_id;

			$product_id = hikaInput::get()->get('cid', array(), 'array');
			if(!empty($product_id) && $productClass->getProducts($product_id)) {
				$products = $productClass->products;
				$product = $products[ (int)$product_id[0] ];
				$product->options = array();

				$originalProduct = $product;

				$orderProduct->product_id = $product->product_id;
				$orderProduct->order_product_name = $product->product_name;
				$orderProduct->order_product_code = $product->product_code;
				$orderProduct->order_product_quantity = 1;

				$currencyClass = hikamarket::get('shop.class.currency');
				$main_currency = (int)$shopConfig->get('main_currency',1);
				$discount_before_tax = (int)$shopConfig->get('discount_before_tax',0);
				$currency_id = $order->order_currency_id;

				if($shopConfig->get('tax_zone_type', 'shipping') == 'billing')
					$zone_id = hikamarket::getZone('billing');
				else
					$zone_id = hikamarket::getZone('shipping');

				$rows = array($product);
				$currencyClass->getPrices($rows, $product_id, $currency_id, $main_currency, $zone_id, $discount_before_tax);
				$currencyClass->pricesSelection($rows[0]->prices, 0);
				if(!empty($rows[0]->prices)) {
					foreach($rows[0]->prices as $price) {
						$orderProduct->order_product_price = $price->price_value;
						$orderProduct->order_product_tax = (@$price->price_value_with_tax - @$price->price_value);
						$orderProduct->order_product_tax_info = @$price->taxes;
					}
				}
			}
		}
		if(!empty($orderProduct->order_product_id) && (int)$orderProduct->order_product_id > 0) {
			if(empty($orderProduct->order_product_parent_id)) {
				$query = 'SELECT hkop.*, hko.order_vendor_id, hmv.* FROM ' . hikamarket::table('shop.order_product') . ' as hkop '.
					' INNER JOIN ' . hikamarket::table('shop.order'). ' AS hko ON hkop.order_id = hko.order_id '.
					' LEFT JOIN ' . hikamarket::table('vendor'). ' AS hmv ON hmv.vendor_id = hko.order_vendor_id '.
					' WHERE hko.order_type = \'subsale\' AND order_product_parent_id = '. (int)$orderProduct->order_product_id .
					' ORDER BY hko.order_id DESC';
				$db->setQuery($query);
				$orderProduct->vendor_data = $db->loadObject();
			}
		} else if(!empty($orderProduct->product_id)) {
			$query = 'SELECT p.product_vendor_id, pp.product_vendor_id AS parent_vendor_id FROM '.hikamarket::table('shop.product').' AS p '.
				' LEFT JOIN '.hikamarket::table('shop.product').' AS pp ON p.product_parent_id = pp.product_id '.
				' WHERE p.product_id = '. (int)$orderProduct->product_id;
			$db->setQuery($query);
			$productVendor = $db->loadObject();
			$orderProduct->vendor_data = $productVendor;

			$vendor_id = 0;
			if(!empty($productVendor->product_vendor_id))
				$vendor_id = (int)$productVendor->product_vendor_id;
			else if(!empty($productVendor->parent_vendor_id))
				$vendor_id = (int)$productVendor->parent_vendor_id;

			$vendorObj = null;
			if(!empty($vendor_id)) {
				$vendorClass = hikamarket::get('class.vendor');
				$vendorObj = $vendorClass->get($vendor_id);
			}
			$orderProduct->vendor = $vendorObj;
		}

		$this->assignRef('orderProduct', $orderProduct);
		$this->assignRef('originalProduct', $originalProduct);

		$ratesType = hikamarket::get('type.rates');
		$this->assignRef('ratesType',$ratesType);

		if(hikashop_level(2)) {
			$null = null;
			$this->fields['item'] = $this->fieldsClass->getFields('display:vendor_order_edit=1', $null, 'item','checkout&task=state');
		}
	}

	public function customer_set() {
		$users = hikaInput::get()->get('cid', array(), 'array');
		$closePopup = hikaInput::get()->getInt('finalstep', 0);

		if($closePopup) {
			$formData = hikaInput::get()->get('data', array(), 'array');
			$users = array( (int)$formData['order']['order_user_id'] );
		}
		$rows = array();
		$data = '';
		$singleSelection = true; //hikaInput::get()->getBool('single', false);
		$order_id = hikaInput::get()->getInt('order_id', 0);

		$elemStruct = array(
			'user_email',
			'user_cms_id',
			'name',
			'username',
			'email'
		);

		$set_address = hikaInput::get()->getInt('set_user_address', 0);

		if(!empty($users)) {
			hikamarket::toInteger($users);
			$db = JFactory::getDBO();
			$query = 'SELECT a.*, b.* FROM '.hikamarket::table('user','shop').' AS a INNER JOIN '.hikamarket::table('users', false).' AS b ON a.user_cms_id = b.id WHERE a.user_id IN ('.implode(',',$users).')';
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
					if($set_address && $singleSelection)
						$d .= ',updates:[\'billing\',\'history\']';
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
		$this->assignRef('order_id', $order_id);

		if($closePopup) {
			$js = 'window.hikashop.ready(function(){window.top.hikamarket.submitBox('.$data.');});';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
		}
	}
}
