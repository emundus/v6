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
class hikamarketShopOrder_backendHelper {
	protected $db = null;

	public function __construct() {
		$this->db = JFactory::getDBO();
	}

	public function processView(&$view) {
		$app = JFactory::getApplication();
		$layout = $view->getLayout();
		$orderClass = hikamarket::get('class.order');

		if(hikamarket::isAdmin() && ($layout == 'show' || substr($layout, 0, 5) == 'show_')) {
			$currencyClass = hikamarket::get('shop.class.currency');

			if($view->order->order_type == 'subsale') {
				$order_vendor_params = $view->order->order_vendor_params;
				if(is_string($order_vendor_params) && !empty($order_vendor_params))
					$order_vendor_params = hikamarket::unserialize($view->order->order_vendor_params);
				else
					$order_vendor_params = null;

				if(!empty($view->order->order_vendor_id)) {
					$vendorClass = hikamarket::get('class.vendor');
					$vendor = $vendorClass->get( $view->order->order_vendor_id );

					if(!empty($vendor)) {
						$view->extra_data['general']['order_vendor'] = array(
							'title' => JText::_('HIKA_VENDOR'),
							'data' => '<a href="'.hikamarket::completeLink('vendor&task=edit&cid='.$vendor->vendor_id).'">'.$vendor->vendor_name.'</a>'
						);
					} else {
						$view->extra_data['general']['order_vendor'] = array(
							'title' => JText::_('HIKA_VENDOR'),
							'data' => $view->order->order_vendor_id
						);
					}
				}

				$view->extra_data['general']['order_parent_id'] = array(
					'title' => JText::_('HIKAM_PARENT_ORDER'),
					'data' => $view->order->order_parent_id.' <a href="'.hikamarket::completeLink('shop.order&task=edit&cid='.$view->order->order_parent_id).'"><i class="fa fa-chevron-right"></i></a>'
				);

				$fixed_fees = 0.0;
				if(!empty($order_vendor_params->fees->fixed)) {
					foreach($order_vendor_params->fees->fixed as $fixed_fee) {
						$fixed_fees += $fixed_fee;
					}
				}
				$view->extra_data['additional']['vendor_fixed_fees'] = array(
					'title' => JText::_('HIKAM_VENDOR_FIXED_FEES'),
					'data' => $currencyClass->format($fixed_fees, $view->order->order_currency_id)
				);

				if(!empty($order_vendor_params->fees->shipping)) {
					$view->extra_data['additional']['vendor_shipping_fees'] = array(
						'title' => JText::_('HIKAM_VENDOR_SHIPPING_FEES'),
						'data' => $currencyClass->format($order_vendor_params->fees->shipping, $view->order->order_currency_id)
					);
				}

				if($view->order->order_vendor_paid > 0) {
					$query = 'SELECT * '.
						' FROM ' . hikamarket::table('shop.order') .
						' WHERE order_parent_id = ' . $view->order->order_parent_id . ' AND order_type = ' . $this->db->Quote('vendorrefund');
					$this->db->setQuery($query);
					$refunds = $this->db->loadObjectList();
					$total = $view->order->order_vendor_price;
					$paid = $view->order->order_vendor_price;
					if(!empty($refunds)) {
						foreach($refunds as $refund) {
							$total += (float)hikamarket::toFloat($refund->order_vendor_price);
							if($refund->order_vendor_paid > 0)
								$paid += (float)hikamarket::toFloat($refund->order_vendor_price);
						}
					}
					$paidIcon = ' <i class="far fa-check-circle"></i>';
					$unpaidIcon = ' <a href="'.hikamarket::completeLink('vendor&task=pay&cid='.(int)$view->order->order_vendor_id).'"><i class="far fa-dot-circle"></i></a>';
					$view->extra_data['additional']['order_vendor_price'] = array(
						'title' => JText::_('VENDOR_TOTAL'),
						'data' => $currencyClass->format($total, $view->order->order_currency_id) . (($paid != $total) ? $unpaidIcon : $paidIcon)
					);
					if($paid != $total) {
						$view->extra_data['additional']['order_vendor_paid'] = array(
							'title' => JText::_('VENDOR_TOTAL_PAID'),
							'data' => $currencyClass->format($paid, $view->order->order_currency_id)
						);
					}
				} else {
					$view->extra_data['additional']['order_vendor_price'] = array(
						'title' => JText::_('VENDOR_TOTAL'),
						'data' => $currencyClass->format($view->order->order_vendor_price, $view->order->order_currency_id)
					);
				}

				$query = 'SELECT hkop.*, hko.order_vendor_id, hmv.vendor_name, hmv.vendor_id '.
					' FROM ' . hikamarket::table('shop.order_product') . ' as hkop '.
					' INNER JOIN ' . hikamarket::table('shop.order'). ' AS hko ON hkop.order_id = hko.order_id '.
					' LEFT JOIN ' . hikamarket::table('vendor'). ' AS hmv ON hmv.vendor_id = hko.order_vendor_id '.
					' WHERE hko.order_type = \'subsale\' AND hko.order_id = '. (int)$view->order->order_id .
					' ORDER BY hko.order_id DESC';
				$this->db->setQuery($query);
				$vendorProducts = $this->db->loadObjectList('order_product_id');

				if(!isset($view->extra_data['products']))
					$view->extra_data['products'] = array();
				$view->extra_data['products']['vendor'] = JText::_('HIKA_VENDOR');
				foreach($view->order->products as &$product) {
					$product->extra_data['vendor'] = '-';
					if(isset($vendorProducts[$product->order_product_id])) {
						$product->extra_data['vendor'] = $currencyClass->format(
							 (float)$vendorProducts[$product->order_product_id]->order_product_vendor_price,
							$view->order->order_currency_id
						);
					}
				}
				unset($product);
				return;
			}

			if($view->order->order_type == 'sale') {
				$query = 'SELECT hkop.*, hko.order_vendor_id, hmv.vendor_name, hmv.vendor_id '.
					' FROM ' . hikamarket::table('shop.order_product') . ' as hkop '.
					' INNER JOIN ' . hikamarket::table('shop.order'). ' AS hko ON hkop.order_id = hko.order_id '.
					' LEFT JOIN ' . hikamarket::table('vendor'). ' AS hmv ON hmv.vendor_id = hko.order_vendor_id '.
					' WHERE hko.order_type = \'subsale\' AND hko.order_parent_id = '. (int)$view->order->order_id .
					' ORDER BY hko.order_id DESC';
				$this->db->setQuery($query);
				$vendorProducts = $this->db->loadObjectList();

				if(!isset($view->extra_data['products']))
					$view->extra_data['products'] = array();
				$view->extra_data['products']['vendor'] = JText::_('HIKA_VENDOR');
				foreach($view->order->products as &$product) {
					$product->extra_data['vendor'] = '-';
					foreach($vendorProducts as $vendorProduct) {
						if((int)$vendorProduct->order_product_parent_id != $product->order_product_id || (int)$vendorProduct->vendor_id <= 1)
							continue;
						if($product->extra_data['vendor'] == '-')
							$product->extra_data['vendor'] = '';
						$product->extra_data['vendor'] .= '<p>'.$vendorProduct->vendor_name.'<br/>'.
							$currencyClass->format(
								 (float)$vendorProduct->order_product_vendor_price,
								$view->order->order_currency_id
							).
							'</p>';
					}
				}
				unset($product);
				return;
			}
			return;
		}

		if(hikamarket::isAdmin() && $layout == 'edit_products') {
			if(empty($view->extra_data))
				$view->extra_data = array();
			if(empty($view->extra_data['products']))
				$view->extra_data['products'] = array();

			$vendor = null;
			$vendor_price = false;

			if(!empty($view->orderProduct->order_product_id) && (int)$view->orderProduct->order_product_id > 0) {
				if(!empty($view->orderProduct->order_product_parent_id)) {
					$vendor_price = true;
					$vendorProduct = $view->orderProduct;
				} else {
					$query = 'SELECT hkop.*, hko.order_vendor_id, hmv.* FROM ' . hikamarket::table('shop.order_product') . ' as hkop '.
						' INNER JOIN ' . hikamarket::table('shop.order'). ' AS hko ON hkop.order_id = hko.order_id '.
						' LEFT JOIN ' . hikamarket::table('vendor'). ' AS hmv ON hmv.vendor_id = hko.order_vendor_id '.
						' WHERE hko.order_type = \'subsale\' AND order_product_parent_id = '. (int)$view->orderProduct->order_product_id .
						' ORDER BY hko.order_id DESC';
					$this->db->setQuery($query);
					$vendorProducts = $this->db->loadObjectList();

					if(!empty($vendorProducts) && count($vendorProducts) == 1)
						$vendorProduct = reset($vendorProducts);
					unset($vendorProducts);
				}

				if(!empty($vendorProduct) && !empty($vendorProduct->order_vendor_id) && $vendorProduct->order_vendor_id > 1) {
					$vendor = @$vendorProduct->order_vendor_id;
					if(!empty($vendorProduct->vendor_name))
						$vendor .= ' - ' . $vendorProduct->vendor_name;
					$vendor_price = true;
				}
			} else {
				$vendor = '<input type="text" name="data[market][product][order_product_vendor_id]" value=""/>';
				$vendor_price = true;

				if(!empty($view->orderProduct->product_id)) {
					$query = 'SELECT p.product_vendor_id, pp.product_vendor_id AS parent_vendor_id FROM '.hikamarket::table('shop.product').' AS p '.
						' LEFT JOIN '.hikamarket::table('shop.product').' AS pp ON p.product_parent_id = pp.product_id '.
						' WHERE p.product_id = '. (int)$view->orderProduct->product_id;
					$this->db->setQuery($query);
					$productVendor = $this->db->loadObject();
					$vendor_id = 0;
					if(!empty($productVendor->product_vendor_id))
						$vendor_id = (int)$productVendor->product_vendor_id;
					else if(!empty($productVendor->parent_vendor_id))
						$vendor_id = (int)$productVendor->parent_vendor_id;

					if(!empty($vendor_id)) {
						$vendorClass = hikamarket::get('class.vendor');
						$vendorObj = $vendorClass->get($vendor_id);
						$vendor = $vendorObj->vendor_id . ' - ' . $vendorObj->vendor_name . '<input type="hidden" name="data[market][product][order_product_vendor_id]" value="'.$vendorObj->vendor_id.'"/>';
					}

					if(!empty($vendor_id) && empty($vendorProduct->order_product_vendor_price)) {
						$vendor_ids = array((int)$vendorObj->vendor_id => (int)$vendorObj->vendor_id);
						$products = array(
							0 => array(
								'_id' => (int)@$view->orderProduct->order_product_id,
								'id' => (int)$view->orderProduct->product_id,
								'vendor' => (int)$vendorObj->vendor_id,
								'fee' => array(),
								'qty' => (int)$view->orderProduct->order_product_quantity,
								'price' => (float)hikamarket::toFloat($view->orderProduct->order_product_price),
								'price_tax' => (float)hikamarket::toFloat($view->orderProduct->order_product_tax)
							)
						);

						$config = hikamarket::config();
						if($config->get('calculate_vendor_price_with_tax', false))
							$full_price = (float)($products[0]['price'] + $products[0]['price_tax']) * (int)$products[0]['qty'];
						else
							$full_price = (float)$products[0]['price'] * (int)$products[0]['qty'];

						$feeClass = hikamarket::get('class.fee');
						$allFees = $feeClass->getProducts($products, $vendor_ids);
						if($config->get('calculate_vendor_price_with_tax', false))
							$view->orderProduct->order_product_vendor_price = (float)hikamarket::toFloat($view->orderProduct->order_product_price) + (float)hikamarket::toFloat($view->orderProduct->order_product_tax);
						else
							$view->orderProduct->order_product_vendor_price = $view->orderProduct->order_product_price;
						$product_fee = $orderClass->getProductFee($view->orderProduct, $products[0]['fee'], $full_price, $full_price, $products[0]['qty']);

						if(empty($vendorProduct))
							$vendorProduct = new stdClass();
						$vendorProduct->order_product_vendor_price = $product_fee['vendor'];
					}
				}
			}

			if(!empty($vendor)) {
				$view->extra_data['products']['vendor_id'] = array(
					'title' => 'HIKA_VENDOR',
					'data' => $vendor
				);
			}
			if($vendor_price) {
				$view->extra_data['products']['vendor_price'] = array(
					'title' => 'HIKAM_VENDOR_UNIT_PRICE',
					'data' => '<input type="text" name="data[market][product][order_product_vendor_price]" value="'.@$vendorProduct->order_product_vendor_price.'"/>'
				);
			}
			return;
		}

		if(hikamarket::isAdmin() && $layout == 'edit_additional') {
			$fixed_fees = 0.0;
			$order_vendor_params = is_string($view->order->order_vendor_params) ? hikamarket::unserialize($view->order->order_vendor_params) : $view->order->order_vendor_params;
			if(!empty($order_vendor_params->fees->fixed)) {
				foreach($order_vendor_params->fees->fixed as $fixed_fee) {
					$fixed_fees += $fixed_fee;
				}
			}
			$view->extra_data['additional']['vendor_fixed_fee'] = array(
				'title' => 'HIKAM_VENDOR_FIXED_FEES',
				'data' => '<input type="text" name="data[market][fixed_fees]" value="'.$fixed_fees.'"/>'
			);
			$view->extra_data['additional']['vendor_shipping_fee'] = array(
				'title' => 'HIKAM_VENDOR_SHIPPING_FEES',
				'data' => '<input type="text" name="data[market][shipping_fees]" value="'.@$order_vendor_params->fees->shipping.'"/>'
			);
			return;
		}

		if(!hikamarket::isAdmin() && $layout == 'show') {
			$config = hikamarket::config();
			$query = 'SELECT o.order_vendor_id FROM ' . hikamarket::table('shop.order') . ' AS o '.
					' WHERE order_type = '. $this->db->Quote('subsale') .' AND order_parent_id = ' . (int)$view->order->order_id .
					' GROUP BY o.order_vendor_id';
			$this->db->setQuery($query);
			$vendors = $this->db->loadColumn();

			if(count($vendors) == 1 && (int)$config->get('vendors_in_cart', 0) == 1) {
				$vendorClass = hikamarket::get('class.vendor');
				$fieldsClass = hikamarket::get('shop.class.field');
				$vendor = $vendorClass->get( reset($vendors) );
				$view->vendor = $vendor;

				$vendorFields = $vendor;
				$extraFields = array(
					'vendor' => $fieldsClass->getFields('frontcomp', $vendorFields, 'plg.hikamarket.vendor')
				);

				$params = null; $js = null;
				$html = hikamarket::getLayout('shop.address', 'address_template', $params, $js);
				foreach($extraFields['vendor'] as $field) {
					$fieldname = $field->field_namekey;
					$html = str_replace('{' . str_replace('vendor_', '', $fieldname) . '}', $fieldsClass->show($field, @$vendor->$fieldname), $html);
				}
				$view->store_address =  str_replace("\n","<br/>\n",trim(str_replace("\n\n","\n",preg_replace('#{(?:(?!}).)*}#i','',$html)),"\n"));
			} else if(count($vendors) > 0 && (int)$config->get('show_sold_by', 0) == 1) {
				$query = 'SELECT hkop.*, hko.order_vendor_id, hmv.vendor_name, hmv.vendor_id '.
					' FROM ' . hikamarket::table('shop.order_product') . ' as hkop '.
					' INNER JOIN ' . hikamarket::table('shop.order'). ' AS hko ON hkop.order_id = hko.order_id '.
					' LEFT JOIN ' . hikamarket::table('vendor'). ' AS hmv ON hmv.vendor_id = hko.order_vendor_id '.
					' WHERE hko.order_type = \'subsale\' AND hko.order_parent_id = '. (int)$view->order->order_id .
					' ORDER BY hko.order_id DESC';
				$this->db->setQuery($query);
				$vendorProducts = $this->db->loadObjectList();

				foreach($view->order->products as &$product) {
					foreach($vendorProducts as $vendorProduct) {
						if((int)$vendorProduct->order_product_parent_id != $product->order_product_id)
							continue;

						if((int)$vendorProduct->vendor_id <= 1)
							break;

						if(empty($product->extraData))
							$product->extraData = array();
						$product->extraData['vendor'] = '<span class="order_product_vendor">'.JText::sprintf('SOLD_BY_VENDOR', $vendorProduct->vendor_name).'</span>';
						break;
					}
				}
				unset($product);
			}
		}

	}

