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
class hikashopCartClass extends hikashopClass {
	public $tables = array('cart_product', 'cart');
	public $pkeys = array('cart_id', 'cart_id');
	public $currentMode = '';

	protected static $cache = array();
	private static $current_cart_id = array(
		'cart' => 0,
		'wishlist' => 0
	);

	protected $db = null;
	protected $app = null;
	protected $config = null;
	protected $user = null;

	public function  __construct($config = array()) {
		$ret = parent::__construct($config);

		$this->db = JFactory::getDBO();
		$this->app = JFactory::getApplication();
		$this->config = hikashop_config();
		$this->user = hikashop_loadUser(true);

		return $ret;
	}

	public function getCurrentCartId($type = 'cart') {
		if(hikashop_isClient('administrator'))
			return false;

		if(empty($type))
			$type = 'cart';
		$type = strtolower($type);
		if(!in_array($type, array('cart', 'wishlist')))
			return false;

		if($type == 'wishlist' && !$this->config->get('enable_wishlist'))
			return false;
		if($type == 'wishlist' && empty($this->user))
			return false;

		if(self::$current_cart_id[$type] > 0)
			return self::$current_cart_id[$type];

		$jsession = JFactory::getSession();
		$filters = array(
			'type' => 'cart.cart_type = ' . $this->db->Quote($type),
			'session' => 'cart.session_id = ' . $this->db->Quote($jsession->getId())
		);

		if((int)@$this->user->user_id > 0)
			$filters['session'] .= ' OR cart.user_id = ' . (int)$this->user->user_id;

		$query = 'SELECT cart.cart_id FROM #__hikashop_cart AS cart ' .
			' WHERE ('.implode(') AND (', $filters) . ') '.
			' ORDER BY cart.cart_current DESC, cart.cart_modified DESC';
		$this->db->setQuery($query);
		$cart_id = $this->db->loadResult();

		if(empty($cart_id) || (int)$cart_id == 0)  {
			$cart_id = $this->_getCartFromSession($type);
			if(!$cart_id)
				return 0;
		}
		$element = (int)$cart_id;

		$options = array(
			'path' => '/',
			'samesite'=> 'Lax',
		);
		if((int)@$this->user->user_id > 0) {
			$options['expires'] = time() - 3600;
			$this->setCookie('hikashop_'.$type.'_id', '', $options);
			$this->setCookie('hikashop_'.$type.'_session_id', '', $options);
		} else {
			$delay = (int)$this->config->get('cart_cookie_retaining_period', 31557600);
			$options['expires'] = time() + $delay;
			$this->setCookie('hikashop_'.$type.'_id', $element, $options);
			$this->setCookie('hikashop_'.$type.'_session_id', $jsession->getId(), $options);
		}

		self::$current_cart_id[$type] = $element;

		return self::$current_cart_id[$type];
	}

	private function setCookie($name, $value, $options) {
		$version = (float)phpversion();
		if($version > 7.2) {
			@setcookie($name, $value, $options);
		} else {
			$header = array('Set-Cookie: '.$name.'='.$value);
			if(isset($options['expires'])) {
				$header[] = 'expires='.$options['expires'];
			}
			if(isset($options['path'])) {
				$header[] = 'path='.$options['path'];
			}
			if(isset($options['samesite'])) {
				$header[] = 'SameSite='.$options['samesite'];
			}
			header(implode('; ', $header));
		}
	}

	private function _getCartFromSession($type) {

		$session_id = hikaInput::get()->getVar('session_id');
		$cart_id = hikashop_getCID('cart_id');
		if(!empty($session_id) && !empty($cart_id)) {
			$_COOKIE['hikashop_'.$type.'_id'] = $cart_id;
			$_COOKIE['hikashop_'.$type.'_session_id'] = $session_id;
		}

		if(!empty($this->user) || empty($_COOKIE['hikashop_'.$type.'_id']) || empty($_COOKIE['hikashop_'.$type.'_session_id']))
			return 0;

		if(!preg_match('/^[-,a-zA-Z0-9]{1,128}$/', $_COOKIE['hikashop_'.$type.'_session_id']))
			return 0;

		$query = 'SELECT cart.cart_id FROM #__hikashop_cart AS cart ' .
			' WHERE cart.session_id = ' . $this->db->Quote($_COOKIE['hikashop_'.$type.'_session_id']) .
			' AND cart.cart_id = ' . (int)$_COOKIE['hikashop_'.$type.'_id'] .
			' AND cart.user_id = 0';
		$this->db->setQuery($query);
		$cart_id = $this->db->loadResult();

		if(!$cart_id)
			return 0;

		$element = new stdClass();
		$element->cart_id = $cart_id;
		$jsession = JFactory::getSession();
		$element->session_id = $jsession->getId();
		$status = parent::save($element);

		if($status)
			return $cart_id;
		return 0;
	}

	public function get($element, $default = null, $options = array()) {
		if(empty(self::$cache))
			self::$cache = array();
		if(empty(self::$cache['get']))
			self::$cache['get'] = array(0 => null);
		if(empty(self::$cache['full']))
			self::$cache['full'] = array();

		if($element === 'reset_cache')
			return $this->resetCartCache($default);

		if((int)$element == 0)
			$element = $this->getCurrentCartId();
		if($element === false)
			return false;

		$from_cache = isset(self::$cache['get'][$element]) && isset(self::$cache['get'][$element]->cart_id);

		if(!$from_cache)
			self::$cache['get'][$element] = parent::get($element, $default);

		if(!is_object(self::$cache['get'][$element]))
			return self::$cache['get'][$element];

		if(!empty(self::$cache['get'][$element]->cart_params) && is_string(self::$cache['get'][$element]->cart_params)) {
			self::$cache['get'][$element]->cart_params = json_decode(self::$cache['get'][$element]->cart_params);
		}
		if(empty(self::$cache['get'][$element]->cart_params))
			self::$cache['get'][$element]->cart_params = new stdClass();

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$this->app->triggerEvent('onBeforeCartLoad', array( &self::$cache['get'][$element], &$options ) );

		if(!hikashop_isClient('administrator') && empty($options['skip_user_check'])) {
			if(self::$cache['get'][$element]->cart_type == 'wishlist' && !$this->config->get('enable_wishlist')) {
				unset(self::$cache['get'][$element]);
				self::$cache['get'][$element] = false;
				return self::$cache['get'][$element];
			}

			if(empty($this->user))
				$this->user = hikashop_loadUser(true);

			$jsession = JFactory::getSession();
			$my_cart = (!empty($this->user) && (int)self::$cache['get'][$element]->user_id == (int)$this->user->user_id) || (self::$cache['get'][$element]->session_id == $jsession->getId());
			$share = (self::$cache['get'][$element]->cart_type == 'wishlist') ? self::$cache['get'][$element]->cart_share : '';

			if( !$my_cart && ( empty($share) || $share == 'nobody' || ($share == 'registered' && empty($this->user)) ) ) {
				unset(self::$cache['get'][$element]);
				self::$cache['get'][$element] = false;
				return self::$cache['get'][$element];
			}

			if((int)self::$cache['get'][$element]->user_id == 0 && !empty($this->user) && !empty($this->user->user_id)) {
				self::$cache['get'][$element]->user_id = (int)$this->user->user_id;
			}
			if((int)self::$cache['get'][$element]->user_id > 0 && !empty($this->user) && (int)self::$cache['get'][$element]->user_id == (int)$this->user->id) {
				self::$cache['get'][$element]->user_id = (int)$this->user->user_id;
			}
		}

		if(!$from_cache) {
			$filters = array(
				'cart_product.cart_id = '.(int)$element, //self::$cache['get'][$element]->cart_id,
			);
			hikashop_addACLFilters($filters, 'product_access', 'product');

			$query = 'SELECT cart_product.*, product.product_type, product.product_parent_id '.
				' FROM ' . hikashop_table('cart_product').' AS cart_product '.
				' LEFT JOIN ' . hikashop_table('product').' AS product ON cart_product.product_id = product.product_id '.
				' WHERE (' . implode(') AND (', $filters) . ') '.
				' ORDER BY cart_product.cart_product_modified ASC';
			$this->db->setQuery($query);
			$products = $this->db->loadObjectList('cart_product_id');

			self::$cache['get'][$element]->cart_products = array();
			self::$cache['get'][$element]->additional = array();
			foreach($products as $k => &$product) {
				$product->product_id = (int)$product->product_id;
				$product->cart_product_id = (int)$product->cart_product_id;
				$product->cart_product_quantity = (int)$product->cart_product_quantity;
				$product->cart_product_modified = (int)$product->cart_product_modified;
				if(isset($product->cart_product_parent_id))
					$product->cart_product_parent_id = (int)$product->cart_product_parent_id;
				else
					$product->cart_product_parent_id = 0;
				if(isset($product->cart_product_option_parent_id))
					$product->cart_product_option_parent_id = (int)$product->cart_product_option_parent_id;
				else
					$product->cart_product_option_parent_id = 0;

				if($product->product_type == 'variant') {
					$parentProduct = new stdClass();
					$parentProduct->cart_product_id = 'p'.$product->cart_product_id;
					$parentProduct->cart_id = (int)$element;
					$parentProduct->product_id = (int)$product->product_parent_id;
					$parentProduct->cart_product_quantity = 0;
					$parentProduct->product_type = 'main';
					$parentProduct->product_parent_id = 0;

					self::$cache['get'][$element]->cart_products['p'.$k] =& $parentProduct;

					$product->cart_product_parent_id = $parentProduct->cart_product_id;

					unset($parentProduct);
				}

				if($product->product_id == 0)
					self::$cache['get'][$element]->additional[$k] =& $product;
				else
					self::$cache['get'][$element]->cart_products[$k] =& $product;
			}
			unset($product);
			$this->app->triggerEvent('onAfterCartLoad', array( &self::$cache['get'][$element], &$options ) );
		}

		return $this->getCloneCache('get', $element);
	}

	protected function resetCartCache($default = null) {
		$currencyClass = hikashop_get('class.currency');
		$currencyClass->getTaxType(true);
		$addressClass = hikashop_get('class.address');
		$addressClass->getCurrentUserAddress('reset_cache');

		hikashop_loadUser(false, true);
		$this->user = hikashop_loadUser(true);

		if(is_numeric($default))
			$default = (int)$default;
		if(is_int($default) && isset(self::$cache['get'][$default])) {
			if(isset(self::$cache['full'][$default]) && !empty(self::$cache['full'][$default]->messages)) {
				if(!isset(self::$cache['msg']))
					self::$cache['msg'] = array();
				self::$cache['msg'][$default] = self::$cache['full'][$default]->messages;
			}
			unset(self::$cache['get'][$default]);
			if(isset(self::$cache['full'][$default]))
				unset(self::$cache['full'][$default]);
			if(self::$current_cart_id['cart'] == (int)$default)
				self::$current_cart_id['cart'] = 0;
			return true;
		}
		if(is_array($default)) {
			hikashop_toInteger($default);
			foreach($default as $k) {
				if(!isset(self::$cache['msg']))
					self::$cache['msg'] = array();
				if(isset(self::$cache['full'][$k]) && !empty(self::$cache['full'][$k]->messages)) {
					self::$cache['msg'][$k] = self::$cache['full'][$k]->messages;
				}
				if(isset(self::$cache['get'][$k]))
					unset(self::$cache['get'][$k]);
				if(isset(self::$cache['full'][$k]))
					unset(self::$cache['full'][$k]);
			}
			if(in_array(self::$current_cart_id['cart'], $default))
				self::$current_cart_id['cart'] = 0;
			return true;
		}
		if($default === null) {
			if(!isset(self::$cache['msg']))
				self::$cache['msg'] = array();
			foreach(self::$cache['full'] as $k => $v) {
				if(!empty(self::$cache['full'][$k]->messages))
					self::$cache['msg'][$k] = self::$cache['full'][$k]->messages;
			}
			self::$cache['get'] = array(0 => null);
			self::$cache['full'] = array();
			self::$current_cart_id = array('cart' => 0, 'wishlist' => 0);
			return true;
		}
		return false;
	}

	public function delete(&$elementsToDelete, $user_id = null) {
		if(!is_array($elementsToDelete))
			$elements = array($elementsToDelete);
		else
			$elements = $elementsToDelete;
		hikashop_toInteger($elements);

		if(!hikashop_isClient('administrator')) {
			if(is_null($user_id))
				$user_id = @$this->user->user_id;
			$jsession = JFactory::getSession();
			$query = 'SELECT cart_id FROM '.hikashop_table('cart').
				' WHERE ((user_id = ' . (int)$user_id.') OR (session_id = ' . $this->db->Quote($jsession->getId()) . ')) AND cart_id IN (' . implode(',', $elements) . ')';
			$this->db->setQuery($query);
			$user_elements = $this->db->loadColumn();
			if(count($elements) != count($user_elements)) {
				hikashop_toInteger($user_elements);
				$elements = $user_elements;
			}

			if(empty($elements))
				return false;
		}

		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$this->app->triggerEvent('onBeforeCartDelete', array( &$elements, &$do ) );

		if(!$do)
			return false;

		$ret = parent::delete($elements);
		if($ret) {
			$this->get('reset_cache');

			$this->app->triggerEvent('onAfterCartDelete', array( &$elements ) );
		}
		return $ret;
	}

	public function save(&$element) {

		if(!hikashop_isClient('administrator') && !empty($element->cart_id)) {
			$element->cart_id = (int)$element->cart_id;

			$element->old = $this->get($element->cart_id);
			if(empty($element->old))
				return false;

			if($element->old->cart_type == 'wishlist' && !empty($element->old->user_id) && (empty($this->user) || $element->old->user_id != $this->user->user_id))
				return false;

			if(empty($element->old->user_id) && empty($this->user)) {
				$jsession = JFactory::getSession();
				if($element->old->session_id != $jsession->getId())
					return false;
			}
		}

		$new = empty($element->cart_id);

		if(!$new)
			$element->cart_id = (int)$element->cart_id;

		if(!hikashop_isClient('administrator') && empty($element->cart_id)) {
			$jsession = JFactory::getSession();

			$element->user_id = !empty($this->user) ? (int)$this->user->user_id : 0;
			$element->session_id = $jsession->getId();
		}

		if(isset($element->cart_name))
			$element->cart_name = strip_tags($element->cart_name);

		if(!hikashop_isClient('administrator'))
			$element->cart_currency_id = hikashop_getCurrency();

		if(!empty($element->cart_params) && is_string($element->cart_params))
			$element->cart_params = json_decode($element->cart_params);
		if(empty($element->cart_params) && !empty($element->old) && !empty($element->old->cart_params))
			$element->cart_params = $element->old->cart_params;
		if(empty($element->cart_params))
			$element->cart_params = new stdClass();

		if(isset($element->cart_share)) {
			$cartShareType = hikashop_get('type.cart_share');
			$cartShareValues = $cartShareType->load();
			if(!isset($cartShareValues[ $element->cart_share ]))
				unset($element->cart_share);
		}
		if($new && empty($element->cart_share))
			$element->cart_share = 'nobody';
		if(isset($element->cart_share) && $element->cart_share == 'email' && empty($element->cart_params->token))
			$element->cart_params->token = JUserHelper::genRandomPassword(12);

		if(isset($element->cart_type) && !in_array($element->cart_type, array('cart', 'wishlist')))
			unset($element->cart_type);
		if(empty($element->cart_type) && $new)
			$element->cart_type = 'cart';

		if($element->cart_type == 'wishlist' && $new) {
			if(!$this->config->get('enable_multiwishlist', 1) && $this->getCurrentCartId('wishlist') > 0)
				return false;
		}

		if(isset($element->cart_payment_id))
			$element->cart_payment_id = (int)$element->cart_payment_id;

		if(isset($element->cart_coupon)) {
			if(is_array($element->cart_coupon))
				$element->cart_coupon = implode("\r\n", $element->cart_coupon);
			$element->cart_coupon = trim($element->cart_coupon);
		}

		if(isset($element->cart_billing_address_id))
			$element->cart_billing_address_id = max((int)$element->cart_billing_address_id, 0);

		if(isset($element->cart_shipping_ids) && is_array($element->cart_shipping_ids))
			$element->cart_shipping_ids = implode(',', $element->cart_shipping_ids);

		if(isset($element->cart_shipping_address_ids))
			$element->cart_shipping_address_ids = max((int)$element->cart_shipping_address_ids, 0);

		if(!empty($element->cart_products)) {
			foreach($element->cart_products as $k => &$cart_product) {
				if(!empty($cart_product->cart_id) && ($new || (int)$cart_product->cart_id != (int)$element->cart_id))
					unset($element->cart_products[$k]);
			}
			unset($cart_product);
		}

		if(hikashop_level(2)) {
			if(!empty($element->cart_fields) && !is_string($element->cart_fields))
				$element->cart_fields = json_encode($element->cart_fields);
		} else {
			unset($element->cart_fields);
		}

		if(!empty($element->additional))
			$element->cart_params->additional = $element->additional;

		if(!hikashop_isClient('administrator') && !empty($element->cart_type)) {
			$current_cart_id = $this->getCurrentCartId($element->cart_type);
			$element->cart_current = (empty($current_cart_id) || (!$new && $element->cart_id == $current_cart_id));
		} elseif(isset($element->cart_current)) {
			$element->cart_current = (int)$element->cart_current;
		}

		if(!isset($element->cart_ip) && empty($element->cart_id) && $this->config->get('cart_ip', 1)) {
			$element->cart_ip = hikashop_getIP();
		}

		$element->cart_modified = time();

		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$this->app->triggerEvent('onBeforeCartSave', array( &$element, &$do ) );

		if(!$do)
			return false;

		if(!empty($element->cart_params) && !is_string($element->cart_params))
			$element->cart_params = json_encode($element->cart_params);
		if(empty($element->cart_params))
			$element->cart_params = '';

		unset($element->volume);
		unset($element->volume_unit);
		unset($element->weight);
		unset($element->weight_unit);
		unset($element->total_quantity);

		if(@$element->shipping === null)
			unset($element->shipping);
		if(@$element->payment === null)
			unset($element->payment);
		if(@$element->coupon === null)
			unset($element->coupon);
		if(@$element->products === null)
			unset($element->products);

		$status = parent::save($element);

		if(!empty($element->cart_params) && is_string($element->cart_params))
			$element->cart_params = json_decode($element->cart_params);
		if(empty($element->cart_params))
			$element->cart_params = new stdClass();

		if(!$status)
			return $status;

		$this->get('reset_cache', $status);

		$element->cart_id = $status;

		if(hikashop_isClient('administrator') && !empty($element->cart_current) && !empty($element->user_id)) {
			$query = 'UPDATE ' . hikashop_table('cart') . ' SET cart_current = 0 WHERE cart_current = 1 AND cart_id != ' . (int)$status . ' AND user_id = ' . (int)$element->user_id;
			$this->db->setQuery($query);
			$this->db->execute();
		}

		$query = 'SELECT * FROM ' . hikashop_table('cart_product') . ' WHERE cart_id = ' . (int)$status;
		$this->db->setQuery($query);
		$dbProducts = $this->db->loadObjectList('cart_product_id');

		$inserts = array();
		$updates = array();

		if(!empty($element->cart_products)) {
			$update_remove_keys = array('cart_product_id', 'cart_product_modified', 'product_parent_id', 'product_type', 'cart_product_total_quantity', 'cart_product_total_variants_quantity');

			foreach($element->cart_products as &$cart_product) {
				if(empty($cart_product->cart_product_quantity))
					continue;
				if(isset($cart_product->cart_product_id) && is_string($cart_product->cart_product_id) && substr($cart_product->cart_product_id, 0, 1) == 'p')
					continue;

				$cart_product->cart_id = $status;

				if(empty($cart_product->cart_product_id) || !isset($dbProducts[(int)$cart_product->cart_product_id])) {
					$i = get_object_vars($cart_product);

					unset($i['product_parent_id']);
					unset($i['product_type']);
					unset($i['extra']);

					if(isset($i['cart_product_parent_id']) && substr($i['cart_product_parent_id'], 0, 1) == 'p')
						unset($i['cart_product_parent_id']);

					$inserts[] = $i;
					continue;
				}

				$updates[ (int)$cart_product->cart_product_id ] = array();

				$n_keys = get_object_vars($cart_product);
				ksort($n_keys);

				$o_keys = get_object_vars($dbProducts[(int)$cart_product->cart_product_id]);
				ksort($o_keys);

				foreach($update_remove_keys as $k) {
					unset($n_keys[$k]);
					unset($o_keys[$k]);
				}

				$keys = array_diff_assoc($n_keys, $o_keys);

				if(isset($keys['cart_product_parent_id']) && substr($keys['cart_product_parent_id'], 0, 1) == 'p')
					unset($keys['cart_product_parent_id']);

				if(!empty($keys)) {
					$keys['cart_product_modified'] = time();
					$cart_product->cart_product_modified = time();
					$updates[ (int)$cart_product->cart_product_id ] = $keys;
				}
			}
			unset($cart_product);
		}

		$filters = array(
			'cart_id = ' . (int)$status
		);
		if(!empty($updates))
			$filters[] = 'cart_product_id NOT IN ('.implode(',', array_keys($updates)) . ')';
		$query = 'DELETE FROM ' . hikashop_table('cart_product') . ' WHERE (' . implode(') AND (', $filters) . ')';
		$this->db->setQuery($query);
		$this->db->execute();

		foreach($updates as $cart_product_id => $update) {
			if(empty($update))
				continue;

			$data = array();
			foreach($update as $k => $v) {
				if(is_array($v) || is_object($v))
					continue;
				$data[] = $this->db->quoteName($k) . ' = ' . ((is_int($v)) ? ((int)$v) : $this->db->Quote($v));
			}
			$query = 'UPDATE ' . hikashop_table('cart_product') . ' SET ' . implode(', ', $data) . ' WHERE cart_product_id = ' . (int)$cart_product_id . ' AND cart_id = ' . (int)$status;
			$this->db->setQuery($query);
			$this->db->execute();
		}

		$insert_timestamp = time();
		$insert_data_struct = array(
			'cart_id' => $status,
			'cart_product_modified' => $insert_timestamp,
			'cart_product_parent_id' => 0,
			'cart_product_option_parent_id' => 0,
		);

		foreach($inserts as $insert) {
			if(empty($insert))
				continue;
			unset($insert['cart_product_id']);
			foreach($insert as $k => $v) {
				if((is_int($v) || is_string($v) || is_float($v)) && !isset($insert_data_struct[$k]))
					$insert_data_struct[$k] = null;

				if(empty($insert['options']) || !hikashop_level(1))
					continue;

				foreach($insert['options'] as $insert_option) {
					foreach($insert_option as $opt_k => $opt_v) {
						if((is_int($opt_v) || is_string($opt_v)) && !isset($insert_data_struct[$opt_k]))
							$insert_data_struct[$opt_k] = null;
					}
				}
			}
		}

		$sql_data = array();

		$product_options = 0;

		foreach($inserts as $insert) {
			if(empty($insert))
				continue;

			$data = (array)$insert_data_struct;
			foreach($data as $k => &$d) {
				if($d !== null)
					continue;
				$d = isset($insert[$k]) ? $this->db->Quote($insert[$k]) : 'NULL';
			}
			unset($d);

			if(empty($insert['options']) || !hikashop_level(1)) {
				$sql_data[] = implode(',', $data);
			} else {
				$product_options++;

				$data['cart_product_parent_id'] = $product_options;
				$sql_data[] = implode(',', $data);

				foreach($insert['options'] as $insert_option) {
					$insert_option = get_object_vars($insert_option);

					$data = (array)$insert_data_struct;
					$data['cart_product_option_parent_id'] = $product_options;
					foreach($data as $k => &$d) {
						if($d !== null)
							continue;
						$d = isset($insert_option[$k]) ? $this->db->Quote($insert_option[$k]) : 'NULL';
					}
					unset($d);

					$sql_data[] = implode(',', $data);
				}
			}
		}

		if(!empty($sql_data)) {
			$columns = array_map(array($this,'_quoteValues'), array_keys($insert_data_struct));
			$query = 'INSERT INTO ' . hikashop_table('cart_product') . ' ('.implode(',', $columns).') VALUES ('.implode('), (', $sql_data).')';
			$this->db->setQuery($query);
			$this->db->execute();
			if($product_options > 0) {
				$query = 'UPDATE ' . hikashop_table('cart_product') . ' AS cp '.
					' INNER JOIN ' . hikashop_table('cart_product') . ' AS cpp ON cp.cart_product_option_parent_id = cpp.cart_product_parent_id AND cp.cart_id = cpp.cart_id '.
					' SET cp.cart_product_option_parent_id = cpp.cart_product_id, cpp.cart_product_parent_id = 0 '.
					' WHERE cp.cart_product_option_parent_id > 0 '.
						' AND cp.cart_product_option_parent_id < ' . (int)($product_options + 1) .
						' AND cp.cart_id = ' . (int)$status .
						' AND cp.cart_product_modified = ' . (int)$insert_timestamp;
				$this->db->setQuery($query);
				$this->db->execute();
			}
		}

		$this->app->triggerEvent('onAfterCartSave', array( &$element ) );
		return $status;
	}

