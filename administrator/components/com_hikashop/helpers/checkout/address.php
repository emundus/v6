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
include_once HIKASHOP_HELPER . 'checkout.php';

class hikashopCheckoutAddressHelper extends hikashopCheckoutHelperInterface {
	protected $params = array(
		'read_only' =>  array(
			'name' => 'READ_ONLY',
			'type' => 'boolean',
			'default' => 0
		),
		'address_selector' => array(
			'name' => 'HIKASHOP_CHECKOUT_ADDRESS_SELECTOR',
			'type' => 'radio',
			'tooltip' => 'checkout_address_selector',
			'default' => 1,
			'showon' => array(
				'key' => 'read_only',
				'values' => array(0)
			)
		),
		'type' => array(
			'name' => 'HIKASHOP_ADDRESS_TYPE',
			'type' => 'radio',
			'default' => 'both',
		),
		'same_address' =>  array(
			'name' => 'SHOW_SHIPPING_SAME_ADDRESS_CHECKBOX',
			'type' => 'boolean',
			'default' => 1,
			'showon' => array(
				'key' => 'read_only',
				'values' => array(0)
			)
		),
		'same_address_pre_checked' =>  array(
			'name' => 'SAME_ADDRESS_CHECKBOX_PRE_CHECKED',
			'type' => 'boolean',
			'default' => 1,
			'showon' => array(
				'key' => 'same_address',
				'values' => array(1)
			)
		),
		'multi_address' =>  array(
			'name' => 'ALLOW_MULTIPLE_ADDRESSES',
			'type' => 'radio',
			'default' => 2,
			'showon' => array(
				'key' => 'read_only',
				'values' => array(0)
			)
		),
	);

	public function getParams() {
		$config = hikashop_config();
		$values = array(
			JHTML::_('select.option', 1, JText::_('HIKASHOP_CHECKOUT_ADDRESS_SELECTOR_LIST')),
			JHTML::_('select.option', 2, JText::_('HIKASHOP_CHECKOUT_ADDRESS_SELECTOR_DROPDOWN'))
		);
		$selector = $config->get('checkout_address_selector',0);
		if($config->get('checkout_legacy', 0))
			$values[] = JHTML::_('select.option', 0, JText::_('HIKASHOP_CHECKOUT_ADDRESS_SELECTOR_POPUP'));
		$this->params['address_selector']['values'] = $values;

		$this->params['type']['values'] = array(
			JHTML::_('select.option', 'billing', JText::_('HIKASHOP_BILLING_ADDRESS')),
			JHTML::_('select.option', 'shipping', JText::_('HIKASHOP_SHIPPING_ADDRESS')),
			JHTML::_('select.option', 'both', JText::_('WIZARD_BOTH'))
		);
		$this->params['multi_address']['values'] = array(
			JHTML::_('select.option', 0, JText::_('HIKASHOP_NO')),
			JHTML::_('select.option', 2, JText::_('FOR_LOGGED_IN_USERS_ONLY')),
			JHTML::_('select.option', 1, JText::_('HIKA_ALWAYS')),
		);
		return parent::getParams();
	}

