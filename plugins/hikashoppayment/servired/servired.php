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

defined('_JEXEC') or die('Restricted access');

class plgHikashoppaymentservired extends hikashopPaymentPlugin {

	var $multiple = true;
	var $name = 'servired';

	var $sync_currencies = array('EUR'=>'978','USD'=>'840');
	var $accepted_currencies = array('EUR','USD');

	var $pluginConfig = array(
		'merchantId' => array('Shop Id', 'input'),
		'terminalId' => array('Terminal ID', 'input'),
		'encriptionKey' => array('Encryption Key', 'input'),
		'paymethods' => array('payment methods', 'list', array(
			'z' => 'Bizum',
			'C' => 'Card only',
			'R' => 'Payment by transfer',
			'T' => 'card + iupay',
			'0' => 'user selection'
		)),
		'testmode' => array('Test mode', 'boolean', '0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		if (empty ($this->payment_params->merchantId) ) {
			$this->app->enqueueMessage('You have to configure a Merchant Id for the Servired plugin payment first : check your plugin\'s parameters, on your website backend','error');
			return false;
		}
		if (empty($this->payment_params->terminalId)) {
			$this->app->enqueueMessage('You have to configure the Terminal ID for the Servired plugin payment first : check your plugin\'s parameters, on your website backend','error');
			return false;
		}
		if (empty($this->payment_params->encriptionKey)) {
			$this->app->enqueueMessage('You have to configure the Encription Key for the Servired plugin payment first : check your plugin\'s parameters, on your website backend','error');
			return false;
		}

		if ($this->payment_params->testmode) {
			$url = 'https://sis-t.redsys.es:25443/sis/realizarPago'; }
		else {
			$url = 'https://sis.redsys.es/sis/realizarPago'; }

		$key = $this->payment_params->encriptionKey;
		$code = $this->payment_params->merchantId;
		$terminal = $this->payment_params->terminalId;

		$url_OK = HIKASHOP_LIVE . "index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id=" . $order->order_id.'&lang='.$this->locale;
		$url_KO = HIKASHOP_LIVE . "index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id=" . $order->order_id.'&lang='.$this->locale;
		$urlMerchant = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&tmpl=component&lang='.$this->locale . $this->url_itemid;
		$amount = round($order->cart->full_total->prices[0]->price_value_with_tax,2)*100;
		$orderServired = $order->order_id;

		if ( (strlen($orderServired ) ) == 3) {
			$orderServired = '0' . $orderServired; }
		if ( (strlen($orderServired ) ) == 2) {
			$orderServired = '00' . $orderServired; }
		if ( (strlen($orderServired ) ) == 1) {
			$orderServired = '000' . $orderServired; }

		if($this->currency->currency_code=='USD') {
			$currency = '840'; }
		else {
			$currency = '978'; }


		$language = '0';
		$lang = JFactory::getLanguage();
		$locale=strtoupper(substr($lang->get('tag'),0,2));

		switch($locale) {
			case 'es':
				$language = '001';
				break;
			case 'en':
				$language = '002';
				break;
			case 'ca':
				$language = '003';
				break;
			case 'fr':
				$language = '004';
				break;
			case 'de':
				$language = '005';
				break;
			case 'nl':
				$language = '006';
				break;
			case 'it':
				$language = '007';
				break;
			case 'sv':
				$language = '008';
				break;
			case 'pt':
				$language = '009';
				break;
			case 'pl':
				$language = '011';
				break;
			case 'gl':
				$language = '013';
				break;
			default: //default to english for other languages
				$language = '002';
				break;
		}

		$params = array(
			"DS_MERCHANT_AMOUNT" =>  (string)$amount ,
			"DS_MERCHANT_ORDER" =>(string)$orderServired ,
			"DS_MERCHANT_MERCHANTCODE" => (string)$code ,
			"DS_MERCHANT_CURRENCY" => (string)$currency ,
			"DS_MERCHANT_TRANSACTIONTYPE" => "0",
			"DS_MERCHANT_TERMINAL" => (string)$terminal ,
			"DS_MERCHANT_MERCHANTURL" => (string)$urlMerchant ,
			"DS_MERCHANT_URLOK" => (string)$url_OK ,
			"DS_MERCHANT_URLKO"=> (string)$url_KO ,
			"DS_MERCHANT_CONSUMERLANGUAGE" => (string)$language
		);

		if ((isset($this->payment_params->paymethods)) && ($this->payment_params->paymethods != '0')) {
			$params["DS_MERCHANT_PAYMETHODS"] = $this->payment_params->paymethods;
		}

		$json = json_encode($params);

		if($this->payment_params->debug) {
			$this->writeToLog("Data sent to Servired : \n\n\n");
			$this->writeToLog(print_r($params,true));
		}

		$encodedparams = base64_encode($json);

		$signature = $this->createsign($encodedparams, $orderServired, $key);

		$this->payment_params->url = $url;

		$vars = array(
			'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
			'Ds_MerchantParameters' => $encodedparams,
			'Ds_Signature' => $signature,
		);

		if($this->payment_params->debug) {
			$this->writeToLog("complet data sent to Servired : \n\n\n");
			$this->writeToLog(print_r($vars,true));
		}

		$this->vars = $vars;
		return $this->showPage('end');
	}