	protected function _quoteValues($a){
		return "`" . str_replace('`', '', $a) . "`";
	}

	public function &getFullCart($cart_id = 0, $options = array()) {
		if((int)$cart_id == 0)
			$cart_id = $this->getCurrentCartId();
		if(empty($cart_id)) {
			$ret = false;
			if(isset(self::$cache['msg'][0])) {
				$ret = new stdClass();
				$ret->messages = self::$cache['msg'][0];
			}
			return $ret;
		}
		$cart_id = (int)$cart_id;

		if(isset(self::$cache['full'][$cart_id]))
			return $this->getCloneCache('full', $cart_id);

		$cart = $this->get($cart_id, null, $options);
		if(empty($cart))
			return $cart;

		$cart->total = new stdClass();
		$cart->full_total = new stdClass();
		$cart->messages = array();
		$cart->package = array();
		$cart->usable_methods = new stdClass();

		if(isset(self::$cache['msg'][$cart_id])) {
			$cart->messages = self::$cache['msg'][$cart_id];
			unset(self::$cache['msg'][$cart_id]);
		}

		$discount_before_tax = (int)$this->config->get('discount_before_tax', 0);
		$zones = array();
		$zone_id = 0;
		$tax_zone_id = 0;

		if($cart->cart_type != 'wishlist') {

			if(!isset($options['auto_select_addresses']))
				$options['auto_select_addresses'] = (bool)$this->config->get('auto_select_addresses', 1);

			$addressClass = hikashop_get('class.address');
			$cart->usable_addresses = new stdClass();
			$cart->usable_addresses->billing = $addressClass->loadUserAddresses((int)$cart->user_id, 'billing');
			$cart->usable_addresses->shipping = $addressClass->loadUserAddresses((int)$cart->user_id, 'shipping');

			$address = null;
			if(!empty($cart->cart_billing_address_id) && isset($cart->usable_addresses->billing[(int)$cart->cart_billing_address_id]))
				$address = $cart->usable_addresses->billing[(int)$cart->cart_billing_address_id];

			if(empty($address) && !empty($options['auto_select_addresses'])) {
				if(!empty($cart->usable_addresses->billing) && is_array($cart->usable_addresses->billing)) {
					$address = reset($cart->usable_addresses->billing);
					$cart->cart_billing_address_id = (int)$address->address_id;
				}
			}

			if(!empty($address)) {
				$cart->billing_address = $address;
			} else {
				$cart->cart_billing_address_id = 0;
			}
			unset($address);

			$address = null;
			if(!empty($cart->cart_shipping_address_ids) && isset($cart->usable_addresses->shipping[(int)$cart->cart_shipping_address_ids]))
				$address = $cart->usable_addresses->shipping[(int)$cart->cart_shipping_address_ids];

			if(empty($address) && !empty($options['auto_select_addresses'])) {
				if(!empty($cart->usable_addresses->shipping) && is_array($cart->usable_addresses->shipping)) {
					$address = reset($cart->usable_addresses->shipping);
					$cart->cart_shipping_address_ids = (int)$address->address_id;
				}
			}

			if(!empty($address)) {
				$cart->shipping_address = $address;
			} else {
				$cart->cart_shipping_address_ids = 0;
			}
			unset($address);

			if((int)$cart->user_id > 0 && (int)$cart->user_id == hikashop_loadUser(false)) {
				if((int)$this->app->getUserState(HIKASHOP_COMPONENT.'.'.'billing_address', 0) != (int)$cart->cart_billing_address_id) {
					$this->app->setUserState(HIKASHOP_COMPONENT.'.'.'billing_address', (int)$cart->cart_billing_address_id);
				}
				if((int)$this->app->getUserState(HIKASHOP_COMPONENT.'.'.'shipping_address', 0) != (int)$cart->cart_shipping_address_ids)
					$this->app->setUserState(HIKASHOP_COMPONENT.'.'.'shipping_address', (int)$cart->cart_shipping_address_ids);
			}

			if(!empty($cart->billing_address) || !empty($cart->shipping_address)) {
				$address_type = '';
				$addressArray = array();
				if(!empty($cart->billing_address)) {
					$addressArray[] =& $cart->billing_address;
				}
				if(!empty($cart->shipping_address) && !is_array($cart->shipping_address)) {
					$addressArray[] =& $cart->shipping_address;
				}
				if(!empty($cart->shipping_address) && is_array($cart->shipping_address)) {
					foreach($cart->shipping_address	as &$addr) {
						$addressArray[] =& $addr;
					}
					unset($addr);
				}
				$addressClass->loadZone($addressArray, 'parent');

				if(!empty($addressClass->fields))
					$cart->address_fields = $addressClass->fields;
			}
		}

		if($cart->cart_type == 'wishlist' && !empty($cart->user_id) && (empty($this->user) || $cart->user_id != $this->user->user_id)) {
			$userClass = hikashop_get('class.user');
			$user = $userClass->get($cart->user_id);
			if(!empty($user)) {
				$cart->user = new stdClass();
				$cart->user->user_id = (int)$user->user_id;
				$cart->user->user_cms_id = (int)$user->user_cms_id;
				$cart->user->user_email = $user->user_email;
				if(!empty($user->username))
					$cart->user->username = $user->username;
				if(!empty($user->email))
					$cart->user->email = $user->email;
			}else {
				$cart->user_id = 0;
			}
		}

		if(empty($cart->cart_products)) {
			$p = new stdClass();
			$p->price_value_with_tax = 0;
			$p->price_value = 0;
			$p->price_currency_id = 0;
			$cart->full_total->prices = array(
				0 => $p
			);
			$cart->products = array();

			return $cart;
		}

		$currencyClass = hikashop_get('class.currency');
		$productClass = hikashop_get('class.product');
		$main_currency = (int)$this->config->get('main_currency', 1);
		$currency_id = hikashop_getCurrency();
		$quantityDisplayType = null;

		if(!in_array($currency_id, $currencyClass->publishedCurrencies()))
			$currency_id = $main_currency;

		$cart->cart_currency_id = $currency_id;

		$filters = array(
			'cart_product.cart_id = '.(int)$cart->cart_id,
			'cart_product.product_id > 0'
		);
		hikashop_addACLFilters($filters, 'product_access', 'product');

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$this->app->triggerEvent('onBeforeCartProductsLoad', array( &$cart, &$options, &$filters) );

		$query = 'SELECT cart_product.cart_product_id, cart_product.cart_product_quantity, cart_product.cart_product_option_parent_id, cart_product.cart_product_parent_id, cart_product.cart_product_wishlist_id, product.* '.
			' FROM ' . hikashop_table('cart_product').' AS cart_product '.
			' LEFT JOIN ' . hikashop_table('product').' AS product ON cart_product.product_id = product.product_id '.
			' WHERE (' . implode(') AND (', $filters) . ') '.
			' ORDER BY cart_product.cart_product_id ASC';
		$this->db->setQuery($query);
		$cart->products = $this->db->loadObjectList('cart_product_id');

		$parent_product_ids = array();
		foreach($cart->products as $k => $product) {
			if(!empty($product->product_parent_id))
				$parent_product_ids[$product->cart_product_id] = (int)$product->product_parent_id;
			$cart->products[$k]->product_name = hikashop_translate($product->product_name);
		}

		$parent_products = null;
		if(!empty($parent_product_ids)) {
			$query = 'SELECT product.* '.
				' FROM ' .  hikashop_table('product').' AS product '.
				' WHERE product.product_id IN ('.implode(',', $parent_product_ids) .') AND product.product_type = ' . $this->db->Quote('main');
			$this->db->setQuery($query);
			$parent_products = $this->db->loadObjectList('product_id');

			foreach($parent_product_ids as $k => $v) {
				if(!isset($parent_products[$v]))
					continue;
				$p = clone($parent_products[$v]);
				if(!isset($p->cart_product_id)) {
					$p->cart_product_id = 'p'.$k;
					$p->cart_product_quantity = 0;
					$p->cart_product_option_parent_id = 0;
					$p->cart_product_parent_id = 0;
				}

				$p->product_name = hikashop_translate($p->product_name);

				$cart->products['p'.$k] = $p;
				$cart->products[$k]->cart_product_parent_id = 'p'.$k;
				unset($p);
			}
		}

		$checkCart = $this->checkCartQuantities($cart, $parent_products);

		$updateCart = false;
		foreach($cart->products as $cart_product_id => $product) {
			if(empty($product->product_id))
				continue;

			if(empty($product->cart_product_quantity) && empty($product->old->quantity))
				continue;

			if(isset($product->old->quantity) && (int)$product->old->quantity != (int)$product->cart_product_quantity) {
				$already_msg = false;
				foreach($cart->messages as $msg) {
					if(!isset($msg['product_id']) || $msg['product_id'] != (int)$product->product_id)
						continue;
					$already_msg = true;
					break;
				}
				if(!$already_msg) {
					$cart->messages[] = array(
						'msg' => JText::sprintf('PRODUCT_QUANTITY_CHANGED', $product->product_name, $product->old->quantity, (int)$product->cart_product_quantity),
						'product_id' => (int)$product->product_id,
						'type' => 'notice'
					);
				}
				$updateCart = true;

				if(isset($cart->cart_products[$cart_product_id]) && $cart->cart_products[$cart_product_id]->cart_product_quantity != $product->cart_product_quantity && $cart->cart_products[$cart_product_id]->cart_product_quantity == $product->old->quantity)
					$cart->cart_products[$cart_product_id]->cart_product_quantity = $product->cart_product_quantity;
			}

		}
		if($updateCart) {
			$this->save($cart);
		}

		$ids = array();
		$mainIds = array();
		foreach($cart->products as $product) {
			$ids[] = (int)$product->product_id;
			$mainIds[] = (empty($product->product_parent_id) || (int)$product->product_parent_id == 0) ? (int)$product->product_id : (int)$product->product_parent_id;
		}

		if(!empty($ids)) {
			$query = 'SELECT * FROM '.hikashop_table('file').
				' WHERE file_ref_id IN (' . implode(',', $ids) . ') AND file_type IN (' . $this->db->Quote('product') . ',' . $this->db->Quote('file') . ') '.
				' ORDER BY file_ref_id ASC, file_ordering ASC, file_id ASC';
			$this->db->setQuery($query);
			$images = $this->db->loadObjectList();
			if(!empty($images)) {
				foreach($cart->products as &$product) {
					$productClass->addFiles($product, $images);
				}
			}
		}

		if(!empty($mainIds)) {

			$query = 'SELECT product_category.*, category.* '.
				' FROM ' . hikashop_table('product_category') . ' AS product_category '.
				' LEFT JOIN ' . hikashop_table('category') . ' AS category ON product_category.category_id = category.category_id '.
				' WHERE product_category.product_id IN (' . implode(',', $mainIds) . ')'.
				' ORDER BY product_category.ordering ASC';
			$this->db->setQuery($query);
			$categories = $this->db->loadObjectList();

			$q = 'SELECT product_id FROM '.hikashop_table('product_related').
				' WHERE product_related_type = '.$this->db->quote('options').
				' AND product_id IN ('.implode(',', $mainIds).')';
			$this->db->setQuery($q);
			$optionsData = $this->db->loadObjectList();
		}

		$product_quantities = array();

		foreach($cart->products as &$product) {

			$product->categories = array();
			if(!empty($categories)) {
				foreach($categories as $category) {
					if($category->product_id == $product->product_id)
						$product->categories[] = $category;
				}
			}

			if(!empty($optionsData)) {
				foreach($optionsData as $option) {
					if($product->product_id == $option->product_id) {
						$product->has_options = true;
						break;
					}
				}
			}

			if(!empty($product->product_parent_id) && isset($parent_products[ (int)$product->product_parent_id ]))
				$product->parent_product = $parent_products[ (int)$product->product_parent_id ];

			if(empty($product_quantities[$product->product_id]))
				$product_quantities[$product->product_id] = 0;
			$product_quantities[$product->product_id] += (int)@$product->cart_product_quantity;

			if($product->product_parent_id > 0) {
				if(empty($product_quantities[$product->product_parent_id]))
					$product_quantities[$product->product_parent_id] = 0;
				$product_quantities[$product->product_parent_id] += (int)@$product->cart_product_quantity;
			}

			if($product->product_parent_id != 0 && isset($product->main_product_quantity_layout))
				$product->product_quantity_layout = $product->main_product_quantity_layout;

			if(empty($product->product_quantity_layout) || $product->product_quantity_layout == 'inherit') {
				$product->product_quantity_layout = $this->config->get('product_quantity_display', 'show_default_div');
				if(!empty($product->categories) ) {
					if(empty($quantityDisplayType))
						$quantityDisplayType = hikashop_get('type.quantitydisplay');
					foreach($product->categories as $category) {
						if(!empty($category->category_quantity_layout) && $quantityDisplayType->check($category->category_quantity_layout, $this->app->getTemplate())) {
							$product->product_quantity_layout = $category->category_quantity_layout;
							break;
						}
					}
				}
			}

			if($product->product_type == 'variant') {
				foreach($cart->products as &$product2) {
					if((int)$product->product_parent_id != (int)$product2->product_id)
						continue;

					if(!isset($product2->variants))
						$product2->variants = array();
					$product2->variants[] =& $product;
					break;
				}
				unset($product2);
			}
		}
		unset($product);
		unset($categories);

		if(!empty($ids)) {
			$query = 'SELECT hk_variant.*, hk_characteristic.* '.
				' FROM '.hikashop_table('variant').' AS hk_variant '.
				' LEFT JOIN '.hikashop_table('characteristic').' AS hk_characteristic ON hk_variant.variant_characteristic_id = hk_characteristic.characteristic_id '.
				' WHERE hk_variant.variant_product_id IN (' . implode(',', $ids) . ') '.
				' ORDER BY hk_variant.ordering, hk_characteristic.characteristic_value';
			$this->db->setQuery($query);
			$characteristics = $this->db->loadObjectList();
		}
		if(!empty($characteristics)) {
			foreach($cart->products as &$product) {

				$mainCharacteristics = array();
				foreach($characteristics as $characteristic) {
					if($product->product_id == $characteristic->variant_product_id) {
						if($product->product_type === 'variant') {
							if(empty($product->characteristics))
								$product->characteristics = array();
							$product->characteristics[] = $characteristic;
						} else {
							if(empty($mainCharacteristics[$product->product_id]))
								$mainCharacteristics[$product->product_id] = array();
							if(empty($mainCharacteristics[$product->product_id][$characteristic->characteristic_parent_id]))
								$mainCharacteristics[$product->product_id][$characteristic->characteristic_parent_id] = array();
							$mainCharacteristics[$product->product_id][$characteristic->characteristic_parent_id][$characteristic->characteristic_id] = $characteristic;
						}
					}
					if(!empty($product->options)) {
						foreach($product->options as $optionElement) {
							if((int)$optionElement->product_id != (int)$characteristic->variant_product_id)
								continue;

							if(empty($mainCharacteristics[$optionElement->product_id]))
								$mainCharacteristics[$optionElement->product_id] = array();
							if(empty($mainCharacteristics[$optionElement->product_id][$characteristic->characteristic_parent_id]))
								$mainCharacteristics[$optionElement->product_id][$characteristic->characteristic_parent_id] = array();
							$mainCharacteristics[$optionElement->product_id][$characteristic->characteristic_parent_id][$characteristic->characteristic_id] = $characteristic;
						}
					}
				}

				if($product->product_type === 'variant')
					continue;

				$this->app->triggerEvent('onAfterProductCharacteristicsLoad', array( &$product, &$mainCharacteristics, &$characteristics ) );

				if(!empty($product->variants)) {
					$this->addCharacteristics($product, $mainCharacteristics, $characteristics);
				}

				if(!empty($product->options)) {
					foreach($product->options as &$optionElement) {
						if(!empty($optionElement->variants)) {
							$this->addCharacteristics($optionElement, $mainCharacteristics, $characteristics);
						}
					}
					unset($optionElement);
				}
			}
			unset($product);
		}

		if(hikashop_level(2)) {
			$fieldsClass = hikashop_get('class.field');
			if(!empty($cart->cart_fields) && is_string($cart->cart_fields))
				$cart->cart_fields = json_decode($cart->cart_fields);

			$cart_fields = new stdClass();
			if(!empty($cart->cart_fields))
				$cart_fields = clone($cart->cart_fields);
			$cart_fields->products =& $cart->products;
			$cart_fields->cart_shipping_ids = @$cart->cart_shipping_ids;
			$cart_fields->cart_payment_id = @$cart->cart_payment_id;

			$cart->order_fields = $fieldsClass->getFields('frontcomp', $cart_fields, 'order');
			unset($cart_fields->products);

			$cart->item_fields = $this->loadFieldsForProducts($ids);
			foreach($cart->item_fields as $field) {
				$namekey = $field->field_namekey;
				foreach($cart->products as $k => &$product) {
					if(isset($product->$namekey) || !isset($cart->cart_products[$k]) || !isset($cart->cart_products[$k]->$namekey))
						continue;
					$product->$namekey = $cart->cart_products[$k]->$namekey;
				}
				unset($product);
			}
		}

		foreach($cart->products as &$product) {
			$product->cart_product_total_quantity = $product_quantities[$product->product_id];
			if($product->product_parent_id > 0)
				$product->cart_product_total_variants_quantity = $product_quantities[$product->product_parent_id];
			else
				$product->cart_product_total_variants_quantity = $product->cart_product_total_quantity;
		}
		unset($product);

		$cart_product_ids = array_merge($ids, $parent_product_ids);
		$cart_products = array();
		foreach($cart->products as &$product) {
			if(!isset($product->parent_product))
				$cart_products[] =& $product;
		}
		unset($product);

		if($cart->cart_type != 'wishlist') {
			$force_shipping = (int)$this->config->get('force_shipping', 0);
			$need_shipping = $force_shipping;
			if(!$need_shipping) {
				$tempPackage = $this->getWeightVolume($cart);
				if($tempPackage['weight']['value'] > 0)  {
					$need_shipping = true;
				}
			}

			$zone_id = null;

			if(!empty($cart->billing_address)) {
				$zone_id = $this->extractZone($cart->billing_address, $zones, true);
				$tax_zone_id = $zone_id;
			}

			if($need_shipping) {
				if(!empty($cart->shipping_address)) {
					$zone_id = $this->extractZone($cart->shipping_address, $zones, $zone_id);
					if($this->config->get('tax_zone_type', 'shipping') == 'shipping')
						$tax_zone_id = $zone_id;
				}

				if(!empty($cart->cart_shipping_ids)) {
					$shipping_ids = explode(',', $cart->cart_shipping_ids);
					hikashop_toInteger($shipping_ids);
					$query = 'SELECT shipping_id, shipping_params FROM ' . hikashop_table('shipping') . ' WHERE shipping_id IN(' . implode(',', $shipping_ids) . ')';
					$this->db->setQuery($query);
					$shippings = $this->db->loadObjectList('shipping_id');
					foreach($shippings as $shipping) {
						if(!empty($shipping->shipping_params) && is_string($shipping->shipping_params))
							$shipping->shipping_params = hikashop_unserialize($shipping->shipping_params);
						$zid = 0;
						if(!empty($shipping->shipping_params->shipping_override_address) && $this->config->get('tax_zone_type', 'shipping') == 'shipping') {
							$zid = explode(',',$this->config->get('main_tax_zone', $tax_zone_id));
							if(count($zid))
								$zid = array_shift($zid);
						}

						if(!empty($shipping->shipping_params->override_tax_zone) && is_numeric($shipping->shipping_params->override_tax_zone)) {
							$zid = $shipping->shipping_params->override_tax_zone;
						}
						if($zid) {
							$tax_zone_id = (int)$zid;
							break;
						}
					}
				}
			}

			if(empty($zone_id)) {
				$zone_id = $this->extractZone(null, $zones, true);
				$tax_zone_id = $zone_id;
			}
			$cart->package['zone'] = $zone_id;
		}

		$user_id = 0;
		if(hikashop_isClient('administrator') || !empty($options['force_user_prices']))
			$user_id = (int)$cart->user_id;

		$currencyClass->getPrices($cart_products, $cart_product_ids, $currency_id, $main_currency, $tax_zone_id, $discount_before_tax, $user_id);

		unset($cart_products);

		foreach($cart->products as &$product) {
			if(empty($product->variants))
				continue;
			foreach($product->variants as &$variant) {
				$productClass->checkVariant($variant, $product);
			}
			unset($variant);
		}
		unset($product);

		foreach($cart->products as &$product) {
			if(empty($product->parent_product))
				continue;
			$productClass->checkVariant($product, $product->parent_product);
		}
		unset($product);

		if(!$this->config->get('display_add_to_cart_for_free_products', 0)) {
			$notUsable = array();
			foreach($cart->products as $cart_product_id => $product) {
				if(empty($product->product_id) || empty($product->cart_product_quantity) || !empty($product->prices))
					continue;

				$notUsable[$cart_product_id] = array('id' => $cart_product_id, 'qty' => 0);
				$cart->messages[] = array('msg' => JText::sprintf('PRODUCT_NOT_AVAILABLE', $product->product_name), 'product_id' => $product->product_id, 'type' => 'notice');
			}
			if(!empty($notUsable)) {
				$saveCart = $this->updateProduct($cart, $notUsable);
				if($saveCart)
					$this->save($cart);
			}
		}

		foreach($cart->products as &$product) {
			$currencyClass->calculateProductPriceForQuantity($product);

			$product->product_name_original = $product->product_name;
			$product->product_name = hikashop_translate($product->product_name);
			$product->product_description_original = $product->product_description;
			$product->product_description = hikashop_translate($product->product_description);
		}
		unset($product);

		$currencyClass->calculateTotal($cart->products, $cart->total, $currency_id);
		$cart->full_total =& $cart->total;

		$this->app->triggerEvent('onAfterCartProductsLoad', array( &$cart ) );

		if(!empty($cart->additional)) {
			$currencyClass->addAdditionals($cart->additional, $cart->additional_total, $cart->full_total, $currency_id);
			$cart->full_total =& $cart->additional_total;
		}

		$cart->package = $this->getWeightVolume($cart);
		$cart->quantity = new stdClass();
		$cart->quantity->total = $cart->package['total_quantity'];
		$cart->quantity->items = $cart->package['total_items'];

		$this->calculateWeightAndVolume($cart);

		if(!empty($cart->cart_type) && $cart->cart_type == 'wishlist' && !empty($cart->cart_coupon))
			$cart->cart_coupon = '';

		if(!empty($cart->cart_coupon)) {
			if(is_string($cart->cart_coupon))
				$cart->cart_coupon = explode("\r\n", $cart->cart_coupon);

			$current_auto_coupon_key = null;
			$discountClass = hikashop_get('class.discount');
			foreach($cart->cart_coupon as $k => $coupon) {
				$discount = $discountClass->load($coupon);

				if(empty($discount))
					continue;

				if((bool)@$cart->cart_params->coupon_autoloaded != (bool)$discount->discount_auto_load)
					unset($cart->cart_coupon[$k]);

				if(empty($discount->discount_auto_load))
					continue;

				if(empty($current_auto_coupon_key))
					$current_auto_coupon_key = $this->generateHash($cart->products, $zone_id);
				if(empty($cart->cart_params->coupon_autoloaded))
					$cart->cart_params->coupon_autoloaded = $this->app->getUserState(HIKASHOP_COMPONENT.'.auto_coupon_key');
				if($current_auto_coupon_key != $cart->cart_params->coupon_autoloaded)
					unset($cart->cart_coupon[$k]);
			}

			if(empty($cart->cart_coupon)) {
				unset($cart->cart_params->coupon_autoloaded);
				if(isset(self::$cache['get'][$cart->cart_id])) {
					unset(self::$cache['get'][$cart->cart_id]->cart_params->coupon_autoloaded);
					self::$cache['get'][$cart->cart_id]->cart_coupon = $cart->cart_coupon;
				}

				$query = 'UPDATE '.hikashop_table('cart').' SET cart_coupon = \'\', cart_params = '.$this->db->Quote(json_encode($cart->cart_params)).' WHERE cart_id = '.(int)$cart->cart_id;
				$this->db->setQuery($query);
				$this->db->execute();
			}
		}

		if(hikashop_level(1) && empty($cart->cart_coupon) && (empty($cart->cart_type) || $cart->cart_type != 'wishlist')) {
			$this->loadAutoCoupon($cart);
		}

		if(!empty($cart->cart_coupon) && $cart->cart_type != 'wishlist') {
			if(empty($discountClass))
				$discountClass = hikashop_get('class.discount');

			$zoneClass = hikashop_get('class.zone');
			$parent_zones = $zoneClass->getZoneParents($zone_id);

			if(is_array($cart->cart_coupon)) {
				foreach($cart->cart_coupon as $coupon) {
					$cart->coupon = $discountClass->loadAndCheck($coupon, $cart->full_total, $parent_zones, $cart->products, true);
					if(!empty($cart->coupon))
						break;
				}
			} else {
				$cart->coupon = $discountClass->loadAndCheck($cart->cart_coupon, $cart->full_total, $parent_zones, $cart->products, true);
			}

			if(empty($cart->coupon) && hikashop_level(1) && $this->loadAutoCoupon($cart)) {
				$cart->coupon = $discountClass->loadAndCheck($cart->cart_coupon, $cart->full_total, $parent_zones, $cart->products, true);
			}
			if(!empty($cart->coupon)) {
				$cart->full_total =& $cart->coupon->total;
			} else {
				$cart->cart_coupon = array();
				unset($cart->coupon);
			}
		}

		$this->app->triggerEvent('onAfterCartCouponLoad', array( &$cart ) );

		$this->checkNegativeTax($cart->full_total);

		$cart->shipping = null;
		if($cart->cart_type != 'wishlist' && ($force_shipping || $cart->package['weight']['value'] > 0)) {
			if(!empty($cart->cart_shipping_ids))
				$cart->cart_shipping_ids = explode(',', $cart->cart_shipping_ids);
			else
				$cart->cart_shipping_ids = array();

			$shippingClass = hikashop_get('class.shipping');
			$cart->usable_methods->shipping = $shippingClass->getShippings($cart, true);

			if(empty($cart->usable_methods->shipping) && !empty($shippingClass->errors)) {
				$cart->usable_methods->shipping_errors = $shippingClass->errors;
			}

			$checkShipping = $shippingClass->checkCartMethods($cart, true);
			if(!$checkShipping) {
				$query = 'UPDATE '.hikashop_table('cart').' SET cart_shipping_ids = '.$this->db->Quote(implode(',', $cart->cart_shipping_ids)).' WHERE cart_id = '.(int)$cart_id;
				$this->db->setQuery($query);
				$this->db->execute();
				if(isset(self::$cache['get'][$cart_id]))
					self::$cache['get'][$cart_id]->cart_shipping_ids = implode(',', $cart->cart_shipping_ids);
			}

			$currencyClass->processShippings($cart->usable_methods->shipping, $cart, $zone_id);

			$cart->shipping = array();
			foreach($cart->cart_shipping_ids as $k => $shipping_id) {
				$warehouse_struct = array();
				if(strpos($shipping_id, '@') !== false) {
					list($shipping_id, $warehouse_id) = explode('@', $shipping_id, 2);
					if(preg_match_all('#([a-zA-Z])*([0-9]+)#iu', $warehouse_id, $keys))
						$warehouse_struct = array_combine($keys[1], $keys[2]);
					if(is_numeric($warehouse_id))
						$warehouse_id = (int)$warehouse_id;
				} else {
					$shipping_id = $shipping_id;
					$warehouse_id = 0;
				}
				$f = false;
				foreach($cart->usable_methods->shipping as $shipping) {
					if($shipping->shipping_id != $shipping_id)
						continue;

					if(is_numeric($shipping->shipping_warehouse_id))
						$shipping->shipping_warehouse_id = (int)$shipping->shipping_warehouse_id;
					if(((is_string($warehouse_id) || is_int($warehouse_id)) && $warehouse_id === $shipping->shipping_warehouse_id) || (is_array($shipping->shipping_warehouse_id) && $shipping->shipping_warehouse_id === $warehouse_struct)) {
						$cart->shipping[] = $shipping;
						$f = true;
						break;
					}
				}
				if(!$f)
					unset($cart->cart_shipping_ids[$k]);
			}

			$cart->usable_methods->shipping_valid = true;
			if(empty($cart->shipping_groups) || count($cart->shipping_groups) == 1) {
				$cart->usable_methods->shipping_valid = empty($cart->usable_methods->shipping_errors);
			} else {
				foreach($cart->shipping_groups as $group) {
					if(empty($group->shippings) && !empty($group->errors)) {
						$cart->usable_methods->shipping_valid = false;
						break;
					}
				}
			}

			if(!empty($cart->shipping))
				$cart->full_total =& $currencyClass->addShipping($cart->shipping, $cart->full_total);
		} else {
			$cart->cart_shipping_ids = '';
			$cart->usable_methods->shipping_valid = true;
		}

		if(hikashop_level(1) && !empty($cart->cart_coupon)){
			if(empty($discountClass))
				$discountClass = hikashop_get('class.discount');
			$discountClass->afterShippingProcessing($cart);
		}

		$before_additional = !empty($cart->additional);

		$this->app->triggerEvent('onAfterCartShippingLoad', array( &$cart ) );

		if(!$before_additional && !empty($cart->additional)) {
			$currencyClass->addAdditionals($cart->additional, $cart->additional_total, $cart->full_total, $currency_id);
			$cart->full_total =& $cart->additional_total;
		}

		$cart->payment = null;
		if($cart->cart_type != 'wishlist' && !empty($cart->full_total->prices[0]) && $cart->full_total->prices[0]->price_value_with_tax > 0.0) {
			$paymentClass = hikashop_get('class.payment');
			$cart->usable_methods->payment = $paymentClass->getPayments($cart, true);

			if(empty($cart->usable_methods->payment) && !empty($paymentClass->errors)) {
				$cart->usable_methods->payment_errors = $paymentClass->errors;
			}

			$checkPayment = $paymentClass->checkCartMethods($cart, true);
			if(!$checkPayment) {
				$query = 'UPDATE '.hikashop_table('cart').' SET cart_payment_id = '.(int)$cart->cart_payment_id.' WHERE cart_id = '.(int)$cart_id;
				$this->db->setQuery($query);
				$this->db->execute();
				if(isset(self::$cache['get'][$cart_id]))
					self::$cache['get'][$cart_id]->cart_payment_id = (int)$cart->cart_payment_id;
			}

			$currencyClass->processPayments($cart->usable_methods->payment, $zone_id);

			if(empty($cart->cart_payment_id) && !empty($cart->usable_methods->payment)) {
				$firstPayment = reset($cart->usable_methods->payment);
				$cart_payment_id = (int)$firstPayment->payment_id;
				unset($firstPayment);

				$this->app->triggerEvent('onHikaShopCartSelectPayment', array( $cart, &$cart_payment_id ));

				$cart->cart_payment_id = (int)$cart_payment_id;
			}

			if(!empty($cart->cart_payment_id) && !empty($cart->usable_methods->payment)) {
				foreach($cart->usable_methods->payment as $payment) {
					if($payment->payment_id == $cart->cart_payment_id) {
						$cart->payment = $payment;
						break;
					}
				}
			}

			if(!empty($cart->payment)) {
				$price_all = @$cart->full_total->prices[0]->price_value_with_tax;
				if(isset($cart->full_total->prices[0]->price_value_without_payment_with_tax))
					$price_all = $cart->full_total->prices[0]->price_value_without_payment_with_tax;

				$payment_price = $cart->payment->payment_price;
				if(isset($cart->payment->payment_price_without_percentage))
					$payment_price = $cart->payment->payment_price_without_percentage;

				$paymentClass->computePrice($cart, $cart->payment, $price_all, $payment_price, (int)$cart->cart_currency_id);
				if(isset($cart->payment->payment_tax))
					$cart->full_total->prices[0]->payment_tax = $cart->payment->payment_tax;

				$currencyClass->addPayment($cart->payment, $cart->full_total);
				if(isset($cart->payment->total)) {
					unset($cart->full_total);
					$cart->full_total =& $cart->payment->total;
				}
			}
		} else
			$cart->usable_methods->payment_valid = true;

		$this->checkNegativeTax($cart->full_total);

		$this->app->triggerEvent('onAfterFullCartLoad', array( &$cart ) );

		self::$cache['full'][$cart_id] =& $cart;
		return $this->getCloneCache('full', $cart_id);
	}