	public function processAdminForm(&$order, &$do, $from = 'order') {
		$order_id = hikamarket::getCID('order_id');
		$task = hikaInput::get()->getVar('subtask', '');
		$data = hikaInput::get()->get('data', array(), 'array');

		if($task == 'products' && $from == 'order')
			return;

		$orderClass = hikamarket::get('class.order');

		if($task == 'products' && isset($data['market']['product']['order_product_vendor_price'])) {
			$product_id = (int)$data['order']['product']['product_id'];
			$order_product_id = null;
			if(isset($data['order']['product']['order_product_id']))
				$order_product_id = (int)$data['order']['product']['order_product_id'];
			$order_product_vendor_price = trim($data['market']['product']['order_product_vendor_price']);
			$order_product_vendor_id = null;
			if(isset($data['market']['product']['order_product_vendor_id']))
				$order_product_vendor_id = (int)trim($data['market']['product']['order_product_vendor_id']);
			$order_product_quantity = null;
			if(isset($data['order']['product']['order_product_quantity']))
				$order_product_quantity = (int)trim($data['order']['product']['order_product_quantity']);

			if(empty($order->hikamarket))
				$order->hikamarket = new stdClass();
			$order->hikamarket->products = array(
				$order_product_id => array(
					'product_id' => $product_id,
					'order_product_id' => $order_product_id,
					'vendor_id' => $order_product_vendor_id,
					'vendor_price' => $order_product_vendor_price,
				)
			);
			if($order_product_quantity !== null)
				$order->hikamarket->products[$order_product_id]['order_product_quantity'] = $order_product_quantity;
		}

		if($task == 'additional' && isset($data['market']['fixed_fees'])) {
			$order_vendor_params = is_string($order->order_vendor_params) ? hikamarket::unserialize($order->order_vendor_params) : $order->order_vendor_params;
			if(empty($order_vendor_params)) $order_vendor_params = new stdClass();
			if(!isset($order_vendor_params->fees)) $order_vendor_params->fees = new stdClass();
			$order_vendor_params->fees->fixed = array(
				0 => (float)hikamarket::toFloat($data['market']['fixed_fees'])
			);
			if(isset($data['market']['shipping_fees']))
				$order_vendor_params->fees->shipping = (float)hikamarket::toFloat($data['market']['shipping_fees']);
			$order->order_vendor_params = $order_vendor_params;

			$vendor_new_total = $orderClass->recalculateVendorPrice($order);

			if(is_string($order->order_payment_params) && !empty($order->order_payment_params))
				$order->order_payment_params = hikamarket::unserialize($order->order_payment_params);
			$feeMode = true;
			if(isset($order->order_payment_params->market_mode))
				$feeMode = $order->order_payment_params->market_mode;

			if(!$feeMode) {
				$config = hikamarket::config();
				if($config->get('shipping_per_vendor', 1) && !empty($order->order_shipping_price))
					$vendor_new_total = $vendor_new_total - $order->order_full_price;
				else
					$vendor_new_total = $vendor_new_total - $order->order_full_price - (float)$order->order_shipping_price;
			}

			if($vendor_new_total != $order->order_vendor_price) {
				$order->order_vendor_price = $vendor_new_total;

				$shopOrderClass = hikamarket::get('shop.class.order');
				$parentOrder = $shopOrderClass->get($order->order_parent_id);
				$orderClass->updateTransaction($parentOrder, $order, $vendor_new_total);
			}

			if(!empty($order->order_payment_params) && !is_string($order->order_payment_params))
				$order->order_payment_params = serialize($order->order_payment_params);

			$order->order_vendor_params = serialize($order->order_vendor_params);
		}
	}

