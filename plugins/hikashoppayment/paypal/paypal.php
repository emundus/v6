<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashoppaymentPaypal extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'AUD','BRL','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD','SEK',
		'DKK','PLN','NOK','HUF','CZK','MXN','MYR','PHP','TWD','THB','ILS','TRY',
		'RUB'
	);

	var $multiple = true;
	var $name = 'paypal';
	var $doc_form = 'paypal';

	var $pluginConfig = array(
		'url' => array('URL', 'input'),
		'email' => array('HIKA_EMAIL', 'input'),
		'address_type' => array('PAYPAL_ADDRESS_TYPE', 'list', array(
			'0' => 'NO_ADDRESS',
			'billing' => 'HIKASHOP_BILLING_ADDRESS',
			'shipping' => 'HIKASHOP_SHIPPING_ADDRESS',
		)),
		'address_override' => array('ADDRESS_OVERRIDE', 'boolean', 0),
		'no_shipping' => array('NO_SHIPPING', 'radio', array(
			'2' => 'REQUIRED',
			'1' => 'HIKASHOP_YES',
			'0' => 'HIKASHOP_NO',
		)),
		'notification' => array('ALLOW_NOTIFICATIONS', 'boolean', 1),
		'details' => array('SEND_DETAILS_OF_ORDER', 'boolean', 0),
		'notes' => array('FEDEX_SHOW_NOTES', 'boolean', 0),
		'validation' => array('ENABLE_VALIDATION', 'boolean', 0),
		'debug' => array('DEBUG', 'boolean', 0),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'cpp_header_image' => array('HEADER_IMAGE', 'input'),
		'ips' => array('IPS', 'ips', ''),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
		'rm' => array('PAYPAL_RETURN_METHOD', 'boolean', 1),
	);

	var $cachedDebug = '';

	public function onBeforeOrderCreate(&$order,&$do){
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if((empty($this->payment_params->email) || empty($this->payment_params->url)) && $this->plugin_data->payment_id == $order->order_payment_id) {
			$this->app->enqueueMessage('Please check your &quot;PayPal&quot; plugin configuration');
			$do = false;
		}
	}

	public function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		if($this->currency->currency_locale['int_frac_digits'] > 2)
			$this->currency->currency_locale['int_frac_digits'] = 2;

		$notify_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&tmpl=component&lang='.$this->locale . $this->url_itemid;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id . $this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id . $this->url_itemid;

		$tax_total = '';
		$discount_total = '';
		$debug = @$this->payment_params->debug;
		if(!isset($this->payment_params->no_shipping))
			$this->payment_params->no_shipping = 1;
		if(!empty($this->payment_params->rm))
			$this->payment_params->rm = 2;

		$vars = array(
			'cmd' => '_cart',
			'upload' => '1',
			'business' => $this->payment_params->email,
			'receiver_email' => $this->payment_params->email,
			'invoice' => $order->order_id,
			'currency_code' => $this->currency->currency_code,
			'return' => $return_url,
			'notify_url' => $notify_url,
			'cancel_return' => $cancel_url,
			'undefined_quantity' => '0',
			'test_ipn' => $debug,
			'shipping' => '0',
			'no_shipping' => $this->payment_params->no_shipping,
			'no_note' => !@$this->payment_params->notes,
			'charset' => 'utf-8',
			'rm' => (int)@$this->payment_params->rm,
			'bn' => 'HikariSoftware_Cart_WPS'
		);

		if(!empty($this->payment_params->address_type)) {
			$address_type = $this->payment_params->address_type . '_address';
			$address = $this->app->getUserState(HIKASHOP_COMPONENT . '.' . $address_type);
			if(!empty($address)) {
				if(!isset($this->payment_params->address_override)) {
					$this->payment_params->address_override = '1';
				}

				$vars['address_override'] = $this->payment_params->address_override;
				$vars['first_name'] = @$order->cart->$address_type->address_firstname;
				$vars['last_name'] = @$order->cart->$address_type->address_lastname;
				$address1 = '';
				$address2 = '';

				if(!empty($order->cart->$address_type->address_street2))
					$address2 = substr($order->cart->$address_type->address_street2, 0, 99);

				if(!empty($order->cart->$address_type->address_street)) {
					if(strlen($order->cart->$address_type->address_street) > 100) {
						$address1 = substr($order->cart->$address_type->address_street, 0, 99);
						if(empty($address2))
							$address2 = substr($order->cart->$address_type->address_street, 99, 199);
					} else {
						$address1 = $order->cart->$address_type->address_street;
					}
				}

				$vars['address1'] = $address1;
				$vars['address2'] = $address2;
				$vars['zip'] = @$order->cart->$address_type->address_post_code;
				$vars['city'] = @$order->cart->$address_type->address_city;
				if((!isset($order->cart->$address_type->address_state->zone_code_3) || is_numeric($order->cart->$address_type->address_state->zone_code_3)) && !empty($order->cart->$address_type->address_country->zone_name)){
					$vars['state'] = @$order->cart->$address_type->address_state->zone_name;
				}else{
					$vars['state'] = @$order->cart->$address_type->address_state->zone_code_3;
				}
				$vars['country'] = @$order->cart->$address_type->address_country->zone_code_2;
				$vars['email'] = $this->user->user_email;
				$vars['night_phone_b'] = @$order->cart->$address_type->address_telephone;

			} elseif(!empty($order->cart->billing_address->address_country->zone_code_2)) {
				$vars['lc'] = $order->cart->billing_address->address_country->zone_code_2;
			}
		} elseif(!empty($order->cart->billing_address->address_country->zone_code_2)) {
			$vars['lc'] = $order->cart->billing_address->address_country->zone_code_2;
		}

		if(!empty($this->payment_params->cpp_header_image)) {
			$vars['cpp_header_image'] = $this->payment_params->cpp_header_image;
		}

		if(empty($this->payment_params->details)) {
			$vars['amount_1'] = number_format(round($order->cart->full_total->prices[0]->price_value_with_tax, (int)$this->currency->currency_locale['int_frac_digits']), 2, '.', '');
			$vars['item_name_1'] = JText::_('CART_PRODUCT_TOTAL_PRICE');
		} else {
			$i = 1;
			$tax = 0;
			$config =& hikashop_config();
			$group = $config->get('group_options',0);
			foreach($order->cart->products as $product) {
				if($group && $product->order_product_option_parent_id) continue;
				$vars['item_name_' . $i] = substr(strip_tags($product->order_product_name), 0, 127);
				$vars['item_number_' . $i] = $product->order_product_code;
				$vars['amount_'.$i] = number_format(round($product->order_product_price, (int)$this->currency->currency_locale['int_frac_digits']), 2, '.', '');
				$vars['quantity_' . $i] = $product->order_product_quantity;
				$tax += round($product->order_product_tax, (int)$this->currency->currency_locale['int_frac_digits']) * $product->order_product_quantity;
				$i++;
			}

			if(!empty($order->cart->additional)){
				foreach($order->cart->additional as $product) {
					if(empty($product->order_product_price) || $product->order_product_price <= 0) continue;
					$vars['item_name_' . $i] = substr(JText::_(strip_tags($product->order_product_name)), 0, 127);
					$vars['item_number_' . $i] = $product->order_product_code;
					$vars['amount_'.$i] = number_format(round($product->order_product_price, (int)$this->currency->currency_locale['int_frac_digits']), 2, '.', '');
					$vars['quantity_' . $i] = 1;
					$tax += round($product->order_product_tax, (int)$this->currency->currency_locale['int_frac_digits']);
					$i++;
				}
			}

			if(!empty($order->order_shipping_price) && bccomp($order->order_shipping_price, 0, 5)) {
				$vars['item_name_' . $i] = JText::_('HIKASHOP_SHIPPING');
				$vars['amount_' . $i] = number_format(round($order->order_shipping_price - @$order->order_shipping_tax, (int)$this->currency->currency_locale['int_frac_digits']), 2, '.', '');
				$tax += round($order->order_shipping_tax, (int)$this->currency->currency_locale['int_frac_digits']);
				$vars['quantity_' . $i] = 1;
				$i++;
			}

			if(!empty($order->order_payment_price) && bccomp($order->order_payment_price, 0, 5)) {
				$vars['item_name_' . $i] = JText::_('HIKASHOP_PAYMENT');
				$vars['amount_' . $i] = number_format(round($order->order_payment_price - @$order->order_payment_tax, (int)$this->currency->currency_locale['int_frac_digits']), (int)$this->currency->currency_locale['int_frac_digits']);
				$tax += round($order->order_payment_tax, (int)$this->currency->currency_locale['int_frac_digits']);
				$vars['quantity_' . $i] = 1;
				$i++;
			}

			if(bccomp($tax, 0, 5))
				$vars['tax_cart'] = number_format(round($tax, (int)$this->currency->currency_locale['int_frac_digits']), 2, '.', '');
			if(!empty($order->cart->coupon) && bccomp($order->order_discount_price, 0, 5)){
				$vars['discount_amount_cart'] = round($order->order_discount_price, (int)$this->currency->currency_locale['int_frac_digits']);
			}
			if(!empty($order->cart->additional)){
				foreach($order->cart->additional as $product) {
					if(empty($product->order_product_price) || $product->order_product_price >= 0) continue;
					if(!isset($vars['discount_amount_cart'])) $vars['discount_amount_cart'] = 0;
					$vars['discount_amount_cart'] += round($product->order_product_price*-1, (int)$this->currency->currency_locale['int_frac_digits']);
				}
			}
			if(isset($vars['discount_amount_cart']))
				$vars['discount_amount_cart'] = number_format($vars['discount_amount_cart'], 2, '.', '');
		}

		if((isset($this->payment_params->validation) && $this->payment_params->validation) || (isset($this->payment_params->enable_validation) && !$this->payment_params->enable_validation)) {
			$vars['paymentaction'] = 'authorization';
		}
		if(empty($this->payment_params->url))
			$this->payment_params->url = 'https://www.paypal.com/cgi-bin/webscr';

		$this->vars = $vars;

		if(!empty($this->payment_params->debug))
			$this->writeToLog($vars);

		return $this->showPage('end');
	}

	public function onPaymentNotification(&$statuses) {

		$vars = array();
		$data = array();
		$filter = JFilterInput::getInstance();
		foreach($_REQUEST as $key => $value) {
			$key = $filter->clean($key);
			if(preg_match('#^[0-9a-z_-]{1,30}$#i', $key) && !preg_match('#^cmd$#i', $key)) {
				$value = hikaInput::get()->getString($key);
				$vars[$key] = $value;
				$data[] = $key . '=' . urlencode($value);
			}
		}
		$data = 'cmd=_notify-validate&'.implode('&', $data);

		$dbOrder = $this->getOrder((int)@$vars['invoice']);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);

		if(!$this->payment_params->notification)
			return false;

		if($this->payment_params->debug)
			$this->writeToLog($vars);

		if(empty($dbOrder)) {
			$this->writeToLog('Could not load any order for your notification ' . @$vars['invoice']);
			return false;
		}

		if($this->payment_params->debug)
			$this->writeToLog($dbOrder);


		$order_id = $dbOrder->order_id;

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id=' . $order_id;
		$order_text = "\r\n" . JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $dbOrder->order_number, HIKASHOP_LIVE);
		$order_text .= "\r\n" . str_replace('<br/>', "\r\n", JText::sprintf('ACCESS_ORDER_WITH_LINK', $url, $url));

		if(!empty($this->payment_params->ips)){
			$ip = hikashop_getIP();
			$ips = str_replace(array('.', '*', ','), array('\.', '[0-9]+', '|'), $this->payment_params->ips);
			if(!preg_match('#('.implode('|',$ips).')#', $ip)) {
				$email = new stdClass();
				$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Paypal') . ' ' . JText::sprintf('IP_NOT_VALID', $dbOrder->order_number);
				$email->body = str_replace('<br/>',"\r\n",JText::sprintf('NOTIFICATION_REFUSED_FROM_IP','Paypal',$ip,implode("\r\n",$this->payment_params->ips)))."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-paypal-error#ip').$order_text;
				$action = false;
				$this->modifyOrder($action, null, null, $email);

				JError::raiseError(403, JText::_('Access Forbidden'));
				return false;
			}
		}

		if(empty($this->payment_params->url))
			$this->payment_params->url = 'https://www.paypal.com/cgi-bin/webscr';
		$url = parse_url($this->payment_params->url);
		if(!isset($url['query']))
			$url['query'] = '';

		if(!isset($url['port'])) {
			if(!empty($url['scheme']) && in_array($url['scheme'], array('https', 'ssl'))) {
				$url['port'] = 443;
			} else {
				$url['port'] = 80;
			}
		}

		if(!empty($url['scheme']) && in_array($url['scheme'], array('https', 'ssl'))) {
			$url['host_socket'] = 'ssl://' . $url['host'];
		} else {
			$url['host_socket'] = $url['host'];
		}

		if($this->payment_params->debug)
			$this->writeToLog($url);

		$response = $this->verifyIPN();

		if(empty($response) || !preg_match('#VERIFIED#i', $response))
			$response = $this->sendRequest($url, $data);

		if($this->payment_params->debug)
			$this->writeToLog($response);

		if(empty($response)){
			$email = new stdClass();
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Paypal').' '.JText::sprintf('PAYPAL_CONNECTION_FAILED',$dbOrder->order_number);
			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('NOTIFICATION_REFUSED_NO_CONNECTION','Paypal'))."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-paypal-error#connection') . $order_text;
			$action = false;
			$this->modifyOrder($action, null, null, $email);

			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return false;
		}

		$this->writeToLog('PayPal transaction id: '.@$vars['txn_id']);

		$history = new stdClass();
		$history->notified = 0;
		$history->amount = @$vars['mc_gross'].@$vars['mc_currency'];
		$history->data = $this->writeToLog();

		$verified = preg_match('#VERIFIED#i', $response);
		if(!$verified) {
			$email = new stdClass();
			if(preg_match('#INVALID#i', $response)) {
				$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Paypal').'invalid transaction';
				$email->body = JText::sprintf("Hello,\r\n A paypal notification was refused because it could not be verified by the paypal server")."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-paypal-error#invalidtnx').$order_text;
				if($this->payment_params->debug)
					$this->writeToLog('invalid transaction');
			} else {
				$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Paypal').'invalid response';
				$email->body = JText::sprintf("Hello,\r\n A paypal notification was refused because the response from the paypal server was invalid")."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-paypal-error#invalidresponse').$order_text;

				if($this->payment_params->debug)
					$this->writeToLog('invalid response');
			}
			$action = false;
			$this->modifyOrder($action, null, $history, $email);
			return false;
		}

		$completed = preg_match('#Completed#i', $vars['payment_status']);
		$pending = preg_match('#Pending#i', $vars['payment_status']);
		if(!$completed && !$pending) {
			$email = new stdClass();
			$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Paypal',$vars['payment_status'],$dbOrder->order_number);
			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Paypal',$vars['payment_status'])).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-paypal-error#status').$order_text;
			$action = false;
			$this->modifyOrder($action, null, $history, $email);

			if($this->payment_params->debug)
				$this->writeToLog('payment ' . $vars['payment_status']);
			return false;
		}

		$price_check = round($dbOrder->order_full_price, (int)$this->currency->currency_locale['int_frac_digits']);
		if($price_check != @$vars['mc_gross'] || $this->currency->currency_code != @$vars['mc_currency']) {
			$email = new stdClass();
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Paypal').JText::_('INVALID_AMOUNT');
			$email->body = str_replace('<br/>', "\r\n", JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER', 'Paypal', $history->amount, $price_check . $this->currency->currency_code)) . "\r\n\r\n" . JText::sprintf('CHECK_DOCUMENTATION', HIKASHOP_HELPURL . 'payment-paypal-error#amount') . $order_text;

			$this->modifyOrder($order_id, $this->payment_params->invalid_status, $history, $email);
			return false;
		}
		if(strtolower(@$vars['receiver_email']) != strtolower($this->payment_params->email) && strtolower(@$vars['business']) != strtolower($this->payment_params->email)){
			$email = new stdClass();
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Paypal').'wrong receiver';
			$email->body = str_replace('<br/>', "\r\n", 'The money was sent to the wrong PayPal account, likely due to the customer trying to cheat.'."\r\n".
														'Notification receiver: ' .@$vars['receiver_email']."\r\n".
														'Notification business: ' .@$vars['business']."\r\n".
														'Your paypal address: ' .$this->payment_params->email."\r\n".
														$order_text);

			$this->modifyOrder($order_id, $this->payment_params->invalid_status, $history, $email);
			return false;
		}

		if($completed) {
			$order_status = $this->payment_params->verified_status;
		} else {
			$order_status = $this->payment_params->pending_status;
			$order_text = JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-paypal-error#pending')."\r\n\r\n".$order_text;
		}
		if($dbOrder->order_status == $order_status)
			return true;

		$history->notified = 1;

		$email = new stdClass();
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Paypal',$vars['payment_status'],$dbOrder->order_number);
		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Paypal',$vars['payment_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',hikashop_orderStatus($order_status))."\r\n\r\n".$order_text;

		$this->modifyOrder($order_id, $order_status, $history, $email);
		return true;
	}

	function writeToLog($data = null) {
		if(!empty($data)) {
			hikashop_writeToLog($data, $this->name);
			if(is_array($data) || is_object($data))
				$data = str_replace(array("\r","\n","\r\n"),"\r\n",print_r($data, true))."\r\n\r\n";
			$this->cachedDebug .= $data;
		}
		return $this->cachedDebug;
	}

	protected function sendRequest($url, $data){
		$response = $this->_sendRequestSocket($url,$data);
		if(!$response)
			$response = $this->_sendRequestCURL($url,$data);
		return $response;
	}

	protected function _sendRequestCURL($url, $data) {
		if(!function_exists('curl_version')) {
			if($this->payment_params->debug)
				echo 'CURL is not available'. "\r\n\r\n";
			return false;
		}

		$uri = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query'] != '' ? '?' . $url['query'] : '');
		$ch = curl_init($uri);

		if(!$ch){
			if($this->payment_params->debug)
				echo 'CURL could not be initialized'. "\r\n\r\n";
			return false;
		}

		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
		curl_setopt($ch, CURLOPT_FAILONERROR,true);

		if($this->payment_params->debug) {
			echo print_r($data, true) . "\r\n\r\n";
		}

		$response = curl_exec($ch);

		$errno = curl_errno($ch);
		$error = curl_error($ch);

		curl_close($ch);

		if (!$response) {
			if($this->payment_params->debug)
				echo 'CURL request didn\t return any data'. "\r\n\r\n";
			return false;
		}

		if($errno){
			if($this->payment_params->debug) {
				echo 'CURL error number: '.$errno. "\r\n\r\n";
				echo 'CURL error message: '.$error. "\r\n\r\n";
			}
		}

		if($this->payment_params->debug) {
			echo print_r($response, true) . "\r\n\r\n";
		}

		return $response;
	}

	protected function _sendRequestSocket($url, $data){
		if(!function_exists('fsockopen')) {
			if($this->payment_params->debug)
				echo 'fsockopen function does not exist'. "\r\n\r\n";
			return false;
		}
		if(!is_callable('fsockopen')) {
			if($this->payment_params->debug)
				echo 'fsockopen function is not callable'. "\r\n\r\n";
			return false;
		}

		$fp = fsockopen($url['host_socket'], $url['port'], $errno, $errstr, 30);
		if(!$fp) {
			if($this->payment_params->debug)
				echo 'fsockopen connection couldn\'t be established'. "\r\n\r\n";
			return false;
		}

		$uri = $url['path'] . ($url['query'] != '' ? '?' . $url['query'] : '');
		$header = 'POST '.$uri.' HTTP/1.1'."\r\n".
			'User-Agent: PHP/'.phpversion()."\r\n".
			'Referer: '.hikashop_currentURL()."\r\n".
			'Server: '.$_SERVER['SERVER_SOFTWARE']."\r\n".
			'Host: '.$url['host']."\r\n".
			'Content-Type: application/x-www-form-urlencoded'."\r\n".
			'Content-Length: '.strlen($data)."\r\n".
			'Accept: */'.'*'."\r\n".
			'Connection: close'."\r\n\r\n";

		if($this->payment_params->debug) {
			echo print_r($header, true) . "\r\n\r\n";
			echo print_r($data, true) . "\r\n\r\n";
		}

		fwrite($fp, $header . $data);
		$response = '';
		while(!feof($fp)) {
			$response .= fgets($fp, 1024);
		}
		fclose ($fp);

		if(empty($response)){
			if($this->payment_params->debug)
				echo 'fsockopen request didn\t return any data'. "\r\n\r\n";
			return false;
		}

		if($this->payment_params->debug) {
			echo print_r($response, true) . "\r\n\r\n";
		}

		$response = substr($response, strpos($response, "\r\n\r\n") + strlen("\r\n\r\n"));

		return $response;
	}

	public function onPaymentConfiguration(&$element) {
		$subtask = hikaInput::get()->getCmd('subtask', '');
		if($subtask == 'ips') {
			$ips = null;
			echo implode(',', $this->_getIPList($ips));
			exit;
		}

		parent::onPaymentConfiguration($element);
		$this->address = hikashop_get('type.address');

		if(empty($element->payment_params->email)) {
			$app = JFactory::getApplication();
			$lang = JFactory::getLanguage();
			$locale = strtolower(substr($lang->get('tag'), 0, 2));
			$app->enqueueMessage(JText::sprintf('ENTER_INFO_REGISTER_IF_NEEDED', 'PayPal', JText::_('HIKA_EMAIL'), 'PayPal', 'https://www.paypal.com/' . $locale . '/mrb/pal=SXL9FKNKGAEM8'));
		}

		if(!empty($element->payment_params->details)){
			$config = hikashop_config();
			$round_calculations = $config->get('round_calculations');
			if(empty($round_calculations)){
				$app = JFactory::getApplication();
				$app->enqueueMessage('When you activate the "Send details of the order" setting, PayPal calculate itself the total amount and round prices during calculations. So you need to have option "Round prices during calculations" turned on in the HikaShop configuration in order for HikaShop to calculate the total in the same way. Otherwise, you might get payments with an amount different than the total amount of the orders and it will create an error.');
			}
		}

		if(defined('OPENSSL_VERSION_NUMBER') && OPENSSL_VERSION_NUMBER < 0x009080bf ){
			$app = JFactory::getApplication();
			$app->enqueueMessage('The OpenSSL version installed on your server is too old and payment notifications will be rejected by PayPal. Please contact your hosting company in order to update it.');
		}
	}

	public function pluginConfigDisplay($fieldType, $data, $type, $paramsType, $key, $element){
		if($fieldType == 'ips') {
			$map = 'data['.$type.']['.$paramsType.']['.$key.']';
			$value = @$element->$paramsType->$key;
			return '
<textarea id="paypal_ips" name="'.$map.'">'.
	(!empty($value) && is_array($value) ? trim(implode(',', $value)):'').
'</textarea><br/>
<a href="#" onclick="return paypal_refreshIps();">'.JText::_('REFRESH_IPS').'</a>
<script type="text/javascript">
function paypal_refreshIps() {
	var w = window, d = document, o = w.Oby;
	o.xRequest("'.hikashop_completeLink('plugins&plugin_type=payment&task=edit&name='.$this->name.'&subtask=ips',true,true).'", null, function(xhr) {
		d.getElementById("paypal_ips").value = xhr.responseText;
	});
	return false;
}
</script>';
		}
	}

	public function onPaymentConfigurationSave(&$element) {
		if(!empty($element->payment_params->ips))
			$element->payment_params->ips = explode(',', $element->payment_params->ips);

		if(strpos($element->payment_params->url,'https://')===false){
			$app = JFactory::getApplication();
			$app->enqueueMessage('The URL must start with https://');
			return false;
		}

		return true;
	}

	public function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'PayPal';
		$element->payment_description='You can pay by credit card or paypal using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,PayPal';

		$element->payment_params->url = 'https://www.paypal.com/cgi-bin/webscr';
		$element->payment_params->notification = 1;
		$element->payment_params->ips = '';
		$element->payment_params->details = 0;
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
		$element->payment_params->address_override = 1;
	}

	protected function verifyIPN() {
		if(!function_exists('curl_version'))
			return false;

		$raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		$myPost = array();
		foreach ($raw_post_array as $keyval) {
			$keyval = explode('=', $keyval);
			if (count($keyval) == 2) {
				if ($keyval[0] === 'payment_date') {
					if (substr_count($keyval[1], '+') === 1) {
						$keyval[1] = str_replace('+', '%2B', $keyval[1]);
					}
				}
				$myPost[$keyval[0]] = urldecode($keyval[1]);
			}
		}
		$req = 'cmd=_notify-validate';
		$get_magic_quotes_exists = false;
		if (function_exists('get_magic_quotes_gpc')) {
			$get_magic_quotes_exists = true;
		}
		foreach ($myPost as $key => $value) {
			if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
				$value = urlencode(stripslashes($value));
			} else {
				$value = urlencode($value);
			}
			$req .= "&$key=$value";
		}

		if(empty($this->payment_params->url))
			$this->payment_params->url = 'https://www.paypal.com/cgi-bin/webscr';
		if(strpos($this->payment_params->url, 'sandbox') === false) {
			$url = 'https://ipnpb.paypal.com/cgi-bin/webscr';
		} else {
			$url = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
		}

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSLVERSION, 6);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
		$res = curl_exec($ch);

		if ( ! ($res)) {
			$errno = curl_errno($ch);
			$errstr = curl_error($ch);
			curl_close($ch);
			$this->writeToLog("cURL error: [$errno] $errstr");
			return false;
		}

		$info = curl_getinfo($ch);
		$http_code = $info['http_code'];
		if ($http_code != 200) {
			$this->writeToLog("PayPal responded with http code $http_code");
			return false;
		}
		curl_close($ch);

		return $res;
	}

	protected function _getIPList(&$ipList) {
		$hosts = array(
			'www.paypal.com',
			'notify.paypal.com',
			'ipnpb.paypal.com',
			'ipn.sandbox.paypal.com',
			'ipnpb.sandbox.paypal.com'
		);

		$ipList = array();
		foreach($hosts as $host) {
			$ips = gethostbynamel($host);
			if(!empty($ips)) {
				if(empty($ipList))
					$ipList = $ips;
				else
					$ipList = array_merge($ipList, $ips);
			}
		}

		if(empty($ipList))
			return $ipList;

		$newList = array();
		foreach($ipList as $k => $ip) {
		$ipParts = explode('.', $ip);
		if(count($ipParts) == 4) {
			array_pop($ipParts);
			$ip = implode('.', $ipParts) . '.*';
		}
		if(!in_array($ip, $newList))
			$newList[] = $ip;
		}
		return $newList;
	}
}
