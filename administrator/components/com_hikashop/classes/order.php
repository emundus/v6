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
class hikashopOrderClass extends hikashopClass {
	var $tables = array('order_product','order');
	var $pkeys = array('order_id','order_id');
	var $mail_success = true;
	var $sendEmailAfterOrderCreation = true;

	public function addressUsed($address_id, $order_id = 0, $type = '') {
		$query = 'SELECT order_id FROM '.hikashop_table('order') . ' WHERE ' .
			' (order_billing_address_id = '.(int)$address_id.' OR order_shipping_address_id = '.(int)$address_id.')';

		if(!empty($order_id) && !empty($type) && in_array($type, array('shipping', 'billing'))) {
			$query .= ' AND (order_id != ' . (int)$order_id . ' OR order_'.($type == 'shipping' ? 'billing' : 'shipping').'_address_id = ' . (int)$address_id . ')';
		}
		$this->database->setQuery($query, 0, 1);
		return (bool)$this->database->loadResult();
	}

	public function save(&$order) {
		$config =& hikashop_config();

		$new = empty($order->order_id);

		if($new) {
			if(!is_object($order))
				$order = new stdClass();

			$order->order_created = time();
			if(empty($order->order_type))
				$order->order_type = 'sale';

			if($config->get('order_ip', 1))
				$order->order_ip = hikashop_getIP();
			$order->old = new stdClass();

			if(empty($order->order_status)) {
				$order->order_status = $config->get('order_created_status','pending');
			}
			if(empty($order->order_currency_id)) {
				$order->order_currency_id = hikashop_getCurrency();
			}
			if(defined('MULTISITES_ID')) {
				$order->order_site_id = MULTISITES_ID;
			}
		} else if(empty($order->old)) {
			$order->old = $this->get($order->order_id);
		}

		$order->order_modified = time();

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();

		$order_type = '';
		if(!empty($order->old->order_type)) $order_type = $order->old->order_type;
		if(!empty($order->order_type)) $order_type = $order->order_type;

		$recalculate = false;
		$recalculate_dimensions = false;
		if(!empty($order->product)) {
			$do = true;
			$app->triggerEvent('onBeforeOrderProductsUpdate', array(&$order, &$do) );
			if(!$do)
				return false;

			$orderProductClass = hikashop_get('class.order_product');
			if(is_array($order->product)) {
				foreach($order->product as &$product) {
					$orderProductClass->update($product);
				}
				unset($product);
			} else {
				$orderProductClass->update($order->product);
			}
			$recalculate = true;
			$recalculate_dimensions = true;
		}

		if(!$new && (isset($order->order_shipping_price) || isset($order->order_payment_price) || isset($order->order_discount_price))) {
			if(isset($order->order_shipping_tax_namekey) || isset($order->order_discount_tax_namekey) || isset($order->order_payment_tax_namekey)) {
				if(!empty($order->old->order_tax_info)) {
					$order->order_tax_info = $order->old->order_tax_info;
					foreach($order->order_tax_info as $k => $tax) {
						if(isset($order->order_shipping_tax_namekey) && $tax->tax_namekey == $order->order_shipping_tax_namekey) {
							$order->order_tax_info[$k]->tax_amount_for_shipping = @$order->order_shipping_tax;
							unset($order->order_shipping_tax_namekey);
						} elseif(isset($order->order_tax_info[$k]->tax_amount_for_shipping)) {
							unset($order->order_tax_info[$k]->tax_amount_for_shipping);
						}

						if(isset($order->order_payment_tax_namekey) && $tax->tax_namekey == $order->order_payment_tax_namekey) {
							$order->order_tax_info[$k]->tax_amount_for_payment = @$order->order_payment_tax;
							unset($order->order_payment_tax_namekey);
						} elseif(isset($order->order_tax_info[$k]->tax_amount_for_payment)) {
							unset($order->order_tax_info[$k]->tax_amount_for_payment);
						}

						if(isset($order->order_discount_tax_namekey) && $tax->tax_namekey == $order->order_discount_tax_namekey) {
							$order->order_tax_info[$k]->tax_amount_for_coupon = @$order->order_discount_tax;
							unset($order->order_discount_tax_namekey);
						} elseif(isset($order->order_tax_info[$k]->tax_amount_for_coupon)) {
							unset($order->order_tax_info[$k]->tax_amount_for_coupon);
						}
					}
				}

				if(isset($order->order_shipping_tax_namekey) && is_string($order->order_shipping_tax_namekey)) {
					if(!isset($order->order_tax_info[$order->order_shipping_tax_namekey])) {
						$order->order_tax_info[$order->order_shipping_tax_namekey] = new stdClass();
						$order->order_tax_info[$order->order_shipping_tax_namekey]->tax_namekey = $order->order_shipping_tax_namekey;
					}
					$order->order_tax_info[$order->order_shipping_tax_namekey]->tax_amount_for_shipping = @$order->order_shipping_tax;
				} else if(isset($order->order_shipping_tax_namekey) && is_array($order->order_shipping_tax_namekey)) {
					foreach($order->order_shipping_tax_namekey as $namekey => $value) {
						if(!isset($order->order_tax_info[$namekey])) {
							$order->order_tax_info[$namekey] = new stdClass();
							$order->order_tax_info[$namekey]->tax_namekey = $namekey;
						}
						$order->order_tax_info[$namekey]->tax_amount_for_shipping = $value;
					}
				}

				if(isset($order->order_payment_tax_namekey)) {
					if(!isset($order->order_tax_info[$order->order_payment_tax_namekey])) {
						$order->order_tax_info[$order->order_payment_tax_namekey] = new stdClass();
						$order->order_tax_info[$order->order_payment_tax_namekey]->tax_namekey = $order->order_payment_tax_namekey;
					}
					$order->order_tax_info[$order->order_payment_tax_namekey]->tax_amount_for_payment = @$order->order_payment_tax;
				}
				if(isset($order->order_discount_tax_namekey)) {
					if(!isset($order->order_tax_info[$order->order_discount_tax_namekey])) {
						$order->order_tax_info[$order->order_discount_tax_namekey] = new stdClass();
						$order->order_tax_info[$order->order_discount_tax_namekey]->tax_namekey = $order->order_discount_tax_namekey;
					}
					$order->order_tax_info[$order->order_discount_tax_namekey]->tax_amount_for_coupon = @$order->order_discount_tax;
				}
			}
			$recalculate = true;
		}
		unset($order->order_shipping_tax_namekey);
		unset($order->order_payment_tax_namekey);
		unset($order->order_discount_tax_namekey);
		unset($order->total_number_of_products);

		if($new && empty($order->order_lang)) {
			$lang = JFactory::getLanguage();
			$order->order_lang = $lang->getTag();
		}

		if($new && $order->order_type == 'sale' && empty($order->order_token)) {
			jimport('joomla.user.helper');
			$order->order_token = JUserHelper::genRandomPassword();
		}

		if($recalculate)
			$this->recalculateFullPrice($order);

		if(!empty($order->cart->products))
			$this->recalculateDimensions($order, $order->cart->products);
		elseif($recalculate_dimensions)
			$this->recalculateDimensions($order);

		$do = true;
		if($new) {
			$app->triggerEvent('onBeforeOrderCreate', array(&$order, &$do) );
		} else {
			$app->triggerEvent('onBeforeOrderUpdate', array(&$order, &$do) );
		}

		if(!$do)
			return false;

		$unsets = array('value', 'order_current_lgid', 'order_current_locale', 'mail_status');
		foreach($unsets as $unset) {
			if(isset($order->$unset))
				unset($order->$unset);
		}

		$serializes = array('order_tax_info', 'order_currency_info', 'order_shipping_params', 'order_payment_params');
		foreach($serializes as $serialize) {
			if(isset($order->$serialize) && !is_string($order->$serialize))
				$order->$serialize = serialize($order->$serialize);
		}

		if(isset($order->order_status) && $order_type == 'sale') {
			$this->capturePayment($order, 0);
		}

		if(empty($order->old))
			unset($order->old);

		if(isset($order->order_url))
			unset($order->order_url);
		if(isset($order->mail_params))
			unset($order->mail_params);

		$order->order_id = parent::save($order);

		foreach($serializes as $serialize) {
			if(isset($order->$serialize) && is_string($order->$serialize))
				$order->$serialize = hikashop_unserialize($order->$serialize);
		}

		if(empty($order->order_id))
			return $order->order_id;

		$already_invoice_number = ((!empty($order->order_invoice_number) && !empty($order->order_invoice_created)) || (!empty($order->old->order_invoice_number) && !empty($order->old->order_invoice_created)));
		if(!empty($order->order_status) && empty($order->order_invoice_id) && empty($order->old->order_invoice_id) && $order_type == 'sale' && !$already_invoice_number) {
			$valid_statuses = explode(',', $config->get('invoice_order_statuses','confirmed,shipped'));
			if(empty($valid_statuses))
				$valid_statuses = array('confirmed','shipped');
			$excludeFreeOrders = $config->get('invoice_exclude_free_orders', 0);
			if(isset($order->order_full_price))
				$total = $order->order_full_price;
			elseif(isset($order->old->order_full_price))
				$total = $order->old->order_full_price;
			else
				$total = 0; //new order for example
			if(in_array($order->order_status, $valid_statuses) && ($total > 0 || !$excludeFreeOrders)) {
				$query = 'SELECT MAX(a.order_invoice_id)+1 FROM '.hikashop_table('order').' AS a WHERE a.order_type = \'sale\'';
				$resetFrequency = $config->get('invoice_reset_frequency', '');
				if(!empty($resetFrequency)) {
					$y = (int)date('Y');
					$m = 1;
					$d = 1;
					if($resetFrequency == 'month')
						$m = (int)date('m');

					if(strpos($resetFrequency, '/') !== false) {
						list($d,$m) = explode('/', $resetFrequency, 2);
						if($d == '*')
							$d = (int)date('d');
						else
							$d = (int)$d;

						if($m == '*')
							$m = (int)date('m');
						else
							$m = (int)$m;

						if($d <= 0) $d = 1;
						if($m <= 0) $m = 1;
					}

					$query .= ' AND a.order_invoice_created >= '.mktime(0, 0, 0, $m, $d, $y);
				}
				$this->database->setQuery($query);
				$order->order_invoice_id = (int)$this->database->loadResult();

				$start_order_invoice_id = (int)$config->get('start_order_invoice_id', 1);
				if($start_order_invoice_id <= 0) $start_order_invoice_id = 1;
				if(empty($order->order_invoice_id) || $order->order_invoice_id <= 1)
					$order->order_invoice_id = $start_order_invoice_id;

				$order->order_invoice_number = hikashop_encode($order, 'invoice');
				$order->order_invoice_created = time();

				$updateOrder = new stdClass();
				$updateOrder->order_id = $order->order_id;
				$updateOrder->order_invoice_id = $order->order_invoice_id;
				$updateOrder->order_invoice_number = $order->order_invoice_number;
				$updateOrder->order_invoice_created = $order->order_invoice_created;
				parent::save($updateOrder);
			}
		}


		if($new && empty($order->order_number)) {
			$order->order_number = hikashop_encode($order);

			$updateOrder = new stdClass();
			$updateOrder->order_id = $order->order_id;
			$updateOrder->order_number = $order->order_number;

			if(empty($order->order_invoice_id)) {
				$valid_statuses = explode(',', $config->get('invoice_order_statuses','confirmed,shipped'));
				if(empty($valid_statuses))
					$valid_statuses = array('confirmed','shipped');
				$created_status = $config->get('order_created_status', 'created');
				if(in_array($created_status, $valid_statuses)) {
					$order->order_invoice_id = $order->order_id;
					$order->order_invoice_number = $order->order_number;
					$order->order_invoice_created = time();
					$updateOrder->order_invoice_id = $order->order_invoice_id;
					$updateOrder->order_invoice_number = $order->order_invoice_number;
				}
			}

			parent::save($updateOrder);
		}

		$orderProductClass = hikashop_get('class.order_product');

		$stock_statuses = $config->get('stock_order_statuses', '');
		if(empty($stock_statuses))
			$stock_statuses = $config->get('invoice_order_statuses', 'confirmed,shipped');
		if(empty($stock_statuses))
			$stock_statuses = 'confirmed,shipped';
		$stock_statuses = explode(',', $stock_statuses);

		if(!empty($order->cart->products)) {
			$this->recalculateDimensions($order, $order->cart->products);
			foreach($order->cart->products as $k => $p) {
				$order->cart->products[$k]->order_id = $order->order_id;

				if(isset($order->cart->full_products[ (int)$p->cart_product_id ])) {
					$order->cart->products[$k]->product_parent_id = $order->cart->full_products[ (int)$p->cart_product_id ]->product_parent_id;
					$order->cart->products[$k]->product_type = $order->cart->full_products[ (int)$p->cart_product_id ]->product_type;
				}
			}

			if($config->get('update_stock_after_confirm') && !in_array($order->order_status, $stock_statuses)) {
				foreach($order->cart->products as $k => $product) {
					$order->cart->products[$k]->no_update_qty = true;
				}
			}

			if(!empty($order->cart->additional)) {
				foreach($order->cart->additional as $k => $p) {
					$order->cart->additional[$k]->product_id = 0;
					$order->cart->additional[$k]->order_product_quantity = 0;
					$order->cart->additional[$k]->order_product_code = 'order additional';
					$order->cart->additional[$k]->order_product_tax = 0;
					if(!empty($p->name))
						$order->cart->additional[$k]->order_product_name = $p->name;
					if(!empty($p->value))
						$order->cart->additional[$k]->order_product_options = $p->value;
					if(!empty($p->price_value))
						$order->cart->additional[$k]->order_product_price = $p->price_value;
					if(!empty($p->price_value_with_tax) && is_numeric($p->price_value_with_tax) && is_numeric($p->price_value))
						$order->cart->additional[$k]->order_product_tax = $p->price_value_with_tax - $p->price_value;
					if(!empty($p->taxes))
						$order->cart->additional[$k]->order_product_tax_info = $p->taxes;

					$order->cart->additional[$k]->order_id = $order->order_id;
				}
				$orderProductClass->save($order->cart->additional);
			}

			$cart_order_products = $orderProductClass->save($order->cart->products);
			if(!empty($cart_order_products) && is_array($cart_order_products)) {
				$order->cart_order_products = $cart_order_products;

				if(!empty($order->order_payment_params->recurring) && !empty($order->order_payment_params->recurring['products'])) {
					$recurring_cart_products = $order->order_payment_params->recurring['products'];

					$recurring_order_products = array();
					foreach($recurring_cart_products as $k) {
						if(isset($order->cart_order_products[$k]))
							$recurring_order_products[] = $order->cart_order_products[$k];
					}
					if(!empty($recurring_order_products)) {
						$order->order_payment_params->recurring['products'] = $recurring_order_products;
						$query = 'UPDATE '.hikashop_table('order').
							' SET order_payment_params = '.$this->database->Quote(serialize($order->order_payment_params)).
							' WHERE order_id = '.(int)$order->order_id;
						$this->database->setQuery($query);
						$this->database->execute();
					}
				}
			}

			if($config->get('update_stock_after_confirm') && !in_array($order->order_status, $stock_statuses)){
				foreach($order->cart->products as $k => $product){
					unset($order->cart->products[$k]->no_update_qty);
				}
			}

			if(!empty($order->order_discount_code) && $order_type == 'sale') {
				$query = 'UPDATE '.hikashop_table('discount').
					' SET discount_used_times = discount_used_times + 1 '.
					' WHERE discount_code='.$this->database->Quote($order->order_discount_code).' AND discount_type=\'coupon\' LIMIT 1';
				$this->database->setQuery($query);
				$this->database->execute();
			}
		} elseif(isset($order->order_status) && !empty($order->old->order_status) && $order_type == 'sale') {

			$update_stock_after_confirm = $config->get('update_stock_after_confirm');
			$order_created_status = $config->get('order_created_status', 'created');
			if($new){
				$stock_statuses[] = $order_created_status;
			}
			$cancelled_statuses = $config->get('cancelled_order_status', 'cancelled,refunded');
			if(empty($cancelled_statuses))
				$cancelled_statuses = 'cancelled,refunded';
			$cancelled_statuses = explode(',', $cancelled_statuses);

			if(!in_array($order->old->order_status, $cancelled_statuses) && in_array($order->order_status, $cancelled_statuses)) {
				if(!isset($order->order_discount_code)) {
					$code = @$order->old->order_discount_code;
				} else {
					$code = $order->order_discount_code;
				}
				$query = 'UPDATE '.hikashop_table('discount').' SET discount_used_times = discount_used_times - 1 WHERE discount_code='.$this->database->Quote($code).' AND discount_type=\'coupon\' LIMIT 1';
				$this->database->setQuery($query);
				$this->database->execute();
			}

			if($update_stock_after_confirm)
				$cancelled_statuses[] = $config->get('order_created_status', 'created');

			$updateQty = null;
			if($update_stock_after_confirm && in_array($order->order_status , $stock_statuses) && isset($order->old->order_status) && $order->old->order_status == $order_created_status) {
				$updateQty = 'minus';
			} elseif((in_array($order->old->order_status, $stock_statuses) || (!$update_stock_after_confirm && $order->old->order_status == $order_created_status) ) && in_array($order->order_status, $cancelled_statuses)) {
				$updateQty = 'plus';
			}

			if($updateQty !== null) {
				$this->loadProducts($order);
				if(!empty($order->products)) {
					foreach($order->products as $product) {
						$product->change = $updateQty;
						$orderProductClass->update($product);
						unset($product->change);
					}
				}
			}
		}

		$historyClass = hikashop_get('class.history');

		if($new) {
			$send_email = $this->sendEmailAfterOrderCreation;
			$app->triggerEvent('onAfterOrderCreate', array(&$order, &$send_email));

			$historyClass->addRecord($order);

			if(!$send_email)
				return $order->order_id;
			$this->loadOrderNotification($order,'order_creation_notification');
			$mailClass = hikashop_get('class.mail');
			if(!empty($order->mail->dst_email)) {
				$mailClass->sendMail($order->mail);
				$mailClass = hikashop_get('class.mail');
			}

			$this->mail_success =& $mailClass->mail_success;
			$emails = $config->get('order_creation_notification_email');
			if(!empty($emails)) {
				if(!empty($order->customer)) {
					$user_email = $order->customer->user_email;
					$user_name = $order->customer->name;
				} else {
					$order->customer = new stdClass();
					$order->customer->user_id = $order->order_user_id;
				}
				$order->customer->user_email = explode(',',$emails);
				$order->customer->name= ' ';
				$this->loadOrderNotification($order,'order_admin_notification');
				$order->mail->subject = trim($order->mail->subject);
				if(empty($order->mail->subject)) {
					$order->mail->subject = JText::sprintf('NEW_ORDER_SUBJECT',$order->order_number,HIKASHOP_LIVE);
				}
				if(!empty($user_email)) {
					if(HIKASHOP_J40) {
						if(!is_array($user_email))
							$user_email = explode(',', $user_email);
						foreach($user_email as $e) {
							$mailClass->mailer->addReplyTo($e, $user_name);
						}
					} elseif(HIKASHOP_J30) {
						$mailClass->mailer->addReplyTo($user_email, $user_name);
					} else {
						$mailClass->mailer->addReplyTo(array($user_email, $user_name));
					}
				}
				if(!empty($order->mail->dst_email)) {
					$mailClass->sendMail($order->mail);
				}
				if(!empty($user_email)) {
					$order->customer->user_email = $user_email;
					$order->customer->name = $user_name;
				}
			}
		} else {
			$send_email = @$order->history->history_notified;
			$app->triggerEvent('onAfterOrderUpdate', array( &$order, &$send_email) );

			$historyClass->addRecord($order);

			if(!$send_email)
				return $order->order_id;

			if(empty($order->mail) && isset($order->order_status)) {
				$this->loadOrderNotification($order,'order_status_notification');
			} elseif(!empty($order->mail)) {
				$order->mail->data = &$order;
				$order->mail->mail_name = 'order_status_notification';
			}
			if(!empty($order->mail)) {
				$mailClass = hikashop_get('class.mail');
				if(!empty($order->mail->dst_email)) {
					$mailClass->sendMail($order->mail);
				}
				$this->mail_success =& $mailClass->mail_success;
			}
		}

		return $order->order_id;
	}

