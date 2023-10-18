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
class plgHikashoppaymentPaygate extends hikashopPaymentPlugin
{
	var $multiple = true;
	var $name = 'paygate';
	var $doc_form = 'paygate';
	var $srv_url = 'https://secure.paygate.co.za/payweb3/';

	var $pluginConfig = array(
		'identifier' => array("Identifier",'input'),
		'key' => array('Key','input'),
		'sandbox' => array('SANDBOX', 'boolean','0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);


	function __construct(&$subject, $config)
	{
		return parent::__construct($subject, $config);
	}


	function onAfterOrderConfirm(&$order,&$methods,$method_id) //On the checkout
	{
		parent::onAfterOrderConfirm($order,$methods,$method_id);

		if (empty($this->payment_params->identifier))
		{
			$this->app->enqueueMessage(JText::sprintf('CONFIGURE_X_PAYMENT_PLUGIN_ERROR','an identifer','Paygate'),'error');
			return false;
		}

		if (empty($this->payment_params->key))
		{
			$this->app->enqueueMessage(JText::sprintf('CONFIGURE_X_PAYMENT_PLUGIN_ERROR','a key','Paygate'),'error');
			return false;
		}

		$date = date('Y-m-d h:i:s');
		$reference = $order->order_id.'-'.$order->order_number;
		$currency = $this->currency->currency_code;
		$amout =round($order->cart->full_total->prices[0]->price_value_with_tax,2)*100;
		$notif_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&tmpl=component&lang='.$this->locale . $this->url_itemid;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id . $this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id . $this->url_itemid;

		if ($this->payment_params->sandbox)
		{
			$id = '10011072130';
			$key = 'secret';
		}
		else
		{
			$id = $this->payment_params->identifier;
			$key = $this->payment_params->key;
		}

		$vars = array(
			'PAYGATE_ID' => $id,
			'REFERENCE' => $reference,
			'AMOUNT' => $amout,
			'CURRENCY' => $currency,
			'RETURN_URL' => $return_url,
			'TRANSACTION_DATE' => $date, //YYYY-MM-DD HH:MM:SS
			'LOCALE' => $this->locale,
			'COUNTRY' => $order->cart->billing_address->address_country->zone_code_3,
			'EMAIL' => $this->user->user_email,
			'NOTIFY_URL' => $notif_url,
		);

		$vars['CHECKSUM'] = $this->paygate_signature($key,$vars);

		if ($this->payment_params->debug)
			var_dump($vars);

		$session = curl_init();
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($session, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($session, CURLOPT_POST,           1);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($session, CURLOPT_VERBOSE,        1);
		curl_setopt($session, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($session, CURLOPT_SSLVERSION, 6);
		curl_setopt($session, CURLOPT_FAILONERROR,    true);

		$tmp = array();
		foreach($vars as $k => $v) {
			$tmp[] = $k . '=' . urlencode(trim($v));
		}
		$tmp = implode('&', $tmp);

		curl_setopt($session, CURLOPT_URL, $this->srv_url.'initiate.trans');
		$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);
		curl_setopt($session, CURLOPT_REFERER, $httpsHikashop);
		curl_setopt($session, CURLOPT_POSTFIELDS, $tmp);

		$data = curl_exec($session);
		$error = curl_errno($session);
		curl_close($session);

		if( $error ) {
			$this->app->enqueueMessage('An error occured: '.$error);
			return false;
		}

		$params = explode('&', $data);
		$ret = array();
		foreach($params as $p) {
			$t = explode('=', $p);
			$ret[strtoupper($t[0])] = $t[1];
		}

		if( $this->payment_params->debug ) {
			var_dump($ret);
			if(!isset($ret['PAYGATE_ID']))
				echo '<pre>'.print_r($data, true).'</pre>';
		}

		if($ret['PAYGATE_ID'] != $vars['PAYGATE_ID']){
			$this->app->enqueueMessage('An error occured: The data was altered and PayGate couldn\'t validate the checksum.');
			return false;
		}

		unset($ret['CHECKSUM']);

		$vars = array(
			'PAY_REQUEST_ID' => $ret['PAY_REQUEST_ID'],
			'CHECKSUM' => $this->paygate_signature($key, $ret)
		);


		if( $this->payment_params->debug ) {
			echo print_r($vars, true) . "\n\n\n";
		}

		$this->vars = $vars;
		$this->url = $this->srv_url . 'process.trans';

		return $this->showPage('end');

	}


	function onPaymentConfiguration(&$element)
	{
		parent::onPaymentConfiguration($element);
	}

	function getPaymentDefaultValues(&$element) //To set the back end default values
	{
		$element->payment_name='Paygate';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->verified_status='confirmed';
	}


	function onPaymentNotification(&$statuses)
	{
		$vars = array();
		$filter = JFilterInput::getInstance();
		foreach($_REQUEST as $key => $value)
		{
			$key = $filter->clean($key);
			$value = hikaInput::get()->getString($key);
			$vars[$key]=$value;
		}

		$explode = explode('-',$vars['REFERENCE']);
		$order_id = $explode[0];
		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);

		$checksum = $this->paygate_signature($this->payment_params->key,$vars,false,true);
		if($this->payment_params->debug)
		{
			$this->writeToLog($vars);
			$this->writeToLog($this->payment_params);
			$this->writeToLog($checksum);
		}

		if (strcasecmp($checksum,$vars['CHECKSUM'])!=0)
		{
			if($this->payment_params->debug)
				$this->writeToLog('Hash error '.$vars['CHECKSUM'].' - '.$checksum."\n\n\n");
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);
		}
		elseif (strcmp($order_id.'-'.$dbOrder->order_number,$vars['REFERENCE'])!=0)
		{
			if($this->payment_params->debug)
				$this->writeToLog('Reference error '.$vars['REFERENCE'].' - '.$this->payment_params->reference."\n\n\n");
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);
		}
		elseif(substr($vars['RISK_INDICATOR'],0,1)=='N')
		{
			if($this->payment_params->debug)
				$this->writeToLog('Card validation error : '.$vars['RISK_INDICATOR']." - Authentication was attempted but NOT successful..\n\n\n");
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);
		}
		elseif($vars['TRANSACTION_STATUS']!='1' or $vars['RESULT_CODE']!='990017')
		{
			if($this->payment_params->debug)
				$this->writeToLog('The payment has been declined. Transaction status : '.$vars['TRANSACTION_STATUS'].' / '.$vars['RESULT_CODE'].' - '.$vars['RESULT_DESC']."\n\n\n");
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);
		}
		else
		{
			$this->modifyOrder($order_id, $this->payment_params->verified_status, true, true);
		}
		echo 'OK';
		exit;
	}


	function paygate_signature($pwd, $parameters, $debug=false, $decode=false)
	{
		$clear_string = '';
		$expectedKey = array (
			'PAYGATE_ID',
			'PAY_REQUEST_ID',
			'REFERENCE',
			'TRANSACTION_STATUS',
			'RESULT_CODE',
			'AUTH_CODE',
			'CURRENCY',
			'AMOUNT',
			'RESULT_DESC',
			'TRANSACTION_ID',
			'RISK_INDICATOR',
			'PAY_METHOD',
			'PAY_METHOD_DETAIL'
		);

		foreach ($parameters as $key => $value)
		{
			if ($decode)
			{
				if (in_array($key,$expectedKey))
					$clear_string .= $value;
			}
			else
				$clear_string .= $value;
		}
		$clear_string .= $pwd;


		if (PHP_VERSION_ID < 50102) //Php >= 5.1.2 needed
		{
			$this->app->enqueueMessage('The Paygate payment plugin requires at least the PHP 5.1.2 version to work, but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}
		else
		{
			if ($debug)
				return $clear_string;
			else
				return hash('md5', $clear_string);
		}
	}

}