	protected function checkNegativeTax(&$total) {
		if(hikashop_toFloat($total->prices[0]->price_value_with_tax) > 0)
			return;
		$total->prices[0]->price_value_with_tax = 0;
		$total->prices[0]->price_value = 0;
		if(isset($total->prices[0]->taxes))
			unset($total->prices[0]->taxes);
	}

	protected function loadAutoCoupon(&$cart) {
		if(!hikashop_level(1))
			return false;
		if(!empty($cart->cart_type) && $cart->cart_type == 'wishlist')
			return false;

		$zone_id = $cart->package['zone'];

		if(!empty($cart->cart_coupon)) {
			if(empty($cart->cart_params->coupon_autoloaded))
				return false;

			$auto_coupon_key = $this->generateHash($cart->products, $zone_id);
			if($cart->cart_params->coupon_autoloaded == $auto_coupon_key)
				return false;
		}

		$filters = array(
			'discount_type = ' . $this->db->Quote('coupon'),
			'discount_published = 1',
			'discount_auto_load = 1'
		);
		hikashop_addACLFilters($filters, 'discount_access');

		$query = 'SELECT * '.
			' FROM ' . hikashop_table('discount').
			' WHERE (' . implode(') AND (', $filters) . ')'.
			' ORDER BY discount_minimum_order DESC, discount_minimum_products DESC';
		$this->db->setQuery($query);
		$coupons = $this->db->loadObjectList();
		if(empty($coupons))
			return;

		$discountClass = hikashop_get('class.discount');
		$zoneClass = hikashop_get('class.zone');
		$zones = $zoneClass->getZoneParents($zone_id);

		foreach($coupons as $coupon) {
			if(!$discountClass->check($coupon, $cart->total, $zones, $cart->products, false))
				continue;

			$auto_coupon_key = $this->generateHash($cart->products, $zone_id);

			$cart->cart_params->coupon_autoloaded = $auto_coupon_key;

			$this->app->setUserState(HIKASHOP_COMPONENT.'.auto_coupon_key', $auto_coupon_key);
			$this->app->setUserState(HIKASHOP_COMPONENT.'.coupon_code', '');

			$query = 'UPDATE '.hikashop_table('cart') .
				' SET cart_coupon = '.$this->db->Quote($coupon->discount_code).', cart_params = '.$this->db->Quote(json_encode($cart->cart_params)) .
				' WHERE cart_id = '.(int)$cart->cart_id;
			$this->db->setQuery($query);
			$this->db->execute();

			self::$cache['get'][$cart->cart_id]->cart_coupon = $coupon->discount_code;
			self::$cache['get'][$cart->cart_id]->cart_params->coupon_autoloaded = $auto_coupon_key;

			$cart->cart_coupon = $coupon->discount_code;
			break;
		}

		return !empty($cart->cart_coupon);
	}

	public function &loadFullCart($additionalInfos = false, $keepEmptyCart = false, $skipChecks = false) {
		return $this->getFullCart(0);
	}