	public function capturePayment(&$order, $total = 0.0) {
		$order_type = isset($order->order_type) ? $order->order_type : @$order->order_type;
		if($order_type != 'sale')
			return false;

		if((float)$total == 0.0 && isset($order->order_status)) {
			$config = hikashop_config();
			$payment_capture_order_status = explode(',', $config->get('payment_capture_order_status', 'shipped'));
			foreach($payment_capture_order_status as &$p) {
				$p = trim($p);
			}
			unset($p);
			if(!in_array($order->order_status, $payment_capture_order_status))
				return false;
		}

		$order_payment_params = isset($order->order_payment_params) ? $order->order_payment_params : @$order->old->order_payment_params;
		if(is_string($order_payment_params) && !empty($order_payment_params))
			$order_payment_params = hikashop_unserialize($order_payment_params);

		if(empty($order_payment_params->payment_authorized))
			return false;

		$payment_method = @$order->old->order_payment_method;
		if(!empty($order->order_payment_method))
			$payment_method = $order->order_payment_method;

		$plugin = hikashop_import('hikashoppayment', $payment_method);
		if(empty($plugin) || !method_exists($plugin, 'onOrderPaymentCapture'))
			return false;

		$order_full_price = isset($order->order_full_price) ? $order->order_full_price : $order->old->order_full_price;
		$order_full_price = (float)hikashop_toFloat($order_full_price);
		$order_capture_price = empty($total) ? $order_full_price : $total;

		if(!empty($order_payment_params->payment_captured)) {
			if(!empty($order_payment_params->payment_captured_value))
				$order_capture_price -= (float)$order_payment_params->payment_captured_value;
			else
				$order_capture_price = 0;
		}
		if($order_capture_price <= 0)
			return false;

		$do = true;
		$max_capture = $order_capture_price;

		JPluginHelper::importPlugin( 'hikashop' );
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeOrderPaymentCapture', array(&$order, $order_full_price, &$order_capture_price, &$do) );
		if(!$do || $order_capture_price <= 0 || $order_capture_price > $max_capture)
			return false;

		$order->order_payment_params = $order_payment_params;

		$ret = $plugin->onOrderPaymentCapture($order, $order_capture_price);
		if(!$ret)
			return false;

		$order->order_payment_params->payment_captured = true;
		if(!empty($order->order_payment_params->payment_captured_value) && ($order->order_payment_params->payment_captured_value + $order_capture_price <= $order_full_price))
			unset($order->order_payment_params->payment_captured_value);
		else if(empty($order->order_payment_params->payment_captured_value) && $order_capture_price != $order_full_price)
			$order->order_payment_params->payment_captured_value = $order_capture_price;

		return true;
	}

