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
class plgHikashoppaymentAtossips extends hikashopPaymentPlugin {

	var $multiple = true;
	var $name = 'atossips';
	var $uniq_merchant = true;

	var $reponse_codes = array (
		'00'=>"Transaction approved or processed successfully",
		'02'=>"Contact card issuer",
		'03'=>"Invalid acceptor",
		'04'=>"Retain the card",
		'05'=>"Do not honour",
		'07'=>"Retain the card, special circumstances",
		'08'=>"Approve after obtaining identification",
		'12'=>"Invalid transaction",
		'13'=>"Invalid amount",
		'14'=>"Invalid cardholder number",
		'15'=>"Card issuer unknown",
		'17'=>"Transaction cancelled by customer",
		'30'=>"Format error",
		'31'=>"Identifier of acquirer entity unknown",
		'33'=>"Card is past expiry date",
		'34'=>"Suspicion of fraud",
		'41'=>"Card lost",
		'43'=>"Card stolen",
		'51'=>"Insufficient funds or credit limit exceeded",
		'54'=>"Card is past expiry date",
		'56'=>"Card missing from file",
		'57'=>"Transaction not permitted for this cardholder",
		'58'=>"Transaction prohibited at terminal",
		'59'=>"Suspicion of fraud",
		'60'=>"The acceptor of the card must contact the Acquirer",
		'61'=>"Exceeds the withdrawal amount limit",
		'63'=>"Security rules not observed",
		'68'=>"Response not received or received too late",
		'90'=>"Momentary system crash",
		'91'=>"Card issuer inaccessible",
		'96'=>"System functioning incorrectly",
		'97'=>"Expiry of the global monitoring delay",
		'98'=>"Server unavailable network routing further request",
		'99'=>"Incident field initiator"
	);
	var $bank_request = array(
		'default_test' => 'https://payment-webinit.simu.sips-services.com/paymentInit',
		'default_prod' => 'https://payment-webinit.sips-services.com/paymentInit',
		'bnp_test' => 'https://payment-webinit.simu.mercanet.bnpparibas.net/paymentInit',
		'bnp_prod' => 'https://payment-webinit.mercanet.bnpparibas.net/paymentInit'
	);

	var $sync_currencies = array(
		'EUR'=>'978','USD'=>'840','GBP'=>'826','JPY'=>'392','CAD'=>'124','AUD'=>'036','CHF'=>'756',
		'MXN'=>'484','TRY'=>'949','NZD'=>'554','NOK'=>'578','BRL'=>'986','ARS'=>'032','KHR'=>'116',
		'TWD'=>'901','SEK'=>'752','DKK'=>'208','KRW'=>'410','SGD'=>'702','XAF'=>'952'
	);

	var $accepted_currencies = array(
		'EUR','USD','GBP','JPY','CAD','AUD','CHF',
		'MXN','TRY','NZD','NOK','BRL','ARS','KHR',
		'TWD','SEK','DKK','KRW','SGD','XAF'
	);


