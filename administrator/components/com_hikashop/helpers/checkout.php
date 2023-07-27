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
class hikashopCheckoutHelper {
	protected static $instance = null;

	public $cart = false;
	public $checkout_workflow = null;
	public $redirectBeforeDisplay = null;

	protected $cart_id = 0;
	protected $shop_closed = false;

	protected $config = null;
	protected $cartClass = null;
	protected $shippingClass = null;
	protected $paymentClass = null;
	protected $addressClass = null;
	protected $currencyClass = null;

	protected $redirect_url = null;
	protected $events = array();
	protected $messages = array();
	protected $images = array();

	public static function &get($cart_id = null) {
		if(self::$instance === null) {
			$classname = class_exists('hikashopCheckoutHelperOverride') ? 'hikashopCheckoutHelperOverride' : 'hikashopCheckoutHelper';
			self::$instance = new $classname($cart_id);
			self::$instance->config = hikashop_config();
			self::$instance->loadWorkflow();
		}
		return self::$instance;
	}

	public function __construct($cart_id = null) {
		$this->cart_id = 0;
		if($cart_id !== null) {
			$this->cartClass = hikashop_get('class.cart');
			$this->cart_id = (int)$cart_id;
			$cart = $this->cartClass->get($this->cart_id);
			if(empty($cart) || $cart->cart_type == 'wishlist')
				$this->cart_id = 0;
		}
	}

	protected function loadWorkflow() {
		$this->checkout_workflow = $this->config->get('checkout_workflow', '');
		if(!empty($this->checkout_workflow))
			$this->checkout_workflow = json_decode($this->checkout_workflow, true);

		if(empty($this->checkout_workflow) || (int)$this->config->get('checkout_workflow_legacy', 0) == 1)
			$this->loadWorkflowLegacy();

		$this->shop_closed = false;

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onCheckoutWorkflowLoad', array(&$this->checkout_workflow, &$this->shop_closed, $this->cart_id));

	}

	protected function loadWorkflowLegacy() {
		$checkout_config = trim($this->config->get('checkout','login_address_shipping_payment_coupon_cart_status_confirm,end'));
		$legacy_steps = explode(',', $checkout_config);

		$this->checkout_workflow = array(
			'steps' => array()
		);
		foreach($legacy_steps as $steps) {
			$steps = explode('_', $steps);
			$content = array();
			foreach($steps as $step) {
				$c = array('task' => $step);
				if($step == 'cartstatus') {
					$c['task'] = 'cart';
					$c['params'] = array('readonly' => true);
				}
				$content[] = $c;
			}
			$this->checkout_workflow['steps'][] = array(
				'content' => $content
			);
		}
	}

	public function isLoggedUser() {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$user_id = $app->getUserState(HIKASHOP_COMPONENT.'.user_id');

		return (empty($user->guest) || !empty($user_id));
	}

	public function isStoreClosed() {
		return $this->shop_closed;
	}

	public function &getCart($reset = false) {
		if(!$reset && $this->cart !== false)
			return $this->cart;

		if(empty($this->cartClass) || $reset)
			$this->cartClass = hikashop_get('class.cart');
		$this->cart = $this->cartClass->getFullCart($this->cart_id);
		return $this->cart;
	}

	public function getCartId() {
		return $this->cart_id;
	}

	public function isShipping() {
		$cart = $this->getCart();
		$config =& hikashop_config();

		return !empty($cart->usable_methods->shipping) || !empty($cart->package['weight']['value']) || $config->get('force_shipping', 0);
	}

	public function getAddresses($type = '') {
		$ret = array(
			'data' => array(),
			'fields' => array()
		);

		if(!in_array($type, array('', 'billing', 'shipping')))
			$type = '';

		if(empty($this->addressClass))
			$this->addressClass = hikashop_get('class.address');

		$cart = $this->getCart();
		if(!empty($cart->user_id))
			$ret['data'] = $this->addressClass->loadUserAddresses($cart->user_id, $type);

		if(!empty($ret['data'])) {
			$this->addressClass->loadZone($ret['data']);
			$ret['fields'] =& $this->addressClass->fields;
		} else {
			$fieldClass = hikashop_get('class.field');
			$fields = $fieldClass->getData('frontcomp', 'address');
			$ret['fields'] =& $fields;
		}

		if(!empty($ret['fields']) && count($ret['fields'])) {
			$ret['billing_fields'] = array();
			$ret['shipping_fields'] = array();
			foreach($ret['fields'] as $k => $field) {
				if($field->field_address_type == 'billing') {
					$ret['billing_fields'][$k] = $field;
					continue;
				}
				if($field->field_address_type == 'shipping') {
					$ret['shipping_fields'][$k] = $field;
					continue;
				}
				if(empty($field->field_address_type)) {
					$ret['billing_fields'][$k] = $field;
					$ret['shipping_fields'][$k] = $field;
				}
			}
		}
		return $ret;
	}