	public function createFromCart($cart_id, $options = array()) {

		if(is_numeric($cart_id)) {
			$cartClass = hikashop_get('class.cart');
			$cart = $cartClass->getFullCart($cart_id);
		} elseif(is_object($cart_id)) {
			$cart =& $cart_id;
		} else {
			return false;
		}

		if(empty($cart) || $cart->cart_type == 'wishlist')
			return false;

		if(!empty($cart->messages))
			return false;

		$config = hikashop_config();
		$currencyClass = hikashop_get('class.currency');


		$currencies = null;
		$currencies = $currencyClass->getCurrencies((int)$cart->full_total->prices[0]->price_currency_id, $currencies);
		$currency = $currencies[(int)$cart->full_total->prices[0]->price_currency_id];
		unset($currencies);

		$order = new stdClass();
		$order->order_user_id = (int)$cart->user_id;
		$order->order_type = 'sale';
		$order->order_status = $config->get('order_created_status', 'created');

		$entries = null;
		if(hikashop_level(2)){
			if(!empty($cart->cart_fields) && is_string($cart->cart_fields))
					$cart->cart_fields = json_decode($cart->cart_fields);
			if(!empty($cart->cart_fields->_entries)) {
				$entries = hikashop_copy($cart->cart_fields->_entries);
			}
		}

		$order->cart =& $cart;

		$order->order_billing_address_id = $cart->cart_billing_address_id;
		if(!empty($cart->usable_methods->shipping) || !empty($cart->package['weight']['value']) || $config->get('force_shipping', 0)) {
			$order->order_shipping_address_id = (int)$cart->cart_shipping_address_ids;
			if(!empty($order->order_shipping_address_id)) {
				$cartClass = hikashop_get('class.cart');
				$cartClass->loadAddress($order->cart, $order->order_shipping_address_id, 'object', 'shipping');
			}
		}
		$order->order_discount_code = @$cart->coupon->discount_code;

		$order->order_currency_id = (int)$cart->full_total->prices[0]->price_currency_id;
		$order->order_full_price = $cart->full_total->prices[0]->price_value_with_tax;
		$order->order_tax_info = @$cart->full_total->prices[0]->taxes;

		$order->order_currency_info = new stdClass();
		$order->order_currency_info->currency_code = $currency->currency_code;
		$order->order_currency_info->currency_rate = $currency->currency_rate;
		$order->order_currency_info->currency_percent_fee = $currency->currency_percent_fee;
		$order->order_currency_info->currency_modified = $currency->currency_modified;

		$order->order_payment_price = 0.0;
		$order->order_payment_tax = 0.0;

		$order->order_shipping_price = 0.0;
		$order->order_shipping_tax = 0.0;
		$order->order_shipping_params = null;

		$order->order_discount_tax = 0.0;
		$order->order_discount_price = 0.0;

		$order->order_shipping_id = '';
		if(!empty($cart->cart_shipping_ids))
			$order->order_shipping_id = $cart->cart_shipping_ids;
		if(!empty($order->order_shipping_id) && is_array($order->order_shipping_id))
			$order->order_shipping_id = implode(';', $order->order_shipping_id);

		$order->order_shipping_method = ''; // Will be set below if there is some shipping
		if(empty($options['skipPayment'])) {
			$order->order_payment_id = $cart->cart_payment_id;
			$order->order_payment_method = ''; // Will be set below if there is some payment
		}

		$order->history = new stdClass();
		$order->history->history_reason = JText::_('ORDER_CREATED');
		$order->history->history_notified = 0;
		$order->history->history_type = 'creation';
		if(!empty($options['historyData'])) {
			$order->history->history_data = $options['historyData'];
		}

		$cart->full_products =& $cart->products;
		unset($cart->products);
		$cart->products = array();
		foreach($cart->full_products as &$product) {
			if((int)$product->cart_product_quantity <= 0)
				continue;

			$orderProduct = new stdClass();
			$orderProduct->product_id = (int)$product->product_id;
			$orderProduct->order_product_quantity = (int)$product->cart_product_quantity;
			$orderProduct->cart_product_id = (int)$product->cart_product_id;
			$orderProduct->cart_product_option_parent_id = (int)$product->cart_product_option_parent_id;
			$orderProduct->order_product_code = $product->product_code;
			$orderProduct->order_product_price = @$product->prices[0]->unit_price->price_value;
			$orderProduct->order_product_wishlist_id = (int)@$product->cart_product_wishlist_id;
			$orderProduct->order_product_wishlist_product_id = (int)@$product->cart_product_wishlist_product_id;
			$orderProduct->product_subscription_id = (int)@$product->product_subscription_id;
			if(!empty($product->product_name_original))
				$orderProduct->order_product_name = $product->product_name_original;
			else
				$orderProduct->order_product_name = $product->product_name;

			$tax = 0;
			if(!empty($product->prices[0]->unit_price->price_value_with_tax) && bccomp(sprintf('%F',$product->prices[0]->unit_price->price_value_with_tax),0,5))
				$tax = $product->prices[0]->unit_price->price_value_with_tax-$product->prices[0]->unit_price->price_value;
			$orderProduct->order_product_tax = $tax;

			$characteristics = '';
			if(!empty($product->characteristics))
				$characteristics = serialize($product->characteristics);
			$orderProduct->order_product_options = $characteristics;

			$orderProduct->order_product_price_before_discount = @$product->prices[0]->unit_price->price_value;
			$orderProduct->order_product_tax_before_discount = $orderProduct->order_product_tax;
			$orderProduct->order_product_discount_code = '';
			$orderProduct->order_product_discount_info = '';

			if(!empty($product->discount)) {
				$orderProduct->discount = hikashop_copy($product->discount);
				$orderProduct->discount->price_value_without_discount = $product->prices[0]->unit_price->price_value_without_discount;
				$orderProduct->discount->price_value_without_discount_with_tax = @$product->prices[0]->unit_price->price_value_without_discount_with_tax;
				$orderProduct->discount->taxes_without_discount = @$product->prices[0]->unit_price->taxes_without_discount;

				$orderProduct->order_product_price_before_discount = $product->prices[0]->unit_price->price_value_without_discount;
				$orderProduct->order_product_tax_before_discount = $orderProduct->discount->taxes_without_discount;
				$orderProduct->order_product_discount_code = $product->discount->discount_code;
				$orderProduct->order_product_discount_info = hikashop_copy($product->discount);
			}

			if(!empty($cart->item_fields)) {
				foreach($cart->item_fields as $field) {
					$namekey = $field->field_namekey;
					if(isset($product->$namekey))
						$orderProduct->$namekey = $product->$namekey;
				}
			}

			if(isset($product->prices[0]->unit_price->taxes))
				$orderProduct->order_product_tax_info = $product->prices[0]->unit_price->taxes;

			if(isset($product->files))
				$orderProduct->files =& $product->files;

			if(!empty($cart->shipping) && !empty($order->order_shipping_id)) {
				$shippings = explode(';', $order->order_shipping_id);
				$shipping_done = false;
				foreach($cart->shipping_groups as $group_key => $group_products) {
					foreach($group_products->products as $group_product) {
						if((int)$group_product->cart_product_id == (int)$product->cart_product_id) {
							foreach( $shippings as $shipping) {
								list($shipping_id, $shipping_group_key) = explode('@', $shipping, 2);
								if($shipping_group_key == $group_key) {
									foreach($cart->shipping as $cart_shipping) {
										if($cart_shipping->shipping_id == $shipping_id) {
											$orderProduct->order_product_shipping_id = $shipping;
											$orderProduct->order_product_shipping_method = $cart_shipping->shipping_type;
											$shipping_done = true;
											break;
										}
									}
									if($shipping_done)
										break;
								}
							}
							if($shipping_done)
								break;
						}
					}
					if($shipping_done)
						break;
				}
			}

			if(isset($product->product_weight_orig) && isset($product->product_weight_unit_orig)) {
				$orderProduct->order_product_weight = $product->product_weight_orig;
				$orderProduct->order_product_weight_unit = $product->product_weight_unit_orig;
			} else {
				$orderProduct->order_product_weight = $product->product_weight;
				$orderProduct->order_product_weight_unit = $product->product_weight_unit;
			}

			$orderProduct->order_product_width = $product->product_width;
			$orderProduct->order_product_length = $product->product_length;
			$orderProduct->order_product_height = $product->product_height;
			$orderProduct->order_product_dimension_unit = $product->product_dimension_unit;

			$cart->products[] = $orderProduct;
		}
		unset($product);

		if(empty($options['skipPayment'])) {
			if(!empty($cart->payment) && !empty($cart->payment->payment_id)) {
				$order->order_payment_method = $cart->payment->payment_type;
			}
			if(!empty($cart->payment) && !empty($cart->payment->payment_price_with_tax) && !empty($cart->payment->payment_price)) {
				$order->order_payment_price = $cart->payment->payment_price_with_tax;
				$order->order_payment_tax = $cart->payment->payment_price_with_tax - $cart->payment->payment_price;
				if(!empty($cart->payment->taxes)) {
					foreach($cart->payment->taxes as $tax) {
						if(isset($order->order_tax_info[$tax->tax_namekey])) {
							$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_payment = $tax->tax_amount;
						} elseif(!empty($order->order_tax_info[$tax->tax_namekey]->tax_amount) && $order->order_tax_info[$tax->tax_namekey]->tax_amount>0) {
							$order->order_tax_info[$tax->tax_namekey] = $tax;
							$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_payment = $order->order_tax_info[$tax->tax_namekey]->tax_amount;
							$order->order_tax_info[$tax->tax_namekey]->tax_amount = 0;
						}
					}
				}
			}
		}

		if(!empty($cart->shipping)) {
			$order->order_shipping_params = new stdClass();
			$order->order_shipping_params->prices = array();

			foreach($cart->shipping as $cart_shipping) {
				$order->order_shipping_price += $cart_shipping->shipping_price_with_tax;

				$price_key = $cart_shipping->shipping_id;
				if(isset($cart_shipping->shipping_warehouse_id)) {
					if(is_string($cart_shipping->shipping_warehouse_id) || is_int($cart_shipping->shipping_warehouse_id)) {
						$price_key .= '@' . $cart_shipping->shipping_warehouse_id;
					} else {
						$price_key .= '@';
						foreach($cart_shipping->shipping_warehouse_id as $k => $v) {
							$price_key .= $k . $v;
						}
					}
				}

				$order->order_shipping_params->prices[$price_key] = new stdClass();
				$order->order_shipping_params->prices[$price_key]->price_with_tax = $cart_shipping->shipping_price_with_tax;

				if(!empty($cart_shipping->shipping_price_with_tax) && !empty($cart_shipping->shipping_price)) {
					$order->order_shipping_tax += $cart_shipping->shipping_price_with_tax - $cart_shipping->shipping_price;
					$order->order_shipping_params->prices[$price_key]->tax = $cart_shipping->shipping_price_with_tax - $cart_shipping->shipping_price;

					if(!empty($cart_shipping->taxes)) {
						$order->order_shipping_params->prices[$price_key]->taxes = array();

						foreach($cart_shipping->taxes as $tax) {
							$order->order_shipping_params->prices[$price_key]->taxes[$tax->tax_namekey] = $tax->tax_amount;
							if(isset($order->order_tax_info[$tax->tax_namekey])) {
								if(empty($order->order_tax_info[$tax->tax_namekey]->tax_amount_for_shipping))
									$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_shipping = 0;
								$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_shipping += $tax->tax_amount;

								if(isset($tax->amount) && empty($order->order_tax_info[$tax->tax_namekey]->amount))
									$order->order_tax_info[$tax->tax_namekey]->amount = $tax->amount;

							} elseif(!empty($order->order_tax_info[$tax->tax_namekey]->tax_amount) && $order->order_tax_info[$tax->tax_namekey]->tax_amount>0) {
								$order->order_tax_info[$tax->tax_namekey] = $tax;
								$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_shipping = $order->order_tax_info[$tax->tax_namekey]->tax_amount;
								$order->order_tax_info[$tax->tax_namekey]->tax_amount = 0;
							}
						}
					}
				}
			}

			if(count($cart->shipping) == 1) {
				$order->order_shipping_method = $cart->shipping[0]->shipping_type;

				if(strpos($order->order_shipping_id, '-') === false)
					$order->order_shipping_id = (int)$order->order_shipping_id;
				elseif(strpos($order->order_shipping_id, '@') !== false)
					$order->order_shipping_id = substr($order->order_shipping_id, 0, strpos($order->order_shipping_id, '@'));
			}
		}


		if(!empty($cart->coupon) && isset($cart->coupon->discount_value)) {
			$order->order_discount_price = $cart->coupon->discount_value;
			if(!empty($cart->coupon->taxes)) {
				$order->order_discount_tax = $cart->coupon->discount_value - $cart->coupon->discount_value_without_tax;
				foreach($cart->coupon->taxes as $tax) {
					if(isset($order->order_tax_info[$tax->tax_namekey])) {
						$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_coupon = $tax->tax_amount;
					} else {
						$order->order_tax_info[$tax->tax_namekey] = $tax;
						$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_coupon = $order->order_tax_info[$tax->tax_namekey]->tax_amount;
						$order->order_tax_info[$tax->tax_namekey]->tax_amount = 0;
					}
				}
			}
		}
		if(hikashop_level(2) && !empty($cart->order_fields)) {
			foreach($cart->order_fields as $k => $v) {
				if(isset($cart->cart_fields->$k))
					$order->$k = $cart->cart_fields->$k;
			}
		}

		if(empty($options['skipPayment'])) {
			$paymentClass = hikashop_get('class.payment');
			$paymentClass->checkPaymentOptions($order);

			if(empty($order->paymentOptions) && !empty($cart->paymentOptions))
				$order->paymentOptions = array_merge($cart->paymentOptions);

			if(!empty($order->paymentOptions)) {
				if(isset($order->paymentOptions['recurring']['optional']))
					unset($order->paymentOptions['recurring']['optional']);

				foreach($order->paymentOptions as $k => $v) {
					if($v === false)
						continue;
					if(empty($order->order_payment_params))
						$order->order_payment_params = new stdClass();
					$order->order_payment_params->$k = $v;
				}
			}
		}

		if(is_numeric($cart_id)) {
			$cleanCart = $cartClass->getFullCart($cart_id);
			unset($cleanCart->products);
		}

		$ret = $this->save($order);

		if(empty($ret))
			return false;

		$order->order_id = (int)$ret;


		if(hikashop_level(2)){
			if(!empty($entries)) {
				$entryClass = hikashop_get('class.entry');
				foreach($entries as $entryData){
					$entryData->order_id = $order->order_id;
					$entryClass->save($entryData);
				}
			}
		}

		$pluginsClass = hikashop_get('class.plugins');
		$removeCart = false;

		if(is_numeric($cart_id)) {
			$cleanCart->products = $cart->products;
			$cart = $cleanCart;
		}

		if(!empty($order->cart->additional)) {
			foreach($order->cart->additional as $k => $p) {
				$order->cart->additional[$k]->product_id = 0;
				$order->cart->additional[$k]->order_product_quantity = 0;
				$order->cart->additional[$k]->order_product_code = 'order additional';
				$order->cart->additional[$k]->order_product_tax = 0;
				if(!empty($p->name))
					$order->cart->additional[$k]->order_product_name = $p->name;
				if(!empty($p->value))
					$order->cart->additional[$k]->order_product_options = $p->value;
				if(!empty($p->price_value))
					$order->cart->additional[$k]->order_product_price = $p->price_value;
				if(!empty($p->price_value_with_tax) && is_numeric($p->price_value_with_tax) && is_numeric($p->price_value))
					$order->cart->additional[$k]->order_product_tax = $p->price_value_with_tax - $p->price_value;
				if(!empty($p->taxes))
					$order->cart->additional[$k]->order_product_tax_info = $p->taxes;
			}
		}

		ob_start();
		if(!empty($cart->shipping)) {
			foreach($cart->shipping as $ship) {
				$data = hikashop_import('hikashopshipping', $ship->shipping_type);
				$data->onAfterOrderConfirm($order, $cart->shipping, $ship->shipping_id);
				if(!empty($data->removeCart))
					$removeCart = true;
			}
		}
		if(empty($options['skipPayment'])) {
			if(!empty($cart->payment)){
				$payment_method = array($cart->payment->payment_id => $cart->payment);

				$data = hikashop_import('hikashoppayment',$cart->payment->payment_type);
				$data->onAfterOrderConfirm($order, $payment_method, $cart->payment->payment_id);
				if(!empty($data->removeCart)){
					$removeCart = true;
				}
			}
		}
		hikaInput::get()->set('hikashop_plugins_html', ob_get_clean());

		if($removeCart) {
			if(empty($order->options))
				$order->options = new stdClass();
			$order->options->remove_cart = true;
		}

		return $order;
	}