	var $pluginConfig = array(
		'merchantID' => array("MERCHANT_ID",'input'),
		'secretKey'=> array("Secret Key",'input'),
		'keyVersion'=> array('Key Version','input'),
		'url' => array('URL','input'),
		'notification'=> array("ALLOW_NOTIFICATIONS_FROM_X",'boolean','0'),
		'mode' => array('Mode', 'list', array(
			'simplified' =>'V2 simplifié',
			'default' => 'V2 normal',
			'no_transaction_reference' => 'V2 avec option de génération automatique du transaction référence dans le contrat',
		)),
		'instalments'=> array('Paiement multiple (maximum 3)', 'input'),
		'period'=> array('délai entre les échéances en jours (maximum 30)', 'input','30'),
		'force_instalments'=> array('FORCE_MULTIPLE_PAYMENTS', 'boolean','0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
	);
	function __construct(&$subject, $config)
	{
		$this->pluginConfig['notification'][0] =  JText::sprintf('ALLOW_NOTIFICATIONS_FROM_X','WordLine SIPS');
		return parent::__construct($subject, $config);
	}

	function needCC(&$method){
		if(@$method->payment_params->period<100 && @$method->payment_params->period>0 && @$method->payment_params->instalments<=50 && @$method->payment_params->instalments>=2 && @$method->payment_params->force_instalments==0){
			$onclick = '';
			$config = hikashop_config();
			if($config->get('auto_submit_methods',1)){
				$onclick = ' onclick="this.form.action=this.form.action+\'#hikashop_payment_methods\';this.form.submit(); return false;"';
			}
			$method->custom_html='<span style="margin-left:10%">'.JHTML::_('hikaselect.booleanlist', "hikashop_multiple_instalments", '',  $onclick, JText::sprintf( 'PAYMENT_IN_X_TIME' , $method->payment_params->instalments ), JText::sprintf( 'PAY_FULL_ORDER' , '1') ).'</span>';
		}
	}

	function onPaymentSave(&$cart, &$rates, &$payment_id) {
		$_SESSION['hikashop_multiple_instalments'] = @$_REQUEST['hikashop_multiple_instalments'];
		return parent::onPaymentSave($cart, $rates, $payment_id);
	}

	function onPaymentConfiguration(&$element) {
		parent::onPaymentConfiguration($element);
		$this->_checkURL($element);
	}
	function _checkURL(&$element) {
		if(empty($element->payment_params->url)) {
			if(!empty($element->payment_params->bank) && isset($element->payment_params->testmode)) {
				$bank = $element->payment_params->bank;
				$environnement = ($element->payment_params->testmode == 0)? 'prod': 'test';

				$url = @$this->bank_request[$bank.'_'.$environnement];
				$element->payment_params->url = $url;
			}
		}
	}
	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		if (empty ($this->payment_params->merchantID) )
		{
			$this->app->enqueueMessage('You have to configure a merchant ID for the Worldline SIPS plugin payment first : check your payment method parameters, on your website backend','error');
			return false;
		}

		if (empty($this->payment_params->keyVersion))
		{
			$this->app->enqueueMessage('You have to configure the Key Version for the Worldline SIPS plugin payment first : check your payment method parameters, on your website backend','error');
			return false;
		}
		if (empty($this->payment_params->secretKey))
		{
			$this->app->enqueueMessage('You have to configure the Secret Key for the WorldLine SIPS plugin payment first : check your payment method parameters, on your website backend','error');
			return false;
		}

		$this->_checkURL($this);
		if (empty($this->payment_params->url))
		{
			$this->app->enqueueMessage('You have to configure the URL for the WorldLine SIPS plugin payment first : check your payment method parameters, on your website backend','error');
			return false;
		}

		$PostUrl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&tmpl=component&lang='.$this->locale . $this->url_itemid;
		$userPostUrl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&tmpl=component&user_return=1&lang='.$this->locale .$this->url_itemid;

		$refName = 'transactionReference';
		$ref = $order->order_id;
		switch(@$this->payment_params->mode) {
			case 'simplified':
				$ref = $this->compute_transactionId();
				$transactionRef = array("s10TransactionId" => $ref);
				$refName = 's10TransactionReference';
				break;
			case 'no_transaction_reference':
				$transactionRef = '';
				break;
			case 'default':
			default:
				$transactionRef = $order->order_id;
				break;
		}

		if (empty($this->payment_params->bank) || ($this->payment_params->bank == 'socg')) {
			$this->payment_params->bank = 'default';
		}

		$vars0 = array(
			"currencyCode" => @$this->sync_currencies[$this->currency->currency_code],
			"merchantId" => trim($this->payment_params->merchantID),
			"normalReturnUrl" => $userPostUrl,
			"amount" => str_replace(array('.',','),'',round($order->cart->full_total->prices[0]->price_value_with_tax,2)*100),
			$refName => $transactionRef,
			"keyVersion" => trim ($this->payment_params->keyVersion),
			"automaticResponseUrl" => $PostUrl,
			"orderId" => $order->order_id,
			"statementReference" => $order->order_number //add the order number in the merchant bank account:
		);

		if(@$this->payment_params->period<100 && @$this->payment_params->period>0 && $this->payment_params->instalments>=2 && $this->payment_params->instalments<=50 && ($this->payment_params->force_instalments==1 || @$_SESSION['hikashop_multiple_instalments']==1)){
			$vars0["paymentPattern"] = "INSTALMENT";
			$dates = array();
			$amounts = array();
			$tref = array();
			$instalment_amount = str_replace(array('.',','),'', round($order->cart->full_total->prices[0]->price_value_with_tax / $this->payment_params->instalments, 2) * 100);

			$unixTime = time();
			$config = JFactory::getConfig();
			if(!HIKASHOP_J30){
				$timeZone = $config->getValue('config.offset');
			} else {
				$timeZone = $config->get('offset');
			}
			$timeZone = new DateTimeZone($timeZone);
			$time = new DateTime();
			$time->setTimestamp($unixTime)->setTimezone($timeZone);

			if(@$this->payment_params->mode == 'no_transaction_reference') {
				$vars0[$refName] = $ref;
			}

			$rest = round($order->cart->full_total->prices[0]->price_value_with_tax, 2);
			$instalment_amount = round($order->cart->full_total->prices[0]->price_value_with_tax / $this->payment_params->instalments, 2);

			for($i = 1; $i <= $this->payment_params->instalments; $i++) {
				if($i == $this->payment_params->instalments) {
					$instalment_amount = $rest;
				} else {
					$rest = $rest - $instalment_amount;
				}
				$amounts[] = str_replace(array('.',','),'', $instalment_amount * 100);
				if($i != 1)
					$transactionRef = $this->compute_transactionId();
				else
					$transactionRef = $ref;
				$tref[] = $transactionRef;
				$dates[] = $time->format('Ymd');
				$time->add(new DateInterval('P'.$this->payment_params->period.'D'));
			}
			$tref_name = 'transactionReferencesList';
			if(@$this->payment_params->mode == 'simplified') {
				$tref_name = 's10TransactionIdsList';
			}
			$vars0["instalmentData"] = array(
				'number' => $this->payment_params->instalments,
				'datesList' => $dates,
				$tref_name => $tref,
				'amountsList' => $amounts,
			);
		}

		if($this->payment_params->debug)
		{
			$this->writeToLog("Data vars for Atos Sips: \n\n\n");
			$this->writeToLog(print_r($vars0,true));
		}


		$data = $this->flatten_to_sips_payload($vars0);

		$secretKey = utf8_encode($this->payment_params->secretKey);

		$seal = hash('sha256', utf8_encode ($data.$secretKey) );

		$vars = array (
			"Data" => $data,
			"InterfaceVersion" => "HP_2.3",
			"Seal" => $seal,
		);

		if($this->payment_params->debug)
		{
			$this->writeToLog("Data sent to Atos Sips: \n\n\n");
			$this->writeToLog(print_r($vars,true));
			$this->writeToLog("payment server url : \n\n\n");
			$this->writeToLog($url,true);
		}

		$this->vars = $vars;
		return $this->showPage('end');

	}