	function onPaymentNotification(&$statuses){


		$decodedParmeters = base64_decode ($_POST["Ds_MerchantParameters"]);

		$dataobject = json_decode($decodedParmeters);

		$order_id = $dataobject->Ds_Order;

		if ( substr($order_id, 0, 3) == '000') {
			$order_id = substr($order_id, 3); }
		if ( substr($order_id, 0, 2) == '00') {
			$order_id = substr($order_id, 2); }
		if ( substr($order_id, 0, 1) == '0') {
			$order_id = substr($order_id, 1); }

		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		$this->loadOrderData($dbOrder);

		if(empty($this->payment_params) ) {

			if($this->payment_params->debug)
			{
				$this->writeToLog("Unable to read Servired Response : Unknow Order! \n\n");
				$this->writeToLog(print_r($order_id,true));
			}
			return false;
		}

		if($this->payment_params->debug) {
			$arraydata = get_object_vars($dataobject);

			$this->writeToLog("Data recieved from Servired :\n\n ");
			$this->writeToLog(print_r($arraydata,true))."\n\n\n";
		}

		$encodedparams = $_POST["Ds_MerchantParameters"];
		$key = $this->payment_params->encriptionKey;

		$notifysign = $this->createsign($encodedparams, $dataobject->Ds_Order, $key);

		if($this->payment_params->debug) {
			$this->writeToLog("Signature (before corrected!) from Servired notification :\n\n ");
			$this->writeToLog($_POST["Ds_Signature"]);
		}
		$receivedsign = $_POST["Ds_Signature"];
		if ( (false !== strpos($receivedsign, "-") ) || (false !== strpos($receivedsign, "_") ) ) {

			$target = array('-', '_');
			$replace = array('+', '/');
			$ServiredSign = str_replace($target, $replace, $receivedsign);
		}
		else {
			$ServiredSign = $receivedsign;
		}
		if($notifysign != $ServiredSign) {
			if($this->payment_params->debug) {

				$this->writeToLog("Signature mismatch :\n\n Signature (corrected!) from Servired notification :\n\n ");
				$this->writeToLog($ServiredSign);
				$this->writeToLog("From HikaShop processing :\n\n ");
				$this->writeToLog($notifysign);
			}
			return('Invalid Signature !');
		}

		$DS_RESPONSE=(int)$dataobject->Ds_Response;

		$ServiredAmount = (string)$dataobject->Ds_Amount . $this->currency->currency_code;

		if ( $DS_RESPONSE>=0 && $DS_RESPONSE<100) {

			$history = new stdClass();
			$history->notified=1;
			$history->data = ob_get_clean();
			$history->amount= $ServiredAmount;

			$this->modifyOrder($order_id, $this->payment_params->verified_status, $history, true);

			echo "FIRMA OK";
			return true;
		}
		else {

			$DS_RESPONSE=(string)$dataobject->Ds_Response;

			$errorarray = array(
				'101' => 'Expired card',
				'102' => 'Card in transitional exception or under suspected fraud',
				'106' => 'PIN attempts exceeded',
				'125' => 'Ineffective Card',
				'129' => 'Security code (CVV2/CVC2) incorrect',
				'180' => 'Card unrelated to the service',
				'184' => 'Error in cardholder authentication',
				'190' => 'Denial of the issuer without specifying reason',
				'191' => 'Erroneous expiry date',
				'202' => 'Card in transitional exception or under suspected fraud with card withdrawal',
				'904' => 'Merchant not registered in FUC',
				'909' => 'System error',
				'913' => 'Repeated order',
				'944' => 'Incorrect Session',
				'950' => 'Refund transaction not permitted',
				'9912' => 'Issuer not available',
				'912' => 'Issuer not available',
				'9064' => 'Incorrect card number of positions',
				'9078' => 'Transaction type not allowed for that card',
				'9093' => 'Nonexistent card',
				'9094' => 'International servers rejection',
				'9104' => 'Merchant with "insurance holder" and cardholder without secure purchase key',
				'9218' => 'The merchant does not accept secure transactions per input/transactions',
				'9253' => 'Card fails check-digit',
				'9256' => 'The merchant cannot perform preauthorisations',
				'9257' => 'This card does not allow preauthorisation operations',
				'9261' => 'Transaction retained due to exceeding the restrictions control in the SIS',
				'9913' => 'Error in the confirmation which the merchant sends to the Virtual POS (only applicable in the SOAP synchronisation option)',
				'9914' => '"KO" confirmation of the merchant (only applicable in the SOAP synchronisation option)',
				'9915' => 'At the request of the user the payment has been cancelled',
				'9928' => 'Cancellation of deferred authorisation performed by the SIS (batch process)',
				'9929' => 'Cancellation of deferred authorisation performed by the merchant',
				'9997' => 'Is being processed in another transaction in the SIS with the same card',
				'9998' => 'Transaction in application process of the card data',
				'9999' => 'Transaction has been redirected to the issuer for authentication'
			);

			foreach($errorarray as $respCode => $msg) {
				if ($DS_RESPONSE == $respCode)
					$match = $msg;
			}

			$details =  (isset($match) ) ? $match : 'HikaShop: Unknown response code from Servired: ' . $DS_RESPONSE;

			if($this->payment_params->debug) {
				$this->writeToLog("Refused status from Servired :\n\n ");
				$this->writeToLog($details,true)."\n\n\n";
			}

			$history = new stdClass();
			$history->notified=1;
			$history->data = ob_get_clean();
			$history->amount= $ServiredAmount;

			$this->modifyOrder($order_id, $this->payment_params->invalid_status, $history, false);

			echo "FIRMA KO";
			return false;
		}
	}

	function createsign($encodedparams, $orderServired, $key) {
		$decodekey = base64_decode($key);

		$iv = str_repeat("\x00", 8);

		$message_padded = $orderServired;
		if (strlen($message_padded) % 8) {
		    $message_padded = str_pad($message_padded,
		        strlen($message_padded) + 8 - strlen($message_padded) % 8, "\0");
		}
		$encryptkey = openssl_encrypt($message_padded, "DES-EDE3-CBC", $decodekey, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);

		$HmacSign = hash_hmac('sha256', $encodedparams, $encryptkey, true);

		$signature = base64_encode($HmacSign);

		return $signature;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='SERVIRED';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
		$element->payment_params->notification= 1;
		$element->payment_params->testmode= 0;
		$element->payment_params->debug = 0;
		$element->payment_params->paymethods = '0';
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}
}