	public function saveForm($task = '') {
		$do = false;
		$forbidden = array();

		$order_id = hikashop_getCID('order_id');
		$addressClass = hikashop_get('class.address');
		$fieldsClass = hikashop_get('class.field');

		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);

		$oldOrder = $this->get($order_id);
		$order = clone($oldOrder);
		$order->history = new stdClass();
		$data = hikaInput::get()->get('data', array(), 'array');
		$validTasksForCustomFields = array('customfields', 'additional');

		if(empty($order_id) || empty($order->order_id)) {
			$this->sendEmailAfterOrderCreation = false;
		} else {
			$order->history->history_notified = false;
		}

		$currentTask = 'billing_address';
		if( (empty($task) || $task == $currentTask) && !empty($data[$currentTask]) ) {
			$oldAddress = null;
			if(!empty($oldOrder->order_billing_address_id)) {
				$oldAddress = $addressClass->get($oldOrder->order_billing_address_id);
			}
			$billing_address = $fieldsClass->getInput(array($currentTask, 'billing_address'), $oldAddress);

			if(!empty($billing_address) && !empty($order_id)){
				$billing_address->address_id = $oldOrder->order_billing_address_id;
				$billing_address->address_user_id = $oldAddress->address_user_id;
				$result = $addressClass->save($billing_address, $order_id, 'billing');
				if($result){
					$order->order_billing_address_id = (int)$result;
					$do = true;
				}
			}else{
				hikaInput::get()->set('fail', 1);
			}
		}

		$currentTask = 'shipping_address';
		if( (empty($task) || $task == $currentTask) && !empty($data[$currentTask]) ) {
			$oldAddress = null;
			if(!empty($oldOrder->order_shipping_address_id)) {
				$oldAddress = $addressClass->get($oldOrder->order_shipping_address_id);
			}
			$shipping_address = $fieldsClass->getInput(array($currentTask, 'shipping_address'), $oldAddress);

			if(!empty($shipping_address) && !empty($order_id)){
				$shipping_address->address_id = $oldOrder->order_shipping_address_id;
				$shipping_address->address_user_id = $oldAddress->address_user_id;
				$result = $addressClass->save($shipping_address, $order_id, 'shipping');
				if($result){
					$order->order_shipping_address_id = (int)$result;
					$result = $this->save($order);
					$do = true;
				}
			}else{
				hikaInput::get()->set('fail', 1);
			}
		}

		$currentTask = 'general';
		if( (empty($task) || $task == $currentTask) && !empty($data[$currentTask]) ) {

			if(!empty($data['order']['order_status'])) {
				$order->order_status = $safeHtmlFilter->clean($data['order']['order_status'],'string');
				$do = true;
			}

			if(!empty($data['notify'])) {
				if(empty($order->history))
					$order->history = new stdClass();
				$order->history->history_notified = true;
			}
		}