	function onPaymentNotification(&$statuses){

		parse_str(strtr($_POST["Data"], '=|', '=&'), $arr);
		$response = array_map('trim', $arr);

		$order_id = (int)$response["orderId"];
		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);

		if($this->payment_params->debug) {

			$this->writeToLog("Data recieved from Worldline SIPS :\n\n ");
			$this->writeToLog($_POST["Data"]);
		}

		if(empty($this->payment_params) ) {

			if($this->payment_params->debug)
			{
				$this->writeToLog("Unable to read Worldline SIPS Response or Unknow Order! \n\n");
			}
			return false;
		}
		$this->loadOrderData($dbOrder);

		$user_return = !empty($_GET['user_return']);

		if($user_return && $dbOrder->order_status == $this->payment_params->verified_status) {
			$EndUrl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id=' . $order_id . $this->url_itemid;
			$this->app->redirect($EndUrl);
		}

		$secretKey = utf8_encode($this->payment_params->secretKey);

		$seal = hash('sha256', utf8_encode ($_POST["Data"].$secretKey) );

		if ($seal != $_POST["Seal"]) {

			if($this->payment_params->debug)
			{
				$this->writeToLog("Seals mismatch !!! Data has been modified! \n See the generated hash: \n\n");
				$this->writeToLog($seal,true);
				$this->writeToLog("compare with the recieved one :\n");
				$this->writeToLog($_POST["Seal"],true);
			}
			return('Invalid Seal!');
		}