	public function check(&$controller, &$params) {
		if(!empty($params['read_only']))
			return true;

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();

		$type = !empty($params['type']) ? $params['type'] : 'both';

		$billing_address_missing = false;
		$shipping_address_missing = false;
		if(empty($cart->cart_billing_address_id) && in_array($type, array('billing', 'both'))) {
			$billing_address_missing = true;
		}

		if(empty($cart->cart_shipping_address_ids) && in_array($type, array('shipping', 'both')) && $checkoutHelper->isShipping()) {

			$shippingAddress_override = $checkoutHelper->getShippingAddressOverride();
			if(empty($shippingAddress_override))
				$shipping_address_missing = true;
		}

		if($billing_address_missing || $shipping_address_missing) {
			$addresses = $checkoutHelper->getAddresses();
			$billing_addresses = false;
			$shipping_addresses = false;
			foreach($addresses['data'] as $address) {
				if(empty($address->address_type) || $address->address_type == 'both') {
					$billing_addresses = true;
					$shipping_addresses = true;
					break;
				}
				if($address->address_type == 'billing')
					$billing_addresses = true;
				elseif($address->address_type == 'shipping')
					$shipping_addresses = true;
			}
		};

		if($billing_address_missing || $shipping_address_missing) {
			$addresses = $checkoutHelper->getAddresses();

			if($billing_address_missing && empty($addresses['billing_fields'])) {
				$billing_address_missing = false;
			}
			if($shipping_address_missing && empty($addresses['shipping_fields'])) {
				$shipping_address_missing = false;
			}
		}

		$app = JFactory::getApplication();
		if($billing_address_missing) {
			if($billing_addresses)
				$app->enqueueMessage(JText::_('SELECT_BILLING_ADDRESS'), 'error');
			else
				$app->enqueueMessage(JText::_('ENTER_BILLING_ADDRESS'), 'notice');
		}
		if($shipping_address_missing) {
			if($shipping_addresses)
				$app->enqueueMessage(JText::_('SELECT_SHIPPING_ADDRESS'), 'error');
			else
				$app->enqueueMessage(JText::_('ENTER_SHIPPING_ADDRESS'), 'notice');
		}
		return !($billing_address_missing || $shipping_address_missing);
	}

	public function validate(&$controller, &$params, $data = array()) {
		$data = hikaInput::get()->get('data', array(), 'array');
		if(!empty($data) && !empty($data['address_' . (int)$params['src']['step'] . '_' . (int)$params['src']['pos']])) {
			$address_data = $data['address_' . (int)$params['src']['step'] . '_' . (int)$params['src']['pos']];
			$new_address_type = @$data['address_type_' . (int)$params['src']['step'] . '_' . (int)$params['src']['pos']];

			$both_types = null;
			if(!empty($new_address_type) && !empty($data['address_selecttype_' . (int)$params['src']['step'] . '_' . (int)$params['src']['pos']]))
				$both_types = (int)@$data['address_bothtypes_' . (int)$params['src']['step'] . '_' . (int)$params['src']['pos']];

			return $this->saveAddress($controller, $params, $address_data, $new_address_type, $both_types);
		}

		$checkout = hikaInput::get()->get('checkout', array(), 'array');
		if(!empty($checkout) && !empty($checkout['address'])) {
			if((!empty($checkout['address']['billing']) && $checkout['address']['billing'] > 0) || (!empty($checkout['address']['shipping']) && $checkout['address']['shipping'] > 0))
				return $this->setCartAddresses($checkout['address']);
			if(!empty($checkout['address']['delete']))
				return $this->deleteAddresses($checkout['address']['delete']);
		}

		return true;
	}