	public function resetCart($cart_id) {
		$cart = $this->get($cart_id);
		if($cart === false || empty($cart))
			return false;

		$cart->cart_products = array();
		$cart->cart_coupon = '';
		$cart->cart_payment_id = 0;
		$cart->cart_shipping_ids = '';
		$cart->cart_billing_address_id = 0;
		$cart->cart_shipping_address_ids = '';
		$cart->cart_params = '';
		$cart->cart_fields = '';

		return $this->save($cart);
	}

	public function cleanCartFromSession($order_id = true, $cart_id = true) {
		if($cart_id === true)
			$cart_id = $this->getCurrentCartId();
		if(!empty($cart_id))
			$this->delete($cart_id);

		$vars = array(
			'cart_id' => 0,
			'coupon_code' => '',
			'cc_number' => '',
			'cc_month' => '',
			'cc_year' => '',
			'cc_CCV' => '',
			'cc_type' => '',
			'cc_owner' => '',
			'cc_valid' => 0,
			'checkout_terms' => 0,
			'checkout_fields_ok' => 0,
			'checkout_fields' => '',
			'checkout_cc' => '',
			'checkout_custom' => null,
			'checkout_shipping_custom' => null,
			'shipping_cache.usable_methods' => null,
		);
		foreach($vars as $k => $v) {
			$this->app->setUserState(HIKASHOP_COMPONENT.'.'.$k, $v);
		}

		unset($_SESSION['hikashop_order_data']);


		if($order_id === false)
			return;

		$user = JFactory::getUser();
		if($user->guest)
			$this->app->setUserState( HIKASHOP_COMPONENT.'.user_id', 0);

		if($order_id === true || empty($order_id))
			$order_id = $this->app->getUserState(HIKASHOP_COMPONENT.'.order_id');
		if(empty($order_id))
			$order_id = hikaInput::get()->getInt('order_id');
		if(empty($order_id))
			return;

		$this->handleReturnURL($order_id);
	}

	public function handleReturnURL($order_id) {
		$orderClass = hikashop_get('class.order');
		$order = $orderClass->get($order_id);
		if(empty($order))
			return;

		$query = 'SELECT * FROM '.hikashop_table('payment').' WHERE '.
				' payment_type = '.$this->db->Quote($order->order_payment_method).' AND payment_id = '.$this->db->Quote($order->order_payment_id);
		$this->db->setQuery($query);
		$paymentData = $this->db->loadObjectList();

		$pluginsClass = hikashop_get('class.plugins');
		$pluginsClass->params($paymentData, 'payment');
		$paymentOptions = reset($paymentData);

		if(!empty($paymentOptions->payment_params->return_url)) {
			$paymentOptions->payment_params->return_url = hikashop_translate($paymentOptions->payment_params->return_url);
			foreach(get_object_vars($order) as $key => $val) {
				if(!is_string($val) && !is_numeric($val) )
					continue;
				$paymentOptions->payment_params->return_url = str_replace('{'.$key.'}', $val, $paymentOptions->payment_params->return_url);
			}
			$this->app->redirect($paymentOptions->payment_params->return_url);
		}
	}

	public function addMessage(&$cart, $msg) {
		if(empty($cart) && !is_object($cart))
			return false;

		$cart->messages[] = $msg;

		$cart_id = isset($cart->cart_id) ? (int)$cart->cart_id : 0;
		if(!empty($cart_id) && empty(self::$cache['get'][$cart_id]))
			return false;

		if(isset(self::$cache['full'][$cart_id])) {
			self::$cache['full'][$cart_id]->messages[] = $msg;
			return true;
		}
		if(!isset(self::$cache['msg']))
			self::$cache['msg'] = array();
		if(empty(self::$cache['msg'][$cart_id]))
			self::$cache['msg'][$cart_id] = array();
		self::$cache['msg'][$cart_id][] = $msg;
		return true;
	}

	public function enqueueCartMessages($cart_id) {
		if((int)$cart_id == 0)
			$cart_id = $this->getCurrentCartId();
		if($cart_id === false)
			return false;
		if(empty(self::$cache['msg'][$cart_id]) && empty(self::$cache['full'][$cart_id]->messages))
			return false;

		$cart_messages = isset(self::$cache['full'][$cart_id]->messages) ? self::$cache['full'][$cart_id]->messages : self::$cache['msg'][$cart_id];

		foreach($cart_messages as $msg) {
			$this->app->enqueueMessage($msg['msg'], $msg['type']);
		}
		unset(self::$cache['msg'][$cart_id]);
		if(isset(self::$cache['full'][$cart_id]->messages))
			self::$cache['full'][$cart_id]->messages = array();
		return true;
	}

	public function addProduct($cart_id, $products, $options = array()) {
		if(empty($products))
			return false;

		$cart = $this->get($cart_id);
		if($cart === false)
			return false;
		if(empty($cart) && empty($cart_id))
			$cart = new stdClass();
		if(empty($cart->cart_products))
			$cart->cart_products = array();

		if(!empty($cart->cart_type) && $cart->cart_type != 'cart' && empty($this->user))
			return false;

		if(empty($options['fields_area']))
			$options['fields_area'] = 'frontcomp';

		if(!is_array($products))
			$products = array(array(
				'id' => (int)$products,
				'qty' => 1
			));

		$product_ids = array();
		foreach($products as $pid => &$p) {
			if(isset($p['pid']))
				unset($p['pid']);

			if(isset($p['id'])) {
				$product_ids[] = (int)$p['id'];
				continue;
			}
			if(is_int($p)) {
				$p = array('id' => (int)$pid, 'qty' => (int)$p);
				$product_ids[] = (int)$pid;
				continue;
			}
			foreach($p as $k => $v) {
				if(!is_int($k) && (int)$k == 0)
					continue;
				$p['id'] = (int)$k;
				$product_ids[] = (int)$k;
				if(!isset($p['qty']))
					$p['qty'] = (int)$v;
				unset($p[$k]);
				break;
			}
		}
		unset($p);

		$currency_id = !empty($cart->cart_currency_id) ? (int)$cart->cart_currency_id : hikashop_getCurrency();
		$ref_prices = array();
		$this->loadRefPrices($ref_prices, $currency_id, $product_ids);

		$fields = null;
		if(hikashop_level(2)) {
			$fields = $this->loadFieldsForProducts($product_ids, $options['fields_area']);
			foreach($fields as $k => $field){
				if($field->field_type == 'customtext')
					unset($fields[$k]);
			}

			foreach($products as $k => &$p) {
				if(empty($p['id']))
					continue;

				$oldData = new stdClass();
				$oldData->product_id = $p['id'];

				if(!empty($p['fields'])) {
					foreach($p['fields'] as $k => $v) {
						$oldData->$k = $v;
					}
				}

				$data = new stdClass();
				$ok = $this->fieldClass->checkFieldsData($fields, $p['fields'], $data, 'item', $oldData);

				if(!$ok) {
					unset($p['fields']);
					if(!empty($this->fieldClass->error_fields)) {
						foreach($this->fieldClass->error_fields as $error_field){
							if(!empty($error_field->field_options['errormessage']))
								$message = $this->fieldClass->trans($error_field->field_options['errormessage']);
							else
								$message = JText::sprintf('FIELD_VALID', $this->fieldClass->trans($error_field->field_realname));
							$this->addMessage($cart, array(
								'msg' => $message,
								'product_id' => $p['id'],
								'type' => 'error'
							));
						}
					}
					$p['qty'] = 0;

					unset($p['options']);
				} else
					$p['fields'] = get_object_vars($data);

				unset($data);
				unset($oldData);
			}
			unset($p);
		}

		$cart_product_options = array();
		$product_option_ids = array();
		if((int)$this->config->get('group_options', 0) == 1) {
			$cart_product_options = $this->getCartProductOptions($cart);
			foreach($cart->cart_products as $cart_product) {
				if(empty($cart_product->cart_product_id) || (is_string($cart_product->cart_product_id) && substr($cart_product->cart_product_id, 0, 1) == 'p'))
					continue;
				if(empty($cart_product->cart_product_option_parent_id))
					continue;
				$product_option_ids[] = $cart_product->product_id;
			}
		} else {
			$extracted_options = array();
			foreach($products as $k => &$p) {
				if(empty($p['options']))
					continue;
				foreach($p['options'] as $o) {
					if(isset($extracted_options[$o['id']]))
						$extracted_options[$o['id']]['qty'] += $o['qty'];
					else
						$extracted_options[$o['id']] = $o;
				}
				$p['options'] = array();
			}
			unset($p);
			if(!empty($extracted_options))
				$products = array_merge($products, array_values($extracted_options));
			$product_option_ids = array_keys($extracted_options);
		}
		if(!empty($product_option_ids))
			$this->loadRefPrices($ref_prices, $currency_id, $product_option_ids);

		foreach($products as $k => $p) {
			if(!empty($cart->cart_products)) {
				foreach($cart->cart_products as &$cart_product) {
					if($cart_product->cart_product_quantity == 0 || (is_string($cart_product->cart_product_id) && substr($cart_product->cart_product_id, 0, 1) == 'p'))
						continue;

					if(!$this->compareCartProducts($p, $cart_product, $cart_product_options, $fields))
						continue;


					unset($products[$k]['id']);
					$products[$k]['pid'] = (int)$cart_product->cart_product_id;

					break;
				}
				unset($cart_product);
			}
		}

		$options['message'] = true;
		$quantityCheck = $this->checkQuantities($products, $cart, $options);
		if($quantityCheck === false || empty($products))
			return false;

		foreach($products as $k => $p) {
			if(!empty($p['pid']) && isset($cart->cart_products[ (int)$p['pid'] ])) {
				$cart_product =& $cart->cart_products[ (int)$p['pid'] ];
				$cart_product->cart_product_quantity += (int)$p['qty'];
				$cart_product->cart_product_modified = time();
				$cart_product->cart_product_ref_price = null;

				unset($cart_product);
				if(empty($cart_product_options[ $p['pid'] ]))
					continue;

				foreach($cart_product_options[ $p['pid'] ] as $option_pid => $option) {
					$cart_product =& $cart->cart_products[$option_pid];

					$cart_product->cart_product_quantity += (int)$p['qty'] * (int)$option['coef'];
					$cart_product->cart_product_modified = time();
					$cart_product->cart_product_ref_price = @$ref_prices[ $p['id'] ];

					unset($cart_product);
				}
				continue;
			}

			if(empty($p['id']))
				continue;

			$cart_product = new stdClass();
			$cart_product->product_id = (int)$p['id']; //$product_id;
			$cart_product->cart_product_quantity = (int)$p['qty']; //$product_data;
			$cart_product->cart_product_modified = time();
			$cart_product->cart_product_ref_price = @$ref_prices[ $p['id'] ];
			if(hikashop_level(2) && !empty($p['fields'])) {
				foreach($p['fields'] as $k => $v) {
					$cart_product->$k = $v;
				}
			}
			if(!empty($p['options'])) {
				$cart_product->options = array();
				foreach($p['options'] as $opt) {
					if(empty($opt['qty']) || (int)$opt['qty'] <= 0)
						$opt['qty'] = 1;

					$product_option = new stdClass();
					$product_option->product_id = (int)$opt['id'];
					if(!empty($opt['coef']) && (int)$opt['coef'] > 0)
						$product_option->cart_product_quantity = (int)$p['qty'] * (int)$opt['coef'];
					else
						$product_option->cart_product_quantity = (int)$opt['qty'];
					$product_option->cart_product_modified = time();
					$cart_product->cart_product_ref_price = @$ref_prices[ $opt['id'] ];

					$cart_product->options[] = $product_option;
				}
			}

			if(!empty($p['wishlist'])) {
				$wishlist = $this->get( (int)$p['wishlist'] );
				if(!empty($wishlist) && $wishlist->cart_type == 'wishlist') {
					$cart_product->cart_product_wishlist_id = -(int)$p['wishlist'];
					if(!empty($p['wishlist_product']) && isset($wishlist->cart_products[(int)$p['wishlist_product']]))
						$cart_product->cart_product_wishlist_product_id = (int)$p['wishlist_product'];
				}
			}
			if(!empty($p['extra']))
				$cart_product->extra = $p['extra'];
			$cart->cart_products[] = $cart_product;
		}

		$status = $this->save($cart);

		if($status && is_array($quantityCheck))
			return $quantityCheck;
		return $status;
	}

	protected function getCartProductOptions(&$cart) {
		$cart_product_options = array();
		foreach($cart->cart_products as $cart_product) {
			if(empty($cart_product->cart_product_id) || (is_string($cart_product->cart_product_id) && substr($cart_product->cart_product_id, 0, 1) == 'p'))
				continue;

			$cpopid = $cart_product->cart_product_option_parent_id;
			if(empty($cpopid))
				continue;
			if(empty($cart_product_options[$cpopid]))
				$cart_product_options[$cpopid] = array();
			$parent_qty = isset($cart->cart_products[$cpopid]) ? (int)$cart->cart_products[$cpopid]->cart_product_quantity : 0;
			$cart_product_options[$cpopid][$cart_product->cart_product_id] = array(
				'id' => $cart_product->product_id,
				'qty' => $cart_product->cart_product_quantity,
				'coef' => ($cart_product->cart_product_quantity / max($parent_qty, 1))
			);
		}
		return $cart_product_options;
	}

	protected function compareCartProducts($p, $cart_product, $cart_product_options = null, $fields = null) {
		if(!isset($p['id']) || $cart_product->product_id != $p['id'])
			return false;
		if(!empty($p['fields']) || !empty($fields)) {
			if(!empty($p['fields'])){
				foreach($p['fields'] as $k => $v) {

					if(is_array($cart_product->$k))
						$cart_product->$k = implode(',', $cart_product->$k);
					if(is_array($v))
						$v= implode(',', $v);

					if($cart_product->$k != $v)
						return false;
				}
			}

			if(!empty($fields)){
				foreach($fields as $field) {
					$namekey = $field->field_namekey;

					if(is_array($cart_product->$namekey))
						$cart_product->$namekey = implode(',', $cart_product->$namekey);
					if(is_array($p['fields'][$namekey]))
						$p['fields'][$namekey] = implode(',', $p['fields'][$namekey]);

					if(!empty($cart_product->$namekey) && (!isset($p['fields'][$namekey]) || $cart_product->$namekey != $p['fields'][$namekey]))
						return false;
				}
			}
		}
		if(!empty($p['extra'])) {
			$do = true;
			JPluginHelper::importPlugin('hikashop');
			$this->app->triggerEvent('onCompareCartProducts', array( $p, $cart_product, &$do ) );
			if(!$do)
				return false;
		}
		if(!hikashop_level(1) || (int)$this->config->get('group_options', 0) == 0)
			return true;

		if(empty($p['options']) && empty($cart_product_options[$cart_product->cart_product_id]))
			return true;


		if(empty($p['options']) || empty($cart_product_options[$cart_product->cart_product_id]))
			return false;

		if(count($p['options']) != count($cart_product_options[$cart_product->cart_product_id]))
			return false;

		foreach($p['options'] as $opt) {
			$f = false;
			$coef = 1;
			if(isset($opt['coef']))
				$coef = (int)$opt['coef'];
			elseif(isset($opt['qty']))
				$coef = ($opt['qty'] / max($p['qty'], 1));

			foreach($cart_product_options[$cart_product->cart_product_id] as $cart_opt) {
				if($opt['id'] != $cart_opt['id'])
					continue;
				if($coef != $cart_opt['coef'])
					return false;

				$f = true;
				break;
			}
			if(!$f)
				return false;
		}
		return true;
	}

	public function loadRefPrices(&$prices, $currency_id, $product_ids) {
		if(empty($prices)) {
			$prices = array();
			$ids = $product_ids;
		} else
			$ids = array_diff($product_ids, array_keys($prices));
		hikashop_toInteger($ids);
		$query = 'SELECT price_product_id, price_value FROM ' . hikashop_table('price') .
			' WHERE price_currency_id = ' . (int)$currency_id . ' AND price_min_quantity = 0 ' .
			' AND price_product_id ' . ((count($ids) > 1) ? (' IN ('.implode(',', $ids).')') : (' = '. reset($ids)) );
		$this->db->setQuery($query);
		$db_prices = $this->db->loadObjectList('price_product_id');
		foreach($db_prices as $i => $price) {
			$prices[$i] = hikashop_toFloat($price->price_value);
		}
		return;
	}

	public function loadFieldsForProducts($product_ids, $area = 'frontcomp') {
		$fields = array();
		if(!hikashop_level(2))
			return $fields;

		if(empty($this->fieldClass))
			$this->fieldClass = hikashop_get('class.field');

		if(empty($product_ids))
			return array();

		hikashop_toInteger($product_ids);

		$query = 'SELECT product_id, product_parent_id '.
			' FROM '.hikashop_table('product').
			' WHERE product_type = '.$this->db->Quote('variant').' AND product_parent_id > 0 AND product_id IN ('.implode(',', $product_ids).')';
		$this->db->setQuery($query);
		$product_parents = $this->db->loadObjectList('product_id');

		$all_product_ids = array();
		$oldData = array();

		foreach($product_ids as $product_id) {
			$e = new stdClass();
			$e->product_id = $product_id;
			$e->product_parent_id = 0;
			$e->product_type = 'main';

			$all_product_ids[$product_id] = 0;
			if(isset($product_parents[$product_id])) {
				$all_product_ids[$product_id] = (int)$product_parents[$product_id]->product_parent_id;
				$e->product_parent_id = $all_product_ids[$product_id];
				$e->product_type = 'variant';
			}

			$oldData[] = $e;
		}
		unset($product_parents);

		if(count($oldData) == 1)
			$oldData = reset($oldData);
		unset($p);
		$allCat = $this->fieldClass->getCategories('item', $oldData);

		$fields =& $this->fieldClass->getData($area, 'item', false, $allCat);
		$this->fieldClass->populateItemFieldValues($fields, $all_product_ids);

		$haveCategories = false;
		foreach($fields as $namekey => $field) {
			if(!is_array($field->field_categories)) {
				$fields[$namekey]->field_categories = explode(',', trim(trim($field->field_categories, ',')));
			}
			if(count($fields[$namekey]->field_categories)) {
				$haveCategories = true;
			}
		}
		if($haveCategories) {
			$query = 'SELECT product_id, category_id FROM '.hikashop_table('product_category').' WHERE product_id IN ('.implode(',', array_keys($all_product_ids)).')';
			$this->db->setQuery($query);
			$product_categories = $this->db->loadObjectList();
		}

		foreach($fields as $namekey => &$field) {
			if(!empty($field->field_options) && is_string($field->field_options))
				$field->field_options = hikashop_unserialize($field->field_options);

			$field->field_categories_products = array();
			foreach($product_categories as $pc) {
				if(!in_array((int)$pc->category_id, $field->field_categories))
					continue;
				$field->field_categories_products[(int)$pc->product_id] = (int)$pc->product_id;
			}
		}
		unset($field);
		unset($allCat);
		return $fields;
	}

	public function updateProduct(&$cart_id, $cart_products) {
		$update = !is_object($cart_id);
		if($update)
			$cart = $this->get($cart_id);
		else
			$cart =& $cart_id;

		if($cart === false || (empty($cart) && empty($cart_id)))
			return false;

		if($cart->cart_type != 'cart' && empty($this->user))
			return false;

		$diff_products = array();
		foreach($cart_products as $p) {
			if(empty($p['id']) || !isset($cart->cart_products[ $p['id'] ]))
				continue;
			$q = (int)$p['qty'];
			if($q == 0 || ($q > 0 && $q == (int)$cart->cart_products[ $p['id'] ]->cart_product_quantity))
				continue;
			$p['qty'] -= (int)$cart->cart_products[ $p['id'] ]->cart_product_quantity;
			$diff_products[] = array(
				'pid' => $p['id'],
				'qty' => $p['qty']
			);

			if($this->currentMode == 'legacy_update' && $p['qty'] < 0) {
				$this->addMessage($cart, array('msg' => JText::_('PRODUCT_SUCCESSFULLY_REMOVED_FROM_CART'), 'product_id' => $cart->cart_products[ $p['id'] ]->product_id, 'type' => 'warning'));
			}
		}

		if(!empty($diff_products)) {
			$quantityCheck = $this->checkQuantities($diff_products, $cart, array('incremental' => true, 'message' => true));
			if(!$quantityCheck)
				return false;
		}


		$update_needed = 0;
		$group = (int)$this->config->get('group_options', 0);

		if($group) {
			$options = array();
			foreach($cart_products as &$p) {
				if(!isset($cart->cart_products[ $p['id'] ]))
					continue;

				if(!empty($cart->cart_products[ $p['id'] ]->cart_product_option_parent_id)) {
					$p['id'] = 0;
					continue;
				}

				foreach($cart->cart_products as $pid => $cart_product) {
					if($pid == $p['id'] || empty($cart_product->cart_product_option_parent_id) || $cart_product->cart_product_option_parent_id != $p['id'])
						continue;
					if((int)$p['qty'] > 0)
						$coef = (int)$cart_product->cart_product_quantity / (int)$cart->cart_products[ $p['id'] ]->cart_product_quantity;
					else
						$coef = 1;

					$options[$pid] = array(
						'id' => $pid,
						'qty' => (int)((int)$p['qty'] * $coef)
					);
				}
			}
			unset($p);
			if(!empty($options)) {
				$cart_products = array_merge($cart_products, $options);
			}
		}

		foreach($cart_products as $p) {
			if(empty($p['id']) || !isset($cart->cart_products[ $p['id'] ]))
				continue;

			$q = (int)$p['qty'];
			if($q > 0 && $q == (int)$cart->cart_products[ $p['id'] ]->cart_product_quantity)
				continue;

			if($q > 0)
				$cart->cart_products[ $p['id'] ]->cart_product_quantity = $q;
			else
				unset($cart->cart_products[ $p['id'] ]);

			$update_needed = true;
		}

		if($update && $update_needed)
			return $this->save($cart);
		return $update_needed;
	}