	public function getShippingAddressOverride() {
		if(!$this->isShipping())
			return false;

		$cart = $this->getCart();
		if(empty($cart->shipping))
			return false;

		$currentShipping = array();
		foreach($cart->shipping as $method) {
			$currentShipping[$method->shipping_id] = hikashop_import('hikashopshipping', $method->shipping_type);
		}

		$override = false;
		foreach($currentShipping as $shipping_id => $selectedMethod) {
			if(!empty($selectedMethod) && method_exists($selectedMethod, 'getShippingAddress')) {
				$override = $selectedMethod->getShippingAddress($shipping_id, $cart);
			}
		}

		return $override;
	}

	public function getDisplayPrice($data, $type, $options = null) {
		if(!isset($data->{$type.'_price'}))
			return '';

		if($data->{$type.'_price'} == 0.0)
			return JText::_('FREE_'.strtoupper($type));

		$config = hikashop_config();
		$defaultParams = $config->get('default_params');

		if(isset($options['price_with_tax']))
			$pt = $options['price_with_tax'];
		else
			$pt = (int)$config->get('price_with_tax', 0);

		if(empty($this->currencyClass))
			$this->currencyClass = hikashop_get('class.currency');

		if(isset($data->{$type.'_currency_id'}))
			$currency = (int)$data->{$type.'_currency_id'};
		else
			$currency = $data->{$type.'_params'}->{$type.'_currency'};

		$price_text = JText::_('PRICE_BEGINNING') . '<span class="hikashop_checkout_'.$type.'_price">';
		if($pt > 0)
			$price_text .= $this->currencyClass->format($data->{$type.'_price_with_tax'}, $currency);

		if($pt == 2)
			$price_text .= JText::_('PRICE_BEFORE_TAX');

		if($pt == 2 || $pt == 0)
			$price_text .= $this->currencyClass->format($data->{$type.'_price'}, $currency);

		if($pt == 2)
			$price_text .= JText::_('PRICE_AFTER_TAX');

		if(!empty($options['show_original_price']) && isset($data->{$type.'_price_orig'}) && $data->{$type.'_currency_orig'} > 0.0) {
			$price_text .= JText::_('PRICE_BEFORE_ORIG');
			if($pt > 0)
				$price_text .= $this->currencyClass->format($data->{$type.'_price_orig_with_tax'}, $data->{$type.'_currency_orig'});

			if($pt == 2)
				$price_text .= JText::_('PRICE_BEFORE_TAX');

			if($pt == 2 || $pt == 0)
				$price_text .= $this->currencyClass->format($data->{$type.'_price_orig'}, $data->{$type.'_currency_orig'});

			if($pt == 2)
				$price_text .= JText::_('PRICE_AFTER_TAX');

			$price_text .= JText::_('PRICE_AFTER_ORIG');
		}
		$price_text .= '</span>'.JText::_('PRICE_END');

		return $price_text;
	}

	public function getPluginImage($name, $type = null) {
		if(!in_array($type, array('payment', 'shipping')))
			return false;

		$ret = new stdClass;

		if(!empty($this->images[$type])) {
			if(!isset($this->images[$type][$name]))
				return false;
			$ret->url = HIKASHOP_IMAGES . $type . '/' . $this->images[$type][$name];
			return $ret;
		}

		jimport('joomla.filesystem.folder');
		$files = JFolder::files(HIKASHOP_MEDIA .'images'.DS.$type.DS);
		$this->images[$type] = array();
		if(!empty($files)) {
			foreach($files as $file) {
				$parts = explode('.', $file);
				array_pop($parts);
				$file_name = implode('.', $parts);
				$this->images[$type][$file_name] = $file;
			}
		}
		if(!isset($this->images[$type][$name]))
			return false;
		$ret->url = HIKASHOP_IMAGES . $type . '/' . $this->images[$type][$name];
		return $ret;
	}