	public function frontSaveFormLegacy($task = '', $acl = true) {
		$do = false;
		$vendor_id = 0;
		$forbidden = array();
		if($acl) {
			if(!hikamarket::loginVendor())
				return false;
			$vendor_id = hikamarket::loadVendor(false);

			if($vendor_id > 1) {
				$forbidden = array( 'billing_address' => 1, 'shipping_address' => 1, 'additional' => 1, 'custom_fields' => 1, 'customer' => 1 , 'product_delete' => 1 );
				if(isset($forbidden[$task]))
					return false;
			}
			if($vendor_id == 1)
				$vendor_id = 0;
		}

		$order_id = hikamarket::getCID('order_id');
		$orderClass = hikamarket::get('shop.class.order');
		$addressClass = hikamarket::get('shop.class.address');
		$fieldsClass = hikamarket::get('shop.class.field');

		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);

		$oldOrder = $this->getRaw($order_id);
		$order = clone($oldOrder);
		$order->history = new stdClass();
		$data = hikaInput::get()->get('data', array(), 'array');

		if(empty($order_id) || empty($order->order_id)) {

			$orderClass->sendEmailAfterOrderCreation = false;
		} else {
			if($acl && $order->order_vendor_id != $vendor_id)
				return hikamarket::deny('order', JText::sprintf('HIKAM_PAGE_DENY'));

			$order->history->history_notified = false;
		}

