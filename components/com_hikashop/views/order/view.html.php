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

		hikashop_setPageTitle('ORDERS');

		$this->loadRef(array(
			'currencyClass' => 'class.currency',
			'cartHelper' => 'helper.cart'
		));

		$params = new HikaParameter();
		$params->set('show_quantity_field', 0);
		$this->assignRef('params', $params);

		$extraFilters = array();
		$pageInfo = $this->getPageInfo('hk_order.order_created', 'desc', $extraFilters);

		$filters = array(
			'hk_order.order_type = ' . $db->Quote('sale'),
			'hk_order.order_user_id = ' . (int)$user_id
		);
		$order = '';
		$searchMap = array(
			'hk_order.order_id',
			'hk_order.order_status',
			'hk_order.order_number'
		);
		$orderingAccept = array(
			'hk_order.'
		);
		$this->processFilters($filters, $order, $searchMap, $orderingAccept);

		$query = ' FROM ' . hikashop_table('order') . ' AS hk_order ' . $filters . $order;
		$this->getPageInfoTotal($query, '*');
		$db->setQuery('SELECT hk_order.*' . $query, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $db->loadObjectList();

		if(!empty($pageInfo->search)) {
			$rows = hikashop_search($pageInfo->search, $rows, 'order_id');
		}

		$this->action_column = false;

		if(hikashop_level(1) && $config->get('allow_payment_button', 1)) {
			$unpaid_statuses = explode(',', $config->get('order_unpaid_statuses', 'created'));
			foreach($rows as &$order) {
				if(in_array($order->order_status, $unpaid_statuses)) {
					$order->show_payment_button = true;
					$this->action_column = true;
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

		$cancellable_order_status = explode(',', trim($config->get('cancellable_order_status', ''), ', '));
		if(!empty($cancellable_order_status)) {
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

		if($this->action_column) {
			$this->loadRef(array(
				'dropdownHelper' => 'helper.dropdown'
			));
		}

		$this->assignRef('rows', $rows);

		$this->getPagination();
		$this->getOrdering('hk_order.order_id', true);

		$category = hikashop_get('type.categorysub');
		$category->type = 'status';
		$category->load(true);
		$this->assignRef('order_statuses',$category);
		$cart = hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
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

		hikashop_get('helper.checkout');
		$checkoutHelper = hikashopCheckoutHelper::get();
		$this->assignRef('checkoutHelper', $checkoutHelper);

		$new_payment_method = JRequest::getVar('new_payment_method', null);
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

		$config =& hikashop_config();

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

		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup', $popup);

		hikashop_setPageTitle(JText::_('HIKASHOP_ORDER').':'.$this->element->order_number);
	}

	public function invoice() {
		$type = 'invoice';
		$this->setLayout('show');
		$order =& $this->_order($type);
		$js = "window.hikashop.ready( function() {setTimeout(function(){window.focus();window.print();setTimeout(function(){hikashop.closeBox();}, 1000);},1000);});";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
	}

	protected function &_order($type) {
		$order_id = hikashop_getCID('order_id');
		$app = JFactory::getApplication();
		if(empty($order_id)){
			$order_id = $app->getUserState('com_hikashop.order_id');
		}
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->loadFullOrder($order_id,($type=='order'?true:false));
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
		$this->assignRef('products', $products);

		$this->assignRef('store_address',$store);
		$this->assignRef('element',$order);
		$this->assignRef('order',$order);
		$this->assignRef('invoice_type',$type);
		$display_type = 'frontcomp';
		$this->assignRef('display_type',$display_type);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		if(is_string($order->order_shipping_method))
			$currentShipping = hikashop_import('hikashopshipping',$order->order_shipping_method);
		else
			$currentShipping = hikashop_import('hikashopshipping', reset($order->order_shipping_method));
		$this->assignRef('currentShipping',$currentShipping);
		$fields = array();
		if(hikashop_level(2)){
			$null = null;
			$fields['entry'] = $fieldsClass->getFields('frontcomp',$null,'entry');
			$fields['item'] = $fieldsClass->getFields('frontcomp',$null,'item');

			if($type=='invoice')
				$fields['order'] = $fieldsClass->getFields('display:invoice=1',$null,'order');
			else
				$fields['order'] = $fieldsClass->getFields('display:front_order=1',$null,'order');
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
