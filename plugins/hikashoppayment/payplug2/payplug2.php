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
class plgHikashoppaymentPayplug2 extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'EUR'
	);

	var $multiple = true;
	var $name = 'payplug2';
	var $pluginConfig = array(
		'token' => array('SECRET_KEY', 'input'),
		'debug' => array('DEBUG', 'boolean', '0'),
		'delivery_type' => array('PAYPLUG_DELIVERY_TYPE', 'list',array(
			'NEW' => 'PAYPLUG_SHIP_TO_SHIPPING_ADDRESS',
			'DIGITAL_GOODS' => 'PAYPLUG_DIGITAL_GOODS',
			'TRAVEL_OR_EVENT' => 'PAYPLUG_TRAVEL_OR_EVENT',
			'OTHER' => 'PAYPLUG_OTHER')
		),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id=' . $order->order_id . $this->url_itemid;
		$notif_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&notif_id='.$method_id.'&order_id='.$order->order_id.'&lang='.$this->locale.$this->url_itemid;

		require_once(dirname(__FILE__).'/lib/init.php');

		try{
			Payplug\Payplug::setSecretKey($this->payment_params->token);
			$delivery_type = $this->payment_params->delivery_type;

			$billing = array(
				'first_name' => substr(@$order->cart->billing_address->address_firstname,0,100),
				'last_name' => substr(@$order->cart->billing_address->address_lastname,0,100),
				'address1' => substr(@$order->cart->billing_address->address_street,0,255),
				'postcode' => substr(@$order->cart->billing_address->address_post_code,0,16),
				'city' => substr(@$order->cart->billing_address->address_city,0,100),
				'country' => @$order->cart->billing_address->address_country->zone_code_2,
				'email' => substr($this->user->user_email,0,255),
			);
			if(!empty($order->cart->billing_address->address_street2))
				$billing['address2'] = substr(@$order->cart->billing_address->address_street2,0,255);

			if(!empty($order->cart->billing_address->address_title) && in_array($order->cart->billing_address->address_title, array('Mr', 'Mrs', 'Miss')))
				$billing['title'] = $order->cart->billing_address->address_title;

			if(!empty($order->cart->shipping_address) && is_object($order->cart->shipping_address)) {
				$shipping = array(
					'first_name' => substr(@$order->cart->shipping_address->address_firstname,0,100),
					'last_name' => substr(@$order->cart->shipping_address->address_lastname,0,100),
					'address1' => substr(@$order->cart->shipping_address->address_street,0,255),
					'postcode' => substr(@$order->cart->shipping_address->address_post_code,0,16),
					'city' => substr(@$order->cart->shipping_address->address_city,0,100),
					'country' => @$order->cart->shipping_address->address_country->zone_code_2,
					'email' => substr($this->user->user_email,0,255),
					'delivery_type' => $delivery_type
				);
				if(!empty($order->cart->shipping_address->address_street2))
					$shipping['address2'] = substr(@$order->cart->shipping_address->address_street2,0,255);

				if(!empty($order->cart->shipping_address->address_title) && in_array($order->cart->shipping_address->address_title, array('Mr', 'Mrs', 'Miss')))
					$shipping['title'] = $order->cart->shipping_address->address_title;
			} else {
				$shipping = $billing;
				$shipping['delivery_type'] = $delivery_type;
			}

			$data = array(
				'amount' => (int)(round($order->cart->full_total->prices[0]->price_value_with_tax,2)*100),
				'currency' => 'EUR',
				'notification_url' => $notif_url,
				'hosted_payment'    => array(
					'return_url' => $return_url,
					'cancel_url' => $cancel_url,
				),
				'metadata' => array(
					'order_id' => $order->order_id,
				),
				'billing' => $billing,
				'shipping' => $shipping,
			);

			if($this->payment_params->debug) {
				hikashop_writeToLog($data);
			}

			$payment = Payplug\Payment::create($data);

			if($this->payment_params->debug) {
				hikashop_writeToLog($payment);
			}

			$paymentUrl = $payment->hosted_payment->payment_url;
		}catch(Exception $e){
			if($this->payment_params->debug) {
				hikashop_writeToLog($e);
			}
			$this->app->enqueueMessage($e->getHttpResponse(), 'error');
			return;
		}
		header("Location: $paymentUrl");
		exit;
	}

	function onPaymentNotification(&$statuses) {
		$method_id = hikaInput::get()->getInt('notif_id', 0);
		$this->pluginParams($method_id);
		$this->payment_params =& $this->plugin_params;
		if(empty($this->payment_params))
			return false;


		require_once(dirname(__FILE__).'/lib/init.php');
		Payplug\Payplug::setSecretKey($this->payment_params->token);
		$input = file_get_contents('php://input');
		if( $this->payment_params->debug ) {
			$this->writeToLog( var_export($input, true) );
		}
		try{
			$resource = \Payplug\Notification::treat($input);

			if($this->payment_params->debug) {
				hikashop_writeToLog($resource);
			}

			if ($resource instanceof \Payplug\Resource\Payment
				&& $resource->is_paid
			) {
			} else if ($resource instanceof \Payplug\Resource\Refund) {
				return;
			} else {
				return;
			}
		}catch(\Payplug\Exception\PayplugException $exception){
			$this->writeToLog($exception->getMessage());
			return;
		}

		if(empty($resource->metadata['order_id'])) {
			if( $this->payment_params->debug ) {
				$this->writeToLog('order id missing');
			}
			return false;
		}
		$order_id = (int)$resource->metadata['order_id'];

		$dbOrder = $this->getOrder($order_id);
		if(empty($dbOrder)) {
			if( $this->payment_params->debug ) {
				$this->writeToLog('order not found');
			}
			return false;
		}
		if($method_id != $dbOrder->order_payment_id) {
			if( $this->payment_params->debug ) {
				$this->writeToLog('payment method id mismatch');
			}
			return false;
		}
		$this->loadOrderData($dbOrder);

		$return_url = hikashop_completeLink('checkout&task=after_end&order_id=' . $order_id . $this->url_itemid);
		$cancel_url = hikashop_completeLink('order&task=cancel_order&order_id=' . $order_id . $this->url_itemid);


		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id.$this->url_itemid;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		$history = new stdClass();
		$history->notified = 0;
		$history->data = '';
		$email = new stdClass();

		$amount = (int)(round($dbOrder->order_full_price,2)*100);
		if( $resource->amount != $amount ) {
			$order_status = $this->payment_params->invalid_status;
			$order_text.= " Amount paid doesn't match.";
			if( $this->payment_params->debug ) {
				$this->writeToLog('Amount paid doesn\'t match ('.$amount.' in order and '.$resource->amount.' in notification)');
			}

			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','PayPlug',$order_status)).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$order_text;
		 	$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','PayPlug',$order_status,$dbOrder->order_number);

			$this->modifyOrder($order_id, $order_status, $history,$email);
			return false;
		}

		if($dbOrder->order_status == $this->payment_params->verified_status) {
			if( $this->payment_params->debug ) {
				$this->writeToLog( 'Already confirmed' );
			}
			return true;
		}

		$order_status = $this->payment_params->verified_status;
		$vars['payment_status'] = 'paid';
		$history->data .= "\n\n" . 'Transaction id: ' . $resource->id;
		$history->notified = 1;

		if( $this->payment_params->debug ) {
			$this->writeToLog($history);
		}
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','PayPlug', $vars['payment_status'], $dbOrder->order_number);
		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS', 'PayPlug', $vars['payment_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$statuses[$order_status])."\r\n\r\n".$order_text;

		$this->modifyOrder($order_id,$order_status,$history,$email);
		return true;
	}

	function onPaymentConfigurationSave(&$element) {
		$app = JFactory::getApplication();
		if(empty($element->payment_params->token)){
			$app->enqueueMessage(JText::sprintf('ENTER_INFO_REGISTER_IF_NEEDED', 'PayPlug', JText::_('HIKA_TOKEN'), 'PayPlug', 'https://www.payplug.com'), 'error');
		}
		return true;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'PayPlug';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card';

		$element->payment_params->email = '';
		$element->payment_params->password = '';
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->verified_status = 'confirmed';
	}
}