	private function saveAddress(&$controller, &$params, $address_data, $new_address_type = '', $both_types = null) {
		$addressClass = hikashop_get('class.address');

		$old_address = new stdClass();
		if(!empty($address_data['address_id'])) {
			$old_address = $addressClass->get((int)$address_data['address_id']);
			if(empty($old_address) || empty($old_address->address_published))
				return false;
		}

		$fieldClass = hikashop_get('class.field');
		$type = '';
		if(!empty($old_address->address_type))
			$type = $old_address->address_type;
		elseif(empty($address_data['address_id']) && !empty($new_address_type))
			$type = $new_address_type;
		$field_type = $type;
		if(!empty($field_type))
			$field_type .= '_';
		$field_type .= 'address';

		$formdata = array($field_type => &$address_data);

		$app = JFactory::getApplication();
		$old_messages = $app->getMessageQueue();


		$null = null;
		$address = $fieldClass->getFilteredInput($field_type, $null, 'ret', $formdata, false, 'frontcomp');

		$checkoutHelper = hikashopCheckoutHelper::get();
		$ret = true;
		if(empty($address)) {
			$error_messages = $fieldClass->messages;
			foreach($error_messages as $i => $err) {
				$checkoutHelper->addMessage('address.error_'.$i, array($err, 'error'));
			}
			$ret = false;
		}

		if($ret) {
			if(isset($formdata[$field_type]) && !empty($formdata[$field_type]['address_id']))
				$address->address_id = (int)$formdata[$field_type]['address_id'];

			$address->address_published = 1;
			if(isset($formdata[$field_type]) && !empty($formdata[$field_type]['address_default']))
				$address->address_default = (int)$formdata[$field_type]['address_default'];
			elseif(!empty($old_address) && !empty($old_address->address_default))
				$address->address_default = 1;
			$checkoutHelper = hikashopCheckoutHelper::get();
			$cart = $checkoutHelper->getCart();
			$address->address_user_id = $cart->user_id;

			$billing_ret = $shipping_ret = 0;
			if(!empty($new_address_type) && $both_types !== null) {
				if($both_types) {
					$shipping_address = hikashop_copy($address);
					$billing_address = hikashop_copy($shipping_address);

					$billing_address->address_type = 'billing';
					$billing_ret = $addressClass->save($billing_address, 0, 'billing');

					$shipping_address->address_type = 'shipping';
					$shipping_ret = $addressClass->save($shipping_address, 0, 'shipping');
				} else {
					$address->address_type = $new_address_type;
				}
			}

			if($both_types === null || !$both_types)
				$ret = $addressClass->save($address);
		}

		if(!$ret) {

			if(!empty($addressClass->message))
				$checkoutHelper->addMessage('address.error', array('msg' => $addressClass->message, 'type' => 'error'));

			$new_messages = $app->getMessageQueue();
			if(count($old_messages) < count($new_messages)) {
				$new_messages = array_slice($new_messages, count($old_messages));
				foreach($new_messages as $i => $msg) {
					$checkoutHelper->addMessage('address.joomla_error_' . $i, array(
						'msg' => $msg['message'],
						'type' => $msg['type']
					));
				}
			}

			$new_address_data = $_SESSION['hikashop_'.$type.'_address_data'];
			$_SESSION['hikashop_'.$type.'_address_data'] = null;
			unset($_SESSION['hikashop_'.$type.'_address_data']);

			$step = $params['src']['workflow_step'];
			$block_pos = $params['src']['pos'];
			$content =& $checkoutHelper->checkout_workflow['steps'][$step]['content'][$block_pos];

			if(empty($content['params']))
				$content['params'] = array();
			$content['params']['edit_address'] = empty($address_data['address_id']) ? true : (int)$address_data['address_id'];

			if(empty($content['params']['err']))
				$content['params']['err'] = array();
			$content['params']['err']['addr'] = $new_address_data;

			$content['params']['new_address_type'] = $new_address_type;

			return false;
		}

		$cartClass = hikashop_get('class.cart');
		$cart = $checkoutHelper->getCart();

		if(!empty($ret) && empty($old_address->address_id)) {
			$addresses = $checkoutHelper->getAddresses();
			if((!empty($new_address_type) && in_array($new_address_type, array('billing', 'shipping'))) || (!empty($addresses) && count($addresses['data']) == 1)) {

				if(!empty($addresses) && count($addresses['data']) == 1)
					$type = null;
				else
					$type = $new_address_type;

				if(isset($billing_ret) && (int)$billing_ret > 0 && isset($shipping_ret) && (int)$shipping_ret > 0 && $both_types !== null && $both_types){
					$cartClass->updateAddress($cart->cart_id, 'billing', $billing_ret);
					$cartClass->updateAddress($cart->cart_id, 'shipping', $shipping_ret);
				}else {
					$cartClass->updateAddress($cart->cart_id, $type, $ret);
				}
			}
		}

		if(!empty($ret)) {
			$cartClass->get('reset_cache',$cart->cart_id);
			$checkoutHelper->getCart(true);
		}

		if(!empty($ret))
			return true;

		if(empty($content['params']['err']))
			$content['params']['err'] = array();
		$content['params']['err']['addr'] = $formdata[$field_type];
		$content['params']['new_address_type'] = $new_address_type;

		$error_message = 'error';
		$checkoutHelper->addMessage('address.error', array(
			$error_message,
			'error'
		));
		return false;
	}