		$amount = str_replace(array('.',','),'',round($dbOrder->order_full_price,2,2)*100);

		if ($amount != $response["amount"]) {

			if($this->payment_params->debug)
			{
				$this->writeToLog("Amount mismatch !!!\n Waited amount: " . $amount . ",\n");
				$this->writeToLog("Amount recieved: " . $response["amount"] . "\n");
			}

			return('Invalid Amount!');
		}


		if(!isset($response["acquirerResponseCode"]) && !empty($response["responseCode"]))
			$response["acquirerResponseCode"] = $response["responseCode"];

		$repCode = trim ($response["acquirerResponseCode"]);
		$notified = 0;

		$EndUrl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$dbOrder->order_id . $this->url_itemid;

		switch( $repCode ) {

			case '00':

				$details =  @$this->reponse_codes[$repCode];
				$details = "Response from Worldline SIPS " . $details . "\n\r /Worldline SIPS authorisation Id :".$response["authorisationId"];

				$status = "Accepted";

				$message ="";

				$order_status = $this->payment_params->verified_status;

				$EndUrl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$dbOrder->order_id . $this->url_itemid;

				$notified = 1;

				break;

			case '08':

				$details =  @$this->reponse_codes[$repCode];
				$details = "Response from Worldline SIPS " . $details . " : Pending status.\n\r /Worldline SIPS authorisation Id :".$response["authorisationId"];

				$status = "Pending";

				$message ="";

				$order_status = $this->payment_params->pending_status;

				$EndUrl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$dbOrder->order_id . $this->url_itemid;

				break;

			case '02':
			case '03':
			case '04':
			case '05':
			case '07':
			case '12':
			case '13':
			case '14':
			case '15':
			case '30':
			case '31':
			case '33':
			case '34':
			case '41':
			case '43':
			case '51':
			case '54':
			case '56':
			case '57':
			case '58':
			case '59':
			case '60':
			case '61':
			case '63':
			case '68':
			case '90':
			case '91':
			case '96':
			case '97':
			case '98':
			case '99':

				$details =  @$this->reponse_codes[$repCode];
				$details = "Response from Worldline SIPS " . $details . " : Invalid status.\n\r /Worldline SIPS authorisation Id :".$response["authorisationId"];

				$status = "Declined";

				$message =JText::_("TRANSACTION_DECLINED");

				$order_status = $this->payment_params->invalid_status;

				break;

			case '17':
				$session_order_id = $this->app->getUserState(HIKASHOP_COMPONENT.'.order_id');
				if($order_status != $dbOrder->order_status && empty($session_order_id)) {
					$details =  @$this->reponse_codes[$repCode];
					$details = "Response from Worldline SIPS " . $details;

					$status = "Cancelled by customer";

					$message =JText::_("ORDER_STATUS_CANCELLED");

					$order_status = $this->payment_params->invalid_status;
				} else {
					$this->app->redirect($EndUrl);
				}
				break;

			default:

				$details = "Unknown response from Worldline SIPS " . $repCode."\n\r /Worldline SIPS authorisation Id :".$response["authorisationId"];

				$status = "Declined";

				$message =JText::_("TRANSACTION_DECLINED");

				$order_status = $this->payment_params->invalid_status;

				break;
		}

