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
class hikashopPaymentPlugin extends hikashopPlugin {
	var $type = 'payment';
	var $accepted_currencies = array();
	var $doc_form = 'generic';
	var $features = array(
		'authorize_capture' => false,
		'recurring' => false,
		'refund' => false
	);
	var $needCallbackFile = false;

	function onPaymentDisplay(&$order, &$methods, &$usable_methods) {
		if(empty($methods) || empty($this->name))
			return true;
		$currencyClass = hikashop_get('class.currency');

		if(!empty($order->total)) {
			$null = null;
			$currency_id = intval(@$order->total->prices[0]->price_currency_id);
			$currency = $currencyClass->getCurrencies($currency_id, $null);
			if(!empty($currency) && !empty($this->accepted_currencies) && !in_array(@$currency[$currency_id]->currency_code, $this->accepted_currencies))
				return true;

			$this->currency = $currency;
			$this->currency_id = $currency_id;
		}

		$this->currencyClass = $currencyClass;
		$shippingClass = hikashop_get('class.shipping');
		$volumeHelper = hikashop_get('helper.volume');
		$weightHelper = hikashop_get('helper.weight');

		foreach($methods as $method) {
			if($method->payment_type != $this->name || !$method->enabled || !$method->payment_published)
				continue;

			if(method_exists($this, 'needCC')) {
				$this->needCC($method);
			} else if(!empty($this->ask_cc)) {
				$method->ask_cc = true;
				if(!empty($this->ask_owner))
					$method->ask_owner = true;
				if(!empty($method->payment_params->ask_ccv))
					$method->ask_ccv = true;
			}

			$price = null;

			if(@$method->payment_params->payment_price_use_tax) {
				if(isset($order->order_full_price))
					$price = $order->order_full_price;
				if(isset($order->total->prices[0]->price_value_with_tax))
					$price = $order->total->prices[0]->price_value_with_tax;
				if(isset($order->full_total->prices[0]->price_value_with_tax))
					$price = $order->full_total->prices[0]->price_value_with_tax;
				if(isset($order->full_total->prices[0]->price_value_without_payment_with_tax))
					$price = $order->full_total->prices[0]->price_value_without_payment_with_tax;
			} else {
				if(isset($order->order_full_price))
					$price = $order->order_full_price;
				if(isset($order->total->prices[0]->price_value))
					$price = $order->total->prices[0]->price_value;
				if(isset($order->full_total->prices[0]->price_value))
					$price = $order->full_total->prices[0]->price_value;
				if(isset($order->full_total->prices[0]->price_value_without_payment))
					$price = $order->full_total->prices[0]->price_value_without_payment;
			}

			if(!empty($method->payment_params->payment_min_price) && bccomp(sprintf('%F',$method->payment_params->payment_min_price), sprintf('%F',$price), 5) == 1) {
				$method->errors['min_price'] = (hikashop_toFloat($method->payment_params->payment_min_price) - $price);
				continue;
			}

			if(!empty($method->payment_params->payment_max_price) && bccomp(sprintf('%F',$method->payment_params->payment_max_price), sprintf('%F',$price), 5) == -1){
				$method->errors['max_price'] = ($price - hikashop_toFloat($method->payment_params->payment_max_price));
				continue;
			}

			if(!empty($method->payment_params->payment_max_volume) && bccomp(sprintf('%F',@$method->payment_params->payment_max_volume), 0, 3)) {
				$method->payment_params->payment_max_volume_orig = $method->payment_params->payment_max_volume;
				$method->payment_params->payment_max_volume = $volumeHelper->convert($method->payment_params->payment_max_volume, @$method->payment_params->payment_size_unit);
				if(bccomp(sprintf('%.10F',$method->payment_params->payment_max_volume), sprintf('%.10F',$order->volume), 10) == -1){
					$method->errors['max_volume'] = ($method->payment_params->payment_max_volume - $order->volume);
					continue;
				}
			}
			if(!empty($method->payment_params->payment_min_volume) && bccomp(sprintf('%F',@$method->payment_params->payment_min_volume), 0, 3)) {
				$method->payment_params->payment_min_volume_orig = $method->payment_params->payment_min_volume;
				$method->payment_params->payment_min_volume = $volumeHelper->convert($method->payment_params->payment_min_volume, @$method->payment_params->payment_size_unit);
				if(bccomp(sprintf('%.10F',$method->payment_params->payment_min_volume), sprintf('%.10F',$order->volume), 10) == 1){
					$method->errors['min_volume'] = ($order->volume - $method->payment_params->payment_min_volume);
					continue;
				}
			}

			if(!empty($method->payment_params->payment_max_weight) && bccomp(sprintf('%F',@$method->payment_params->payment_max_weight), 0, 3)) {
				$method->payment_params->payment_max_weight_orig = $method->payment_params->payment_max_weight;
				$method->payment_params->payment_max_weight = $weightHelper->convert($method->payment_params->payment_max_weight, @$method->payment_params->payment_weight_unit);
				if(bccomp(sprintf('%.5F',$method->payment_params->payment_max_weight), sprintf('%.5F',$order->weight), 5) == -1){
					$method->errors['max_weight'] = ($method->payment_params->payment_max_weight - $order->weight);
					continue;
				}
			}
			if(!empty($method->payment_params->payment_min_weight) && bccomp(sprintf('%F',@$method->payment_params->payment_min_weight),0,3)){
				$method->payment_params->payment_min_weight_orig = $method->payment_params->payment_min_weight;
				$method->payment_params->payment_min_weight = $weightHelper->convert($method->payment_params->payment_min_weight, @$method->payment_params->payment_weight_unit);
				if(bccomp(sprintf('%.5F',$method->payment_params->payment_min_weight), sprintf('%.5F',$order->weight), 5) == 1){
					$method->errors['min_weight'] = ($order->weight - $method->payment_params->payment_min_weight);
					continue;
				}
			}

			if(!empty($method->payment_params->payment_max_quantity) && (int)$method->payment_params->payment_max_quantity) {
				if((int)$method->payment_params->payment_max_quantity < (int)$order->total_quantity){
					$method->errors['max_quantity'] = ($method->payment_params->payment_max_quantity - $order->total_quantity);
					continue;
				}
			}
			if(!empty($method->payment_params->payment_min_quantity) && (int)$method->payment_params->payment_min_quantity){
				if((int)$method->payment_params->payment_min_quantity > (int)$order->total_quantity){
					$method->errors['min_quantity'] = ($order->total_quantity - $method->payment_params->payment_min_quantity);
					continue;
				}
			}

			$method->features = $this->features;

			if(!$this->checkPaymentDisplay($method, $order))
				continue;

			if(!empty($order->paymentOptions) && !empty($order->paymentOptions['recurring']) && empty($order->paymentOptions['recurring']['optional']) && empty($method->features['recurring']))
				continue;
			if(!empty($order->paymentOptions) && !empty($order->paymentOptions['term']) && empty($method->features['authorize_capture']))
				continue;
			if(!empty($order->paymentOptions) && !empty($order->paymentOptions['refund']) && empty($method->features['refund']))
				continue;

			if((int)$method->payment_ordering > 0 && !isset($usable_methods[(int)$method->payment_ordering]))
				$usable_methods[(int)$method->payment_ordering] = $method;
			else
				$usable_methods[] = $method;
		}

		return true;
	}