	public function addProductEntry($cart_id, &$entriesData) {
		$ret = false;

		$fieldClass = hikashop_get('class.field');
		$fields =& $fieldClass->getData('frontcomp', 'entry');

		$productsToAdd = array();
		$coupons = array();

		foreach($entriesData as $entryData) {
			foreach($fields as $field) {
				$n = $field->field_namekey;
				if(!isset($entryData->$n))
					continue;

				$value = $entryData->$n;

				if(!empty($field->field_options) && !is_array($field->field_options))
					$field->field_options = hikashop_unserialize($field->field_options);

				if($field->field_type == 'coupon' && !empty($field->coupon[$value])) {
					$coupons[] = $field->coupon[$value];
				}

				if(empty($field->field_options['product_id']))
					continue;

				$ok = false;
				if(is_numeric($value) && is_numeric($field->field_options['product_value'])) {
					if($value === $field->field_options['product_value']) {
						$ok = true;
					}
				} elseif(is_string($value) && !empty($field->field_options['product_value']) && is_array($field->field_options['product_value']) && in_array($value,$field->field_options['product_value'])) {
					$ok = true;
				} elseif($value == $field->field_options['product_value']) {
					$ok = true;
				}

				if($ok) {
					$id = (int)$field->field_options['product_id'];
					if(empty($productsToAdd[$id]))
						$productsToAdd[$id] = array('id' => $id, 'qty' => 0);
					$productsToAdd[$id]['qty']++;
				}
			}
		}
		unset($fields);

		if(!empty($productsToAdd)) {
			$ret = $this->addProduct($cart_id, $productsToAdd);
		}

		if(is_array($coupons) && count($coupons) > 1) {
			$total = 0.0;
			$currency = hikashop_getCurrency();
			$currencyClass = hikashop_get('class.currency');
			$coupon_ids = array();
			foreach($coupons as $item) {
				$currencyClass->convertCoupon($item, $currency);
				$total += $item->discount_flat_amount;
				$coupon_ids[ (int)$item->discount_id ] = (int)$item->discount_id;
			}

			if(!empty($coupon_ids)) {
				$query = 'UPDATE '.hikashop_table('discount').' SET discount_used_times=discount_used_times+1 WHERE discount_id IN (' . implode(',', $coupon_ids) . ')';
				$this->db->setQuery($query);
				$this->db->execute();
			}

			$coupon = new stdClass();
			$coupon->discount_type = 'coupon';
			$coupon->discount_currency_id = $currency;
			$coupon->discount_flat_amount = $total;
			$coupon->discount_quota = 1;
			$coupon->discount_published = 1;
			jimport('joomla.user.helper');
			$coupon->discount_code = JUserHelper::genRandomPassword(30);

			$discountClass = hikashop_get('class.discount');
			if(!empty($total))
				$discountClass->save($coupon);
		}

		if(is_array($coupons) && count($coupons) == 1)
			$coupon = reset($coupons);

		if(!empty($coupon) && !empty($coupon->discount_code)) {
			$ret = $this->addCoupon($cart_id, $coupon->discount_code);
		}

		return $ret;
	}

	public function updatePayment($cart_id, $payment_id) {
		$cart = $this->getFullCart($cart_id);
		if($cart === false)
			return false;
		if(empty($cart) && empty($cart_id))
			$cart = new stdClass();

		$found = false;
		foreach($cart->usable_methods->payment as $payment) {
			if((int)$payment->payment_id == (int)$payment_id) {
				$found = true;
				break;
			}
		}
		if(!$found)
			return false;

		$cart->cart_payment_id = (int)$payment_id;
		return $this->save($cart);
	}

	public function updatePaymentCustom($cart_id, $payment_id, $custom_html) {
		$cart = $this->getFullCart($cart_id);
		if($cart === false)
			return false;
		if($cart->payment->payment_id != $payment_id)
			return false;

		$cart_id = $cart->cart_id;
		self::$cache['full'][$cart_id]->payment->custom_html = $custom_html;
		foreach(self::$cache['full'][$cart_id]->usable_methods->payment as &$p) {
			if($p->payment_id != $payment_id)
				continue;
			$p->custom_html = $custom_html;
		}
		unset($p);

		return true;
	}

	public function updateShipping($cart_id, $shipping_ids) {
		$cart = $this->getFullCart($cart_id);
		if($cart === false)
			return false;
		if(empty($cart) && empty($cart_id)) {
			$cart = new stdClass();
			$cart->cart_shipping_ids = array();
		}

		$shippings = $cart->cart_shipping_ids;
		if(empty($shippings))
			$shippings = array();

		$shipping_data = array();
		foreach($shippings as $s) {
			if(strpos($s, '@') === false) {
				$shipping_data[0] = $s;
				continue;
			}
			list($s_id, $w_id) = explode('@', $s, 2);
			if(is_numeric($w_id))
				$w_id = (int)$w_id;
			if(is_numeric($s_id))
				$s_id = (int)$s_id;
			$shipping_data[ $w_id ] = $s_id;
		}

		if(!is_array($shipping_ids)) {
			if(count($cart->shipping_groups) == 1) {
				$key = reset(array_keys($cart->shipping_groups));
				$shipping_ids = array($key => $shipping_ids);
			} else
				$shipping_ids = array(0 => $shipping_ids);
		}

		$modified = false;
		foreach($shipping_ids as $shipping_group => $shipping_id) {
			$warehouse_struct = null;

			foreach($cart->usable_methods->shipping as $shipping) {
				if($shipping->shipping_id != $shipping_id)
					continue;

				if(is_array($shipping->shipping_warehouse_id) && $warehouse_struct === null && preg_match_all('#([a-zA-Z])*([0-9]+)#iu', $shipping_group, $keys))
					$warehouse_struct = array_combine($keys[1], $keys[2]);
				if(is_numeric($shipping->shipping_warehouse_id))
					$shipping->shipping_warehouse_id = (int)$shipping->shipping_warehouse_id;

				if(((is_string($shipping_group) || is_int($shipping_group)) && $shipping_group === $shipping->shipping_warehouse_id) || (is_array($shipping->shipping_warehouse_id) && $shipping->shipping_warehouse_id === $warehouse_struct)) {
					$modified = true;
					$shipping_data[ $shipping_group ] = $shipping_id;
					break;
				}
			}
		}

		if(!$modified)
			return false;

		foreach($shipping_data as $k => &$v) {
			$v .= '@' . $k;
		}
		unset($v);

		$cart->cart_shipping_ids = $shipping_data;
		return $this->save($cart);
	}

	public function updateShippingCustomData($cart_id, $customData) {
		$cart = $this->getFullCart($cart_id);
		if($cart === false)
			return false;

		$shipping_data = isset($cart->cart_params->shipping) ? $cart->cart_params->shipping : array();
		if(empty($shipping_data) && empty($customData))
			return true;

		$shipping_data = (array)$shipping_data;

		$initial_hash = md5(serialize($shipping_data));

		foreach($customData as $warehouse => $subData) {
			if(!isset($shipping_data[$warehouse]))
				$shipping_data[$warehouse] = new stdClass();
			foreach($subData as $id => $pluginData) {
				$shipping_data[$warehouse]->{$id} = $pluginData;
			}
		}

		foreach($shipping_data as $k => &$v) {
			if(!isset($cart->shipping_groups[$k])) {
				unset($shipping_data[$k]);
				continue;
			}

		}
		unset($v);

		$final_hash = md5(serialize($shipping_data));
		if($final_hash == $initial_hash)
			return true;

		$cart->cart_params->shipping = $shipping_data;
		return $this->save($cart);
	}

	public function updateFields($cart_id, $fieldsData) {
		$cart = $this->getFullCart($cart_id);
		if($cart === false)
			return false;

		$old = new stdClass();
		$old->products = $cart->products;
		$fieldClass = hikashop_get('class.field');

		$fields = array();
		$cart->cart_fields = $fields;
		return $this->save($cart);
	}

	public function updateTerms($cart_id, $value, $key = 'terms_checked') {
		$cart = $this->get($cart_id);
		if($cart === false)
			return false;

		if(!empty($cart->cart_params) && is_string($cart->cart_params))
			$cart->cart_params = json_decode($cart->cart_params);
		if(empty($cart->cart_params))
			$cart->cart_params = new stdClass();

		if(isset($cart->cart_params->$key) && $cart->cart_params->$key == $value)
			return true;

		$cart->cart_params->$key = $value;
		return $this->save($cart);
	}

	public function addCoupon($cart_id, $coupon) {
		$cart = $this->get($cart_id);
		if($cart === false)
			return false;

		if(empty($cart) && empty($cart_id)) {
			$cart = new stdClass();
			$fullcart = new stdClass();
			$fullcart->total = new stdClass();
			$fullcart->products = array();
		} else {
			$fullcart = $this->getFullCart($cart_id);
		}

		$coupon = trim($coupon);

		if(!empty($cart->cart_coupon)) {
			if(is_string($cart->cart_coupon))
				$cart->cart_coupon = explode("\r\n", trim($cart->cart_coupon));
			if(in_array($coupon, $cart->cart_coupon))
				return false;
		}

		$discountClass = hikashop_get('class.discount');
		$zoneClass = hikashop_get('class.zone');

		$zone_id = hikashop_getZone('shipping');
		$zones = $zoneClass->getZoneParents($zone_id);
		if(!$discountClass->loadAndCheck($coupon, $fullcart->total, $zones, $fullcart->products, true))
			return false;

		if(empty($cart->cart_coupon)) {
			$cart->cart_coupon = $coupon;
		} else {
			$cart->cart_coupon[] = $coupon;
		}

		return $this->save($cart);
	}

	public function removeCoupon($cart_id, $coupon) {
		$cart = $this->get($cart_id);
		if($cart === false)
			return false;
		if(empty($cart) && empty($cart_id))
			$cart = new stdClass();

		if(empty($cart->cart_coupon))
			return false;

		if(is_string($cart->cart_coupon))
			$cart->cart_coupon = explode("\r\n", trim($cart->cart_coupon));

		if(!is_array($coupon))
			$coupon = array($coupon);
		foreach($coupon as $c) {
			$k = array_search($c, $cart->cart_coupon);

			if($c == '1' && $k === false)
				$k = 0;
			if($k === false)
				return false;

			unset($cart->cart_coupon[$k]);
		}

		return $this->save($cart);
	}

	public function updateAddress($cart_id, $type, $addresses) {
		$cart = $this->get($cart_id);
		if($cart === false || $cart->user_id <= 0)
			return false;

		if(!in_array($type, array('billing', 'shipping', null)))
			return false;

		$address_id = (int)$addresses;
		if(empty($address_id))
			return false;

		if($type == 'billing' && isset($cart->cart_billing_address_id) && $cart->cart_billing_address_id == $address_id)
			return true;
		elseif($type == 'shipping'&& isset($cart->cart_shipping_address_ids) && $cart->cart_shipping_address_ids == $address_id)
			return true;
		elseif($type == null && isset($cart->cart_shipping_address_ids) && $cart->cart_shipping_address_ids == $address_id && isset($cart->cart_billing_address_id) && $cart->cart_billing_address_id == $address_id)
			return true;

		$addressClass = hikashop_get('class.address');
		$addr = $addressClass->get($address_id);
		if(!$addressClass->isAddressValid($addr, array('user_id' => $cart->user_id))) {
			$this->app->enqueueMessage(JText::_('THE_'.strtoupper($addr->address_type).'_ADDRESS_YOU_SELECTED_CANNOT_BE_USED_AS_SOME_INFORMATION_IS_MISSING'), 'error');
			return false;
		}

		if($type == 'billing' || $type === null) {
			$cart->cart_billing_address_id = $address_id;
		}
		if($type == 'shipping' || $type == null) {
			$cart->cart_shipping_address_ids = $address_id;
		}

		$ret = $this->save($cart);
		if($ret) {
			$this->get('reset_cache',$cart->cart_id);
			$checkoutHelper = hikashopCheckoutHelper::get();
			$checkoutHelper->getCart(true);
		}
		return $ret;
	}

	public function update($product_id, $quantity = 1, $add = 0, $type = 'product', $resetCartWhenUpdate = true, $force = false, $cart_id = null) {

		if($cart_id === null) {
			$cart_type = hikaInput::get()->getString('cart_type', 'cart');
			$cart_id = hikaInput::get()->getInt($cart_type.'_id', 0);
			if(!empty($cart_id)) {
				$cart = $this->get($cart_id);
				if(empty($cart))
					$cart_id = 0;
			}
			if(empty($cart_id))
				$cart_id = $this->getCurrentCartId($cart_type);
		}

		$data = array(
			0 => array(
				'id' => $product_id,
				'qty' => $quantity
			)
		);

		if(is_array($product_id)) {
			$data = array();
			foreach($product_id as $k => $v) {
				if(isset($v['cart_product_quantity'])) {
					$data[] = array(
						'id' => (int)$k,
						'qty' => (int)$v['cart_product_quantity']
					);
				} elseif(in_array($type, array('item','product')) && is_numeric($v)) {
					$data[] = array(
						'id' => (int)$k,
						'qty' => (int)$v
					);
				}
			}
		}
		$options = hikaInput::get()->get('hikashop_product_option', array(), 'array');
		$options_qty = hikaInput::get()->get('hikashop_product_option_qty', array(), 'array');
		if(!empty($options) && is_array($options)) {
			$data[0]['options'] = array();
			foreach($options as $k => $option) {
				if(empty($option) || (int)$option == 0)
					continue;
				if(isset($options_qty[$k]) && empty($options_qty[$k]))
					continue;
				if(!isset($options_qty[$k]) || (int)$options_qty[$k] < 0) $options_qty[$k] = 0;
				$qty = !empty($options_qty[$k]) ? (int)$options_qty[$k] : $quantity;
				$coef = !empty($options_qty[$k]) ? 0 : 1;
				$data[0]['options'][] = array(
					'id' => (int)$option,
					'qty' => $qty,
					'coef' => $coef
				);
			}
			if(empty($data[0]['options']))
				unset($data[0]['options']);
		}
		$formData = hikaInput::get()->get('data', array(), 'array');
		if(!empty($formData['item'])) {
			foreach($data as &$d) {
				$d['fields'] = $formData['item'];
			}
			unset($d);
		}
		if(!empty($formData['extra'])) {
			foreach($data as &$d) {
				$d['extra'] = $formData['extra'];
			}
			unset($d);
		}

		$this->currentMode = 'legacy_update';


		$from_id = hikaInput::get()->getInt('from_id', 0);
		if(!empty($from_id)) {
			foreach($data as &$d) {
				$d['wishlist'] = $from_id;
			}
			unset($d);
		}
		if($type == 'product' && !empty($data) && !empty($add))
			return $this->addProduct($cart_id, $data);
		if($type == 'product' && is_int($product_id) && empty($add)) {
			$p = reset($data);
			$fields = null;
			if(hikashop_level(2)) {
				$product_ids = array($p['id']);
				$fields = $this->loadFieldsForProducts($product_ids, 'frontcomp');
				foreach($fields as $k => $field){
					if($field->field_type == 'customtext')
						unset($fields[$k]);
				}
			}
			$cart = $this->get($cart_id);
			if(!empty($cart->cart_products)) {
				foreach($cart->cart_products as &$cart_product) {
					if($cart_product->cart_product_quantity == 0 || (is_string($cart_product->cart_product_id) && substr($cart_product->cart_product_id, 0, 1) == 'p'))
						continue;

					$cart_product_options = $this->getCartProductOptions($cart);
					if(!$this->compareCartProducts($p, $cart_product, $cart_product_options, $fields))
						continue;
					$p['id'] = (int)$cart_product->cart_product_id;
					$cart_products = array($p);
					return $this->updateProduct($cart_id, $cart_products);
				}
			}
			return $this->addProduct($cart_id, $data);
		}

		if($type == 'item' && empty($add) && !empty($data))
			return $this->updateProduct($cart_id, $data);

		if($type == 'coupon' && !empty($product_id) && $quantity > 0)
			return $this->addCoupon($cart_id, $product_id);

		if($type == 'coupon' && !empty($product_id) && $quantity == 0)
			return $this->removeCoupon($cart_id, $product_id);

		return false;
	}

	public function updateEntry($quantity, &$cartContent, $product_id, $add, $resetCartWhenUpdate = true, $type = 'product', $force = false) {
	}

	public function setCurrent($cart_id, $unset = false) {
		$cart = $this->get((int)$cart_id);
		if(empty($cart))
			return false;

		$type = $cart->cart_type;
		if(!in_array($type, array('cart', 'wishlist')))
			return false;
		if((int)$cart->cart_current == 1 && !$unset)
			return true;

		if(hikashop_isClient('administrator') && (int)$cart->user_id == 0)
			return false;

		$filters = array(
			'type' => 'cart_type = '.$this->db->Quote($type),
			'user' => 'user_id = '.(int)$cart->user_id,
		);

		if(!hikashop_isClient('administrator') && (empty($this->user) || empty($this->user->user_id))) {
			$jsession = JFactory::getSession();
			$filters['user'] = 'session_id = ' . $this->db->Quote($jsession->getId());
		}

		$query = 'UPDATE '.hikashop_table('cart').' SET cart_current = 0 WHERE ('.implode(') AND (', $filters).')';
		$this->db->setQuery($query);
		$this->db->execute();

		if($unset) {
			self::$current_cart_id[$cart->cart_type] = 0;
			return true;
		}

		$query = 'UPDATE '.hikashop_table('cart').' SET cart_current = 1 WHERE cart_id = '.(int)$cart_id.' AND ('.implode(') AND (', $filters).')';
		$this->db->setQuery($query);
		$this->db->execute();

		if(!hikashop_isClient('administrator'))
			self::$current_cart_id[$cart->cart_type] = (int)$cart_id;
		return true;
	}

	public function sessionToUser($cart_id, $session, $user_id, $set_current = true) {
		if((int)$cart_id <= 0)
			return false;

		$cart_id = (int)$cart_id;

		$new_session = $session;
		if(!hikashop_isClient('administrator')) {
			hikashop_loadUser(false, true);
			$this->user = hikashop_loadUser(true);
			if((int)$user_id != (int)@$this->user->user_id)
				return false;

			$jsession = JFactory::getSession();
			$new_session = $jsession->getId();
		}

		if(!isset(self::$cache['get'][$cart_id]))
			return false;
		if(self::$cache['get'][$cart_id]->session_id != $session)
			return false;

		if(!empty($user_id) && (int)self::$cache['get'][$cart_id]->user_id === (int)$user_id)
			return true;

		if(!empty(self::$cache['get'][$cart_id]->user_id) && !empty($user_id))
			return false;

		$query = 'UPDATE '.hikashop_table('cart').
				' SET user_id = ' . (int)$user_id .', session_id = '.$this->db->Quote($new_session).
				' WHERE cart_id = '.(int)$cart_id.' AND session_id = '.$this->db->Quote($session);
		$this->db->setQuery($query);
		$ret = $this->db->execute();

		if(!$ret)
			return $ret;

		self::$cache['get'][$cart_id]->user_id = $user_id;
		self::$cache['get'][$cart_id]->session_id = $new_session;
		if(isset(self::$cache['full'][$cart_id])) {
			self::$cache['full'][$cart_id]->user_id = $user_id;
			self::$cache['full'][$cart_id]->session_id = $new_session;
		}


		if(!$set_current)
			return $ret;

		$this->setCurrent($cart_id);
		return $ret;
	}