		$currentTask = 'billing_address';
		$aclTask = 'billingaddress';
		if( (empty($task) || $task == $currentTask) && !empty($data[$currentTask]) && !isset($forbidden[$currentTask]) && (!$acl || hikamarket::acl('order/edit/'.$aclTask)) ) {
			$oldAddress = null;
			if(!empty($oldOrder->order_billing_address_id)) {
				$oldAddress = $addressClass->get($oldOrder->order_billing_address_id);
			}
			$billing_address = $fieldsClass->getInput(array($currentTask, 'address'), $oldAddress);

			if(!empty($billing_address) && !empty($order_id)){
				$result = $addressClass->save($billing_address, $order_id, 'billing');
				if($result){
					$order->order_billing_address_id = $result;
					$order->history->history_reason = 'Billing address modified';
					$do = true;
				}
			}
		}

		$currentTask = 'shipping_address';
		$aclTask = 'shippingaddress';
		if( (empty($task) || $task == $currentTask) && !empty($data[$currentTask]) && !isset($forbidden[$currentTask]) && (!$acl || hikamarket::acl('order/edit/'.$aclTask)) ) {
			$oldAddress = null;
			if(!empty($oldOrder->order_shipping_address_id)) {
				$oldAddress = $addressClass->get($oldOrder->order_shipping_address_id);
			}
			$shipping_address = $fieldsClass->getInput(array($currentTask, 'address'), $oldAddress);

			if(!empty($shipping_address) && !empty($order_id)){
				$result = $addressClass->save($shipping_address, $order_id, 'shipping');
				if($result){
					$order->order_shipping_address_id = $result;
					$order->history->history_reason = 'Shipping address modified';
					$do = true;
				}
			}
		}

