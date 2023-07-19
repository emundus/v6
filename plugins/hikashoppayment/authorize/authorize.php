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
class plgHikashoppaymentAuthorize extends hikashopPaymentPlugin
{
	var $accepted_currencies = array('AUD', 'USD', 'CAD', 'EUR', 'GBP', 'NZD');
	var $multiple = true;
	var $name = 'authorize';
	var $pluginConfig = array(
		'url' => array('URL', 'input'),
		'login_id' => array('AUTHORIZE_LOGIN_ID', 'input'),
		'transaction_key' => array('AUTHORIZE_TRANSACTION_KEY', 'input'),
		'signature_key' => array('AUTHORIZE_SIGNATURE_KEY', 'input'),
		'md5_hash' => array('AUTHORIZE_MD5_HASH', 'input'),
		'capture' => array('INSTANTCAPTURE', 'boolean','1'),
		'duplicate_window' => array('DUPLICATE_WINDOW', 'input'),
		'notification' => array('ALLOW_NOTIFICATIONS_FROM_X', 'boolean','1'),
		'details' => array('SEND_DETAILS_OF_ORDER', 'boolean','1'),
		'api' => array('API', 'list',array(
			'sim' => 'SIM',
			'aim' => 'AIM',
			'dpm' => 'DPM')
		),
		'ask_ccv' => array('CARD_VALIDATION_CODE', 'boolean','0'),
		'validation' => array('ENABLE_VALIDATION', 'boolean', 0),
		'test_payments' => array('Test payments', 'boolean', 0),
		'debug' => array('DEBUG', 'boolean','0'),
		'return_url' => array('RETURN_URL', 'input'),
		'x_logo_url' => array('LOGO', 'input'),
		'negative_coupon' => array('Negative coupon value', 'list',array(
			'0' => 'No (for most payment gateways)',
			'1' => 'Yes (for Payeezy)')
		),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	var $debugData = array();

	function  __construct(&$subject, $config){
		parent::__construct($subject, $config);
		$this->pluginConfig['notification'][0] =  JText::sprintf('ALLOW_NOTIFICATIONS_FROM_X','Authorize.net');
	}

	function needCC(&$method) {
		if(@$method->payment_params->api=='aim'){
			$method->ask_cc=true;
			if($method->payment_params->ask_ccv){
				$method->ask_ccv = true;
			}
			return true;
		}
		return false;
	}

	function onBeforeOrderCreate(&$order,&$do){
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;
		if($this->payment_params->api != 'aim'){
			return true;
		}

		if(!function_exists('curl_init')){
			$this->app->enqueueMessage('The Authorize.net payment plugin in AIM mode needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			$do = false;
			return false;
		}
		$vars = $this->_loadStandardVars($order);

		$vars["x_delim_data"]= "TRUE";
		$vars["x_delim_char"] = "|";

		$this->ccLoad();

		if($this->payment_params->ask_ccv){
			$vars["x_card_code"] = $this->cc_CCV;
		}

		$vars["x_card_num"] = $this->cc_number;
		$vars["x_exp_date"] = $this->cc_month.$this->cc_year;
		$vars["x_tran_key"] = $this->payment_params->transaction_key;
		$post_string = "";

		if($this->payment_params->debug){
			hikashop_writeToLog($vars);
		}

		foreach( $vars as $key => $value ){
			if(is_array($value)){
				foreach($value as $v){
					$post_string .= $key.'=' . urlencode( $v ) . '&';
				}
			}else{
				$post_string .= $key.'=' . urlencode( $value ) . '&';
			}
		}
		$post_string = rtrim( $post_string, '& ');
		$request = curl_init($this->payment_params->url);
		curl_setopt($request, CURLOPT_HEADER, 0);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($request, CURLOPT_POST, true);
		curl_setopt($request, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
		$post_response = curl_exec($request);

		if($this->payment_params->debug){
			hikashop_writeToLog($post_response);
		}

		if(empty($post_response)){
			$this->app->enqueueMessage('The connection to the payment platform did not succeed. It is often caused by the hosting company blocking external connections so you should contact him for further guidance. The cURL error message was: '.curl_error($request),'error');
			$do = false;
			return false;
		}
		curl_close ($request);
		$response_array = explode("|",$post_response);
		$response_code        = (int)@$response_array[0];
		$response_subcode     = @$response_array[1];
		$response_reason_code = @$response_array[2];
		$response_reason_text = @$response_array[3];
		$transaction_id = @$response_array[6];

		$history = new stdClass();
		$history->notified=0;
		$history->amount= round($order->cart->full_total->prices[0]->price_value_with_tax,2).'USD';
		$history->data = '';
		if(!empty($transaction_id)) $history->data = 'Authorize.net transaction id: '.$transaction_id . "\r\n\r\n";

		$this->response_code = $response_code;

		switch($response_code) {
			case 2:
				$this->app->enqueueMessage(JText::_('TRANSACTION_DECLINED_WRONG_CARD'));
				$this->ccClear();
				$do = false;
				break;
			case 3:
			default:
				$this->app->enqueueMessage(JText::sprintf('TRANSACTION_PROCESSING_ERROR',$response_reason_code.' '.$response_reason_text));
				$this->ccClear();
				$do = false;
				break;
			case 1:
				$this->modifyOrder($order,$this->payment_params->verified_status,$history,false);
				break;
			case 4:
				$this->modifyOrder($order,$this->payment_params->pending_status,$history,false);
				break;
		}
		$this->vars = $vars;
		return true;
	}

	function onAfterOrderCreate(&$order,&$send_email){
		$this->loadOrderData($order);
		$this->loadPaymentParams($order);
		if(hikashop_isClient('administrator'))
			return true;
		if(empty($order->order_payment_method) || $order->order_payment_method != $this->name)
			return true;
		if(empty($this->payment_params))
			return false;

		if($this->payment_params->api != 'aim'){
			return true;
		}

		if(!empty($this->response_code)){
			switch($this->response_code){
				case 1:
					$email = new stdClass();
					$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Authorize.net','Accepted',$order->order_number);
					$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=listing';
					$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$order->order_number,HIKASHOP_LIVE);
					$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
					$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Authorize.net','Accepted')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->order_status)."\r\n\r\n".$order_text;
					$email->body = $body;

					$this->modifyOrder($order,$order->order_status,false,$email);
					break;
				case 4:
					$email = new stdClass();
					$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Authorize.net','Pending',$order->order_number);
					$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=listing';
					$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$order->order_number,HIKASHOP_LIVE);
					$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
					$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Authorize.net','Pending')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->order_status)."\r\n\r\n".$order_text;
					$email->body = $body;

					$this->modifyOrder($order,$order->order_status,false,$email);

					break;
			}
			$class = hikashop_get('class.cart');
			$class->cleanCartFromSession();
		}
	}

	function _loadStandardVars(&$order){

		$this->loadOrderData($order);
		$this->loadPaymentParams($order);

		$vars = array(
			"x_amount" => round($order->cart->full_total->prices[0]->price_value_with_tax,(int)$this->currency->currency_locale['int_frac_digits']),
			"x_currency_code" => $this->currency->currency_code,
			"x_version" => '3.1',
			"x_test_request" => (@$this->payment_params->test_payments?'TRUE':'FALSE'),
		);
		$vars["x_relay_response"] = 'FALSE';
		$vars["x_customer_ip"] = $order->order_ip;
		if(!empty($this->payment_params->duplicate_window) && strlen($this->payment_params->duplicate_window) > 0 && is_numeric($this->payment_params->duplicate_window)) {
			$vars["x_duplicate_window"] = $this->payment_params->duplicate_window;
		}
		if(!isset($this->payment_params->capture))
			$this->payment_params->capture=1;
		if($this->payment_params->capture){
			$vars["x_type"] = 'AUTH_CAPTURE';
		}else{
			$vars["x_type"] = 'AUTH_ONLY';
		}
		$vars["x_login"] = $this->payment_params->login_id;
		if(!empty($order->order_id)){
			$vars["x_invoice_num"] = $order->order_id;
			$vars["x_po_num"] = $vars["x_invoice_num"];
		}

		$vars["x_email"]=$this->user->user_email;

		if(!empty($order->cart->billing_address)){
			$vars["x_first_name"]=mb_substr(@$order->cart->billing_address->address_firstname,0,50);
			$vars["x_last_name"]=mb_substr(@$order->cart->billing_address->address_lastname,0,50);
			$vars["x_address"]=mb_substr(@$order->cart->billing_address->address_street,0,60);
			$vars["x_company"]=mb_substr(@$order->cart->billing_address->address_company,0,50);
			$vars["x_country"]=mb_substr(@$order->cart->billing_address->address_country->zone_name_english,0,60);
			$vars["x_zip"]=mb_substr(@$order->cart->billing_address->address_post_code,0,20);
			$vars["x_city"]=mb_substr(@$order->cart->billing_address->address_city,0,40);
			$vars["x_state"]=mb_substr(@$order->cart->billing_address->address_state->zone_name_english,0,40);
			$vars["x_phone"]=mb_substr(@$order->cart->billing_address->address_telephone,0,25);
		}
		if(!empty($order->cart->shipping_address)){
			$vars["x_ship_to_first_name"]=mb_substr(@$order->cart->shipping_address->address_firstname,0,50);
			$vars["x_ship_to_last_name"]=mb_substr(@$order->cart->shipping_address->address_lastname,0,50);
			$vars["x_ship_to_address"]=mb_substr(@$order->cart->shipping_address->address_street,0,60);
			$vars["x_ship_to_company"]=mb_substr(@$order->cart->shipping_address->address_company,0,50);
			$vars["x_ship_to_country"]=mb_substr(@$order->cart->shipping_address->address_country->zone_name_english,0,60);
			$vars["x_ship_to_zip"]=mb_substr(@$order->cart->shipping_address->address_post_code,0,20);
			$vars["x_ship_to_city"]=mb_substr(@$order->cart->shipping_address->address_city,0,40);
			$vars["x_ship_to_state"]=mb_substr(@$order->cart->shipping_address->address_state->zone_name_english,0,40);
		}

		if(isset($this->payment_params->details) && !$this->payment_params->details){
			return $vars;
		}

		$i = 1;
		$tax = 0;
		$vars["x_line_item"]=array();
		$config =& hikashop_config();
		$group = $config->get('group_options',0);
		foreach($order->cart->products as $product){
			if($group && !empty($product->order_product_option_parent_id))
				continue;
			if(bccomp(sprintf('%F',$product->order_product_tax),0,5)){
				$tax+=$product->order_product_quantity*round($product->order_product_tax,(int)$this->currency->currency_locale['int_frac_digits']);
				$has_tax = 'YES';
			}else{
				$has_tax = 'NO';
			}
			$vars["x_line_item"][]=mb_substr($product->order_product_code,0,30).'<|>'.mb_substr(strip_tags($product->order_product_name),0,30).'<|>'.mb_substr(strip_tags($product->order_product_name),0,30).'<|>'.$product->order_product_quantity.'<|>'.round($product->order_product_price,(int)$this->currency->currency_locale['int_frac_digits']).'<|>'.$has_tax;
		}

		if(!empty($order->cart->coupon) && @$order->cart->coupon->discount_value>0){
			$negative = '';
			if((int)@$this->payment_params->negative_coupon)
				$negative = '-';
			$vars["x_line_item"][]='coupon<|>'.JText::_('HIKASHOP_COUPON').'<|>'.JText::_('HIKASHOP_COUPON').'<|>1<|>'.$negative.round($order->cart->coupon->discount_value,(int)$this->currency->currency_locale['int_frac_digits']).'<|>N';
		}
		if(!empty($order->order_payment_price)){
			$vars["x_line_item"][]='payment<|>'.JText::_('HIKASHOP_PAYMENT').'<|>'.JText::_('HIKASHOP_PAYMENT').'<|>1<|>'.round($order->order_payment_price,(int)$this->currency->currency_locale['int_frac_digits']).'<|>N';
		}

		if(count($vars["x_line_item"])>=30){
			if(bccomp(sprintf('%F',$tax),0,5)){
				$has_tax = 'YES';
			}else{
				$has_tax = 'NO';
			}
			$vars["x_line_item"]=array(mb_substr(strip_tags(JText::_('HIKASHOP_FINAL_TOTAL')),0,30).'<|>'.mb_substr(strip_tags(JText::_('HIKASHOP_FINAL_TOTAL')),0,30).'<|><|>1<|>'.round($order->cart->full_total->prices[0]->price_value,(int)$this->currency->currency_locale['int_frac_digits']).'<|>'.$has_tax);
		}

		if(bccomp(sprintf('%F',$tax),0,5)){
			$vars['x_tax']=$tax;
			$vars['x_tax_exempt']='FALSE';
		}else{
			$vars['x_tax_exempt']='TRUE';
		}
		if(!empty($order->order_shipping_price)){
			$vars["x_freight"]=round($order->order_shipping_price,(int)$this->currency->currency_locale['int_frac_digits']);
		}
		return $vars;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		parent::onAfterOrderConfirm($order, $methods, $method_id);
		if(@$this->payment_params->api=='aim'){
			$viewType='thankyou';
			$this->payment_params->return_url =  HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$this->url_itemid;
			$this->removeCart = true;
		}else{
			$vars = $this->_loadStandardVars($order);
			$viewType = 'end';

			$this->payment_params->iframe = 1;

			$vars["x_show_form"] = 'PAYMENT_FORM';
			if(@$this->payment_params->notification){
				$vars["x_relay_url"] = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=authorize&tmpl=component&lang='.$this->locale.$this->url_itemid;
				$vars["x_relay_response"] = 'TRUE';
			}
			if($this->payment_params->api == 'dpm') {
				$vars["x_relay_url"] = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=authorize&x_po_num=' . (int)$order->order_id . '&tmpl=component&lang='.$this->locale.$this->url_itemid;
			}
			$vars["x_fp_sequence"] = $vars["x_invoice_num"];
			$vars["x_fp_timestamp"] = time();
			$vars["x_fp_hash"] = $this->calculateFP($vars["x_login"], $vars["x_amount"], $vars["x_fp_sequence"], $vars["x_fp_timestamp"], $vars["x_currency_code"]);
			if(!empty($this->payment_params->x_logo_url)){
				$vars['x_logo_url']=$this->payment_params->x_logo_url;
			}
			$this->vars = $vars;
		}
		if($this->payment_params->debug){
			hikashop_writeToLog($this->vars);
		}

		return $this->showPage($viewType);
	}

	function calculateFP($x_login, $x_amount, $x_fp_sequence, $x_fp_timestamp, $x_currency_code) {
		$data_to_hash = $x_login . "^" . $x_fp_sequence . "^" . $x_fp_timestamp . "^" . $x_amount . "^" . $x_currency_code;
		if(!empty($this->payment_params->signature_key)) {
			return hash_hmac('sha512', $data_to_hash, hex2bin($this->payment_params->signature_key));
		}
		return hash_hmac("md5", $data_to_hash, $this->payment_params->transaction_key);
	}

	function onPaymentNotification(&$statuses){
		$vars = array();
		$data = array();
		$filter = JFilterInput::getInstance();

		if($this->payment_params->debug){
			hikashop_writeToLog($_REQUEST);
		}
		foreach($_REQUEST as $key => $value){
			$key = $filter->clean($key);
			$value = hikaInput::get()->getString($key);
			$vars[$key]=$value;
		}
		$order_id = (int)@$vars['x_po_num'];

		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);
		if($this->payment_params->debug){
			hikashop_writeToLog($vars);
			hikashop_writeToLog($dbOrder);
		}
		if(empty($dbOrder)){
			echo "Could not load any order for your notification ".@$vars['x_po_num'];
			return 'Order unkown';
		}
		$this->payment_params->return_url =  HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order_id.$this->url_itemid;
		if(@$this->payment_params->api=='dpm' && @$_GET['iframe']){
			$vars = json_decode(base64_decode($vars['iframe']),true);
			$this->vars =& $vars;
			$name = 'end';
		}else{
			$name = 'thankyou';
		}

		ob_start();
		$this->showPage($name);
		$msg = ob_get_clean();

		if(@$this->payment_params->api=='dpm'&&@$_GET['iframe']){
			echo $msg;
			ob_start();
			return;
		}
		if(!$this->payment_params->notification){
			echo 'Notification not activated for authorize.net';
			return $msg;
		}

		$vars['x_Hash_calculated']=$this->hash($_REQUEST);

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		if(isset($vars['x_SHA2_Hash'])) {
			$vars['x_SHA2_Hash'] = strtoupper(@$vars['x_SHA2_Hash']);

			if ($vars['x_SHA2_Hash'] != $vars['x_Hash_calculated']) {

				$email = new stdClass();
				$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Authorize.net').'invalid response';
				$body = JText::sprintf("Hello,\r\n An Authorize.net notification was refused because the response from the Authorize.net server was invalid. The hash received was ".$vars['x_SHA2_Hash']." while the calculated hash was ".$vars['x_Hash_calculated'].". Please cehck that you're set the same signature key in Authorize.net and the plugin")."\r\n\r\n".$order_text;
				$email->body = $body;

				$this->modifyOrder($order_id, $this->payment_params->invalid_status,false,$email);

				if($this->payment_params->debug){
					echo 'invalid hash'."\n\n\n";
				}
				return 'Invalid notification.';
			}
		} else {
			$vars['x_MD5_Hash'] = strtoupper(@$vars['x_MD5_Hash']);

			if ($vars['x_MD5_Hash'] != $vars['x_Hash_calculated']) {

				$email = new stdClass();
				$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Authorize.net').'invalid response';
				$body = JText::sprintf("Hello,\r\n An Authorize.net notification was refused because the response from the Authorize.net server was invalid. The hash received was ".$vars['x_MD5_Hash']." while the calculated hash was ".$vars['x_Hash_calculated'].". Please cehck that you're set the same signature key in Authorize.net and the plugin")."\r\n\r\n".$order_text;
				$email->body = $body;

				$this->modifyOrder($order_id, $this->payment_params->invalid_status,false,$email);

				if($this->payment_params->debug){
					echo 'invalid hash'."\n\n\n";
				}
				return 'Invalid notification.';
			}
		}
		$vars['x_response_code']=(int)@$vars['x_response_code'];

		if(!in_array($vars['x_response_code'],array(1,4))) {
			if($vars['x_response_code']==2){
				$vars['payment_status']='Declined';
			}else{
				$vars['payment_status']='Error';
			}

			$email = new stdClass();
			$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Authorize.net',$vars['payment_status'],$dbOrder->order_number);
			$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Authorize.net',$vars['payment_status'])).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$order_text;
			$email->body = $body;

			$this->modifyOrder($order_id, $this->payment_params->invalid_status,false,$email);

			if($this->payment_params->debug){
				echo 'payment with code '.@$vars['x_response_code'].' : '.@$vars['x_response_reason_text']."\n\n\n";
			}
			return 'The transaction was declined';
		}

		if(!empty($vars['x_trans_id'])) echo 'Authorize.net transaction id: '.$vars['x_trans_id'] . "\r\n\r\n";

		$history = new stdClass();
		$history->notified=0;
		$history->amount= @$vars['x_amount'].'USD';
		$history->data = ob_get_clean();

	 	$price_check = round($dbOrder->order_full_price, 2 );
	 	if($price_check != @$vars['x_amount']){
	 		$email = new stdClass();
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Authorize.net').JText::_('INVALID_AMOUNT');
			$body = str_replace('<br/>',"\r\n",JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER','Authorize.net',$history->amount,$price_check.'USD'))."\r\n\r\n".$order_text;
			$email->body = $body;

			$this->modifyOrder($order_id, $this->payment_params->invalid_status,$history,$email);

	 		return $msg;
	 	}

	 	if($vars['x_response_code']==1){
	 		$order_status = $this->payment_params->verified_status;
	 		$vars['payment_status']='Accepted';
	 	}else{
	 		$order_status = $this->payment_params->pending_status;
	 		$order_text = @$vars['x_response_reason_text']."\r\n\r\n".$order_text;
	 		$vars['payment_status']='Pending';
	 	}

		if($dbOrder->order_status == $order_status)
			return true;

	 	$config =& hikashop_config();
		if($config->get('order_confirmed_status','confirmed') == $order_status){
			$history->notified=1;
		}

		$email = new stdClass();
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Authorize.net',$vars['payment_status'],$dbOrder->order_number);
		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Authorize.net',$vars['payment_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$statuses[$order_status])."\r\n\r\n".$order_text;
		$email->body = $body;

		$this->modifyOrder($order_id, $order_status, $history, $email);
		return $msg;
	}