	public function convert($cart_id, $merge = true) {
		if(!$this->config->get('enable_wishlist'))
			return false;

		$cart = $this->get((int)$cart_id);
		if(empty($cart))
			return false;

		$type = $cart->cart_type;
		if(!in_array($type, array('cart', 'wishlist')))
			return false;

		if(!hikashop_isClient('administrator')) {
			$jsession = JFactory::getSession();
			if($cart->user_id != $this->user->user_id && $cart->session_id != $jsession->getId())
				return false;
		}

		$currentCartId = 0;
		if($type == 'wishlist' && $merge && !hikashop_isClient('administrator'))
			$currentCartId = $this->getCurrentCartId();

		if(empty($currentCartId)) {
			$cart->cart_type = ($type == 'wishlist') ? 'cart' : 'wishlist';

			return $this->save($cart);
		}

		if((int)$cart_id == (int)$currentCartId)
			return $currentCartId;

		$query = 'UPDATE ' . hikashop_table('cart_product') .
			' SET cart_id = ' . (int)$currentCartId .
			' WHERE cart_id = ' . (int)$cart_id;
		$this->db->setQuery($query);
		$ret = $this->db->execute();

		if(!$ret)
			return false;

		$this->delete($cart_id);
		$this->resetCartCache($currentCartId);
		return $currentCartId;
	}

	public function moveTo($cart_id, $cart_product_ids, $dest_id, $cart_type = null) {
		$cart_id = (int)$cart_id;
		$dest_id = (int)$dest_id;

		if($cart_id == $dest_id)
			return false;

		if($dest_id < 0) {
			if(!in_array($cart_type, array('cart','wishlist')))
				return false;
			if($cart_type == 'cart' && !$this->config->get('enable_multicart'))
				return false;
			if($cart_type == 'wishlist' && (!$this->config->get('enable_wishlist') || !hikashop_level(1)))
				return false;
		}

		$cart = $this->get($cart_id);
		if(empty($cart))
			return false;

		if($cart_id == 0)
			$cart_id = $cart->cart_id;

		if(!hikashop_isClient('administrator')) {
			$user_id = hikashop_loadUser(false);

			if($cart->user_id != $user_id)
				return false;

			if($dest_id >= 0) {
				$dest = $this->get($dest_id);
				if(empty($dest) || $dest->user_id != $user_id)
					return false;
			}
		}

		if($cart_id == $dest_id)
			return false;

		$cart_products = array_keys($cart->cart_products);
		$cart_product_ids = array_intersect($cart_products, $cart_product_ids);
		if(empty($cart_product_ids))
			return false;

		hikashop_toInteger($cart_product_ids);

		if(hikashop_level(1) && (int)$this->config->get('group_options', 0)) {
			foreach($cart->cart_products as $cart_product) {
				if(empty($cart_product->cart_product_option_parent_id) || !in_array((int)$cart_product->cart_product_option_parent_id, $cart_product_ids))
					continue;
				$cart_product_ids[] = $cart_product->cart_product_id;
			}
		}

		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$this->app->triggerEvent('onBeforeCartProductMove', array( $cart_id, $dest_id, &$cart_product_ids, &$do ) );

		if(!$do)
			return false;

		if($dest_id < 0) {
			$dest_cart = new stdClass();
			$dest_cart->user_id = $cart->user_id;
			$dest_cart->cart_type = empty($cart_type) ? 'cart' : $cart_type;
			$dest_id = $this->save($dest_cart);
			if(empty($dest_id))
				return false;
		}

		$query = 'UPDATE '.hikashop_table('cart_product').
			' SET cart_id = '.(int)$dest_id.
			' WHERE cart_id = '.(int)$cart_id.' AND cart_product_id IN ('.implode(',', $cart_product_ids).')';
		$this->db->setQuery($query);
		$ret = $this->db->execute();

		if(!$ret)
			return false;

		$query = 'UPDATE '.hikashop_table('cart').' SET cart_modified = '.time().' WHERE cart_id IN ('.(int)$cart_id.','.(int)$dest_id.')';
		$this->db->setQuery($query);
		$this->db->execute();

		$this->resetCartCache($cart_id);
		$this->resetCartCache($dest_id);

		$this->app->triggerEvent('onAfterCartProductMove', array( $cart_id, $dest_id, $cart_product_ids ) );

		return $dest_id;
	}

	public function cartProductsToArray(&$cart_products, $options = array()) {
		$ret = array();

		$haveOptions = false;
		$product_ids = array();
		foreach($cart_products as $cart_product) {
			if(!empty($cart_product->product_id))
				$product_ids[ (int)$cart_product->product_id ] = (int)$cart_product->product_id;

			if(empty($cart_product->cart_product_option_parent_id) && empty($cart_product->order_product_option_parent_id))
				continue;
			$haveOptions = true;
			break;
		}
		$fields = $this->loadFieldsForProducts($product_ids, 'frontcomp');

		foreach($cart_products as $cart_product) {
			$qty = !empty($cart_product->cart_product_quantity) ? (int)$cart_product->cart_product_quantity : (int)@$cart_product->order_product_quantity;
			if(empty($qty))
				continue;

			if(!empty($cart_product->cart_product_option_parent_id) || !empty($cart_product->order_product_option_parent_id))
				continue;

			$o = array(
				'id' => $cart_product->product_id,
				'qty' => $qty
			);
			if(!empty($options['wishlist'])) {
				$o['wishlist'] = (int)$options['wishlist'];
				$o['wishlist_product'] = (int)$cart_product->cart_product_id;
			}

			if($haveOptions && hikashop_level(1)) {
				foreach($cart_products as $option_product) {
					if(empty($option_product->cart_product_option_parent_id) && empty($option_product->order_product_option_parent_id))
						continue;
					if(!empty($option_product->cart_product_option_parent_id) && (int)$option_product->cart_product_option_parent_id != $cart_product->cart_product_id)
						continue;
					if(!empty($option_product->order_product_option_parent_id) && (int)$option_product->order_product_option_parent_id != $cart_product->order_product_id)
						continue;

					if(empty($o['options']))
						$o['options'] = array();

					$o['options'][] = array(
						'id' => (int)$option_product->product_id,
						'qty' => !empty($option_product->cart_product_quantity) ? (int)$option_product->cart_product_quantity : (int)@$option_product->order_product_quantity
					);
				}
			}
			if(!empty($fields) && hikashop_level(2)) {
				$o['fields'] = array();
				foreach($fields as $namekey => $field) {
					if(empty($cart_product->$namekey))
						continue;
					$o['fields'][$namekey] = $cart_product->$namekey;
				}
				if(empty($o['fields']))
					unset($o['fields']);
			}

			$ret[] = $o;
		}

		return $ret;
	}

	public function calculateWeightAndVolume(&$cart) {
		$cart->volume = 0;
		$cart->weight = 0;
		$cart->total_quantity = 0;

		$cart->package = $this->getWeightVolume($cart);

		if(!empty($cart->package['volume']['unit'])) {
			$cart->volume_unit = $cart->package['volume']['unit'];
			$cart->volume = $cart->package['volume']['value'];
		}
		if(!empty($cart->package['weight']['unit'])) {
			$cart->weight_unit = $cart->package['weight']['unit'];
			$cart->weight = $cart->package['weight']['value'];
		}
		if(!empty($cart->package['total_quantity'])) {
			$cart->total_quantity = $cart->package['total_quantity'];
		}
	}

	protected function &getCloneCache($type, $id) {
		$false = false;
		if(!isset(self::$cache[$type][$id]))
			return $false;

		if(!is_object(self::$cache[$type][$id]))
			return self::$cache[$type][$id];

		$ret = unserialize(serialize(self::$cache[$type][$id]));
		return $ret;
	}

	protected function getWeightVolume(&$cart) {
		$ret = array(
			'volume' => array(
				'value' => 0,
				'unit' => null
			),
			'weight' => array(
				'value' => 0,
				'unit' => null
			),
			'total_quantity' => 0,
			'total_items' => 0
		);

		if(!empty($cart->package))
			$ret = array_merge($cart->package, $ret);

		if(empty($cart->products))
			return $ret;

		$volumeHelper = hikashop_get('helper.volume');
		$ret['volume']['unit'] = $volumeHelper->getSymbol();

		$weightHelper = hikashop_get('helper.weight');
		$ret['weight']['unit'] = $weightHelper->getSymbol();

		$group = (int)$this->config->get('group_options', 0);

		foreach($cart->products as $k => &$product) {
			if(empty($product->cart_product_quantity))
				continue;

			if(!empty($product->cart_product_parent_id) && (!bccomp(sprintf('%F',$product->product_length), 0, 5) || !bccomp(sprintf('%F',$product->product_width), 0, 5) || !bccomp(sprintf('%F',$product->product_height), 0, 5))) {
				if(isset($product->parent_product)) {
					if (!bccomp(sprintf('%F',$product->product_length), 0, 5) && !bccomp(sprintf('%F',$product->product_width), 0, 5) && !bccomp(sprintf('%F',$product->product_height), 0, 5)) {
						$product->product_length = $product->parent_product->product_length;
						$product->product_width = $product->parent_product->product_width;
						$product->product_height = $product->parent_product->product_height;
						$product->product_dimension_unit = $product->parent_product->product_dimension_unit;
					} else {
						if (bccomp(sprintf('%F',$product->product_length), 0, 5)) $product->product_length = $product->parent_product->product_length;
						if (bccomp(sprintf('%F',$product->product_width), 0, 5)) $product->product_width = $product->parent_product->product_width;
						if (bccomp(sprintf('%F',$product->product_height), 0, 5)) $product->product_height = $product->parent_product->product_height;
					}
				} elseif(is_string($product->cart_product_id) && substr($product->cart_product_id, 0, 1) == 'p' && isset($cart->products[ $product->cart_product_id ])) {
					$parent_product = &$cart->products[ $product->cart_product_id ];
					$product->product_length = $parent_product->product_length;
					$product->product_width = $parent_product->product_width;
					$product->product_height = $parent_product->product_height;
					$product->product_dimension_unit = $parent_product->product_dimension_unit;
					unset($parent_product);
				} else {
					foreach($cart->products as $parent_product) {
						if($parent_product->cart_product_id != $product->cart_product_parent_id)
							continue;

						$product->product_length = $parent_product->product_length;
						$product->product_width = $parent_product->product_width;
						$product->product_height = $parent_product->product_height;
						$product->product_dimension_unit = $parent_product->product_dimension_unit;
						break;
					}
				}
			}
			if(bccomp(sprintf('%F',$product->product_length), 0, 5) && bccomp(sprintf('%F',$product->product_width), 0, 5) && bccomp(sprintf('%F',$product->product_height), 0, 5)) {
				$product->product_volume = $product->product_length * $product->product_width * $product->product_height;
				$product->product_total_volume = $product->product_volume * $product->cart_product_quantity;
				$product->product_total_volume_orig = $product->product_total_volume;
				$product->product_dimension_unit_orig = $product->product_dimension_unit;
				$product->product_total_volume = $volumeHelper->convert($product->product_total_volume, $product->product_dimension_unit);

				$ret['volume']['value'] += $cart->products[$k]->product_total_volume;
			}

			if(!empty($product->cart_product_parent_id) && !bccomp(sprintf('%F',$product->product_weight), 0, 5)) {
				if(isset($product->parent_product)) {
					$product->product_weight = $product->parent_product->product_weight;
					$product->product_weight_unit = $product->parent_product->product_weight_unit;
				} elseif(is_string($product->cart_product_id) && substr($product->cart_product_id, 0, 1) == 'p' && isset($cart->products[ $product->cart_product_id ])) {
					$parent_product = &$cart->products[ $product->cart_product_id ];
					$product->product_weight = $parent_product->product_weight;
					$product->product_weight_unit = $parent_product->product_weight_unit;
					unset($parent_product);
				} else {
					foreach($cart->products as $parent_product) {
						if($parent_product->cart_product_id != $product->cart_product_parent_id)
							continue;

						$product->product_weight = $parent_product->product_weight;
						$product->product_weight_unit = $parent_product->product_weight_unit;
						break;
					}
				}
			}
			if(bccomp(sprintf('%F',$product->product_weight), 0, 5)) {
				$product->product_weight_orig = $product->product_weight;
				$product->product_weight_unit_orig = $product->product_weight_unit;
				$product->product_weight = $weightHelper->convert($product->product_weight, $product->product_weight_unit);
				$product->product_weight_unit = $ret['weight']['unit'];

				$ret['weight']['value'] += $product->product_weight * $product->cart_product_quantity;
			}

			$ret['total_quantity'] += $product->cart_product_quantity;
			if(!$group || empty($product->cart_product_option_parent_id))
				$ret['total_items'] += $product->cart_product_quantity;
		}
		unset($product);

		return $ret;
	}