		$currentTask = 'general';
		if( (empty($task) || $task == $currentTask) && !empty($data[$currentTask]) && (!$acl || hikamarket::acl('order/edit/'.$currentTask)) ) {

			if(!empty($data['order']['order_status']) && $this->isValidOrderStatus($data['order']['order_status'])) {
				if($oldOrder->order_type == 'subsale' && (int)$oldOrder->order_vendor_paid > 0 && $config->get('filter_orderstatus_paid_order', 1)) {
					$config = hikamarket::config();
					$valid_order_statuses = explode(',', $config->get('valid_order_statuses', 'confirmed,shipped'));
					if(in_array($oldOrder->order_status, $valid_order_statuses) && in_array($order->order_status, $valid_order_statuses)) {
						$order->order_status = $data['order']['order_status'];
						$do = true;
					}
				} else {
					$order->order_status = $data['order']['order_status'];
					$do = true;
				}
			}

			if($vendor_id == 0 && !empty($data['notify']) && hikamarket::acl('order/edit/notify')) {
				if(empty($order->history))
					$order->history = new stdClass();
				$order->history->history_notified = true;
			}
		}

		$currentTask = 'additional';
		if( (empty($task) || $task == $currentTask) && !empty($data[$currentTask]) && !isset($forbidden[$currentTask]) && (!$acl || hikamarket::acl('order/edit/'.$currentTask)) ) {
			$history_data = array();

			if(isset($data['order']['order_discount_code'])) {
				$order->order_discount_code = $safeHtmlFilter->clean(strip_tags($data['order']['order_discount_code']), 'string');
				$do = true;
			}
			if(isset($data['order']['order_discount_price'])) {
				$order->order_discount_price = (float)hikamarket::toFloat($data['order']['order_discount_price']);
				$do = true;
			}
			if(isset($data['order']['order_discount_tax'])) {
				$order->order_discount_tax = (float)hikamarket::toFloat($data['order']['order_discount_tax']);
				$do = true;
			}
			if(isset($data['order']['order_discount_tax_namekey'])) {
				$order->order_discount_tax_namekey = $safeHtmlFilter->clean(strip_tags($data['order']['order_discount_tax_namekey']), 'string');
				$do = true;
			}

			if(!empty($data['order']['shipping'])) {

				if(is_string($data['order']['shipping'])) {
					$s = $safeHtmlFilter->clean(strip_tags($data['order']['shipping']), 'string');
					list($shipping_method, $shipping_id) = explode('_', $s, 2);
					$order->order_shipping_method = $shipping_method;
					$order->order_shipping_id = $shipping_id;
					$do = true;
				}

				if(is_array($data['order']['shipping'])) {
					$order->order_shipping_method = '';
					$shippings = array();
					$order->order_shipping_params->prices = array();

					foreach($data['order']['shipping'] as $shipping_group => $shipping_value) {
						list($shipping_method, $shipping_id) = explode('_', $shipping_value, 2);
						$n = $shipping_id . '@' . $shipping_group;
						$shippings[] = $n;
						$order->order_shipping_params->prices[$n] = new stdClass();
						$order->order_shipping_params->prices[$n]->price_with_tax = (float)hikamarket::toFloat(@$data['order']['order_shipping_prices'][$shipping_group]);
						$order->order_shipping_params->prices[$n]->tax = (float)hikamarket::toFloat(@$data['order']['order_shipping_taxs'][$shipping_group]);
					}
					$order->order_shipping_id = implode(';', $shippings);
					$do = true;

					if(!empty($data['order']['warehouses'])) {
						$orderProductClass = hikamarket::get('shop.class.order_product');
						$this->db->setQuery('SELECT * FROM '.hikamarket::table('shop.order_product').' WHERE order_id = '.(int)$order_id);
						$order_products = $this->db->loadObjectList('order_product_id');
						foreach($data['order']['warehouses'] as $pid => $w) {
							if(isset($order_products[$pid])) {
								$p = $order_products[$pid];
								list($shipping_method, $shipping_id) = explode('_', $data['order']['shipping'][$w], 2);
								$p->order_product_shipping_id = $shipping_id . '@' . $w;
								$p->order_product_shipping_method = $shipping_method;
								$orderProductClass->update($p);
							}
						}
					}
				}
			}
			if(isset($data['order']['order_shipping_price'])) {
				$order->order_shipping_price = (float)hikamarket::toFloat(trim($data['order']['order_shipping_price']));
				$do = true;
			}
			if(isset($data['order']['order_shipping_tax'])) {
				$order->order_shipping_tax = (float)hikamarket::toFloat(trim($data['order']['order_shipping_tax']));
				$do = true;
			}

			if(!empty($data['order']['payment'])) {
				list($payment_method, $payment_id) = explode('_', $data['order']['payment'], 2);
				$order->order_payment_method = $payment_method;
				$order->order_payment_id = $payment_id;
				$do = true;
			}
			if(isset($data['order']['order_payment_price'])) {
				$order->order_payment_price = (float)hikamarket::toFloat(trim($data['order']['order_payment_price']));
				$do = true;
			}
			if(isset($data['order']['order_payment_tax'])) {
				$order->order_payment_tax = (float)hikashop_toFloat($data['order']['order_payment_tax']);
				$do = true;
			}
			if(isset($data['order']['order_payment_tax_namekey'])) {
				$order->order_payment_tax_namekey = $safeHtmlFilter->clean($data['order']['order_payment_tax_namekey'], 'string');
				$do = true;
			}

			if($do && !empty($history_data)) {
				$order->history->history_reason = 'Order additional modified';
				$order->history->history_data = implode('<br/>', $history_data);
			}
		}