	function onPaymentSave(&$cart, &$rates, &$payment_id) {
		$usable = array();
		$this->onPaymentDisplay($cart, $rates, $usable);
		$payment_id = (int)$payment_id;

		foreach($usable as $usable_method) {
			if($usable_method->payment_id == $payment_id)
				return $usable_method;
		}

		return false;
	}

	function onPaymentCustomSave(&$cart, &$method, $formData) {
		return $formData;
	}

	function onPaymentConfiguration(&$element) {
		$this->pluginConfiguration($element);

		if(empty($element) || empty($element->payment_type)) {
			$element = new stdClass();
			$element->payment_type = $this->pluginName;
			$element->payment_params= new stdClass();
			$this->getPaymentDefaultValues($element);
		}

		$this->order_statuses = hikashop_get('type.categorysub');
		$this->order_statuses->type = 'status';
		$this->currency = hikashop_get('type.currency');
		$this->weight = hikashop_get('type.weight');
		$this->volume = hikashop_get('type.volume');
	}

	function onPaymentConfigurationSave(&$element) {
		if(empty($this->pluginConfig))
			return true;
		$formData = hikaInput::get()->get('data', array(), 'array');
		if(!isset($formData['payment']['payment_params']))
			return true;
		foreach($this->pluginConfig as $key => $config) {
			if($config[1] == 'textarea' || $config[1] == 'big-textarea') {
				$element->payment_params->$key = @$formData['payment']['payment_params'][$key];
			}
		}
		return true;
	}