	protected function checkQuantities(&$products, &$cart, $options = array()) {
		$ids = array();
		$pids = array();
		foreach($products as $k => &$product) {
			if(empty($product['pid']) && (empty($product['id']) || (int)$product['id'] <= 0)) {
				unset($products[$k]);
				continue;
			}
			if(!empty($product['pid']) && !isset($cart->cart_products[ (int)$product['pid'] ])) {
				unset($products[$k]);
				continue;
			}

			$product['qty_orig'] = (int)$product['qty'];

			if($product['qty'] == 0 || ($product['qty'] < 0 && empty($options['incremental']))) {
				$product['qty'] = 0;
				continue;
			}

			if(!empty($product['pid'])) {
				$pids[ (int)$product['pid'] ] = (int)$product['pid'];
				$i = (int)$cart->cart_products[ (int)$product['pid'] ]->product_id;
				$ids[ $i ] = (int)$i;
			} else {
				$ids[ (int)$product['id'] ] = (int)$product['id'];
			}
		}
		unset($product);

		if(empty($ids)) {
			$products = array();
			return false;
		}


		$ret = true;
		$errors = array();
		$now = time();
		$wishlist = (!empty($cart->cart_type) && $cart->cart_type == 'wishlist');
		$limits = array('min' => false, 'max' => false);
		$b_ids = array();

		$filters = array(
			'product_id' => 'p.product_id IN (' . implode(',', $ids) . ')',
			'product_published' => 'p.product_published = 1',
			'product_type' => 'p.product_type IN (' . $this->db->Quote('main') . ',' . $this->db->Quote('variant') . ')',
		);
		$extra_filters = '';
		hikashop_addACLFilters($filters, 'product_access', 'p');

		$cart_product_ids = array();
		foreach($cart->cart_products as $cart_product) {
			if(!empty($cart_product->product_id))
				$cart_product_ids[ (int)$cart_product->product_id ] = (int)$cart_product->product_id;
		}
		if(!empty($cart_product_ids)) {
			$extra_filters = ' OR p.product_id IN (' . implode(',', $cart_product_ids) . ')';
		}

		$query = 'SELECT p.*, pp.product_published as parent_product_published, pp.product_quantity as parent_product_quantity, '.
				' pp.product_min_per_order as parent_product_min_per_order, pp.product_max_per_order as parent_product_max_per_order, '.
				' pp.product_sale_start as parent_product_sale_start, pp.product_sale_end as parent_product_sale_end, '.
				' pp.product_access as parent_product_access, pp.product_name as parent_product_name '.
			' FROM ' . hikashop_table('product') . ' AS p '.
			' LEFT JOIN ' . hikashop_table('product') . ' AS pp ON (p.product_parent_id > 0 AND pp.product_id = p.product_parent_id) '.
			' WHERE (('.implode(') AND (', $filters).'))'.$extra_filters;
		$this->db->setQuery($query);
		$db_products = $this->db->loadObjectList('product_id');

		foreach($products as $k => &$product) {
			if(!empty($product['pid'])) {
				$id = (int)$cart->cart_products[ (int)$product['pid'] ]->product_id;
			} else {
				$id = $product['id'];
			}
			if(!isset($db_products[ $id ]))
				continue;

			$product['data'] =& $db_products[ $id ];
			if($product['data']->product_type == 'variant')
				$product['data']->product_name = trim($product['data']->parent_product_name .' '. $product['data']->product_name);
			$b_ids[] = $id;
		}
		unset($product);
		if(!$wishlist && hikashop_level(1) && !empty($b_ids)) {
			$query = 'SELECT pr.product_id, pr.product_related_id, pr.product_related_quantity, FLOOR(p.product_quantity / pr.product_related_quantity) as bundle_quantity '.
				' FROM '.hikashop_table('product_related').' AS pr '.
				' INNER JOIN '.hikashop_table('product').' AS p ON pr.product_related_id = p.product_id '.
				' WHERE (pr.product_id IN (' . implode(',', $b_ids).') OR pr.product_related_id IN (' . implode(',', $b_ids).')) AND pr.product_related_type = ' . $this->db->Quote('bundle') . ' AND p.product_quantity >= 0';
			$this->db->setQuery($query);
			$bundles = $this->db->loadObjectList();

			if(!empty($bundles)) {
				foreach($bundles as $k => $bundle) {
					if( isset($db_products[ $bundle->product_id ]) && ($db_products[ $bundle->product_id ]->product_quantity < 0 || $db_products[ $bundle->product_id ]->product_quantity > (int)$bundle->bundle_quantity))
						$db_products[ $bundle->product_id ]->product_quantity = (int)$bundle->bundle_quantity;
					if(!isset($db_products[ $bundle->product_related_id ]))
						continue;
					$qty_already_in_cart = 0;
					foreach($cart->cart_products as $cart_product) {
						if($cart_product->product_id == $bundle->product_id && $cart_product->cart_product_quantity > 0)
							$qty_already_in_cart += $cart_product->cart_product_quantity * $bundle->product_related_quantity;
					}
					$db_products[ $bundle->product_related_id ]->product_quantity = max(0, $db_products[ $bundle->product_related_id ]->product_quantity - $qty_already_in_cart);
				}
			}
		}

		unset($b_ids);

		JPluginHelper::importPlugin('hikashop');
		$this->app->triggerEvent('onBeforeProductQuantityCheck', array(&$products, &$cart, &$options) );

		foreach($products as $k => &$product) {
			if(!empty($product['pid'])) {
				$id = (int)$cart->cart_products[ (int)$product['pid'] ]->product_id;
			} else {
				$id = $product['id'];
			}

			if(empty($product['qty']) || ($product['qty'] < 0 && empty($options['incremental'])))
				continue;

			$msg = '';
			if(!isset($product['data'])) {
				$msg = JText::sprintf('PRODUCT_NOT_AVAILABLE', $id);
			}

			if(!$wishlist && empty($msg) && $product['data']->product_sale_end > 0 && $product['data']->product_sale_end < $now) {
				$msg = JText::sprintf('PRODUCT_NOT_SOLD_ANYMORE', $product['data']->product_name);
			}
			if(!$wishlist && empty($msg) && $product['data']->product_sale_start > $now) {
				$msg = JText::sprintf('PRODUCT_NOT_YET_ON_SALE', $product['data']->product_name);
			}
			if(!$wishlist && empty($msg) && $product['data']->product_quantity == 0) {
				$msg = JText::sprintf('NOT_ENOUGH_STOCK_FOR_PRODUCT', $product['data']->product_name);
			}

			if(!empty($msg)) {
				$ret = false;
				$product['qty'] = 0;
				unset($product['data']);
				$this->addMessage($cart, array('msg' => $msg, 'product_id' => $id, 'type' => 'error'));

				continue;
			}

			if(!empty($product['options']) && $this->config->get('group_options', 0)) {
				$product_options = array();
				foreach($product['options'] as $opt) {
					$coef = (!empty($opt['coef']) && (int)$opt['coef'] > 0) ? (int)$opt['coef'] : 1;
					$product_options[] = array(
						'id' => (int)$opt['id'],
						'qty' => (int)$opt['qty'] * $coef
					);
				}
				$quantityCheck = $this->checkQuantities($product_options, $cart, $options);
				if(!$quantityCheck) {
					$ret = false;
					$product['qty'] = 0;
					unset($product['data']);

					continue;
				}
			}

			if((int)$product['data']->product_parent_id > 0 && $product['data']->product_type == 'variant') {
				$msg = '';

				if(empty($msg) && empty($product['data']->parent_product_published)) {
					$msg = JText::sprintf('PRODUCT_NOT_AVAILABLE', $id);
				}
				if(empty($msg) && !in_array($product['data']->parent_product_access, array('', 'all')) && hikashop_level(2)) {
					if(!empty($cart->user_id)) {
						$user_id = $cart->user_id;
					} elseif(empty($cart->cart_id)) {
						$user_id = hikashop_loadUser();
					}
					if(!empty($user_id)) {
						$userClass = hikashop_get('class.user');
						$user_groups = $userClass->getGroups( (int)$user_id );
						$parent_product_groups = explode(',', $product['data']->parent_product_access);
						hikashop_toInteger($parent_product_groups);
						$intersect = array_intersect($user_groups, $parent_product_groups);
					}
					if(empty($intersect))
						$msg = JText::sprintf('PRODUCT_NOT_AVAILABLE', $id);
				}

				if(!$wishlist && empty($msg) && (empty($product['data']->product_sale_start) && $product['data']->parent_product_sale_start > 0 && (int)$product['data']->parent_product_sale_start > $now)) {
					$msg = JText::sprintf('PRODUCT_NOT_YET_ON_SALE', $product['data']->product_name);
				}
				if(!$wishlist && empty($msg) && (empty($product['data']->product_sale_end) && (int)$product['data']->parent_product_sale_end > 0 && (int)$product['data']->parent_product_sale_end < $now)) {
					$msg = JText::sprintf('PRODUCT_NOT_SOLD_ANYMORE', $product['data']->product_name);
				}
				if(!$wishlist && empty($msg) && ((int)$product['data']->product_quantity == -1 && (int)$product['data']->parent_product_quantity == 0)) {
					$msg = JText::sprintf('NOT_ENOUGH_STOCK_FOR_PRODUCT', $product['data']->product_name);
				}

				if(!empty($msg)) {
					$ret = false;
					$product['qty'] = 0;
					unset($product['data']);
					$this->addMessage($cart, array('msg' => $msg, 'product_id' => $id, 'type' => 'error'));
					continue;
				}

				if(!$wishlist && empty($limits['max']) && (int)$product['data']->parent_product_quantity > 0 && (int)$product['data']->product_quantity == -1)
					$limits['max'] = true;
			}

			if(!$wishlist) {
				if(empty($limits['min']) && (!empty($product['data']->product_min_per_order) || !empty($product['data']->parent_product_min_per_order)))
					$limits['min'] = true;
				if(empty($limits['max']) && (!empty($product['data']->product_max_per_order) || !empty($product['data']->parent_product_max_per_order)))
					$limits['max'] = true;
				if(empty($limits['max']) && (int)$product['data']->product_quantity > 0 || (int)$product['data']->parent_product_quantity > 0)
					$limits['max'] = true;
			}
		}
		unset($product);

		if(!empty($limits['min']) || !empty($limits['max'])) {
			$counts = array();
			foreach($cart->cart_products as $cart_product) {
				$i = (int)$cart_product->product_id;
				if(empty($counts[$i]))
					$counts[$i] = 0;
				$counts[$i] += (int)$cart_product->cart_product_quantity;

				if(!isset($db_products[$i]) || empty($db_products[$i]->product_parent_id))
					continue;

				$j = (int)$db_products[$i]->product_parent_id;
				if(empty($counts[$j]))
					$counts[$j] = 0;
				$counts[$j] += (int)$cart_product->cart_product_quantity;
			}

			foreach($products as $k => &$product) {
				if(empty($product['qty']) || ($product['qty'] < 0 && empty($options['incremental'])))
					continue;

				$min = !empty($product['data']->product_min_per_order) ? (int)$product['data']->product_min_per_order : (int)$product['data']->parent_product_min_per_order;
				$max = !empty($product['data']->product_max_per_order) ? (int)$product['data']->product_max_per_order : (int)$product['data']->parent_product_max_per_order;

				$min = max($min, 1);
				if($max < $min) $max = 0;

				if((int)$product['data']->product_quantity > 0 || ((int)$product['data']->product_quantity < 0 && (int)$product['data']->parent_product_quantity > 0)) {
					$q = ((int)$product['data']->product_quantity > 0) ? (int)$product['data']->product_quantity : (int)$product['data']->parent_product_quantity;
					$max = ($max > 0) ? min($q, $max) : $q;

					if((int)$product['data']->product_quantity > 0) {
						if((int)$product['data']->product_max_per_order == 0 || (int)$product['data']->product_quantity < (int)$product['data']->product_max_per_order)
							$products[$k]['data']->product_max_per_order = $product['data']->product_max_per_order = (int)$product['data']->product_quantity;
					} else {
						if((int)$product['data']->parent_product_max_per_order == 0 || (int)$product['data']->parent_product_quantity < (int)$product['data']->parent_product_max_per_order)
							$products[$k]['data']->parent_product_max_per_order = $product['data']->parent_product_max_per_order = (int)$product['data']->parent_product_quantity;
					}
				}

				if($min <= 1 && empty($max))
					continue;

				$i = (isset($product['id']) ? $product['id'] : (int)$product['data']->product_id);
				$qty_min = isset($counts[ $i ]) ? $counts[ $i ] : 0;
				$qty_max = $qty_min;

				if(!empty($product['data']->product_parent_id)) {
					if(isset($counts[ (int)$product['data']->product_parent_id ])){
						if(empty($product['data']->product_min_per_order))
							$qty_min = $counts[ (int)$product['data']->product_parent_id ];
						if(empty($product['data']->product_max_per_order))
							$qty_max = $counts[ (int)$product['data']->product_parent_id ];
					}

					foreach($products as $k => &$product2) {
						if(empty($product2['qty']) || ($product2['qty'] < 0 && empty($options['incremental'])))
							continue;
						if($product['data']->product_parent_id == $product2['data']->product_parent_id && $product['data']->product_id != $product2['data']->product_id){
							if(empty($product['data']->product_min_per_order))
								$qty_min += $product2['qty'];
							if(empty($product['data']->product_max_per_order))
								$qty_max += $product2['qty'];
						}
					}
				}

				$qty_min += (int)$product['qty'];
				$qty_max += (int)$product['qty'];

				if($qty_min < $min || ($max >= $min && $qty_max > $max) || ($max > 0 && $min > $max)) {
					$ret = false;
					$product['qty'] = 0;
					$msg = ($qty_min < $min) ? 'NOT_ENOUGH_QTY_FOR_PRODUCT' : (($qty_max > $max) ? 'TOO_MUCH_QTY_FOR_PRODUCT' : 'INVALID_MIN_MAX_FOR_PRODUCT');

					$this->addMessage($cart, array(
						'msg' => JText::sprintf($msg, $product['data']->product_name),
						'product_id' => $id,
						'type' => 'error'
					));
					continue;
				}
			}
			unset($product);
		}

		if(hikashop_level(1)) {
			$limitClass = hikashop_get('class.limit');
			$limits = $limitClass->getCart($cart, $products);

			$limit_errors = $limitClass->checkLimits($limits, $cart, $products);
			if($limit_errors !== true) {
				$ret = false;
			}
			unset($limit_errors);
		}


		$cart_products = array();
		if(isset($cart->products)) {
			$cart_products =& $cart->products;
		} elseif(!empty($cart->cart_id)) {
			$filters = array(
				'cart_product.cart_id = '.(int)$cart->cart_id,
				'cart_product.product_id > 0'
			);
			hikashop_addACLFilters($filters, 'product_access', 'product');

			$query = 'SELECT cart_product.cart_product_id, cart_product.cart_product_quantity, cart_product.cart_product_option_parent_id, cart_product.cart_product_parent_id, cart_product.cart_product_wishlist_id, product.* '.
				' FROM ' . hikashop_table('cart_product').' AS cart_product '.
				' LEFT JOIN ' . hikashop_table('product').' AS product ON cart_product.product_id = product.product_id '.
				' WHERE (' . implode(') AND (', $filters) . ') '.
				' ORDER BY cart_product.cart_product_id ASC';
			$this->db->setQuery($query);
			$cart_products = $this->db->loadObjectList('cart_product_id');
		}
		foreach($products as $k => &$product) {
			if(empty($product['qty']))
				continue;

			$wantedQuantity = $product['qty_orig'];
			$quantity = $product['qty'];
			$cart_product_id = $k;
			$displayErrors = true;

			$old_messages = $this->app->getMessageQueue();

			$this->app->triggerEvent('onAfterProductQuantityCheck', array(&$product['data'], &$wantedQuantity, &$quantity, &$cart_products, &$cart_product_id, &$displayErrors) );

			if($quantity == $product['qty'])
				continue;

			$product['qty'] = $quantity;

			$new_messages = $this->app->getMessageQueue();
			if(count($old_messages) < count($new_messages)) {
				$new_messages = array_slice($new_messages, count($old_messages));
				foreach($new_messages as $msg) {
					$this->addMessage($cart, array(
						'msg' => $msg['message'],
						'product_id' => $product['id'],
						'type' => $msg['type']
					));
				}
			} else if($displayErrors && $wantedQuantity > $quantity) {
				$ret = false;
				$this->addMessage($cart, array(
					'msg' => JText::sprintf( ($quantity == 0 ? 'LIMIT_REACHED_REMOVED' : 'LIMIT_REACHED'), $product['data']->product_name),
					'product_id' => $product['id'],
					'type' => 'notice'
				));
			}
		}
		unset($product);
		unset($cart_products);

		$this->app->triggerEvent('onAfterProductCheckQuantities', array(&$products, &$cart, $options) );

		foreach($products as $k => $product) {
			if(!empty($product['qty']))
				continue;
			unset($products[$k]);
		}

		if(empty($products))
			$ret = false;
		elseif(!empty($options['ignore_errors']))
			return array('status' => false, 'errors' => $cart->messages);

		return $ret;
	}

	protected function checkCartQuantities(&$cart, $parent_products) {
		$ret = true;
		if(empty($cart->products))
			return $ret;

		JPluginHelper::importPlugin('hikashop');

		if(!in_array($cart->cart_type, array('cart', 'wishlist'))) {
			$this->app->triggerEvent('onUnknownCheckCartQuantities', array(&$cart, $parent_products, &$ret) );
			return $ret;
		}

		$wishlist = (!empty($cart->cart_type) && $cart->cart_type == 'wishlist');

		if(hikashop_level(1)) {
			$b_ids = array();
			foreach($cart->products as $cart_product_id => $product) {
				if(empty($product->product_id))
					continue;
				$b_ids[] = (int)$product->product_id;
			}
			if(!empty($b_ids)) {
				$query = 'SELECT pr.product_id, pr.product_related_id, pr.product_related_quantity, FLOOR(p.product_quantity / pr.product_related_quantity) as bundle_quantity '.
					' FROM '.hikashop_table('product_related').' AS pr '.
					' INNER JOIN '.hikashop_table('product').' AS p ON pr.product_related_id = p.product_id '.
					' WHERE pr.product_id IN (' . implode(',', $b_ids).') AND pr.product_related_type = ' . $this->db->Quote('bundle') . ' AND p.product_quantity >= 0';
				$this->db->setQuery($query);
				$bundles = $this->db->loadObjectList();
				if(!empty($bundles)) {
					foreach($cart->products as &$product) {
						if(empty($product->product_id))
							continue;
						$min_bundle_quantity = null;
						foreach($bundles as $bundle) {
							if($bundle->product_id != $product->product_id)
								continue;
							if(is_null($min_bundle_quantity) || $min_bundle_quantity > $bundle->bundle_quantity)
								$min_bundle_quantity = $bundle->bundle_quantity;

							foreach($cart->products as &$product2) {
								if(empty($product2->product_id))
									continue;
								if( $product2->product_id != $bundle->product_related_id)
									continue;
								$product2->bundle_quantity = $product2->product_quantity - $bundle->product_related_quantity*$product->cart_product_quantity;
							}
						}
						if($product->product_quantity < 0 && !is_null($min_bundle_quantity))
							$product->bundle_quantity = (int)$min_bundle_quantity;
					}
					unset($product);
				}
			}
			unset($b_ids);
		}

		$this->app->triggerEvent('onBeforeCheckCartQuantities', array(&$cart, &$parent_products) );

		$limits = array('min' => false, 'max' => false);
		$notUsable = array();
		$errorMessagesProductNames = array();
		$userClass = null;
		foreach($cart->products as $cart_product_id => &$product) {
			if(empty($product->product_id))
				continue;
			if(!is_numeric($cart_product_id) && substr($cart_product_id, 0, 1) == 'p')
				continue;

			$parent_product = new stdClass();

			if(!isset($product->old))
				$product->old = new stdClass();

			if((int)$product->cart_product_quantity <= 0)
				$product->cart_product_quantity = 0;
			$product->old->quantity = (int)$product->cart_product_quantity;
			if(empty($product->cart_product_quantity) && empty($product->old->quantity))
				continue;

			if((int)$product->product_parent_id > 0 && $product->product_type == 'variant' && isset($parent_products[ (int)$product->product_parent_id ]))
				$errorMessagesProductNames[$cart_product_id] = trim($parent_products[ (int)$product->product_parent_id ]->product_name . ' ' . $product->product_name);
			else
				$errorMessagesProductNames[$cart_product_id] = $product->product_name;

			if(empty($product->product_published)) {
				$notUsable[$cart_product_id] = array('id' => $cart_product_id, 'qty' => 0);
				continue;
			}

			if(!$wishlist && (int)$product->product_quantity != -1 && (int)$product->product_quantity < (int)$product->cart_product_quantity) {
				$notUsable[$cart_product_id] = array('id' => $cart_product_id, 'qty' => 0);
				$cart->messages[] = array('msg' => JText::sprintf('NOT_ENOUGH_STOCK_FOR_PRODUCT', $errorMessagesProductNames[$cart_product_id]), 'product_id' => $product->product_id, 'type' => 'notice');
				continue;
			}
			if(!$wishlist && isset($product->bundle_quantity) && (int)$product->bundle_quantity < (int)$product->cart_product_quantity) {
				$notUsable[$cart_product_id] = array('id' => $cart_product_id, 'qty' => 0);
				$cart->messages[] = array('msg' => JText::sprintf('NOT_ENOUGH_STOCK_FOR_PRODUCT', $errorMessagesProductNames[$cart_product_id]), 'product_id' => $product->product_id, 'type' => 'notice');
				continue;
			}

			if(!$wishlist && !empty($product->product_sale_start) && (int)$product->product_sale_start > time()) {
				$notUsable[$cart_product_id] = array('id' => $cart_product_id, 'qty' => 0);
				$cart->messages[] = array('msg' => JText::sprintf('PRODUCT_NOT_YET_ON_SALE', $errorMessagesProductNames[$cart_product_id]), 'product_id' => $product->product_id, 'type' => 'notice');
				continue;
			}

			if(!$wishlist && !empty($product->product_sale_end) && (int)$product->product_sale_end < time()) {
				$notUsable[$cart_product_id] = array('id' => $cart_product_id, 'qty' => 0);
				$cart->messages[] = array('msg' => JText::sprintf('PRODUCT_NOT_SOLD_ANYMORE', $errorMessagesProductNames[$cart_product_id]), 'product_id' => $product->product_id, 'type' => 'notice');
				continue;
			}

			if((int)$product->product_parent_id > 0 && $product->product_type == 'variant') {

				if(!isset($parent_products[ (int)$product->product_parent_id ])) {
					$notUsable[$cart_product_id] = array('id' => $cart_product_id, 'qty' => 0);
					$cart->messages[] = array('msg' => JText::sprintf('PRODUCT_NOT_AVAILABLE', $errorMessagesProductNames[$cart_product_id]), 'product_id' => $product->product_id, 'type' => 'notice');
					continue;
				}

				$parent_product = $parent_products[ (int)$product->product_parent_id ];

				if(empty($parent_product->product_published)) {
					$notUsable[$cart_product_id] = array('id' => $cart_product_id, 'qty' => 0);
					$cart->messages[] = array('msg' => JText::sprintf('PRODUCT_NOT_AVAILABLE', $errorMessagesProductNames[$cart_product_id]), 'product_id' => $product->product_id, 'type' => 'notice');
					continue;
				}
				if(!$wishlist && (int)$product->product_quantity == -1 && (int)$parent_product->product_quantity == 0) {
					$notUsable[$cart_product_id] = array('id' => $cart_product_id, 'qty' => 0);
					$cart->messages[] = array('msg' => JText::sprintf('NOT_ENOUGH_STOCK_FOR_PRODUCT', $errorMessagesProductNames[$cart_product_id]), 'product_id' => $product->product_id, 'type' => 'notice');
					continue;
				}
				if(!$wishlist && empty($product->product_sale_start) && (int)$parent_product->product_sale_start > 0 && (int)$parent_product->product_sale_start > time()) {
					$notUsable[$cart_product_id] = array('id' => $cart_product_id, 'qty' => 0);
					$cart->messages[] = array('msg' => JText::sprintf('PRODUCT_NOT_YET_ON_SALE', $errorMessagesProductNames[$cart_product_id]), 'product_id' => $product->product_id, 'type' => 'notice');
					continue;
				}
				if(!$wishlist && empty($product->product_sale_end) && (int)$parent_product->product_sale_end > 0 && (int)$parent_product->product_sale_end < time()) {
					$notUsable[$cart_product_id] = array('id' => $cart_product_id, 'qty' => 0);
					$cart->messages[] = array('msg' => JText::sprintf('PRODUCT_NOT_SOLD_ANYMORE', $errorMessagesProductNames[$cart_product_id]), 'product_id' => $product->product_id, 'type' => 'notice');
					continue;
				}

				if(!in_array($parent_product->product_access, array('', 'all')) && hikashop_level(2)) {
					if(empty($userClass))
						$userClass = hikashop_get('class.user');
					$user_groups = $userClass->getGroups( (int)$cart->user_id );
					$parent_product_groups = explode(',', $parent_product->product_access);
					hikashop_toInteger($parent_product_groups);
					$intersect = array_intersect($user_groups, $parent_product_groups);
					if(empty($intersect)){
						$notUsable[$cart_product_id] = array('id' => $cart_product_id, 'qty' => 0);
						$cart->messages[] = array('msg' => JText::sprintf('PRODUCT_NOT_AVAILABLE', $errorMessagesProductNames[$cart_product_id]), 'product_id' => $product->product_id, 'type' => 'notice');
						continue;
					}
				}

				if(!$wishlist && empty($limits['max']) && (int)$parent_product->product_quantity > 0 && (int)$product->product_quantity == -1)
					$limits['max'] = true;
			}

			if(!$wishlist) {
				if(empty($limits['min']) && (!empty($product->product_min_per_order) || !empty($parent_product->product_min_per_order)))
					$limits['min'] = true;
				if(empty($limits['max']) && (!empty($product->product_max_per_order) || !empty($parent_product->product_max_per_order)))
					$limits['max'] = true;

				if(empty($limits['max']) && (int)$product->product_quantity > 0 && (int)$product->product_parent_id == 0)
					$limits['max'] = true;
			}
		}
		unset($product);
		unset($userClass);

		if(!empty($limits['min']) || !empty($limits['max'])) {
			$counts = array();
			foreach($cart->products as $product) {
				$i = (int)$product->product_id;
				if(empty($counts[$i]))
					$counts[$i] = 0;
				$counts[$i] += (int)$product->cart_product_quantity;

				if(empty($product->product_parent_id))
					continue;

				$j = (int)$product->product_parent_id;
				if(empty($counts[$j]))
					$counts[$j] = 0;
				$counts[$j] += (int)$product->cart_product_quantity;
			}

			$empty_parent_product = new stdClass();
			$empty_parent_product->product_min_per_order = $empty_parent_product->product_max_per_order = $empty_parent_product->product_quantity = 0;

			foreach($cart->products as $cart_product_id => &$product) {
				if(!is_numeric($cart_product_id) && substr($cart_product_id, 0, 1) == 'p')
					continue;

				if(empty($product->product_parent_id))
					$parent_product = $empty_parent_product;
				else
					$parent_product = $parent_products[ (int)$product->product_parent_id ];

				$min = !empty($product->product_min_per_order) ? (int)$product->product_min_per_order : (int)$parent_product->product_min_per_order;
				$max = !empty($product->product_max_per_order) ? (int)$product->product_max_per_order : (int)$parent_product->product_max_per_order;

				$min = max($min, 1);
				if($max < $min) $max = 0;

				if((int)$product->product_quantity > 0 || ((int)$product->product_quantity < 0 && (int)$parent_product->product_quantity > 0)) {
					$q = ((int)$product->product_quantity > 0) ? (int)$product->product_quantity : (int)$parent_product->product_quantity;
					$max = ($max > 0) ? min($q, $max) : $q;

					if((int)$product->product_quantity > 0) {
						if((int)$product->product_max_per_order == 0 || (int)$product->product_quantity < (int)$product->product_max_per_order)
							$product->product_max_per_order = (int)$product->product_quantity;
					} else {
						if((int)$parent_product->product_max_per_order == 0 || (int)$parent_product->product_max_per_order < (int)$parent_product->product_quantity)
							$parent_product->product_max_per_order = (int)$parent_product->product_quantity;
					}
				}

				if($min <= 1 && empty($max))
					continue;

				$i = (int)$product->product_id;
				$qty_min = $qty_max = $counts[ $i ];

				if(empty($product->product_min_per_order) && !empty($product->product_parent_id) && isset($counts[ (int)$product->product_parent_id ]))
					$qty_min = $counts[ (int)$product->product_parent_id ];
				if(empty($product->product_max_per_order) && !empty($product->product_parent_id) && isset($counts[ (int)$product->product_parent_id ]))
					$qty_max = $counts[ (int)$product->product_parent_id ];

				if($qty_min < $min || ($max >= $min && $qty_max > $max) || ($max > 0 && $min > $max)) {
					$notUsable[$cart_product_id] = array('id' => $cart_product_id, 'qty' => 0);

					$msg = ($qty_min < $min) ? 'NOT_ENOUGH_QTY_FOR_PRODUCT' : (($qty_max > $max) ? 'TOO_MUCH_QTY_FOR_PRODUCT' : 'INVALID_MIN_MAX_FOR_PRODUCT');

					$cart->messages[] = array('msg' => JText::sprintf($msg, $errorMessagesProductNames[$cart_product_id]), 'product_id' => $product->product_id, 'type' => 'warning');
					continue;
				}
			}
			unset($product);
		}

		if(!empty($notUsable)) {
			if(!$wishlist)
				$this->updateProduct($cart->cart_id, $notUsable);

			foreach($notUsable as $k => $v) {
				unset($cart->cart_products[$k]);
				unset($cart->products[$k]);
				if(isset($cart->products['p'.$k])) {
					unset($cart->cart_products['p'.$k]);
					unset($cart->products['p'.$k]);
				}
			}
			$ret = false;
		}

		if(!$wishlist && hikashop_level(1)) {
			if(!$this->limitCartProducts($cart))
				$ret = false;

			$limitClass = hikashop_get('class.limit');
			$limits = $limitClass->getCart($cart);
			if(!$limitClass->checkLimits($limits, $cart))
				$ret = false;
		}

		$product_keys = array_reverse(array_keys($cart->cart_products));
		foreach($product_keys as $k) {
			if(!is_numeric($k) && substr($k, 0, 1) == 'p')
				continue;
			$p =& $cart->products[$k];

			$wantedQuantity = (int)$p->cart_product_quantity;
			$quantity = (int)$p->cart_product_quantity;
			$cart_product_id = $k;
			$displayErrors = true;

			$old_messages = $this->app->getMessageQueue();

			$this->app->triggerEvent('onAfterProductQuantityCheck', array(&$p, &$wantedQuantity, &$quantity, &$cart->products, &$cart_product_id, &$displayErrors) );

			if((int)$quantity < 0 || (int)$quantity == (int)$p->cart_product_quantity) {
				unset($p);
				continue;
			}

			$cart->products[$k]->cart_product_quantity = (int)$quantity;
			$cart->cart_products[$k]->cart_product_quantity = (int)$quantity;

			$new_messages = $this->app->getMessageQueue();
			if(count($old_messages) < count($new_messages)) {
				$new_messages = array_slice($new_messages, count($old_messages));
				foreach($new_messages as $msg) {
					$cart->messages[] = array(
						'msg' => $msg['message'],
						'product_id' => $p->product_id,
						'type' => $msg['type']
					);
				}
			} elseif($displayErrors && $wantedQuantity > $quantity) {
				$cart->messages[] = array(
					'msg' => JText::sprintf( ($quantity == 0 ? 'LIMIT_REACHED_REMOVED' : 'LIMIT_REACHED'), $p->product_name),
					'product_id' => $p->product_id,
					'type' => 'warning'
				);
			}

			unset($p);
		}

		$this->app->triggerEvent('onAfterCheckCartQuantities', array(&$cart, $parent_products, &$ret) );

		$notUsable = array();
		foreach($cart->cart_products as $k => $p) {
			if(!is_numeric($k) && substr($k, 0, 1) == 'p')
				continue;
			if(empty($p->cart_product_quantity) || (int)$p->cart_product_quantity < 0)
				$notUsable[$k] = array('id' => $k, 'qty' => 0);
		}
		if(!empty($notUsable)) {
			$this->updateProduct($cart->cart_id, $notUsable);
			foreach($notUsable as $k => $v) {
				unset($cart->cart_products[$k]);
				unset($cart->products[$k]);
				if(isset($cart->products['p'.$k])) {
					unset($cart->cart_products['p'.$k]);
					unset($cart->products['p'.$k]);
				}
			}
			$ret = false;
		}

		return $ret;
	}