		$currentTask = 'customfields';
		$validTasks = array('customfields', 'additional');
		if( (empty($task) || in_array($task, $validTasks)) && !empty($data[$currentTask]) && !isset($forbidden[$currentTask]) && (!$acl || hikamarket::acl('order/edit/'.$currentTask)) ) {

			$old = null;
			$orderFields = $fieldsClass->getInput(array('orderfields','order'), $old, true, 'data', false, 'backend');
			if(!empty($orderFields)) {
				$do = true;
				foreach($orderFields as $key => $value) {
					$order->$key = $value;
				}
			}
		}

		$currentTask = 'customer';
		if( (empty($task) || $task == $currentTask) && !isset($forbidden[$currentTask]) && (!$acl || hikamarket::acl('order/edit/'.$currentTask)) ) {
			$order_user_id = (int)$data['order']['order_user_id'];
			if($order_user_id > 0) {
				$order->order_user_id = $order_user_id;
				$do = true;

				$set_address = hikaInput::get()->getInt('set_user_address', 0);
				if($set_address) {
					$this->db->setQuery('SELECT address_id FROM '.hikamarket::table('shop.address').' WHERE address_user_id = '. (int)$order_user_id . ' AND address_published = 1 ORDER BY address_default DESC, address_id ASC LIMIT 1');
					$address_id = $this->db->loadResult();
					if($address_id)
						$order->order_billing_address_id = (int)$address_id;
				}
			}
		}

