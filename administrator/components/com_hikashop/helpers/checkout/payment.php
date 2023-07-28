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

class hikashopCheckoutPaymentHelper extends hikashopCheckoutHelperInterface {
	protected $params = array(
		'read_only' =>  array(
			'name' => 'READ_ONLY',
			'type' => 'boolean',
			'default' => 0
		),
		'show_title' =>  array(
			'name' => 'SHOW_TITLE',
			'type' => 'boolean',
			'default' => 1
		),
		'payment_selector' => array(
			'name' => 'HIKASHOP_CHECKOUT_DISPLAY_SELECTOR',
			'type' => 'radio',
			'tooltip' => 'checkout_payment_selector',
			'default' => 1,
			'showon' => array(
				'key' => 'read_only',
				'values' => array(0)
			)
		),
		'price_with_tax' => array(
			'name' => 'PRICE_WITH_TAX',
			'type' => 'inherit',
			'default' => -1,
		),
	);

	public function getParams() {
		$config = hikashop_config();
		$values = array(
			JHTML::_('select.option', 1, JText::_('HIKASHOP_CHECKOUT_ADDRESS_SELECTOR_LIST')),
			JHTML::_('select.option', 2, JText::_('HIKASHOP_CHECKOUT_ADDRESS_SELECTOR_DROPDOWN'))
		);
		$this->params['payment_selector']['values'] = $values;

		$this->params['price_with_tax']['values'] = array(
			JHTML::_('select.option', '2', JText::_('WIZARD_BOTH'))
		);

		return parent::getParams();
	}

	public function check(&$controller, &$params) {
		if(!empty($params['read_only']))
			return true;

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();

		if((empty($cart->full_total->prices[0]) || $cart->full_total->prices[0]->price_value_with_tax == 0.0) || !empty($cart->payment)) {
			return true;
		}
		$checkoutHelper->addMessage('payment.checkfailed', array(
			JText::_('SELECT_PAYMENT'),
			'error'
		));
		return false;
	}

	public function haveEmptyContent(&$controller, &$params) {
		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();
		if((empty($cart->full_total->prices[0]) || $cart->full_total->prices[0]->price_value_with_tax == 0.0)) {
			return true;
		}
		return false;
	}