	public function addCharacteristics(&$product, &$mainCharacteristics, &$characteristics) {
		$product->characteristics = @$mainCharacteristics[$product->product_id][0];
		if(is_array($product->characteristics) && count($product->characteristics) > 0) {
			foreach($product->characteristics as $k => $characteristic) {
				if(!empty($mainCharacteristics[$product->product_id][$k]))
					$product->characteristics[$k]->default = end($mainCharacteristics[$product->product_id][$k]);
			}
		}
		if(empty($product->variants))
			return;

		foreach($characteristics as $characteristic) {
			foreach($product->variants as $k => $variant) {
				if((int)$variant->product_id != (int)$characteristic->variant_product_id)
					continue;
				$product->characteristics[$characteristic->characteristic_parent_id]->values[$characteristic->characteristic_id] = $characteristic;
			}
		}
		foreach($product->variants as $j => $variant) {
			$chars = array();
			if(!empty($variant->characteristics)) {
				foreach($variant->characteristics as $k => $val) {
					$i = 0;
					$ordering = (int)@$product->characteristics[$val->characteristic_parent_id]->ordering;
					while(isset($chars[$ordering]) && $i < 30) {
						$i++;
						$ordering++;
					}
					$chars[$ordering] = $val;
				}
			}
			ksort($chars);
			$product->variants[$j]->characteristics = $chars;
		}
	}

	public function loadAddress(&$order, $address, $loading_type = 'parent', $address_type = 'shipping') {
		$addressClass = hikashop_get('class.address');
		$name = $address_type.'_address';
		if(!is_object($order))
			$order = new stdClass();
		$order->$name = $addressClass->get($address);
		if(empty($order->$name))
			return;

		$array = array(&$order->$name);
		$addressClass->loadZone($array, $loading_type);
		if(!empty($addressClass->fields)) {
			$order->fields =& $addressClass->fields;
		}
	}

	protected function extractZone($address, &$zones, $fallback = false) {
		$field = 'address_country';
		if(!empty($address->address_state)) {
			if(is_array($address->address_state)) {
				if(count($address->address_state)) {
					$first_state = reset($address->address_state);
					if(!empty($first_state))
						$field = 'address_state';
				}
			} else {
				$field = 'address_state';
			}
		}


		$value = null;
		if($address !== null)
			$value = $address->$field;
		if(is_array($value)) {
			$value = reset($value);
		}

		if($address !== null && empty($zones[$value])) {
			$zoneClass = hikashop_get('class.zone');
			$zones[$value] = $zoneClass->get($value);
		}
		if($address !== null && !empty($zones[$value]))
			return $zones[$value]->zone_id;

		if($fallback === false)
			return 0;

		if($fallback !== true && $fallback > 0)
			return $fallback;

		$zone_id = (int)$this->app->getUserState(HIKASHOP_COMPONENT.'.zone_id', 0);
		if(!empty($zone_id))
			return $zone_id;

		$zone_id = explode(',', $this->config->get('main_tax_zone', 0));
		if(count($zone_id))
			$zone_id = (int)array_shift($zone_id);
		else
			$zone_id = 0;
		$this->app->setUserState(HIKASHOP_COMPONENT.'.zone_id', $zone_id);

		return $zone_id;
	}

	public function saveForm() {
		if(!hikashop_isClient('administrator'))
			return $this->frontSaveForm();

		$cart_id = hikashop_getCID('cart_id');
		$formData = hikaInput::get()->get('data', array(), 'array');

		$cart = new stdClass();
		$cart->cart_id = $cart_id;
		foreach($formData['cart'] as $column => $value) {
			hikashop_secureField($column);
			if(is_array($value) || is_object($value))
				continue;

			$cart->$column = $value;
		}

		if(isset($formData['item'])) {
			$cart->cart_products = array();

			$oldCart = $this->get($cart_id);
			$product_ids = array();
			foreach($oldCart->cart_products as $p) {
				if(empty($p->product_id))
					continue;
				$product_ids[(int)$p->product_id] = (int)$p->product_id;
			}
			$fields = $this->loadFieldsForProducts($product_ids, 'backend');

			foreach($formData['item'] as $k => $qty) {
				$p = new stdClass();
				$p->cart_id = (int)$cart_id;
				$p->cart_product_id = (int)$k;
				$p->cart_product_quantity = (int)$qty;

				if(!empty($formData['products'][$k]['field']) && !empty($oldCart->cart_products[$k]->product_id)) {
					$oldData = new stdClass();
					$oldData->product_id = (int)$oldCart->cart_products[$k]->product_id;

					$data = new stdClass();
 					$ok = $this->fieldClass->checkFieldsData($fields, $formData['products'][$k]['field'], $data, 'item', $oldData);

					if($ok && !empty($data)) {
						foreach($data as $key => $value) {
							if(isset($p->$key))
								continue;
							$p->$key = $value;
						}
					}
				}

				$cart->cart_products[$k] = $p;
			}
		}

		$status = $this->save($cart);

		return $status;
	}

	public function frontSaveForm() {
		$cart_id = hikashop_getCID('cart_id');
		$user_id = hikashop_loadUser(false);
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);

		$oldCart = $this->get($cart_id);
		if(empty($oldCart) || !in_array($oldCart->cart_type, array('cart','wishlist')))
			return false;

		if($oldCart->cart_type != 'cart' && $oldCart->user_id != $user_id)
			return false;

		$cart = new stdClass();
		$cart->cart_id = (int)$cart_id;
		$cart->cart_type = $oldCart->cart_type;

		$formData = hikaInput::get()->get('data', array(), 'array');
		if(isset($formData['cart_name']))
			$cart->cart_name = $safeHtmlFilter->clean($formData['cart_name'], 'string');

		if($oldCart->cart_type == 'wishlist') {
			if(!empty($formData['cart_share']))
				$cart->cart_share = $safeHtmlFilter->clean($formData['cart_share'], 'string');
		}

		$status = true;
		if(isset($formData['products'])) {
			$cart->cart_products = $oldCart->cart_products;

			$updatingProducts = array();
			foreach($formData['products'] as $k => $item) {
				$k = (int)$k;

				if(!isset($oldCart->cart_products[$k]))
					continue;

				if((int)$item['quantity'] == 0 && !is_numeric($item['quantity']))
					continue;

				$updatingProducts[$k] = array(
					'id' => $k,
					'qty' => (int)$item['quantity'],
				);
			}

			if(empty($updatingProducts))
				return $status;

			$old_message_count = !empty($cart->messages) ? count($cart->messages) : 0;

			$status = $this->updateProduct($cart, $updatingProducts);

			if(!$status && !empty($cart->messages) && count($cart->messages) > $old_message_count) {
				$msgs = array_slice($cart->messages, $old_message_count);
				foreach($msgs as $msg) {
					$this->app->enqueueMessage($msg['msg'], $msg['type']);
				}
			}
		}

		if($status !== false)
			$status = $this->save($cart);

		return $status;
	}

	public function generateHash(&$cart_products, $zone_id) {
		$remove_columns = array('cart_modified', 'cart_coupon', 'product_hit', 'product_sales', 'product_modified', 'variants', 'characteristics', 'wanted_quantity');
		$products = array();
		foreach($cart_products as $cart_product) {
			$product = new stdClass();
			foreach(get_object_vars($cart_product) as $k => $row) {
				if(in_array($k,$remove_columns))
					continue;
				$product->$k = $row;
			}
			$products[] = $product;
		}
		return sha1($zone_id . '_' . serialize($products) . '_' . hikashop_loadUser(false));
	}

	protected function limitCartProducts(&$cart) {
		$item_limit = (int)$this->config->get('cart_item_limit', 0);
		if($item_limit <= 0)
			return true;

		$current_items = 0;
		foreach($cart->cart_products as $product) {
			$current_items += (int)$product->cart_product_quantity;
		}
		if($current_items <= $item_limit)
			return true;

		$remove = $current_items - $item_limit;

		$product_cart_ids = array_reverse(array_keys($cart->cart_products));
		$id_changed = 0;
		$name_changed = '';
		foreach($product_cart_ids as $k) {
			if(!is_numeric($k) && substr($k, 0, 1) == 'p')
				continue;

			$q = (int)$cart->cart_products[$k]->cart_product_quantity;
			if($q <= $remove) {
				$remove -= $q;
				$cart->cart_products[$k]->cart_product_quantity = 0;
				$cart->products[$k]->cart_product_quantity = 0;
				if(empty($id_changed)) {
					$id_changed = $cart->products[$k]->product_id;
					$name_changed = $cart->products[$k]->product_name;
				}
			} else {
				$q -= $remove;
				$cart->cart_products[$k]->cart_product_quantity = $q;
				$cart->products[$k]->cart_product_quantity = $q;
				$remove = 0;
				if(empty($id_changed)) {
					$id_changed = $cart->products[$k]->product_id;
					$name_changed = $cart->products[$k]->product_name;
				}
			}
			if(empty($remove))
				break;
		}

		$cart->messages[] = array(
			'msg' => JText::sprintf('LIMIT_REACHED_REMOVED', $name_changed),
			'product_id' => $id_changed,
			'type' => 'warning'
		);

		return false;
	}

	public function checkSubscription($cart) {
	}

	function loadNotification($cart_id, $type) {
		$cart = $this->getFullCart($cart_id, array('skip_user_check' => true));
		if(empty($cart)) {
			return false;
		}

		$mailClass = hikashop_get('class.mail');
		$data = new stdClass();
		$data->cart = $cart;
		$userClass = hikashop_get('class.user');
		$data->user = $userClass->get($cart->user_id);
		$mail = $mailClass->get($type, $data);
		$subject = strtoupper($type).'_NOTIFICATION_SUBJECT';
		if(!empty($mail->subject))
			$subject = $mail->subject;
		$mail->subject = JText::sprintf($subject, $data->user->name, $data->user->user_email, $data->cart->cart_name);
		$mail->from_email = $this->config->get('from_email');
		$mail->from_name = $this->config->get('from_name');
		$mail->dst_email = $data->user->user_email;
		if(!empty($data->user->name))
			$mail->dst_name = $data->user->name;

		return $mail;
	}

	public function getShareUrl(&$cart) {
		$cart_share_url = '';
		global $Itemid;
		$menu_id = $Itemid;
		if(in_array($cart->cart_share, array('public', 'email'))) {
			$menusClass = hikashop_get('class.menus');
			if($Itemid)
				$menu_id = $menusClass->getPublicMenuItemId($Itemid);
			if(empty($menu_id))
				$menu_id = $menusClass->getPublicMenuItemId();
		}
		$url_itemid = '';
		if(!empty($menu_id))
			$url_itemid = '&Itemid=' . $menu_id;
		$link_token = ($cart->cart_share == 'email' && !empty($cart->cart_params->token) ? '&token='.$cart->cart_params->token : '');
		$cart_share_url = hikashop_cleanURL('index.php?option=com_hikashop&ctrl=cart&task=show&cid='.(int)$cart->cart_id . $link_token . $url_itemid);

		return $cart_share_url;
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

		$type = @$options['type'];
		if(empty($type))
			$type = 'cart';

		$select = array('c.*', 'u.user_email');
		$table = array(hikashop_table('cart').' AS c');
		$where = array(
			'cart_type' => 'c.cart_type = '.$this->db->Quote($type)
		);

		if(!empty($search)) {
			$searchMap = array('c.cart_id', 'c.cart_name', 'u.user_email');
			if(!HIKASHOP_J30)
				$searchVal = '\'%' . $this->db->getEscaped(HikaStringHelper::strtolower($search), true) . '%\'';
			else
				$searchVal = '\'%' . $this->db->escape(HikaStringHelper::strtolower($search), true) . '%\'';
			$where['search'] = '('.implode(' LIKE '.$searchVal.' OR ', $searchMap).' LIKE '.$searchVal.')';
		}

		$order = ' ORDER BY c.cart_modified DESC';

		$query = 'SELECT '.implode(', ', $select) . ' FROM ' . implode(' ', $table) . ' LEFT JOIN #__hikashop_user as u ON c.user_id = u.user_id WHERE ' . implode(' AND ', $where).$order;
		$this->db->setQuery($query, $page, $limit);

		$ret[0] = $this->db->loadObjectList('cart_id');

		if(count($ret[0]) < $limit)
			$fullLoad = true;

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

	public function getCartProductData($product_id, $options=null, $fields=null) {
		$cart = $this->getFullCart();
		if(empty($cart) || empty($cart->products))
			return null;
		$cart_product_options = $this->getCartProductOptions($cart);
		foreach($cart->products as $p) {
			$data = array(
				'id' => $product_id,
			);
			if($this->compareCartProducts($data, $p, $cart_product_options)) {
				return $p;
			}
		}
		return null;
	}

	public function syncInit() {
		static $done = false;
		if(!$done) {
			$done = true;
			global $Itemid;
			$url_itemid = '';
			if(!empty($Itemid)) {
				$url_itemid .= '&Itemid='.$Itemid;
			}
			$cart = $this->getFullCart();
			$ret = $this->getCartProductsInfo($cart);
?>
<script>
window.hikashop.ready(function(){
	window.hikashop.cartInfoUrl = '<?php echo hikashop_completeLink('product&task=cartinfo'.$url_itemid, 'ajax', false, true); ?>';
	window.hikashop.cartInfo = <?php echo json_encode($ret);?>;
	window.hikashop.syncInit();
});
</script>
<?php
		}
	}


	public function getCartProductsInfo(&$cart) {
		$ret = array();
		if(!empty($cart->messages))
			$ret['messages'] = $cart->messages;

		if(empty($cart->cart_products))
			$ret['empty'] = true;

		if(!empty($cart->products) && empty($ret['empty'])) {
			$ret['products'] = array();
			foreach($cart->products as $product) {
				if(!empty($product->cart_product_option_parent_id))
					continue;
				if(empty($product->cart_product_quantity))
					continue;
				$data = array(
					'product_id' => (int)$product->product_id,
					'cart_product_id' => (int)$product->cart_product_id,
					'quantity' => (int)$product->cart_product_quantity,
					'product_name' => $product->product_name,
				);

				$options = array();
				foreach($cart->products as $option) {
					if(empty($option->cart_product_option_parent_id))
						continue;
					if(empty($option->cart_product_quantity))
						continue;
					if($option->cart_product_option_parent_id == $product->cart_product_id) {
						$options[] = (int)$option->product_id;
					}
				}
				if(count($options)) {
					$data['options'] = $options;
				}

				$ret['products'][] = $data;
			}
		}

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onGetCartProductsInfo', array(&$cart, &$ret) );

		return $ret;
	}

	 public function initCart() {
	 	$ret = new stdClass();
	 	return $ret;
	 }
}