	function onPaymentConfigurationSave(&$element) {
		if(empty($this->pluginConfig))
			return true;

		$formData = hikaInput::get()->get('data', array(), 'array');
		if(!isset($formData['payment']['payment_params']))
			return true;

		$app = JFactory::getApplication();

		if(empty($element->payment_params->md5_hash) && empty($element->payment_params->signature_key))
			$app->enqueueMessage(JText::sprintf('ENTER_INFO', 'Authorize.net', JText::_('AUTHORIZE_SIGNATURE_KEY')), 'error');

		return true;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='Authorize';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express,Discover';

		$element->payment_params->url='https://secure.authorize.net/gateway/transact.dll';
		$element->payment_params->api='sim';
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}

	function hash($vars) {
		if(!empty($this->payment_params->signature_key)) {
			$keys = array('x_trans_id','x_test_request','x_response_code','x_auth_code','x_cvv2_resp_code','x_cavv_response',
                'x_avs_code','x_method','x_account_number','x_amount','x_company','x_first_name','x_last_name','x_address',
                'x_city','x_state','x_zip','x_country','x_phone','x_fax','x_email','x_ship_to_company','x_ship_to_first_name',
                'x_ship_to_last_name','x_ship_to_address','x_ship_to_city','x_ship_to_state','x_ship_to_zip','x_ship_to_country',
                'x_invoice_num');
			$signature_key_hash = '^';
			foreach($keys as $key) {
				$signature_key_hash.= @$vars[$key].'^';
			}
			return strtoupper(hash_hmac("sha512", $signature_key_hash, hex2bin($this->payment_params->signature_key)));
		}

		if (@$vars['x_amount'] == '' || @$vars['x_amount'] == '0'){
			@$vars['x_amount'] = '0.00';
		}
		return strtoupper(md5(@$this->payment_params->md5_hash.@$this->payment_params->login_id.@$vars['x_trans_id'],@$vars['x_amount']));
	}
}

if(!function_exists('hash_hmac')){
	function hash_hmac($algo, $data, $key, $raw_output = false){
		$algo = strtolower($algo);
		$pack = 'H'.strlen($algo('test'));
		$size = 64;
		$opad = str_repeat(chr(0x5C), $size);
		$ipad = str_repeat(chr(0x36), $size);

		if (strlen($key) > $size) {
			$key = str_pad(pack($pack, $algo($key)), $size, chr(0x00));
		} else {
			$key = str_pad($key, $size, chr(0x00));
		}

		for ($i = 0; $i < strlen($key) - 1; $i++) {
			$opad[$i] = $opad[$i] ^ $key[$i];
			$ipad[$i] = $ipad[$i] ^ $key[$i];
		}

		$output = $algo($opad.pack($pack, $algo($ipad.$data)));

		return ($raw_output) ? pack($pack, $output) : $output;
	}
}