	function onBeforeOrderCreate(&$order, &$do) {
		if($do === false)
			return true;

		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator'))
			return true;

		if(empty($order->order_payment_method) || $order->order_payment_method != $this->name)
			return true;

		if(!empty($order->order_type) && $order->order_type != 'sale')
			return true;

		$this->loadOrderData($order);
		$this->loadPaymentParams($order);
		if(empty($this->payment_params)) {
			$do = false;
			return true;
		}
		return false;
	}
	function onAfterHikaPluginUpdate($type, &$element) {
		$this->createCallbackFile($type, $element);
	}
	function onAfterHikaPluginCreate($type, &$element) {
		$this->createCallbackFile($type, $element);
	}

	function createCallbackFile($type, &$element) {
		if($type != 'payment' || $element->payment_type != $this->name)
			return true;
		if(!$this->needCallbackFile)
			return true;

		$path = JPATH_ROOT.DS.$this->getCallbackFilename($element);
		if(file_exists($path))
			return true;
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.path');
		$content = $this->getCallbackContent($element);
		$result = JFile::write($path, $content);
		if(!$result) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('CALLBACK_FILE_COULD_NOT_BE_ADDED', $path), 'error');
		}
		return $result;
	}

	function getCallbackFilename(&$element) {
		return $this->name.'_'.$element->payment_id.'.php';
	}
	function getCallbackContent(&$element) {
		$lang = JFactory::getLanguage();
		$locale = strtolower(substr($lang->get('tag'),0,2));
		$content = '<?php
$_GET[\'option\']=\'com_hikashop\';
$_GET[\'tmpl\']=\'component\';
$_GET[\'ctrl\']=\'checkout\';
$_GET[\'task\']=\'notify\';
$_GET[\'notif_payment\']=\''.$this->name.'\';
$_GET[\'format\']=\'html\';
$_GET[\'lang\']=\''.$locale.'\';
$_GET[\'notif_id\']=\''.$element->payment_id.'\';
$_REQUEST[\'option\']=\'com_hikashop\';
$_REQUEST[\'tmpl\']=\'component\';
$_REQUEST[\'ctrl\']=\'checkout\';
$_REQUEST[\'task\']=\'notify\';
$_REQUEST[\'notif_payment\']=\''.$this->name.'\';
$_REQUEST[\'format\']=\'html\';
$_REQUEST[\'lang\']=\''.$locale.'\';
$_REQUEST[\'notif_id\']=\''.$element->payment_id.'\';
include(\'index.php\');
';
		return $content;
	}

	function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		$this->payment = $methods[$method_id];
		$this->payment_params =& $this->payment->payment_params;
		$this->payment_name = $this->payment->payment_name;
		$this->loadOrderData($order);
		$this->order = $order;
	}

	function onPaymentNotification(&$statuses) {
	}

	function onOrderPaymentCapture(&$order, $total) { return false; }

	function onOrderAuthorizationCancel(&$order) { return false; }

	function onOrderAuthorizationRenew(&$order) { return false; }

	function onOrderPaymentRefund(&$order, $total) { return false; }

	function getOrder($order_id) {
		$ret = null;
		if(empty($order_id))
			return $ret;
		$orderClass = hikashop_get('class.order');
		$ret = $orderClass->get($order_id);
		return $ret;
	}

	function modifyOrder(&$order_id, $order_status, $history = null, $email = null, $payment_params = null) {
		if(is_object($order_id)) {
			$order =& $order_id;
		} else {
			$order = new stdClass();
			$order->order_id = $order_id;
		}

		if($order_status !== null)
			$order->order_status = $order_status;

		$history_notified = 0;
		$history_amount = '';
		$history_data = '';
		$history_type = '';
		if(!empty($history)) {
			if($history === true) {
				$history_notified = 1;
			} else if(is_array($history)) {
				$history_notified = (int)@$history['notified'];
				$history_amount = @$history['amount'];
				$history_data = @$history['data'];
				$history_type = @$history['type'];
			} else {
				$history_notified = (int)@$history->notified;
				$history_amount = @$history->amount;
				$history_data = @$history->data;
				$history_type = @$history->type;
			}
		}

		$order->history = new stdClass();
		$order->history->history_reason = JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
		$order->history->history_notified = $history_notified;
		$order->history->history_payment_method = $this->name;
		$order->history->history_type = 'payment';
		if(!empty($history_amount))
			$order->history->history_amount = $history_amount;
		if(!empty($history_data))
			$order->history->history_data = $history_data;
		if(!empty($history_type))
			$order->history->history_type = $history_type;

		if($payment_params !== null) {
			if(isset($order->order_payment_params)) {
				foreach($payment_params as $k => $v) {
					$order->order_payment_params->$k = $v;
				}
			} else {
				$order->order_payment_params = $payment_params;
			}
		}

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeModifyOrder', array(&$order, &$order_status, &$history, &$email));

		$orderClass = hikashop_get('class.order');
		if(!is_object($order_id) && $order_id !== false) {
			$orderClass->save($order);
		}

		$config =& hikashop_config();
		$recipients = trim($config->get('payment_notification_email', ''));
		if(empty($email) || empty($recipients))
			return;

		$payment_status = $order_status;
		$mail_status = hikashop_orderStatus($order_status);
		if(is_object($order_id))
			$id = @$order->order_id;
		else
			$id = $order_id;

		if(!empty($id)) {
			$mailClass = hikashop_get('class.mail');
			$dbOrder = $orderClass->get($id);
			$message = str_replace('<br/>', "\r\n", JText::sprintf('PAYMENT_NOTIFICATION_STATUS', $this->name, $payment_status)) . ' ' .
				JText::sprintf('ORDER_STATUS_CHANGED', $mail_status) .
				"\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $dbOrder->order_number, HIKASHOP_LIVE);
			if(is_string($email))
				$message.= "\r\n\r\n" . $email;
			$orderMail = $orderClass->loadNotification((int)$id, 'payment_notification', $message);
			if(empty($orderMail->mail->subject))
				$orderMail->mail->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', $this->name, $payment_status, $dbOrder->order_number);		

			if(HIKASHOP_J30) {
				$mailClass->mailer->addReplyTo($orderMail->mail->dst_email, $orderMail->mail->dst_name);
			} else {
				$mailClass->mailer->addReplyTo(array($orderMail->mail->dst_email, $orderMail->mail->dst_name));
			}
			$orderMail->mail->dst_email = $recipients;
			$orderMail->mail->dst_name = '';

			$mailClass->sendMail($orderMail->mail);
			return;
		}

		$mailer = JFactory::getMailer();
		$order_number = '';

		global $Itemid;
		$this->url_itemid = empty($Itemid) ? '' : '&Itemid=' . $Itemid;

		if(is_object($order_id)) {
			$subject = JText::sprintf('PAYMENT_NOTIFICATION', $this->name, $payment_status);
			$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=listing'. $this->url_itemid;
			if(isset($order->order_id))
				$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id=' . $order->order_id . $this->url_itemid;
			if(isset($order->order_number))
				$order_number = $order->order_number;
		} elseif($order_id !== false) {
			$dbOrder = $orderClass->get($order_id);
			$order_number = $dbOrder->order_number;
			$subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', $this->name, $payment_status, $order_number);
			$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id=' . $order_id . $this->url_itemid;
		}

		$order_text = '';
		if(is_string($email))
			$order_text = "\r\n\r\n" . $email;

		$body = str_replace('<br/>', "\r\n", JText::sprintf('PAYMENT_NOTIFICATION_STATUS', $this->name, $payment_status)) . ' ' .
			JText::sprintf('ORDER_STATUS_CHANGED', $mail_status) .
			"\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $order_number, HIKASHOP_LIVE);
		if(!empty($url))
			$body .= "\r\n".str_replace('<br/>', "\r\n", JText::sprintf('ACCESS_ORDER_WITH_LINK', $url));
		$body .= $order_text;

		if(is_object($email)) {
			if(!empty($email->subject))
				$subject = $email->subject;
			if(!empty($email->body))
				$body = $email->body;
		}

		$sender = array(
			$config->get('from_email'),
			$config->get('from_name')
		);
		$mailer->setSender($sender);
		$mailer->addRecipient(explode(',', $recipients));

		$mailer->setSubject($subject);
		$mailer->setBody($body);
		$mailer->Send();

	}

	function loadOrderData(&$order) {
		$this->app = JFactory::getApplication();
		$lang = JFactory::getLanguage();

		$currencyClass = hikashop_get('class.currency');
		$cartClass = hikashop_get('class.cart');

		$this->currency = 0;
		if(!empty($order->order_currency_id)) {
			$currencies = null;
			$currencies = $currencyClass->getCurrencies($order->order_currency_id, $currencies);
			$this->currency = $currencies[$order->order_currency_id];
		}

		hikashop_loadUser(true, true);
		$this->user = hikashop_loadUser(true);

		$this->locale = strtolower(substr($lang->get('tag'), 0, 2));

		$this->url_itemid = '';
		if(empty($order->customer->user_cms_id))
			$this->url_itemid = '&order_token='.$order->order_token;

		global $Itemid;
		$this->url_itemid .= empty($Itemid) ? '' : '&Itemid=' . $Itemid;

		$billing_address = $this->app->getUserState(HIKASHOP_COMPONENT.'.billing_address');
		if(isset($order->cart_billing_address_id))
			$billing_address = (int)$order->cart_billing_address_id;
		if(isset($order->cart->cart_billing_address_id))
			$billing_address = (int)$order->cart->cart_billing_address_id;
		if(isset($order->order_billing_address_id))
			$billing_address = (int)$order->order_billing_address_id;
		if(!empty($billing_address))
			$cartClass->loadAddress($order->cart, $billing_address, 'object', 'billing');

		$shipping_address = $this->app->getUserState(HIKASHOP_COMPONENT.'.shipping_address');
		if(isset($order->cart_shipping_address_ids))
			$shipping_address = (int)$order->cart_shipping_address_ids;
		if(isset($order->cart->cart_shipping_address_ids))
			$shipping_address = (int)$order->cart->cart_shipping_address_ids;
		if(isset($order->order_shipping_address_id))
			$shipping_address = (int)$order->order_shipping_address_id;
		if(!empty($shipping_address))
			$cartClass->loadAddress($order->cart, $shipping_address, 'object', 'shipping');
	}

	function loadPaymentParams(&$order) {
		$payment_id = @$order->order_payment_id;
		$this->payment_params = null;
		if(!empty($order->order_payment_method) && $order->order_payment_method == $this->name && !empty($payment_id) && $this->pluginParams($payment_id))
			$this->payment_params =& $this->plugin_params;
		return ($this->payment_params !== null);
	}

	function ccLoad($ccv = true) {
		$cart_id = hikaInput::get()->getInt('cart_id', 0);

		hikashop_get('helper.checkout');
		$checkoutHelper = hikashopCheckoutHelper::get($cart_id);
		$cc = $checkoutHelper->getCreditCard($this->plugin_data, false);

		if(!empty($cc)) {
			$fields = array('cc_number' => 'num', 'cc_month' => 'mm', 'cc_year' => 'yy', 'cc_CCV' => 'ccv', 'cc_type' => 'type', 'cc_owner' => 'owner');
			foreach($fields as $key => $field) {
				$this->$key = '';
				if(!empty($cc->$field))
					$this->$key = $cc->$field;
			}
			return true;
		}


		if(!isset($this->app))
			$this->app = JFactory::getApplication();
		$this->cc_number = $this->app->getUserState(HIKASHOP_COMPONENT.'.cc_number');
		if(!empty($this->cc_number)) $this->cc_number = base64_decode($this->cc_number);

		$this->cc_month = $this->app->getUserState(HIKASHOP_COMPONENT.'.cc_month');
		if(!empty($this->cc_month)) $this->cc_month = base64_decode($this->cc_month);

		$this->cc_year = $this->app->getUserState(HIKASHOP_COMPONENT.'.cc_year');
		if(!empty($this->cc_year)) $this->cc_year = base64_decode($this->cc_year);

		$this->cc_type = $this->app->getUserState( HIKASHOP_COMPONENT.'.cc_type');
		if(!empty($this->cc_type)){
			$this->cc_type = base64_decode($this->cc_type);
		}
		$this->cc_owner = $this->app->getUserState( HIKASHOP_COMPONENT.'.cc_owner');
		if(!empty($this->cc_owner)){
			$this->cc_owner = base64_decode($this->cc_owner);
		}
		$this->cc_CCV = '';
		if($ccv) {
			$this->cc_CCV = $this->app->getUserState(HIKASHOP_COMPONENT.'.cc_CCV');
			if(!empty($this->cc_CCV)) $this->cc_CCV = base64_decode($this->cc_CCV);
		}
		return !empty($this->cc_number);
	}

	function ccClear() {
		if(!isset($this->app))
			$this->app = JFactory::getApplication();
		$this->app->setUserState(HIKASHOP_COMPONENT.'.cc_number', '');
		$this->app->setUserState(HIKASHOP_COMPONENT.'.cc_month', '');
		$this->app->setUserState(HIKASHOP_COMPONENT.'.cc_year', '');
		$this->app->setUserState(HIKASHOP_COMPONENT.'.cc_type', '');
		$this->app->setUserState(HIKASHOP_COMPONENT.'.cc_owner', '');
		$this->app->setUserState(HIKASHOP_COMPONENT.'.cc_CCV', '');
		$this->app->setUserState(HIKASHOP_COMPONENT.'.cc_valid', 0);
		$this->app->setUserState(HIKASHOP_COMPONENT.'.checkout_cc', null);
	}

	function cronCheck() {
		if(empty($this->name))
			return false;

		$pluginsClass = hikashop_get('class.plugins');
		$type = 'hikashop';
		if($this->type == 'payment')
			$type = 'hikashoppayment';
		if($this->type == 'shipping')
			$type = 'hikashopshipping';
		$plugin = $pluginsClass->getByName($type, $this->name);
		if(empty($plugin))
			return false;
		if(empty($plugin->params['period']))
			$plugin->params['period'] = 7200; // 2 hours

		if(!empty($plugin->params['last_cron_update']) && ((int)$plugin->params['last_cron_update'] + (int)$plugin->params['period']) > time())
			return false;

		$plugin->params['last_cron_update'] = time();
		$pluginsClass->save($plugin);
		return true;
	}

	function renewalOrdersAuthorizations(&$messages) {
		$db = JFactory::getDBO();

		$date = hikashop_getDate(time(), '%Y/%m/%d');
		$search = hikashop_getEscaped('s:18:"payment_auth_renew";s:10:"'.$date.'";');
		$query = 'SELECT * FROM '.hikashop_table('order').
				' WHERE order_type = \'sale\' AND order_payment_method = '.$db->Quote($this->name).' AND order_payment_params LIKE \'%'.$search.'%\''.
				' ORDER BY order_payment_id';
		$db->setQuery($query);
		$orders = $db->loadObjectList();
		if(!empty($orders)) {
			$cpt = 0;
			foreach($orders as $order) {
				$order->order_payment_params = hikashop_unserialize($order->order_payment_params);
				$ret = $this->onOrderAuthorizationRenew($order);

				if($ret) {
					$order_payment_params = serialize($order->order_payment_params);
					$query = 'UPDATE '.hikashop_table('order').' SET order_payment_params = '.$db->quote($order_payment_params).' WHERE order_id = '.(int)$order->order_id;
					$db->setQuery($query);
					$db->execute();

					$cpt++;
				}

				unset($order_payment_params);
				unset($order->order_payment_params);
				unset($order);
			}

			if($cpt > 0)
				$messages[] = '['.ucfirst($this->name).'] '.JText::_sprintf('X_ORDERS_AUTHORIZATION_RENEW', $cpt);
		}
	}

	function getOrderUrl(&$order) {
		$url = '#';
		$user = JFactory::getUser();
		global $Itemid;
		$url_itemid = (!empty($Itemid)) ? '&Itemid='.$Itemid : '';
		if(!$user->guest){
			$url = hikashop_completeLink('order&task=show&cid='.@$order->order_id.$url_itemid);
		}else{
			$url = hikashop_completeLink('order&task=show&cid='.@$order->order_id.'&order_token='.@$order->order_token.$url_itemid);
		}
		return $url;
	}

	function writeToLog($data = null) {
		hikashop_writeToLog($data, $this->name);
	}

	function getPaymentDefaultValues(&$element){}

	function checkPaymentDisplay(&$method, &$order) { return true; }
}