	public function getCreditCard($payment, $hide = true) {
		$cart = $this->getCart();
		$method = null;
		foreach($cart->usable_methods->payment as $p) {
			if($p->payment_type == $payment->payment_type && $p->payment_id == $payment->payment_id)
				$method = $p;
		}
		if(empty($method) || !$method->ask_cc)
			return false;

		$app = JFactory::getApplication();
		$checkout_cc = $app->getUserState(HIKASHOP_COMPONENT.'.checkout_cc', null);
		if(empty($checkout_cc))
			return false;
		if(is_string($checkout_cc))
			$checkout_cc = json_decode(base64_decode($checkout_cc));
		$var = $method->payment_id;
		if(!isset($checkout_cc->$var))
			return false;
		if(!is_object($checkout_cc->$var))
			return false;

		$ret = $checkout_cc->$var;
		if($hide){
			$l = strlen($ret->num);
			$ret->num = str_repeat('X', $l - 4).substr($ret->num, $l - 4);
			if(!empty($ret->ccv))
				$ret->ccv = str_repeat('X', strlen($ret->ccv));
		}
		return $ret;
	}

	public function getCustomHtml($data, $input_name) {
		if(is_string($data))
			return $data;

		$ret = '';
		return $ret;
	}

	public function completeLink($url, $ajax = false, $redirect = false, $js = false, $Itemid = 0) {
		$config = hikashop_config();
		$menusClass = hikashop_get('class.menus');

		$config_itemid = (int)$config->get('checkout_itemid', 0);

		$setCtrl = true;
		$checkout_itemid = !empty($checkout_itemid) ? $checkout_itemid : $Itemid;

		$valid_menu = $menusClass->loadAMenuItemId('checkout', 'show', $checkout_itemid);
		if(!empty($valid_menu)) {
			$setCtrl = false;
		} else {
			$valid_menu = $menusClass->loadAMenuItemId('', '', $checkout_itemid);
			if(!$valid_menu) {
				$checkout_itemid = $menusClass->loadAMenuItemId('', '');
			}
		}

		if(!$setCtrl) {
			$jconfig = JFactory::getConfig();
			if(!$jconfig->get('sef'))
				$setCtrl = true;
			if(class_exists('Sh404sefHelperGeneral')) {
				$params = Sh404sefHelperGeneral::getComponentParams();
				if($params->get('Enabled'))
					$setCtrl = true;
			}
			if(class_exists('\Forsef') && \Forsef::isEnabled()) {
				$setCtrl = true;
			}
		}

		$cart_id = $this->getCartId();
		$url .= ($cart_id > 0) ? '&cart_id=' . $cart_id : '';
		$tmpl = '';
		if($ajax) {
			if(HIKASHOP_J30) {
				$tmpl = '&tmpl=raw';
			} else {
				$tmpl = '&tmpl=component';
			}
		}
		$link = 'index.php?option=' . HIKASHOP_COMPONENT . ($setCtrl ? '&ctrl=checkout' : '') . (!empty($url) ? '&'.$url : '') . '&Itemid=' . $checkout_itemid . $tmpl;
		$ret = JRoute::_($link, !$redirect);
		if($js) return str_replace('&amp;', '&', $ret);
		return $ret;
	}

	public function getRedirectUrl($override = false) {
		if(!$override && !empty($this->redirect_url))
			return $this->redirect_url;

		$this->redirect_url = $this->config->get('redirect_url_when_cart_is_empty', '');
		if(!$override && !empty($this->redirect_url)) {
			$this->redirect_url = hikashop_translate($this->redirect_url);
			if(!preg_match('#^https?://#', $this->redirect_url))
				$this->redirect_url = JURI::base() . ltrim($this->redirect_url, '/');
			$this->redirect_url = JRoute::_($this->redirect_url, false);
			return $this->redirect_url;
		}

		global $Itemid;
		$url = '';
		$itemid_to_use = $Itemid;
		$menusClass = hikashop_get('class.menus');
		if(!empty($itemid_to_use))
			$ok = $menusClass->loadAMenuItemId('product', 'listing', $itemid_to_use);
		if(empty($ok))
			$ok = $menusClass->loadAMenuItemId('product', 'listing');
		if($ok)
			$itemid_to_use = $ok;

		if(!empty($itemid_to_use))
			$url = '&Itemid=' . $itemid_to_use;

		$this->redirect_url = hikashop_completeLink('product&task=listing' . $url, false, true);
		return $this->redirect_url;
	}