	public function validate(&$controller, &$params, $data = array()) {
		if(!empty($params['read_only']))
			return true;

		if(empty($data))
			$data = hikaInput::get()->get('checkout', array(), 'array');
		if(empty($data['payment']))
			return true;

		$payment_id = (int)$data['payment']['id'];
		if(empty($payment_id))
			return false;

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();

		$payment_price = $this->getPaymentPrice($cart);

		$selectionOnly = hikaInput::get()->getInt('selectionOnly', 0);
		if($selectionOnly) {
			$cart_markers = $checkoutHelper->getCartMarkers();
		}

		$payment_change = ((int)$cart->cart_payment_id != $payment_id);

		$cartClass = hikashop_get('class.cart');
		$ret = $cartClass->updatePayment($cart->cart_id, $payment_id);

		$new_payment_price = $payment_price;
		if($payment_change) {
			$cart = $checkoutHelper->getCart(true);
			$new_payment_price = $this->getPaymentPrice($cart);
		}

		if($ret && !empty($data['payment']['card'][$payment_id])) {
			$app = JFactory::getApplication();
			$checkout_cc = $app->getUserState(HIKASHOP_COMPONENT.'.checkout_cc', null);
			if(is_string($checkout_cc))
				$checkout_cc = json_decode(base64_decode($checkout_cc));
			if(is_object($checkout_cc))
				$checkout_cc = get_object_vars($checkout_cc);
			if(empty($checkout_cc))
				$checkout_cc = array();

			if(is_array($data['payment']['card'][$payment_id])) {
				$checkout_cc[$payment_id] = array();
				$fields = array('num', 'mm', 'yy', 'ccv', 'type', 'owner');
				foreach($fields as $field) {
					if(isset($data['payment']['card'][$payment_id][$field]) && is_string($data['payment']['card'][$payment_id][$field]))
						$checkout_cc[$payment_id][$field] = trim($data['payment']['card'][$payment_id][$field]);
				}

				$cards = $this->checkCreditCard($checkout_cc[$payment_id]['num']);
				if($cards === false) {
					$ret = false;
					$checkoutHelper->addMessage('payment', array(JText::_('CREDIT_CARD_INVALID'), 'error'));
				}

				if(isset($checkout_cc[$payment_id]['mm']) && isset($checkout_cc[$payment_id]['yy'])) {
				}

				$plugin = hikashop_import('hikashoppayment', $cart->payment->payment_type);
				if(method_exists($plugin, 'needCC')) {
					$plugin->needCC($cart->payment);
				} else if(!empty($plugin->ask_cc)) {
					if(!empty($cart->payment->payment_params->ask_ccv))
						$cart->payment->ask_ccv = true;
				}
				if(!empty($cart->payment->ask_ccv) && empty($checkout_cc[$payment_id]['ccv'])){
					$ret = false;
					$checkoutHelper->addMessage('payment', array(JText::_('CCV_MISSING'), 'error'));
				}

			} else if(is_string($data['payment']['card'][$payment_id]) && $data['payment']['card'][$payment_id] === 'reset')
				unset($checkout_cc[$payment_id]);

			if($ret)
				$app->setUserState(HIKASHOP_COMPONENT.'.checkout_cc', base64_encode(json_encode($checkout_cc)));
		}

		if($ret && !empty($data['payment']['custom'][$payment_id])) {
			$app = JFactory::getApplication();
			$checkout_custom = $app->getUserState(HIKASHOP_COMPONENT.'.checkout_custom', null);
			if(is_string($checkout_custom))
				$checkout_custom = json_decode(base64_decode($checkout_custom), true);
			if(empty($checkout_custom))
				$checkout_custom = array();
			if(!isset($checkout_custom[ $payment_id ]))
				$checkout_custom[$payment_id] = array();

			$plugin = hikashop_import('hikashoppayment', $cart->payment->payment_type);
			$checkout_custom[$payment_id] = $plugin->onPaymentCustomSave($cart, $cart->payment, $data['payment']['custom'][$payment_id]);

			$app->setUserState(HIKASHOP_COMPONENT.'.checkout_custom', base64_encode(json_encode($checkout_custom)));
		}

		if($ret && !empty($cart->payment->custom_html)) {
			$plugin = hikashop_import('hikashoppayment', $cart->payment->payment_type);

			$submitstep = !empty($params['src']['context']) && $params['src']['context'] == 'submitstep';

			if(!$submitstep) {
				$app = JFactory::getApplication();
				$old_messages = $app->getMessageQueue();
			}

			$paymentData = $plugin->onPaymentSave($cart, $cart->usable_methods->payment, $cart->payment->payment_id);

			if($paymentData !== false) {
				$cartClass->updatePaymentCustom($cart->cart_id, $cart->payment->payment_id, $paymentData->custom_html);
			} else {
				$ret = false;
				if(!$submitstep) {
					$new_messages = $app->getMessageQueue();
					if(count($old_messages) < count($new_messages)) {
						$new_messages = array_slice($new_messages, count($old_messages));
						foreach($new_messages as $i => $msg) {
							$checkoutHelper->addMessage('payment.error_'.$i, array($msg['message'], $msg['type']));
						}
					}
				}
			}
		}

		$tmpl = hikaInput::get()->getCmd('tmpl', '');
		if(hikaInput::get()->getInt('selectionOnly', 0) && in_array($tmpl, array('ajax', 'raw', 'component'))) {
			$data = array(
				'ret' => $ret,
				'events' => array(),
			);

			if($ret && $payment_change) {
				$data['events'][] = 'checkout.payment.updated';

				$checkoutHelper->generateBlockEvents($cart_markers);
				$events = $checkoutHelper->getEvents();
				foreach($events as $evtName => $params) {
					$data['events'][] = $evtName;
				}
			}

			if($new_payment_price != $payment_price)
				$data['events'][] = 'checkout.cart.updated';

			ob_end_clean();
			echo json_encode($data);
			exit;
		}

		if($ret && $payment_change) {
			$eventParams = null;
			if(!empty($params['src']))
				$eventParams = array('src' => $params['src']);
			$checkoutHelper->addEvent('checkout.payment.updated', $eventParams);
		}

		return $ret;
	}

	public function display(&$view, &$params) {
		if(!isset($params['read_only']))
			$params['read_only'] = false;
		if(!isset($params['show_title']))
			$params['show_title'] = true;
		if(!isset($params['payment_selector']))
			$params['payment_selector'] = 0;
		if($params['read_only'])
			$params['payment_selector'] = 0;

		if(!isset($params['price_with_tax']))
			$params['price_with_tax'] = -1;
		if($params['price_with_tax'] == -1) {
			$config = hikashop_config();
			$params['price_with_tax'] = $config->get('price_with_tax', 0);
		}

		$checkoutHelper = hikashopCheckoutHelper::get();
		$cart = $checkoutHelper->getCart();

		if(empty($cart->full_total->prices[0]) || $cart->full_total->prices[0]->price_value_with_tax == 0.0)
			return;

		if(empty($cart->usable_methods->payment)) {
			$checkoutHelper->addMessage('payment.error_no_method', array(
				JText::_('NO_PAYMENT_METHODS_FOUND'),
				'error'
			));
		}
	}