	private function setCartAddresses($data) {
		$billing = (!empty($data['billing']) && $data['billing'] > 0) ? (int)$data['billing'] : 0;
		$shipping = (!empty($data['shipping']) && $data['shipping'] > 0) ? (int)$data['shipping'] : 0;

		if(empty($billing) && empty($shipping))
			return true;

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();
		$cartClass = hikashop_get('class.cart');
		$addressClass = hikashop_get('class.address');

		$app = JFactory::getApplication();
		$old_messages = $app->getMessageQueue();

		$ret_billing = true;
		if(!empty($billing)) {
			$addr = $addressClass->get($billing);
			if(!$addressClass->isAddressValid($addr)) {
				$checkoutHelper->addMessage('address.info_missing', array(
					'msg' => JText::_('THE_BILLING_ADDRESS_YOU_SELECTED_CANNOT_BE_USED_AS_SOME_INFORMATION_IS_MISSING'),
					'type' => 'error'
				));
				return false;
			}
			$ret_billing = $cartClass->updateAddress($cart->cart_id, 'billing', $billing);
		}

		$ret_shipping = true;
		if(!empty($shipping)) {
			$addr = $addressClass->get($shipping);
			if(!$addressClass->isAddressValid($addr)) {
				$checkoutHelper->addMessage('address.info_missing', array(
					'msg' => JText::_('THE_SHIPPING_ADDRESS_YOU_SELECTED_CANNOT_BE_USED_AS_SOME_INFORMATION_IS_MISSING'),
					'type' => 'error'
				));
				return false;
			}
			$ret_shipping = $cartClass->updateAddress($cart->cart_id, 'shipping', $shipping);
		}

		if($ret_shipping && $ret_billing) {
			return true;
		}
		$new_messages = $app->getMessageQueue(true);

		if(count($old_messages) < count($new_messages)) {
			$new_messages = array_slice($new_messages, count($old_messages));
			foreach($new_messages as $i => $msg) {
				$checkoutHelper->addMessage('address.joomla_error_' . $i, array(
					'msg' => $msg['message'],
					'type' => $msg['type']
				));
			}
		}

		return false;
	}

	private function deleteAddresses($address_id) {
		if(empty($address_id))
			return true;

		$address_id = (int)$address_id;

		$addressClass = hikashop_get('class.address');
		$ret = $addressClass->delete($address_id);
		if(!$ret)
			return false;

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();

		if((int)$cart->cart_billing_address_id != $address_id && (int)$cart->cart_shipping_address_ids != $address_id)
			return true;

		$addressClass->loadUserAddresses('reset_cache');

		if((int)$cart->cart_billing_address_id == $address_id)
			$cart->cart_billing_address_id = 0;

		if((int)$cart->cart_shipping_address_ids == $address_id)
			$cart->cart_shipping_address_ids = 0;
		$cartClass = hikashop_get('class.cart');
		$cartClass->save($cart);

		return true;
	}