	public function getCartMarkers() {
		$cart = $this->getCart(true);

		$user = (int)$cart->user_id;
		if(empty($user))
			$user = $cart->session_id;

		foreach($cart->cart_products as &$p) {
			unset($p->cart_product_modified);
		}
		unset($p);

		$total = hikashop_copy($cart->full_total);
		if(!empty($total->prices)) {
			foreach($total->prices as &$price ) {
				if(!empty($price->taxes)) {
					foreach($price->taxes as $i => $tax) {
						foreach(get_object_vars($tax) as $k => $v) {
							if($k != 'tax_namekey')
								unset($price->taxes[$i]->$k);
						}
					}
				}
				if(!empty($price->taxes_without_discount)) {
					foreach($price->taxes_without_discount as $i => $tax) {
						foreach(get_object_vars($tax) as $k => $v) {
							if($k != 'tax_namekey')
								unset($price->taxes_without_discount[$i]->$k);
						}
					}
				}
			}
		}
		$fullprice = md5(serialize($total));
		$products = md5(serialize($cart->cart_products) . serialize(@$cart->additional));
		$paymentMethods = hikashop_copy(@$cart->usable_methods->payment);
		if(!empty($paymentMethods)) {
			foreach($paymentMethods as &$paymentMethod ) {
				unset($paymentMethod->total);
				if(!empty($paymentMethod->custom_html_ignore_cache))
					unset($paymentMethod->custom_html);
			}
		}
		$payments = md5(serialize(@$paymentMethods));
		$shippingMethods = hikashop_copy(@$cart->usable_methods->shipping);
		if(!empty($shippingMethods)) {
			foreach($shippingMethods as &$shippingMethod ) {
				unset($shippingMethod->taxes);
				if(!empty($shippingMethod->custom_html_ignore_cache))
					unset($shippingMethod->custom_html);
			}
		}
		$shippings = md5(serialize(@$shippingMethods));
		$address_override = md5(serialize($this->getShippingAddressOverride()));
		$fields = null;
		if(!empty($cart->order_fields))
			$fields = array_keys($cart->order_fields);
		$order_fields = md5(serialize($fields));
		$billing_addreses = md5(serialize(@$cart->usable_addresses->billing));
		$shipping_addreses = md5(serialize(@$cart->usable_addresses->shipping));

		$shipping = $cart->cart_shipping_ids;
		if(is_array($cart->cart_shipping_ids)) {
			$shipping = '';
			foreach($cart->cart_shipping_ids as $selection) {
				$shipping .=','.$selection;
			}
		}

		$payment_price = 0.0;
		if(isset($cart->payment->payment_price))
			$payment_price = $cart->payment->payment_price;

		$shipping_price = 0.0;
		$shipping_products = '';
		if(!empty($cart->shipping)) {
			if(isset($cart->shipping->shipping_price)) {
				$shipping_price = hikashop_toFloat($cart->shipping->shipping_price);
			} else {
				foreach($cart->shipping as $s) {
					$shipping_price += hikashop_toFloat($s->shipping_price);
				}
			}
			if(isset($cart->shipping_groups) && count($cart->shipping_groups) > 1) {
				$s = array();
				foreach($cart->shipping_groups as $k => $v) {
					$s[$k] = array();
					foreach($v->products as $p) {
						$s[$k][] = (int)$p->product_id;
					}
				}
				$shipping_products = md5(serialize($s));
			}
		}

		$ret = array(
			'full_price' => $fullprice, // hash of the full amount of the cart
			'cart_products' => $products, // hash of the products (& additional)
			'cart_empty' => (count($cart->cart_products) == 0),
			'coupon' => $cart->cart_coupon, // for the coupon
			'payment_selected' => (int)$cart->cart_payment_id, // status && payment
			'payment_price' => $payment_price, // cart
			'payment_list' => $payments, // payment
			'shipping_selected' => $shipping, // status && shipping
			'shipping_price' => $shipping_price, // cart
			'shipping_list' => $shippings, // shipping
			'shipping_group_products' => $shipping_products, // shipping
			'billing_address' => (int)$cart->cart_billing_address_id,
			'billing_addresses' => $billing_addreses,
			'shipping_address' => (int)$cart->cart_shipping_address_ids,
			'shipping_address_overirde' => $address_override,
			'shipping_addresses' => $shipping_addreses,
			'user' => $user,
			'order_fields' => $order_fields,
		);

		$markers = array();
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onCheckoutGetCartMarkers', array(&$markers, &$cart));
		if(!empty($markers)) {
			$ret['plugins'] = $markers;
		}

		return $ret;
	}