		$currentTask = 'additional';
		if( (empty($task) || $task == $currentTask) && !empty($data[$currentTask]) && !isset($forbidden[$currentTask]) ) {

			if(isset($data['order']['order_discount_code'])) {
				$order->order_discount_code = $safeHtmlFilter->clean($data['order']['order_discount_code'],'string');
				$do = true;
			}
			if(isset($data['order']['order_discount_price'])) {
				$order->order_discount_price = (float)hikashop_toFloat($data['order']['order_discount_price']);
				$do = true;
			}
			if(isset($data['order']['order_discount_tax'])) {
				$order->order_discount_tax = (float)hikashop_toFloat($data['order']['order_discount_tax']);
				$do = true;
			}
			if(isset($data['order']['order_discount_tax_namekey'])) {
				$order->order_discount_tax_namekey = $safeHtmlFilter->clean($data['order']['order_discount_tax_namekey'],'string');
				$do = true;
			}

			if(!empty($data['order']['shipping'])) {
				$order->order_shipping_params->prices = array();

				if(is_string($data['order']['shipping'])) {
					list($shipping_method, $shipping_id) = explode('_', $data['order']['shipping'], 2);
					$order->order_shipping_method = $safeHtmlFilter->clean($shipping_method,'string');
					$order->order_shipping_id = $safeHtmlFilter->clean($shipping_id,'string');
					$do = true;
				}

				if(is_array($data['order']['shipping'])) {
					$order->order_shipping_method = '';
					$shippings = array();

					foreach($data['order']['shipping'] as $shipping_group => $shipping_value) {
						list($shipping_method, $shipping_id) = explode('_', $shipping_value, 2);
						$n = $safeHtmlFilter->clean($shipping_id,'string') . '@' . $safeHtmlFilter->clean($shipping_group,'string');
						$shippings[] = $n;
						$order->order_shipping_params->prices[$n] = new stdClass();
						$order->order_shipping_params->prices[$n]->price_with_tax = @$data['order']['order_shipping_prices'][$shipping_group];
						$order->order_shipping_params->prices[$n]->tax = @$data['order']['order_shipping_taxs'][$shipping_group];
					}
					$order->order_shipping_id = implode(';', $shippings);
					$do = true;

					if(!empty($data['order']['warehouses'])) {
						$orderProductClass = hikashop_get('class.order_product');
						$db = JFactory::getDBO();
						$db->setQuery('SELECT * FROM '.hikashop_table('order_product').' WHERE order_id = '.(int)$order_id);
						$order_products = $db->loadObjectList('order_product_id');
						foreach($data['order']['warehouses'] as $pid => $w) {
							if(isset($order_products[$pid]) && isset($data['order']['shipping'][$w])) {
								$p = $order_products[$pid];
								list($shipping_method, $shipping_id) = explode('_', $data['order']['shipping'][$w], 2);
								$p->order_product_shipping_id = $safeHtmlFilter->clean($shipping_id,'string') . '@' . $safeHtmlFilter->clean($w,'string');
								$p->order_product_shipping_method = $safeHtmlFilter->clean($shipping_method,'string');
								$orderProductClass->update($p);
							}
						}
					}
				}
			}
			if(isset($data['order']['order_shipping_price'])) {
				$order->order_shipping_price = (float)hikashop_toFloat($data['order']['order_shipping_price']);
				$do = true;
			}
			if(isset($data['order']['order_shipping_tax'])) {
				$order->order_shipping_tax = (float)hikashop_toFloat($data['order']['order_shipping_tax']);
				$do = true;
			}
			if(isset($data['order']['order_shipping_tax_namekey'])) {
				$order->order_shipping_tax_namekey = $safeHtmlFilter->clean($data['order']['order_shipping_tax_namekey'], 'string');
				$do = true;
			}

			if(!empty($data['order']['payment'])) {
				list($payment_method, $payment_id) = explode('_', $data['order']['payment'], 2);
				$order->order_payment_method = $safeHtmlFilter->clean($payment_method,'string');
				$order->order_payment_id = (int)$safeHtmlFilter->clean($payment_id,'string');
				$do = true;
			}
			if(isset($data['order']['order_payment_price'])) {
				$order->order_payment_price = (float)hikashop_toFloat($data['order']['order_payment_price']);
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

			if(isset($data['order']['additional'])) {
				$task = 'products';
				$data['products'] = '1';
				$validTasksForCustomFields[] = $task;
				$do = true;
			}

			if(!empty($data['notify'])) {
				if(empty($order->history))
					$order->history = new stdClass();
				$order->history->history_notified = true;
			}
		}

		$currentTask = 'customfields';
		if( (empty($task) || in_array($task, $validTasksForCustomFields)) && !empty($data[$currentTask]) ) {

			$old = clone($order); 
			$this->loadProducts($old);
			$orderFields = $fieldsClass->getInput(array('orderfields','order'), $old, true, 'data', false, 'backend');
			if(!empty($orderFields)) {
				$do = true;
				foreach($orderFields as $key => $value) {
					if( !is_null($value) || (is_array($value) && count($value) > 0 ))
						$order->$key = $value;
				}
			}elseif($orderFields === false && $order->order_shipping_id == $oldOrder->order_shipping_id && $order->order_payment_id == $oldOrder->order_payment_id) {
				hikaInput::get()->set('fail', 1);
				return false;
			}
		}

		$currentTask = 'guest';
		if( (empty($task) || $task == $currentTask) ) {
			$email = hikaInput::get()->getString('email');
			if(empty($email)) {
				hikaInput::get()->set('fail', 1);
				return false;
			} else {
				$user = new stdClass();
				$user->user_email = $email;
				$class = hikashop_get('class.user');
				if($class->save($user)) {
					$data['order'] = array();
					$data['order']['order_user_id'] = $user->user_id;
					$task = 'customer';
				}
			}
		}
		$currentTask = 'customer';
		if( (empty($task) || $task == $currentTask) ) {
			$order_user_id = (int)$data['order']['order_user_id'];
			if($order_user_id > 0) {
				$order->order_user_id = $order_user_id;
				$do = true;

				$set_address = hikaInput::get()->getInt('set_user_address', 0);
				if($set_address) {
					$order->order_billing_address_id = hikaInput::get()->getInt('billing_address', 0);
					$order->order_shipping_address_id = hikaInput::get()->getInt('shipping_address', 0);
				}
			}
		}

		$currentTask = 'products';
		$config = hikashop_config();
		$createdStatus = $config->get('order_created_status', 'created');
		$noUpdateQty = 0;
		if($createdStatus == $order->order_status && $config->get('update_stock_after_confirm'))
			$noUpdateQty = 1;
		if( (empty($task) || $task == $currentTask) && !empty($data[$currentTask]) ) {
			$orderProductClass = hikashop_get('class.order_product');
			$productData = $data['order']['product'];

			if(isset($productData['many']) && $productData['many'] == true) {
				unset($productData['many']);
				$product = new stdClass();
				$order->product = array();
				foreach($productData as $singleProduct) {
					foreach($singleProduct as $key => $value) {
						hikashop_secureField($key);
						$product->$key = $safeHtmlFilter->clean($value, 'string');
					}
					if($noUpdateQty)
						$product->no_update_qty = true;
					$orderProductClass->update($product);
					$order->product[] = $product;
				}
			} else if(isset($productData['order_id'])) {
				$product = new stdClass();

				$fieldClass = hikashop_get('class.field');
				$oldData = null;
				if(!empty($data['order']['product']['order_product_id'])) {
					$oldData = $orderProductClass->get($data['order']['product']['order_product_id']);
				}

				$item_fields = $fieldClass->getFields('display:order_edit=1', $oldData, 'item', 'user&task=state');
				$ret = $fieldClass->_checkOneInput($item_fields, $productData, $product, 'item', $oldData);
				foreach($productData as $key => $value) {
					hikashop_secureField($key);
					if(isset($items_fields[$key]))
						continue;
					if(is_array($value))
						$value = implode(',', $value);
					$product->$key = $safeHtmlFilter->clean($value, 'string');
				}
				$product->order_id = (int)$order_id;
				if($noUpdateQty)
					$product->no_update_qty = true;
				$orderProductClass->update($product);
				$order->product = array( $product );
			} else {
				$order->product = array();
				foreach($productData as $p) {
					$product = new stdClass();
					foreach($p as $key => $value) {
						hikashop_secureField($key);
						$product->$key = $safeHtmlFilter->clean($value, 'string');
					}
					$product->order_id = (int)$order_id;
					if($noUpdateQty)
						$product->no_update_qty = true;
					$orderProductClass->update($product);

					$order->product[] = $product;
				}
			}
			$this->recalculateFullPrice($order);
			$do = true;
		}

		if(!empty($task) && $task == 'product_delete' ) {
			$order_product_id = hikaInput::get()->getInt('order_product_id', 0);
			if($order_product_id > 0) {
				$orderProductClass = hikashop_get('class.order_product');
				$order_product = $orderProductClass->get($order_product_id);
				if(!empty($order_product) && $order_product->order_id == $order_id) {
					$order_product->order_product_quantity = 0;
					if($noUpdateQty)
						$order_product->no_update_qty = true;
					$orderProductClass->update($order_product);
					$order->product = array($order_product);

					$this->recalculateFullPrice($order);
					$do = true;
				}
			}
		}

		if($do) {
			if(!empty($data['history']['store_data'])) {
				if(isset($data['history']['msg']))
					$order->history->history_data = $safeHtmlFilter->clean($data['history']['msg'], 'string');
				else
					$order->history->history_data = $safeHtmlFilter->clean(@$data['history']['history_data'], 'string');
			}
			if(!empty($data['history']['usermsg_send'])) {
				if(isset($data['history']['usermsg'])) {
					$order->usermsg = new stdClass();
					$order->usermsg->usermsg = $safeHtmlFilter->clean($data['history']['usermsg'], 'string');
				}
			}
			$result = $this->save($order);

			return $result;
		}
		return false;
	}

	public function recalculateDimensions(&$order, $products = null) {
		if(empty($products)) {
			$query = 'SELECT * FROM '.hikashop_table('order_product').' WHERE order_id = ' . (int)$order->order_id;
			$this->database->setQuery($query);
			$products = $this->database->loadObjectList();
		}

		if(empty($products) || !count($products))
			return;

		$volumeHelper = hikashop_get('helper.volume');
		$weightHelper = hikashop_get('helper.weight');

		$order->order_dimension_unit = null;
		$order->order_weight_unit = null;

		$order->order_volume = 0.0;
		$order->order_weight = 0.0;

		if(!empty($order->order_id)) {
			if(!isset($order->order_dimension_unit)  || !isset($order->order_weight_unit)){
				$dbOrder = $this->get($order->order_id);
				if(!isset($order->order_dimension_unit))
					$order->order_dimension_unit = $dbOrder->order_dimension_unit;
				if(!isset($order->order_weight_unit))
					$order->order_weight_unit = $dbOrder->order_weight_unit;
			}
		}

		foreach($products as $product) {
			if(empty($product->order_product_quantity))
				continue;

			if(!isset($order->order_dimension_unit) || is_null($order->order_dimension_unit))
				$order->order_dimension_unit = $product->order_product_dimension_unit;
			if(!isset($order->order_weight_unit) || is_null($order->order_weight_unit))
				$order->order_weight_unit = $product->order_product_weight_unit;

			if(bccomp(sprintf('%F',@$product->order_product_length), 0, 5) && bccomp(sprintf('%F',@$product->order_product_width), 0, 5) && bccomp(sprintf('%F',@$product->order_product_height), 0, 5)) {
				$product_volume = $product->order_product_length * $product->order_product_width * $product->order_product_height;
				$product_total_volume = $product_volume * $product->order_product_quantity;
				$product_total_volume = $volumeHelper->convert($product_total_volume, $product->order_product_dimension_unit, $order->order_dimension_unit);

				$order->order_volume += $product_total_volume;
			}

			if(bccomp(sprintf('%F',@$product->order_product_weight), 0, 5)) {
				$product_weight = $weightHelper->convert($product->order_product_weight, $product->order_product_weight_unit, $order->order_weight_unit);
				$order->order_weight += $product_weight * $product->order_product_quantity;
			}
		}
	}