		$currentTask = 'products';
		if( (empty($task) || $task == $currentTask) && !empty($data[$currentTask]) && !isset($forbidden[$currentTask]) && (!$acl || hikamarket::acl('order/edit/'.$currentTask)) ) {
			$orderProductClass = hikamarket::get('shop.class.order_product');
			$productData = $data['order']['product'];
			if(isset($productData['order_id'])) {
				$product = new stdClass();
				foreach($productData as $key => $value) {
					hikamarket::secureField($key);
					$product->$key = $safeHtmlFilter->clean($value, 'string');
				}

				if($order->order_type == 'sale') {
					$product_id = (int)$productData['product_id'];
					$order_product_id = null;
					if(isset($productData['order_product_id']))
						$order_product_id = (int)$productData['order_product_id'];
					$order_product_vendor_price = (float)hikamarket::toFloat(trim($productData['order_product_vendor_price']));
					$order_product_vendor_id = null;
					if(isset($productData['order_product_vendor_id']))
						$order_product_vendor_id = (int)trim($productData['order_product_vendor_id']);
					$order_product_quantity = null;
					if(isset($data['order']['product']['order_product_quantity']))
						$order_product_quantity = (int)trim($data['order']['product']['order_product_quantity']);

					if(empty($order->hikamarket))
						$order->hikamarket = new stdClass();
					$order->hikamarket->products = array(
						$order_product_id => array(
							'product_id' => $product_id,
							'order_product_id' => $order_product_id,
							'vendor_id' => $order_product_vendor_id,
							'vendor_price' => $order_product_vendor_price,
						)
					);
					if($order_product_quantity !== null)
						$order->hikamarket->products[$order_product_id]['order_product_quantity'] = $order_product_quantity;
					unset($product->order_product_vendor_id);
					unset($product->order_product_vendor_price);
				}

				$product->order_id = (int)$order_id;
				$orderProductClass->update($product);
			} else {
				foreach($productData as $data) {
					$product = new stdClass();
					foreach($data as $key => $value) {
						hikamarket::secureField($key);
						$product->$key = $safeHtmlFilter->clean(strip_tags($value), 'string');
					}
					$product->order_id = (int)$order_id;
					$orderProductClass->update($product);
				}
			}
			$orderClass->recalculateFullPrice($order);
			$do = true;
		}