	public function generateBlockEvents($markers, $params = null) {
		$newMarkers = $this->getCartMarkers();

		$events = array(
			'full_price' => 'checkout.cart.updated',
			'cart_products' => 'checkout.cart.updated',
			'coupon' => array('checkout.coupon.updated', 'checkout.cart.updated'),
			'payment_selected' => 'checkout.payment.changed',
			'payment_price' => 'checkout.cart.updated',
			'payment_list' => 'checkout.payment.updated',
			'shipping_selected' => 'checkout.shipping.changed',
			'shipping_price' => 'checkout.cart.updated',
			'shipping_list' => 'checkout.shipping.updated',
			'shipping_group_products' => 'checkout.shipping.updated',
			'shipping_address_overirde' => 'checkout.address.updated',
			'billing_addresses' => 'checkout.address.updated',
			'shipping_addresses' => 'checkout.address.updated',
			'user' => 'checkout.user.updated',
			'order_fields' => 'checkout.fields.updated',
		);

		if(!empty($newMarkers['cart_empty'])) {
			if(empty($params))
				$params = array();
			$params['cart_empty'] = true;
		}

		foreach($markers as $k => $v) {
			if($v === $newMarkers[$k])
				continue;
			if(!isset($events[$k]))
				continue;

			$evt = $events[$k];
			if(is_array($evt)) {
				foreach($evt as $e) {
					$this->addEvent($e, $params);
				}
			} else {
				$this->addEvent($evt, $params);
			}
		}

		if(!empty($markers['plugins'])) {
			$app = JFactory::getApplication();
			foreach($markers['plugins'] as $k => $v) {
				if($v === $newMarkers['plugins'][$k])
					continue;
				$evts = array();
				$app->triggerEvent('onCheckoutProcessCartMarker', array($k, &$evts, $v, $newMarkers['plugins'][$k]));
				foreach($evts as $e) {
					$this->addEvent($e, $params);
				}
			}
		}

		return true;
	}

	public function addEvent($name, $params = null) {
		if(isset($this->events[$name]))
			return false;
		$this->events[$name] = $params;
		return true;
	}

	public function getEvents() {
		return $this->events;
	}


	public function addMessage($name, $message = null) {
		if(isset($this->messages[$name]))
			return false;
		$this->messages[$name] = $message;
		return true;
	}

	public function displayMessages($name = null, $display = true) {
		if(empty($this->messages) && $name !== 'cart')
			return;

		$key = ($name !== null) ? $name.'.' : null;

		$messagesToReturn = array();

		foreach($this->messages as $n => $msg) {
			if($key !== null && $n !== $name && strpos($n, $key) !== 0)
				continue;

			unset($this->messages[$n]);

			$messagesToReturn[] = $msg;
			if(!$display)
				continue;

			if(!is_array($msg)) {
				hikashop_display($msg);
				continue;
			}
			if(!isset($msg['msg'])) {
				if(!isset($msg[1]))
					hikashop_display($msg[0]);
				else
					hikashop_display($msg[0], $msg[1]);
				continue;
			}
			if(!isset($msg['type']))
				$msg['type'] = 'error';
			hikashop_display($msg['msg'], $msg['type']);
		}

		if($name !== 'cart')
			return $messagesToReturn;

		$cart = $this->getCart();
		if(empty($cart->messages))
			return $messagesToReturn;

		foreach($cart->messages as $msg) {
			hikashop_display($msg['msg'], $msg['type']);
		}

		return $messagesToReturn;
	}

	public function isMessages($name = null) {
		if(empty($this->messages))
			return false;
		if($name === null)
			return true;
		foreach($this->messages as $n => $msg) {
			if($n !== $name && strpos($n,  $name.'.') !== 0)
				continue;
			return true;
		}
		return false;
	}
}

class hikashopCheckoutHelperInterface {
	protected $params = array();

	public function getParams() {
		return $this->params;
	}

	public function check(&$controller, &$params) {
		return true;
	}

	public function validate(&$controller, &$params, $data = array()) {
		return true;
	}

	public function display(&$view, &$params) {
	}

	public function haveEmptyContent(&$controller, &$params) {
		return false;
	}

	public function checkMarker($markerName, $oldMarkers, $newMarkers, &$controller, $params) {
		return true;
	}
}