	public function recalculateFullPrice(&$order, $products = null) {
		if(empty($products)) {
			$query = 'SELECT * FROM '.hikashop_table('order_product').' WHERE order_id = ' . (int)$order->order_id;
			$this->database->setQuery($query);
			$products = $this->database->loadObjectList();
		}
		$total = 0.0;
		$taxes = array();
		$bases = array();
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();

		if(empty($order->old) && !empty($order->order_id)) {
			$order->old = $this->get($order->order_id);
		}
		$old = @$order->old;

		$rounding = 2;
		$currencyClass = hikashop_get('class.currency');
		if(!empty($old->order_currency_id)) {
			$rounding = $currencyClass->getRounding($old->order_currency_id, true);
		}

		foreach($products as $i => $product) {
			if($product->order_product_code != 'order additional') {
				$app->triggerEvent( 'onBeforeCalculateProductPriceForQuantityInOrder', array( &$products[$i]) );
				if(function_exists('hikashop_product_price_for_quantity_in_order')) {
					hikashop_product_price_for_quantity_in_order($product);
				} else {
					$product->order_product_total_price = ((float)$product->order_product_price + (float)$product->order_product_tax) * (int)$product->order_product_quantity;
				}
				$app->triggerEvent('onAfterCalculateProductPriceForQuantityInOrder', array( &$products[$i]) );
			} else {
				$product->order_product_total_price = ((float)$product->order_product_price + (float)$product->order_product_tax);
			}

			$total += $currencyClass->round($product->order_product_total_price, $rounding);

			if(!empty($product->order_product_tax_info)) {
				if(is_string($product->order_product_tax_info))
					$product_taxes = hikashop_unserialize($product->order_product_tax_info);
				else
					$product_taxes = $product->order_product_tax_info;

				foreach($product_taxes as $tax) {
					if(!isset($taxes[$tax->tax_namekey])) {
						$taxes[$tax->tax_namekey] = 0;
						$bases[$tax->tax_namekey] = 0;
					}
					if($product->order_product_code == 'order additional') {
						$taxes[$tax->tax_namekey] += (float)@$tax->tax_amount;
						$bases[$tax->tax_namekey] += (float)@$tax->amount;
					} else {
						$taxes[$tax->tax_namekey] += (float)@$tax->tax_amount * $product->order_product_quantity;
						$bases[$tax->tax_namekey] += (float)@$tax->amount * $product->order_product_quantity;
					}
				}
			}
		}

		if(!isset($order->order_discount_price))
			$order->order_discount_price = @$old->order_discount_price;

		if(!isset($order->order_shipping_price))
			$order->order_shipping_price = @$old->order_shipping_price;

		if(!isset($order->order_payment_price))
			$order->order_payment_price = @$old->order_payment_price;

		$additionals = 0 - $currencyClass->round((float)$order->order_discount_price, $rounding) + $currencyClass->round((float)$order->order_shipping_price, $rounding) + $currencyClass->round((float)$order->order_payment_price, $rounding);

		$order->order_full_price = $total + $additionals;

		if($order->order_full_price < 0 && $total > 0)
			$order->order_full_price = 0;

		$config =& hikashop_config();
		if(!isset($order->order_tax_info) || empty($order->order_tax_info)) {
			if(!empty($old->order_tax_info)) {
				$order->order_tax_info = $old->order_tax_info;
			} elseif($config->get('detailed_tax_display', 1)) {
				$order->order_tax_info = array();
			}
		}

		if(!empty($order->order_tax_info) || $config->get('detailed_tax_display', 1)) {
			if(is_string($order->order_tax_info))
				$order->order_tax_info = hikashop_unserialize($order->order_tax_info);

			if(count($order->order_tax_info)){
				foreach($order->order_tax_info as $k => $tax) {
					$order->order_tax_info[$k]->todo = true;
				}
			}
			if(!empty($taxes)) {
				foreach($taxes as $namekey => $amount) {
					$found = false;
					foreach($order->order_tax_info as $k => $tax) {
						if($tax->tax_namekey == $namekey) {
							$tax_additionals = @$tax->tax_amount_for_shipping + @$tax->tax_amount_for_payment - @$tax->tax_amount_for_coupon;
							$gross_additionals = $tax_additionals - $order->order_discount_price + $order->order_shipping_price + $order->order_payment_price;
							$order->order_tax_info[$k]->tax_amount = $amount + $tax_additionals;
							$order->order_tax_info[$k]->amount = $bases[$namekey] + $gross_additionals;
							if($order->order_full_price == 0) {
								$order->order_tax_info[$k]->tax_amount = 0;
								$order->order_tax_info[$k]->amount = 0;
							}
							unset($order->order_tax_info[$k]->todo);
							$found = true;
							break;
						}
					}
					if(!$found) {
						$obj = new stdClass();
						$obj->tax_namekey = $namekey;
						$obj->tax_amount = $amount;
						$obj->amount = $bases[$namekey];

						if($order->order_full_price == 0) {
							$obj->tax_amount = 0;
							$obj->amount = 0;
						}
						$order->order_tax_info[$namekey] = $obj;
					}
				}
			}

			$unset = array();
			foreach($order->order_tax_info as $k => $tax) {
				if(isset($tax->todo)) {
					$tax_additionals = @$tax->tax_amount_for_shipping + @$tax->tax_amount_for_payment - @$tax->tax_amount_for_coupon;
					$gross_additionals = $tax_additionals - $order->order_discount_price + $order->order_shipping_price + $order->order_payment_price;
					$order->order_tax_info[$k]->tax_amount = $tax_additionals;
					$order->order_tax_info[$k]->amount = $gross_additionals;
					if($order->order_full_price == 0) {
						$order->order_tax_info[$k]->tax_amount = 0;
						$order->order_tax_info[$k]->amount = 0;
					}
					if(!bccomp(sprintf('%F',$order->order_tax_info[$k]->tax_amount),0,5)) {
						$unset[]=$k;
					} else {
						unset($order->order_tax_info[$k]->todo);
					}
				}
			}
			if(!empty($unset)) {
				foreach($unset as $u) {
					unset($order->order_tax_info[$u]);
				}
			}
		}
	}

	public function loadAddresses(&$order, $type) {
		$this->loadAddress($order->order_shipping_address_id,$order,'shipping','name',$type);
		$this->loadAddress($order->order_billing_address_id,$order,'billing','name',$type);

		if(empty($order->fields)) {
			$fieldClass = hikashop_get('class.field');
			$order->fields = $fieldClass->getData($type,'address');
		}
		if(!empty($order->fields)) {
			$order->billing_fields = array();
			$order->shipping_fields = array();
			foreach($order->fields as $k => $field) {
				if($field->field_address_type == 'billing') {
					$order->billing_fields[$k] = $field;
					continue;
				}
				if($field->field_address_type == 'shipping') {
					$order->shipping_fields[$k] = $field;
					continue;
				}
				if(empty($field->field_address_type)) {
					$order->billing_fields[$k] = $field;
					$order->shipping_fields[$k] = $field;
				}
			}
		}
	}

	public function loadFullOrder($order_id, $additionalData = false, $checkUser = true) {
		$order = $this->get($order_id);
		if(empty($order))
			return null;

		$app = JFactory::getApplication();
		$type = 'frontcomp';

		$userClass = hikashop_get('class.user');
		$order->customer = $userClass->get($order->order_user_id);

		if(hikashop_isClient('administrator')) {
			if(hikashop_level(1)) {
				$query='SELECT * FROM '.hikashop_table('geolocation').' WHERE geolocation_type=\'order\' AND geolocation_ref_id='.$order_id;
				$this->database->setQuery($query);
				$order->geolocation = $this->database->loadObject();
			}

			$query='SELECT * FROM '.hikashop_table('history').' WHERE history_order_id='.$order_id.' ORDER BY history_id DESC, history_created DESC';
			$this->database->setQuery($query);
			$order->history = $this->database->loadObjectList();

			if(!empty($order->order_partner_id)) {
				$order->partner = $userClass->get($order->order_partner_id);
			}
			$type = 'backend';
		} elseif($checkUser) {
			if(empty($order->customer->user_cms_id) || (int)$order->customer->user_cms_id == 0) {
				$token = hikaInput::get()->getVar('order_token');

				if(empty($token))
					$token = $app->getUserState('com_hikashop.order_token');

				if(empty($order->order_token) || $token != $order->order_token) {
					return null;
				}
			} elseif(hikashop_loadUser(false) != $order->order_user_id) {
				return null;
			}
		}

		$order->order_subtotal = $order->order_full_price + $order->order_discount_price - $order->order_shipping_price - $order->order_payment_price;

		$this->loadAddresses($order, $type);

		if(!empty($order->order_shipping_id)) {
			$order->shippings = array();
			if(strpos($order->order_shipping_id, ';') !== false) {
				$shipping_ids = explode(';', $order->order_shipping_id);
			} else {
				$shipping_ids = array($order->order_shipping_id);
			}
			hikashop_toInteger($shipping_ids);

			$query = 'SELECT * FROM ' . hikashop_table('shipping') . ' WHERE shipping_id IN (' . implode(',', $shipping_ids).')';
			$this->database->setQuery($query);
			$order->shippings = $this->database->loadObjectList('shipping_id');
			foreach($order->shippings as $k => $shipping) {
				if(!empty($shipping->shipping_name))
					$order->shippings[$k]->shipping_name = hikashop_translate($shipping->shipping_name);
				if(!empty($shipping->shipping_description))
					$order->shippings[$k]->shipping_description = hikashop_translate($shipping->shipping_description);
			}
		}

		if(!empty($order->order_shipping_method)) {
			$currentShipping = hikashop_import('hikashopshipping', $order->order_shipping_method);
			if($currentShipping && method_exists($currentShipping, 'getShippingAddress')) {
				$override = $currentShipping->getShippingAddress($order->order_shipping_id, $order);
				if($override !== false) {
					$order->override_shipping_address = $override;
				}
			}
		} else {
			$shipping_ids = explode(';', $order->order_shipping_id);
			$shippingClass = hikashop_get('class.shipping');
			$overrides = array();
			foreach($shipping_ids as $shipping_id) {
				$e = explode('@', $shipping_id);
				$shipping_id = reset($e);
				$shipping = $shippingClass->get($shipping_id);
				if(!$shipping)
					continue;
				$currentShipping = hikashop_import('hikashopshipping', $shipping->shipping_type);
				if(!$currentShipping || !method_exists($currentShipping, 'getShippingAddress'))
					continue;
				$override = $currentShipping->getShippingAddress($shipping_id, $order);
				if($override !== false)
					$overrides[] = $override;
			}
			if(count($overrides) == count($shipping_ids))
				$order->override_shipping_address = reset($overrides);
		}

		if(!empty($order->order_payment_id)) {
			$query = 'SELECT * FROM ' . hikashop_table('payment') . ' WHERE payment_id = ' . (int)$order->order_payment_id;
			$this->database->setQuery($query);
			$order->payment = $this->database->loadObject();
			if(!empty($order->payment->payment_name))
				$order->payment->payment_name = hikashop_translate($order->payment->payment_name);
			if(!empty($order->payment->payment_description))
				$order->payment->payment_description = hikashop_translate($order->payment->payment_description);
		}

		$this->loadProducts($order);

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();

		$order->order_subtotal_no_vat = $order->order_subtotal = 0;
		foreach($order->products as $k => $product) {
			$app->triggerEvent( 'onBeforeCalculateProductPriceForQuantityInOrder', array( &$order->products[$k]) );
			if(function_exists('hikashop_product_price_for_quantity_in_order')) {
				hikashop_product_price_for_quantity_in_order($order->products[$k]);
			} else {
				$order->products[$k]->order_product_total_price_no_vat = $product->order_product_price*$product->order_product_quantity;
				$order->products[$k]->order_product_total_price = ($product->order_product_price+$product->order_product_tax)*$product->order_product_quantity;
			}
			$app->triggerEvent( 'onAfterCalculateProductPriceForQuantityInOrder', array( &$order->products[$k]) );

			$order->order_subtotal_no_vat += $order->products[$k]->order_product_total_price_no_vat;
			$order->order_subtotal += $order->products[$k]->order_product_total_price;
		}

		if($additionalData) {
			$this->getOrderAdditionalInfo($order);
		}

		$app->triggerEvent( 'onAfterLoadFullOrder', array( &$order) );

		return $order;
	}