		if(!empty($task) && $task == 'product_delete' && !isset($forbidden['product_delete']) && (!$acl || hikamarket::acl('order/edit/products')) ) {
			$order_product_id = hikaInput::get()->getInt('order_product_id', 0);
			if($order_product_id > 0) {
				$orderProductClass = hikamarket::get('shop.class.order_product');
				$order_product = $orderProductClass->get($order_product_id);
				if(!empty($order_product) && $order_product->order_id == $order_id) {
					$order_product->order_product_quantity = 0;
					$orderProductClass->update($order_product);

					$order->history->history_reason = 'Delete order product';
					$order->history->history_data = JText::sprintf('HIKAM_ORDER_PRODUCT_REMOVED', $order_product->order_product_name, $order_product->product_id);

					$orderClass->recalculateFullPrice($order);
					$do = true;
				}
			}
		}

		if(!$do)
			return false;

		if(!empty($data['history']['store_data'])) {
			if(isset($data['history']['msg']))
				$order->history->history_data = $safeHtmlFilter->clean($data['history']['msg'], 'string');
			else
				$order->history->history_data = $safeHtmlFilter->clean(@$data['history']['history_data'], 'string');
		}
		$result = $orderClass->save($order);

		if($result && $order->order_type == 'subsale' && $oldOrder->order_status != $order->order_status) {
			$shopConfig = hikamarket::config(false);
			$admin_notify_orders = explode(',', $config->get('admin_notify_subsale', 'cancelled,refunded'));
			if(in_array($order->order_status, $admin_notify_orders)) {

				$mailClass = hikamarket::get('class.mail');
				$vendorClass = hikamarket::get('class.vendor');
				$mainVendor = $vendorClass->get(1);
				if(empty($mainVendor->vendor_email))
					$mainVendor->vendor_email = $shopConfig->get('payment_notification_email', '');
				if(empty($mainVendor->vendor_email))
					$mainVendor->vendor_email = $shopConfig->get('order_creation_notification_email', '');

				if(!isset($order->hikamarket))
					$order->hikamarket = new stdClass();
				$order->hikamarket->vendor = $mainVendor;
				$mailClass->sendVendorOrderEmail($order);
			}
		}

		return $result;
	}
}