	public function display(&$view, &$params) {
		$checkoutHelper = hikashopCheckoutHelper::get();

		$jversion = preg_replace('#[^0-9\.]#i','', JVERSION);
		if(version_compare($jversion, '3.4.0', '>='))
			JHTML::_('behavior.formvalidator');
		else
			JHTML::_('behavior.formvalidation');

		$params['js'] = '';

		$params['show_billing'] = true;
		$params['show_shipping'] = $checkoutHelper->isShipping();

		if(!in_array(@$params['type'], array('billing', 'both', ''))) {
			$params['show_billing'] = false;
		}
		if(!in_array(@$params['type'], array('shipping', 'both', ''))) {
			$params['show_shipping'] = false;
		}

		if(!isset($params['same_address']))
			$params['same_address'] = 1;
		if(!isset($params['same_address_pre_checked']))
			$params['same_address_pre_checked'] = 1;
		if(!isset($params['multi_address']))
			$params['multi_address'] = 2;


		$config = hikashop_config();
		$params['auto_select_addresses'] = (bool)$config->get('auto_select_addresses', 1);

		$user = JFactory::getUser();
		if($params['multi_address'] == 2)
			$params['multi_address'] = empty($user->guest);

		$params['display'] = $checkoutHelper->isLoggedUser() && ($params['show_billing'] || $params['show_shipping']);

		if(!isset($params['readonly']))
			$params['readonly'] = false;

		if(!isset($params['address_selector']))
			$params['address_selector'] = (int)$view->config->get('checkout_address_selector', 0);
		if(empty($params['address_selector']))
			$params['address_selector'] = 1;

		$params['display_cancel'] = true;

		if(empty($params['read_only']) && $params['display'] == true) {
			$addresses = $checkoutHelper->getAddresses();

			if(empty($addresses) || empty($addresses['data']))
				$params['edit_address'] = true;

			if($params['show_billing'] && empty($addresses['billing_fields'])) {
				$params['show_billing'] = false;
			}
			if($params['show_shipping'] && empty($addresses['shipping_fields'])) {
				$params['show_shipping'] = false;
			}
			if(!$params['show_billing'] && !$params['show_shipping'])
				$params['display'] = false;

			$checkout = hikaInput::get()->get('checkout', array(), 'array');
			$address_id = 0;
			if(!empty($checkout['address']['edit'])) {
				$address_id = (int)$checkout['address']['edit'];
				if(isset($addresses['data'][ $address_id ]))
					$params['edit_address'] = $address_id;
			} elseif(isset($checkout['address']['billing']) && $checkout['address']['billing'] == 0) {
				$checkout['address']['new'] = 'billing';
			} elseif(isset($checkout['address']['shipping']) && $checkout['address']['shipping'] == 0) {
				$checkout['address']['new'] = 'shipping';
			}else{
				$billing_addresses = false;
				$shipping_addresses = false;
				foreach($addresses['data'] as $address) {
					if(empty($address->address_type) || $address->address_type == 'both') {
						$billing_addresses = true;
						$shipping_addresses = true;
						break;
					}
					if($address->address_type == 'billing')
						$billing_addresses = true;
					elseif($address->address_type == 'shipping')
						$shipping_addresses = true;
				}
				if($params['show_billing'] && $params['show_shipping'] && !$billing_addresses && !$shipping_addresses ) {
					$checkout['address']['new'] = 'billing';
					$params['display_cancel'] = false;
				} elseif($params['show_billing'] && !$billing_addresses) {
					$checkout['address']['new'] = 'billing';
					$params['display_cancel'] = false;
				} elseif($params['show_shipping'] && !$shipping_addresses) {
					$checkout['address']['new'] = 'shipping';
					$params['display_cancel'] = false;
				}
			}

			if(!empty($checkout['address']['new'])) {
				$params['edit_address'] = true;
				$params['new_address_type'] = $checkout['address']['new'];
			}
			if(isset($params['new_address_type']) && !in_array($params['new_address_type'], array('billing', 'shipping')))
				unset($params['new_address_type']);

			if(!empty($params['edit_address'])) {
				if(empty($view->fieldClass))
					$view->fieldClass = hikashop_get('class.field');
				$view->edit_address = new stdClass();
				if(!empty($params['err']['addr'])) {
					$view->edit_address = $params['err']['addr'];
					unset($params['err']['addr']);
				} elseif((int)$params['edit_address'] > 0 && isset($addresses['data'][ (int)$params['edit_address'] ])) {
					$addressClass = hikashop_get('class.address');
					$view->edit_address = $addressClass->get($params['edit_address']);
				}
				$fields_type = 'fields';
				if(!empty($view->edit_address->address_type) && in_array($view->edit_address->address_type, array('billing', 'shipping')))
					$fields_type = $view->edit_address->address_type . '_fields';

				$view->fieldClass->prepareFields($addresses[$fields_type], $view->edit_address, 'address', 'checkout&task=state');
				$params['js'] .= $view->fieldClass->jsToggle($addresses[$fields_type], $view->edit_address, 0, 'hikashop_checkout_', array('return_data' => true, 'suffix_type' => '_'.$view->step.'_'.$view->block_position));
			}
		}
	}
}