	public function getOrderAdditionalInfo(&$order) {
		if(hikashop_level(2)) {
			$query = 'SELECT * FROM '.hikashop_table('entry').' WHERE order_id = '.$order->order_id;
			$this->database->setQuery($query);
			$order->entries = $this->database->loadObjectList();
		}

		$product_ids = array();
		if(isset($order->cart->products)) {
			$products =& $order->cart->products;
		} else {
			$products =& $order->products;
		}
		if(!empty($products)) {
			foreach($products as $product) {
				if(!empty($product->product_id))
					$product_ids[] = (int)$product->product_id;
			}
		}
		if(count($product_ids)) {
			$query = 'SELECT * FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$product_ids).') AND product_type=\'variant\'';
			$this->database->setQuery($query);
			$productInfos = $this->database->loadObjectList();

			if(!empty($productInfos)) {
				foreach($productInfos as $product) {
					foreach($products as $item) {
						if($product->product_id == $item->product_id && !empty($product->product_parent_id)) {
							$item->product_parent_id = $product->product_parent_id;
							$product_ids[]=(int)$product->product_parent_id;
						}
					}
				}
			}
			$filters = array('a.file_ref_id IN ('.implode(',',$product_ids).')','a.file_type=\'file\'','a.file_free_download=0');
			$query = 'SELECT b.*, a.* FROM '.hikashop_table('file').' AS a '.
				' LEFT JOIN '.hikashop_table('download').' AS b ON b.order_id='.$order->order_id.' AND a.file_id = b.file_id '.
				' WHERE ('.implode(') AND (',$filters).')'.
				' ORDER BY a.file_ref_id ASC, a.file_ordering ASC, b.file_pos ASC';
			$this->database->setQuery($query);
			$files = $this->database->loadObjectList();
			if(!empty($files)) {
				foreach($products as $k => $product) {
					$products[$k]->files = array();
					foreach($files as $file) {
						if($product->product_id == $file->file_ref_id) {
							$this->_setDownloadFile($file, $products, $k, $files);
						}
					}
					if(empty($products[$k]->files) && !empty($product->product_parent_id)) {
						foreach($files as $file) {
							if($product->product_parent_id==$file->file_ref_id) {
								$this->_setDownloadFile($file, $products, $k, $files);
							}
						}
					}
					ksort($products[$k]->files);
				}
			}
			$filters = array('a.file_ref_id IN ('.implode(',',$product_ids).')','a.file_type =\'product\'');
			$query = 'SELECT a.* FROM '.hikashop_table('file').' AS a WHERE '.implode(' AND ',$filters).' ORDER BY file_ref_id ASC, file_ordering ASC';
			$this->database->setQuery($query);
			$images = $this->database->loadObjectList();
			if(!empty($images)) {
				foreach($products as $k => $product) {
					$products[$k]->images = array();
					foreach($images as $image) {
						if($product->product_id == $image->file_ref_id) {
							$products[$k]->images[]=$image;
						}
					}

					if(!empty($products[$k]->images) || empty($product->product_parent_id))
						continue;
					foreach($images as $image) {
						if($product->product_parent_id == $image->file_ref_id) {
							$products[$k]->images[]=$image;
						}
					}
				}
			}
		}
	}

	public function _setDownloadFile(&$file, &$products, $k, &$files) {
		static $files_pos = array();

		$total_product_quantity = 0;
		foreach($products as $i => $product) {
			if($product->product_id == $file->file_ref_id) {
				$total_product_quantity += (int)$product->order_product_quantity;
			}
		}
		$product_quantity =  (int)$products[$k]->order_product_quantity;

		if(empty($file->file_limit)) {
			$config =& hikashop_config();
			$file->file_limit = (int)$config->get('download_number_limit', 0);
		} else {
			$file->file_limit = (int)$file->file_limit;
		}

		if(substr($file->file_path, 0, 1) == '@' || substr($file->file_path, 0, 1) == '#') {
			if(!isset($files_pos[$file->file_id]))
				$files_pos[$file->file_id] = 0;

			if(empty($file->file_pos)) {
				for($i = 1; $i <= $product_quantity; $i++) {
					$pos = $files_pos[$file->file_id] + $i;
					$f = clone($file);
					$f->file_pos = $pos;
					$id = sprintf('%05d-%05d-%05d', $file->file_ordering, $file->file_id, $pos);
					$products[$k]->files[$id] = $f;
					unset($f);
				}
				$files_pos[$file->file_id] += $product_quantity;
			} else {
				for($i = 1; $i <= $product_quantity; $i++) {
					$pos = $files_pos[$file->file_id] + $i;

					$skip = false;
					foreach($files as $fileFromDB) {
						if($fileFromDB->file_id == $file->file_id && $fileFromDB->file_pos == $pos) {
							$skip = true;
						}
					}
					if($skip) {
						continue;
					}
					$id = sprintf('%05d-%05d-%05d', $file->file_ordering, $file->file_id, $i);
					$f = clone($file);
					$f->file_pos = $pos;
					$f->download_number = 0;
					$products[$k]->files[$id] = $f;
					unset($f);
				}
				$files_pos[$file->file_id] += $product_quantity;

				$id = sprintf('%05d-%05d-%05d', $file->file_ordering, $file->file_id, (int)$file->file_pos);
				$products[$k]->files[$id] = $file;
			}
		} else {
			$file->file_pos = 0;
			$file->file_limit *= $product_quantity;
			$id = sprintf('%05d-%05d-%05d', $file->file_ordering, $file->file_id, (int)$file->file_pos);
			$products[$k]->files[$id] = $file;
		}
	}

	public function loadProducts(&$order) {
		$ids = array();
		if(is_array($order)) {
			foreach($order as $o) {
				if(!empty($o->order_id))
					$ids[] = (int)$o->order_id;
			}
		} elseif(is_object($order)) {
			$ids[] = (int)$order->order_id;
		} else {
			return;
		}

		if(!count($ids))
			return;
		$query = 'SELECT a.* FROM '.hikashop_table('order_product').' AS a WHERE a.order_id IN ('.implode(',', $ids). ') ORDER BY order_product_id ASC';
		$this->database->setQuery($query);
		$products = $this->database->loadObjectList();
		if(is_array($order)) {
			foreach($order as $k => $o) {
				$order[$k]->total_number_of_products = 0;
				$order[$k]->additional = array();
				$order[$k]->products = array();
				$found = false;
				foreach($products as $p) {
					if($p->order_id == $o->order_id) {
						$found = true;
						$order[$k]->products[] = $p;
					}
				}
				if($found) {
					foreach($order[$k]->products as $l => $product) {
						$this->_processProductToOrder($order[$k], $product, $l);
					}
				}
			}
		} else {
			$order->total_number_of_products = 0;
			$order->additional = array();
			$order->products =& $products;
			foreach($order->products as $l => $product) {
				$this->_processProductToOrder($order, $product, $l);
			}
		}
	}

	private function _processProductToOrder(&$order, &$product, $k) {
		if(!empty($product->order_product_name))
			$order->products[$k]->order_product_name = hikashop_translate($product->order_product_name);
		if(!empty($product->order_product_tax_info)) {
			$order->products[$k]->order_product_tax_info = hikashop_unserialize($order->products[$k]->order_product_tax_info);
		}
		if(!empty($product->order_product_discount_info)) {
			$order->products[$k]->order_product_discount_info = hikashop_unserialize($order->products[$k]->order_product_discount_info);
		}
		if(!empty($product->order_product_params))
			$order->products[$k]->order_product_params = json_decode($product->order_product_params);
		if($product->order_product_code == 'order additional') {
			unset($order->products[$k]);
			$order->additional[] = $product;
		} else if(!empty($product->order_product_options)) {
			$order->products[$k]->order_product_options = hikashop_unserialize($order->products[$k]->order_product_options);
		}
		if(!empty($order->products[$k]->order_product_options['type']) && $order->products[$k]->order_product_options['type'] == 'bundle' && !empty($product->order_product_option_parent_id)) {
			foreach($order->products as $j => $main_product) {
				if($product->order_product_option_parent_id == $main_product->order_product_id) {
					if(!isset($main_product->bundle))
						$main_product->bundle = array();
					$main_product->bundle[] = $product;
					break;
				}
			}
		}
		if($product->order_product_quantity == 0) {
			unset($order->products[$k]);
		} else {
			if(empty($product->order_product_option_parent_id))
				$order->total_number_of_products += $product->order_product_quantity;
		}
	}

	public function loadAddress($address, &$order, $address_type = 'shipping', $display = 'name', $type = 'frontcomp') {
		$addressClass = hikashop_get('class.address');
		$name = $address_type.'_address';
		$order->$name = $addressClass->get($address);
		if(empty($order->$name))
			return;

		$data =& $order->$name;
		$array = array(&$data);
		$addressClass->loadZone($array, $display, $type);
		if(!empty($addressClass->fields)) {
			$order->fields =& $addressClass->fields;
		}
	}

	public function orderNumber(&$order) {
		return true;
	}

	public function get($order_id, $trans = true) {
		$order = parent::get($order_id);
		if(empty($order))
			return $order;

		$app = JFactory::getApplication();
		$translationHelper = hikashop_get('helper.translation');
		$locale = '';
		$lgid = 0;

		if(!empty($lgid)) {
			$order->order_current_lgid = $lgid;
			$order->order_current_locale = $locale;
		}
		if(!empty($order->order_tax_info) && is_string($order->order_tax_info)) {
			$order->order_tax_info = hikashop_unserialize($order->order_tax_info);
		}else{
			$order->order_tax_info = array();
		}
		if(!empty($order->order_payment_params) && is_string($order->order_payment_params)) {
			$order->order_payment_params = hikashop_unserialize($order->order_payment_params);
		}else{
			$order->order_payment_params = new stdClass();
		}
		if(!empty($order->order_shipping_params) && is_string($order->order_shipping_params)) {
			$order->order_shipping_params = hikashop_unserialize($order->order_shipping_params);
		}else{
			$order->order_shipping_params = new stdClass();
		}
		return $order;
	}

	public function loadMail(&$product) {
		if(!empty($product)) {
			$product->order = parent::get($product->order_id);
			$userClass = hikashop_get('class.user');
			if(isset($product->order->order_user_id))
				$product->customer = $userClass->get($product->order->order_user_id);
			else{
				$product->customer = hikaInput::get()->getInt('user_id','0');
				if(!isset($product->order))$product->order=new stdClass();
				$product->order->order_number = 0;
			}
			$product->order_lang = $product->order->order_lang;
			$this->loadMailNotif($product);
		}
		return $product;
	}

	public function loadMailNotif(&$element) {
		$this->loadLocale($element);

		global $Itemid;
		$url = '';
		if(!empty($Itemid)) {
			$url='&Itemid='.$Itemid;
		}
		$element->order_url = hikashop_contentLink('order&task=show&cid[]='.$element->order_id.$url, $element, false, false, false, true);

		$element->order = $this->get($element->order_id);
		$element->mail_status = hikashop_orderStatus($element->order->order_status);

		$mailClass = hikashop_get('class.mail');
		$element->mail = $mailClass->get('order_notification',$element);
		$element->mail->subject = JText::sprintf($element->mail->subject,$element->order->order_number,HIKASHOP_LIVE);
		if(!empty($element->customer->user_email)) {
			$element->mail->dst_email =& $element->customer->user_email;
		} else {
			$element->mail->dst_email = '';
		}
		if(!empty($element->customer->name)) {
			$element->mail->dst_name =& $element->customer->name;
		} else {
			$element->mail->dst_name = '';
		}
		$lang = JFactory::getLanguage();
		$override_path = hikashop_getLanguagePath(JPATH_ROOT).DS.'overrides'.DS.$lang->getTag().'.override.ini';
		$lang->load(HIKASHOP_COMPONENT, JPATH_SITE, null, true );
		if(file_exists($override_path))
			hikashop_loadTranslationFile($override_path);
	}

	public function loadNotification($order_id, $type = 'order_status_notification', $params = null) {
		$order = $this->get($order_id);
		$this->loadOrderNotification($order,$type, $params);
		return $order;
	}

	public function loadOrderNotification(&$order, $type = 'order_status_notification', $params = null) {
		if(empty($order->order_user_id) || empty($order->order_status) || empty($order->order_token)) {
			$dbOrder = parent::get($order->order_id);
			$order->order_user_id = @$dbOrder->order_user_id;
			if(empty($order->order_status)) $order->order_status = @$dbOrder->order_status;
			if(empty($order->order_token)) $order->order_token = @$dbOrder->order_token;
		}
		if(empty($order->customer) || (int)@$order->customer->user_id != (int)$order->order_user_id) {
			$userClass = hikashop_get('class.user');
			$order->customer = $userClass->get($order->order_user_id);
		}

		$locale = $this->loadLocale($order);

		global $Itemid;
		$url = '';
		if(!empty($Itemid))
			$url='&Itemid='.$Itemid;
		if(isset($order->url_itemid))
			$url = (!empty($order->url_itemid) ? '&Itemid=':'') . $order->url_itemid;
		if((empty($order->customer->user_cms_id) || (int)$order->customer->user_cms_id == 0) && !empty($order->order_token)) {
			$url .= '&order_token='.urlencode($order->order_token);
		}
		$order->order_url = hikashop_contentLink('order&task=show&cid[]='.$order->order_id.$url, $order, false, false, false, true);

		$fieldsClass = hikashop_get('class.field');
		$fieldsClass->getData('reset', '');

		if(!isset($order->mail_status))
			$order->mail_status = '';
		if(empty($order->mail_status) && isset($order->order_status))
			$order->mail_status = hikashop_orderStatus($order->order_status);

		$mail_status = $order->mail_status;
		if(!empty($params))
			$order->mail_params = $params;
		$mailClass = hikashop_get('class.mail');
		$order->mail = $mailClass->get($type,$order);
		$order->mail_status = $mail_status;
		$order->mail->subject = JText::sprintf($order->mail->subject, $order->order_number, $mail_status, HIKASHOP_LIVE);

		if(!empty($order->customer->user_email)) {
			$order->mail->dst_email = $order->customer->user_email;
		} else {
			$order->mail->dst_email = '';
		}

		if(!empty($order->customer->name)) {
			$order->mail->dst_name = $order->customer->name;
		} else {
			$order->mail->dst_name = '';
		}

		$this->loadBackLocale();

		$fieldsClass->getData('reset', '');
	}

	public function loadBackLocale() {
		if(empty($this->oldLocale))
			return true;

		hikashop_loadHikashopTranslations($this->oldLocale);
		unset($this->oldLocale);
	}

	public function loadLocale(&$order) {
		$locale = '';

		if(!empty($order->order_lang))
			$locale = $order->order_lang;

		if(empty($locale) && !empty($order->old->order_lang))
			$locale = $order->old->order_lang;

		$lang = JFactory::getLanguage();
		$currentLocale = $lang->getTag();

		if(empty($locale) || $locale == $currentLocale)
			return $currentLocale;
		$this->oldLocale = $currentLocale;

		hikashop_loadHikashopTranslations($locale);
		return $locale;
	}

	public function delete(&$elements) {
		if (!is_array($elements)) {
			$elements = array($elements);
		}

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();

		$do = true;
		$app->triggerEvent('onBeforeOrderDelete', array(&$elements, &$do));
		if (!$do) {
			return false;
		}


		$fieldClass = hikashop_get('class.field');
		$fieldClass->handleBeforeDelete($elements, 'order');
		$fieldClass->handleBeforeDelete($elements, 'item');

		hikashop_toInteger($elements);
		$query = 'SELECT order_billing_address_id, order_shipping_address_id '.
				' FROM ' . hikashop_table('order') .
				' WHERE order_id IN (' . implode(',', $elements) . ')';
		$this->database->setQuery($query);
		$orders = $this->database->loadObjectList();

		$result = parent::delete($elements);
		if (!$result) {
			return false;
		}

		if (!empty($orders)) {
			$addresses = array();
			foreach ($orders as $order) {
				$addresses[(int)$order->order_billing_address_id] = (int)$order->order_billing_address_id;
				$addresses[(int)$order->order_shipping_address_id] = (int)$order->order_shipping_address_id;
			}

			$addressClass = hikashop_get('class.address');
			foreach ($addresses as $address) {
				$addressClass->delete($address, true);
			}
		}

		$historyClass = hikashop_get('class.history');
		$historyClass->deleteRecords($elements);


		$fieldClass->handleAfterDelete($elements, 'order');
		$fieldClass->handleAfterDelete($elements, 'item');

		$app->triggerEvent('onAfterOrderDelete', array(&$elements));

		return $result;
	}

	public function copyOrder($order_id) {
		$order = $this->loadFullOrder($order_id);

		unset($order->order_id);
		unset($order->order_created);
		unset($order->order_number);
		unset($order->order_invoice_id);
		unset($order->order_invoice_number);
		unset($order->order_invoice_created);
		unset($order->order_subtotal);
		unset($order->override_shipping_address);
		unset($order->order_subtotal_no_vat);
		unset($order->history);
		unset($order->shipping_address);
		unset($order->billing_address);

		$order->cart =& $order;
		$this->sendEmailAfterOrderCreation = false;
		foreach($order->products as $k => $product) {
			$order->products[$k]->cart_product_id = $order->products[$k]->order_product_id;
			$order->products[$k]->cart_product_option_parent_id = $order->products[$k]->order_product_option_parent_id;
		}

		return $this->save($order);
	}
	public function getList($filters = array(), $options = array()) {
		$query = 'SELECT * '.
			' FROM ' .  hikashop_table('order') .
			' ORDER BY order_created DESC';
		$this->db->setQuery($query);
		$ret = $this->db->loadObjectList();
		return $ret;
	}

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$fullLoad = false;
		$displayFormat = !empty($options['displayFormat']) ? $options['displayFormat'] : @$typeConfig['displayFormat'];

		$start = (int)@$options['start']; // TODO
		$limit = (int)@$options['limit'];
		$page = (int)@$options['page'];
		if($limit <= 0)
			$limit = 50;

		$select = array('o.*', 'u.user_email');
		$table = array(hikashop_table('order').' AS o');
		$where = array(
			'order_type' => 'o.order_type = '.$this->db->Quote('sale')
		);

		if(!empty($search)) {
			$searchMap = array('o.order_id', 'o.order_number', 'u.user_email');
			if(!HIKASHOP_J30)
				$searchVal = '\'%' . $this->db->getEscaped(HikaStringHelper::strtolower($search), true) . '%\'';
			else
				$searchVal = '\'%' . $this->db->escape(HikaStringHelper::strtolower($search), true) . '%\'';
			$where['search'] = '('.implode(' LIKE '.$searchVal.' OR ', $searchMap).' LIKE '.$searchVal.' OR '.implode(' = '.$thid->db->Quote($search).' OR ', $searchMap).' = '.$thid->db->Quote($search).')';
		}

		$order = ' ORDER BY o.order_created DESC';

		$query = 'SELECT '.implode(', ', $select) . ' FROM ' . implode(' ', $table) . ' LEFT JOIN #__hikashop_user as u ON o.order_user_id = u.user_id WHERE ' . implode(' AND ', $where).$order;
		$this->db->setQuery($query, $page, $limit);

		$orders = $this->db->loadObjectList('order_id');

		if(count($orders) < $limit)
			$fullLoad = true;

		$currencyClass = hikashop_get('class.currency');
		foreach($orders as $order) {
			$order->order_full_price = $currencyClass->format($order->order_full_price, $order->order_currency_id);
			$ret[0][$order->order_id] = $order;
		}

		if(!empty($value)) {
			if($mode == hikashopNameboxType::NAMEBOX_SINGLE && isset($ret[0][$value])) {
				$ret[1][$value] = $ret[0][$value];
			} elseif($mode == hikashopNameboxType::NAMEBOX_MULTIPLE && is_array($value)) {
				foreach($value as $v) {
					if(isset($ret[0][$v])) {
						$ret[1][$v] = $ret[0][$v];
					}
				}
			}
		}
		return $ret;
	}

	public function createRecurringSuborder($order_id) {
		$order = $this->loadFullOrder($order_id, false, false);

		if(empty($order))
			return false;

		$config = hikashop_config();

		$order->old = $this->get($order_id);
		$rem = array(
			'order_id', 'order_number', 'order_invoice_id', 'order_invoice_number',
			'order_created', 'order_invoice_created',
			'order_subtotal', 'order_subtotal_no_vat', 'override_shipping_address',
			'history', 'shipping_address', 'billing_address'
		);
		foreach($rem as $r) {
			if(!isset($order->old->$r) && isset($order->$r))
				$order->old->$r = $order->$r;
			unset($order->$r);
		}

		$order->order_status = $config->get('order_created_status', 'created');

		if(!empty($order->order_payment_params->recurring) && !empty($order->order_payment_params->recurring['products'])) {
			$order_products = $order->order_payment_params->recurring['products'];
			foreach($order->products as $k => $product) {
				if(!in_array((int)$product->order_product_id, $order_products)) {
					unset($order->products[$k]);
					continue;
				}
				$order->products[$k]->order_product_parent_id = (int)$product->order_product_id;
			}
			$this->recalculateFullPrice($order, $order->products);
		}

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();

		$app->triggerEvent('onBeforeCreateRecurringSuborder', array($order_id, &$order));

		$this->sendEmailAfterOrderCreation = false;
		foreach($order->products as $k => $product) {
			$order->products[$k]->cart_product_id = $order->products[$k]->order_product_id;
			$order->products[$k]->cart_product_option_parent_id = $order->products[$k]->order_product_option_parent_id;
		}
		$order->cart =& $order;

		$ret = $this->save($order);

		$app->triggerEvent('onAfterCreateRecurringSuborder', array($order_id, &$order));

		return $ret;
	}
}
