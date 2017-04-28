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
include_once HIKASHOP_HELPER . 'checkout.php';

class hikashopCheckoutAddressHelper extends hikashopCheckoutHelperInterface {
	public function check(&$controller, &$params) {
		if(!empty($params['read_only']))
			return true;

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();

		$address_missing = false;
		if(empty($cart->cart_billing_address_id)){
			$address_missing = true;
		}
		if(empty($cart->cart_shipping_address_ids)){
			$address_missing = true;
		}

		if($address_missing){
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('CREATE_OR_SELECT_ADDRESS'),'error');
	}
		return !$address_missing;
	}

	public function validate(&$controller, &$params, $data = array()) {
		$data = JRequest::getVar('data', array(), '', 'array');
		if(!empty($data) && !empty($data['address_' . (int)$params['src']['step'] . '_' . (int)$params['src']['pos']])) {
			$address_data = $data['address_' . (int)$params['src']['step'] . '_' . (int)$params['src']['pos']];
			$new_address_type = @$data['address_type_' . (int)$params['src']['step'] . '_' . (int)$params['src']['pos']];
			return $this->saveAddress($controller, $params, $address_data, $new_address_type);
		}

		$checkout = JRequest::getVar('checkout', array(), '', 'array');
		if(!empty($checkout) && !empty($checkout['address'])) {
			if(!empty($checkout['address']['billing']) || !empty($checkout['address']['shipping']))
				return $this->setCartAddresses($checkout['address']);
			if(!empty($checkout['address']['delete']))
				return $this->deleteAddresses($checkout['address']['delete']);
		}

		return true;
	}

	private function saveAddress(&$controller, &$params, $address_data, $new_address_type = '') {
		$addressClass = hikashop_get('class.address');

		$old_address = new stdClass();
		if(!empty($address_data['address_id'])) {
			$old_address = $addressClass->get((int)$address_data['address_id']);
			if(empty($old_address) || empty($old_address->address_published))
				return false;
		}

		$fieldClass = hikashop_get('class.field');
		$type = 'address';
		$formdata = array('address' => &$address_data);
		$null = null;
		$address = $fieldClass->getInput($type, $null, 'ret', $formdata, false, 'frontcomp');

		$checkoutHelper = hikashopCheckoutHelper::get();
		$ret = true;
		if(empty($address)) {
			$error_messages = $fieldClass->messages;
			foreach($error_messages as $i => $err) {
				$checkoutHelper->addMessage('address.error_'.$i, $err);
			}
			$ret = false;
		}

		if($ret) {
			$address->address_published = 1;
			if(!empty($old_address) && !empty($old_address->address_default))
				$address->address_default = 1;
			$address->address_user_id = hikashop_loadUser(false);

			$ret = $addressClass->save($address);
		}

		if(!$ret) {

			if(!empty($addressClass->message))
				$checkoutHelper->addMessage('address.error', array('msg' => $addressClass->message, 'type' => 'error'));

			$new_address_data = $_SESSION['hikashop_'.$type.'_data'];
			$_SESSION['hikashop_'.$type.'_data'] = null;
			unset($_SESSION['hikashop_'.$type.'_data']);

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

		$cart_refreshed = false;
		$cartClass = hikashop_get('class.cart');
		$cart = $checkoutHelper->getCart();

		if(!empty($ret) && empty($old_address->address_id)) {
			$addresses = $checkoutHelper->getAddresses();
			if((!empty($new_address_type) && in_array($new_address_type, array('billing', 'shipping'))) || (!empty($addresses) && count($addresses['data']) == 1)) {

				if(!empty($addresses) && count($addresses['data']) == 1)
					$type = null;
				else
					$type = $new_address_type;

				$cartClass->updateAddress($cart->cart_id, $type, $ret);

				$checkoutHelper->getCart(true);
				$cart_refreshed = true;
			}
		}

		if(!empty($ret) && !$cart_refreshed) {
			$cartClass->get('reset_cache',$cart->cart_id);
			$checkoutHelper->getCart(true);
		}


		if(!empty($ret))
			return true;

		if(empty($content['params']['err']))
			$content['params']['err'] = array();
		$content['params']['err']['addr'] = $address;
		$content['params']['new_address_type'] = $new_address_type;

		$error_message = 'error';
		$checkoutHelper->addMessage('address.error', array(
			$error_message,
			'error'
		));
		return false;
	}

	private function setCartAddresses($data) {
		$billing = (!empty($data['billing'])) ? (int)$data['billing'] : 0;
		$shipping = (!empty($data['shipping'])) ? (int)$data['shipping'] : 0;

		if(empty($billing) && empty($shipping))
			return true;

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();
		$cartClass = hikashop_get('class.cart');

		$ret_billing = true;
		if(!empty($billing)) {
			$ret_billing = $cartClass->updateAddress($cart->cart_id, 'billing', $billing);
		}

		$ret_shipping = true;
		if(!empty($shipping)) {
			$ret_shipping = $cartClass->updateAddress($cart->cart_id, 'shipping', $shipping);
		}

		if($ret_shipping && $ret_billing)
			return true;
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

		return true;
	}

	public function display(&$view, &$params) {
		$checkoutHelper = hikashopCheckoutHelper::get();

		$params['js'] = '';

		$params['show_billing'] = true;
		$params['show_shipping'] = $checkoutHelper->isShipping();


		$params['display'] = $checkoutHelper->isLoggedUser() && ($params['show_billing'] || $params['show_shipping']);

		$params['address_selector'] = (int)$view->config->get('checkout_address_selector', 0);

		if(empty($params['read_only']) && $params['display'] == true) {
			$addresses = $checkoutHelper->getAddresses();

			if(empty($addresses) || empty($addresses['data']))
				$params['edit_address'] = true;

			$checkout = JRequest::getVar('checkout', array(), '', 'array');
			$address_id = 0;
			if(!empty($checkout['address']['edit'])) {
				$address_id = (int)$checkout['address']['edit'];
				if(isset($addresses['data'][ $address_id ]))
					$params['edit_address'] = $address_id;
			} elseif(isset($checkout['address']['billing']) && $checkout['address']['billing'] == 0) {
				$checkout['address']['new'] = 'billing';
			} elseif(isset($checkout['address']['shipping']) && $checkout['address']['shipping'] == 0) {
				$checkout['address']['new'] = 'shipping';
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
				$view->fieldClass->prepareFields($addresses['fields'], $view->edit_address, 'address', 'checkout&task=state');
				$params['js'] .= $view->fieldClass->jsToggle($addresses['fields'], @$addresses['data'][ $address_id ], 0, 'hikashop_checkout_', array('return_data' => true, 'suffix_type' => '_'.$view->step.'_'.$view->block_position));
			}
		}
	}
}