	protected function checkCreditCard($cardnum) {
		$cardnum = trim($cardnum);

		if(!preg_match('/^[0-9]{8,19}$/', $cardnum))
			return false;

		$cards = array(
			0 => array('name' => 'Visa', 'length' => array(13,16,19), 'prefixes' => '4', 'checkdigit' => true),
			1 => array('name' => 'MasterCard', 'length' => array(16), 'prefixes' => '51,52,53,54,55,2221,2222,2223,2224,2225,2226,2227,2228,2229,223,224,225,226,227,228,229,23,24,25,26,271,2720', 'checkdigit' => true),
			2 => array('name' => 'DinersClub', 'length' => array(14,16), 'prefixes' => '305,36,38,54,55', 'checkdigit' => true),
			3 => array('name' => 'CarteBlanche','length' => array(14),'prefixes' => '300,301,302,303,304,305', 'checkdigit' => true),
			4 => array('name' => 'AmEx', 'length' => array(15), 'prefixes' => '34,37','checkdigit' => true),
			5 => array('name' => 'Discover', 'length' => array(16), 'prefixes' => '6011,622,64,65', 'checkdigit' => true),
			6 => array('name' => 'JCB', 'length' => array(16,19), 'prefixes' => '35', 'checkdigit' => true),
			7 => array('name' => 'enRoute', 'length' => array(15), 'prefixes' => '2014,2149', 'checkdigit' => false),
			8 => array('name' => 'Solo', 'length' => array(16,18,19), 'prefixes' => '6334,6767', 'checkdigit' => true),
			9 => array('name' => 'Switch', 'length' => array(16,18,19), 'prefixes' => '4903,4905,4911,4936,564182,633110,6333,6759', 'checkdigit' => true),
			10 => array('name' => 'Maestro', 'length' => array(12,13,14,15,16,18,19), 'prefixes' => '50,56,57,58,59,60,61,62,63,64,65,66,67,68,69', 'checkdigit' => true),
			11 => array('name' => 'UATP', 'length' => array(15), 'prefixes' => '1', 'checkdigit' => true),
			12 => array('name' => 'LaserCard', 'length' => array(16,17,18,19), 'prefixes' => '6304,6706,6771,6709', 'checkdigit' => true),
			13 => array('name' => 'UnionPay', 'length' => array(16,17,18,19), 'prefixes' => '62', 'checkdigit' => true),
			14 => array('name' => 'Isracard', 'length' => array(8), 'prefixes' => '0,1,2,3,4,5,6,7,8,9', 'checkdigit' => false),
			15 => array('name' => 'Direct', 'length' => array(9), 'prefixes' => '0,1,2,3,4,5,6,7,8,9', 'checkdigit' => false),
			16 => array('name' => 'Bankcard', 'length' => array(16), 'prefixes' => '62', 'checkdigit' => true),
			17 => array('name' => 'China UnionPay', 'length' => array(16,17,18,19), 'prefixes' => '62', 'checkdigit' => true),
			18 => array('name' => 'InterPayment', 'length' => array(16,17,18,19), 'prefixes' => '636', 'checkdigit' => true),
			19 => array('name' => 'InstaPayment', 'length' => array(16), 'prefixes' => '637,638,639', 'checkdigit' => true),
			20 => array('name' => 'Laser', 'length' => array(16,17,18,19), 'prefixes' => '6304,6706,6771,6709', 'checkdigit' => true),
			21 => array('name' => 'Dankort', 'length' => array(16), 'prefixes' => '5019', 'checkdigit' => true),
			22 => array('name' => 'NSPK MIR', 'length' => array(16), 'prefixes' => '2200,2201,2202,2203', 'checkdigit' => true),
			23 => array('name' => 'Verve', 'length' => array(16,19), 'prefixes' => '506,6500', 'checkdigit' => true),
			24 => array('name' => 'CARDGUARD EAD BG ILS', 'length' => array(16), 'prefixes' => '5392', 'checkdigit' => true),
		);

		$valid = array();
		$checksum = false;

		$cardlen = strlen($cardnum);
		foreach($cards as $card) {
			if(!in_array($cardlen, $card['length']))
				continue;

			if($card['prefixes'] == null) {
				$valid[] = $card;
				$checksum = ($checksum || $card['checkdigit']);
				continue;
			}

			$prefixes = explode(',', $card['prefixes']);
			foreach($prefixes as $prefix) {
				if(substr($cardnum, 0, strlen($prefix)) == $prefix) {
					$valid[] = $card;
					$checksum = ($checksum || $card['checkdigit']);
					break;
				}
			}
		}

		$card_checksum = false;
		if($checksum) {
			$card_checksum = 0;
			$j = 1;
			for($i = strlen($cardnum) - 1; $i >= 0; $i--) {
				$calc = (int)substr($cardnum, $i, 1) * $j;

				if($calc > 9) {
					$card_checksum++;
					$calc -= 10;
				}

				$card_checksum += $calc;

				$j = ($j == 1) ? 2 : 1;
			}

			$card_checksum = ($card_checksum % 10 == 0);
		}

		if(!$card_checksum) {
			foreach($valid as $k => $v) {
				if($v['checkdigit'])
					unset($valid[$k]);
			}
		}

		if(empty($valid))
			return false;
		return $valid;
	}

	protected function getPaymentPrice(&$cart) {
		if(empty($cart->payment))
			return 0.0;
		return (float)hikashop_toFloat($cart->payment->payment_price);
	}
}