		if($order_status == $dbOrder->order_status) {
			$this->writeToLog('status already there !');
			if($user_return) {
				$this->app->enqueueMessage($message);
				$this->app->redirect($EndUrl);
			}
			return;
		}

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		$email = new stdClass();
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Atos Sips',$status,$dbOrder->order_number);
		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Atos Sips',$status)).' '.JText::_('STATUS_NOT_CHANGED')
		."\r\n" . $details . "\r\n".$order_text;
		$email->body = $body;

		$history = new stdClass();
		$history->notified = $notified;
		$history->amount = $amount.$this->currency->currency_code;
		$history->data = $details;

		if($this->payment_params->debug) {

			$this->writeToLog("Transaction Result :\n ".$details."\n\n");
		}

		if (empty($user_return) ) {

			$this->modifyOrder($order_id, $order_status, $history, $email);
		}


		if($user_return) {
			$this->writeToLog();

			$this->app->enqueueMessage($message);
			$this->app->redirect($EndUrl);
		}
		return false;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='WorldLine SIPS';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
		$element->payment_params->notification= 1;
		$element->payment_params->mode= 'default';
		$element->payment_params->testmode= 0;
		$element->payment_params->debug = 0;
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}

	function compute_transactionId() {
		$currentDateHour = date('H');
		$currentDateMin = date('i');
		$currentDateSec = date('s');
		$currentDateMillArr = explode(' ', microtime());
		$currentDateMill = (int)round($currentDateMillArr[0] * 1000);
		$intPeriodeMs = 0;
		$intPeriodeMs += $currentDateHour * 60 * 60 * 1000;
		$intPeriodeMs += $currentDateMin * 60 * 1000;
		$intPeriodeMs += $currentDateSec * 1000;
		$intPeriodeMs += $currentDateMill;

		$dblProjectionTid = 0;
		$dblProjectionTid += (-5.636E-26 * pow($intPeriodeMs, 4));
		$dblProjectionTid += (7.061E-18 * pow($intPeriodeMs, 3));
		$dblProjectionTid += (-6.692E-11 * pow($intPeriodeMs, 2));
		$dblProjectionTid += (8.566E-4 * pow($intPeriodeMs, 1));
		$dblProjectionTid = floor($dblProjectionTid);
		$intProjectionTid = (int) $dblProjectionTid;

		if ($intProjectionTid > 999999)
			$intProjectionTid = 999999;
		if ($intProjectionTid < 0)
			$intProjectionTid = 0;

		static $inc = 0;
		$intProjectionTid = $intProjectionTid + $inc;
		$inc++;
		$strProjectionTid = $intProjectionTid;
		$strProjectionTid=str_pad($strProjectionTid, 6, "0", STR_PAD_LEFT);

		return $strProjectionTid;
	}

	function flatten_to_sips_payload($input) {
		$keyStack = array();
		return implode("|", $this->flatten_undefined($input, $keyStack));
	}

	function flatten_undefined($object, $keyStack) {
		$result = array();
		if(is_array($object)){
			$result = array_merge($result, $this->flatten_array($object, $keyStack));
		}else if(!empty($keyStack)){
			$result[] = implode('.', $keyStack) . '=' . $object;
		}else{
			$result[] = $object;
		}
		return $result;
	}

	function flatten_array($array, $keyStack) {
		$simpleValues = array();$result = array();

		foreach($array as $key => $value){
			if(is_int($key)){
				if(is_array($value)){
					$noKeyStack = array();
					$simpleValues = array_merge($simpleValues, $this->flatten_array($value, $noKeyStack));
				}else{
					$simpleValues[] = $value;
				}
			}else{
				$keyStack[] = $key;
				$result = array_merge($result, $this->flatten_undefined($value, $keyStack));
				array_pop($keyStack);
			}
		}

		if(!empty($simpleValues)){
			if(empty($keyStack)){
				$result = array_merge($result, $simpleValues);
			}else{
				$result[] = implode(".", $keyStack) . '=' . implode(",", $simpleValues);
			}
		}
		return $result;
	}
}
