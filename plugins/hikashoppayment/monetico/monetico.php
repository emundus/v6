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
class plgHikashoppaymentMonetico extends hikashopPaymentPlugin {
	public $accepted_currencies = array('EUR','USD','GBP','CHF');
	public $multiple = true;
	public $name = 'monetico';
	public $doc_form = 'monetico';

	public $pluginConfig = array(
		'tpe' => array('TPE', 'input'),
		'key' => array('Key', 'input'),
		'societe' => array('Societe', 'input'),
		'locale' => array('Locale', 'list', array(
			'' => 'Auto',
			'en' => 'English',
			'fr' => 'French',
			'de' => 'German',
			'it' => 'Italian',
			'es' => 'Spanish',
			'nl' => 'Dutch',
			'pt' => 'Portuguese',
		)),
		'ThreeDSecureChallenge' => array('Challenge 3DSecure', 'list', array(
			'no_preference' => "Pas de préférence",
			'challenge_preferred' => "Challenge préféré",
			'challenge_mandated' => "Challenge requis",
			'no_challenge_requested' => "Pas de challenge demandé",
		)),
		'notify_url' => array('NOTIFY_URL_DEFINE','input'),
		'debug' => array('DEBUG', 'boolean','0'),
		'sandbox' => array('SANDBOX', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus', 'cancelled'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus', 'confirmed')
	);


	public function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'Monetico Paiement';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
		$element->payment_params->locale = 'fr';
		$element->payment_params->ThreeDSecureChallenge = 'challenge_preferred';
		$element->payment_params->notify_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=monetico&tmpl=component&';

	}

	public function onBeforeOrderCreate(&$order,&$do){
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if(empty($this->payment_params->tpe) || empty($this->payment_params->societe) || empty($this->payment_params->key)) {
			$this->app->enqueueMessage('Please check your &quot;Monetico Paiement&quot; payment method configuration');
			$do = false;
		}
	}

	public function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=monetico&tmpl=component&monetico_return=1&orderId='.$order->order_id.'&lang='.$this->locale. $this->url_itemid;

		if(empty($this->payment_params->locale)) {
			if( in_array($this->locale, array('fr','en','de','it','es','nl','pt')) ) {
				$localeCM = strtoupper($this->locale);
			}else {
				$localeCM = 'FR';
			}
		} else {
			$localeCM = strtoupper($this->payment_params->locale);
		}

		if(@$this->payment_params->sandbox) {
			$this->url =  'https://p.monetico-services.com/test/paiement.cgi';
		} else {
			$this->url =  'https://p.monetico-services.com/paiement.cgi';
		}

		if(empty($this->payment_params->ThreeDSecureChallenge)) {
			$this->payment_params->ThreeDSecureChallenge = 'challenge_preferred';
		}

		$billing = array();
		if(!empty($order->cart->billing_address->address_title)) {
			$order->cart->billing_address->address_title = preg_replace("/[^A-Za-z]/", '', $order->cart->billing_address->address_title);
			if(!empty($order->cart->billing_address->address_title))
				$billing['civility'] = substr($order->cart->billing_address->address_title,0,32);
		}
		if(!empty($order->cart->billing_address->address_firstname))
			$billing['firstName'] = substr($order->cart->billing_address->address_firstname,0,45);
		if(!empty($order->cart->billing_address->address_firstname))
			$billing['lastName'] = substr($order->cart->billing_address->address_lastname,0,45);
		if(!empty($order->cart->billing_address->address_street))
			$billing['addressLine1'] = substr($order->cart->billing_address->address_street, 0, 50);
		if(!empty($order->cart->billing_address->address_street2))
			$billing['addressLine2'] = substr($order->cart->billing_address->address_street2, 0, 50);
		if(!empty($order->cart->billing_address->address_city))
			$billing['city'] = substr($order->cart->billing_address->address_city, 0, 50);
		if(!empty($order->cart->billing_address->address_country))
			$billing['country'] = $order->cart->billing_address->address_country->zone_code_2;
		else
			$billing['country'] = 'FR';
		if(in_array($billing['country'], array('US','CA')))
			$billing['stateOrProvince'] = $order->cart->billing_address->address_state->zone_code_2;
		if(!empty($order->cart->billing_address->address_post_code))
			$billing['postalCode'] = substr($order->cart->billing_address->address_post_code, 0, 10);

		$contexte_commande = array( 'billing' => $billing );

		if(!empty($order->cart->shipping_address)) {
			$shipping = array();
			if(!empty($order->cart->shipping_address->address_title)) {
				$order->cart->shipping_address->address_title = preg_replace("/[^A-Za-z]/", '', $order->cart->shipping_address->address_title);
				if(!empty($order->cart->shipping_address->address_title))
					$shipping['civility'] = substr($order->cart->shipping_address->address_title,0,32);
			}
			if(!empty($order->cart->shipping_address->address_firstname))
				$shipping['firstName'] = substr($order->cart->shipping_address->address_firstname,0,45);
			if(!empty($order->cart->shipping_address->address_firstname))
				$shipping['lastName'] = substr($order->cart->shipping_address->address_lastname,0,45);
			if(!empty($order->cart->shipping_address->address_street))
				$shipping['addressLine1'] = substr($order->cart->shipping_address->address_street, 0, 50);
			if(!empty($order->cart->shipping_address->address_street2))
				$shipping['addressLine2'] = substr($order->cart->shipping_address->address_street2, 0, 50);
			if(!empty($order->cart->shipping_address->address_city))
				$shipping['city'] = substr($order->cart->shipping_address->address_city, 0, 50);
			if(!empty($order->cart->shipping_address->address_country))
				$shipping['country'] = $order->cart->shipping_address->address_country->zone_code_2;
			else
				$shipping['country'] = 'FR';
			if(in_array($shipping['country'], array('US','CA')))
				$shipping['stateOrProvince'] = $order->cart->shipping_address->address_state->zone_code_2;
			if(!empty($order->cart->shipping_address->address_post_code))
				$shipping['postalCode'] = substr($order->cart->shipping_address->address_post_code, 0, 10);
			$contexte_commande['shipping'] = $shipping;
		}

		$config =& hikashop_config();
		$group = $config->get('group_options',0);
		$products = array();
		foreach($order->cart->products as $product) {
			if($group && $product->order_product_option_parent_id) continue;
			if(empty($product->order_product_quantity)) continue;
			$product_data = array();
			$product_data['name'] = substr(strip_tags($product->order_product_name), 0, 45);
			$product_data['productSKU'] = substr($product->order_product_code,0,255);
			$product_data['quantity'] = (int)$product->order_product_quantity;
			$product_data['unitPrice'] = round($product->order_product_price + $product->order_product_tax*100);
			$products[] = $product_data;
		}

		$rawContexteCommand = '{';
		if (isset($billing)) {
			$billing_address = array();
			foreach($billing as $key => $val) {
				$billing_address[] ='"'.$key.'" : "'.$val.'"';
			}
			$rawContexteCommand .= '
                "billing" :
                {
                               '.implode(",\r\n", $billing_address).'
                }';
		}
		if (isset($shipping)) {
			$shipping_address = array();
			foreach($shipping as $key => $val) {
				$shipping_address[] ='"'.$key.'" : "'.$val.'"';
			}
			$rawContexteCommand .= ',
                "shipping" :
                {
                               '.implode(",\r\n", $shipping_address).'
                }';
		}
		$rawContexteCommand .= '
}';

		$utf8ContexteCommande = utf8_encode( $rawContexteCommand );
		$contexte_commande = base64_encode( $utf8ContexteCommande );

		$this->vars = array(
			'TPE' => trim($this->payment_params->tpe),
			'ThreeDSecureChallenge' => $this->payment_params->ThreeDSecureChallenge,
			'contexte_commande' =>  $contexte_commande,
			'date' => date('d/m/Y:H:i:s'),
			'lgue' => $localeCM,
			'mail' => $this->user->user_email,
			'montant' => number_format($order->cart->full_total->prices[0]->price_value_with_tax, 2, '.', '') . $this->currency->currency_code,
			'reference' => $order->order_number,
			'societe' => trim($this->payment_params->societe),
			'texte-libre' => '',
			'url_retour_err' => $return_url,
			'url_retour_ok' => $return_url,
			'version' => '3.0',
		);

		$this->vars['MAC'] = $this->generateHash($this->vars, $this->payment_params);

		if( @$this->payment_params->debug ) {
			$this->writeToLog('Context_commande before crypte' . $rawContexteCommand);
			$this->writeToLog('Data sent');
			$this->writeToLog(var_export($this->vars));
		}

		$this->showPage('end');
		return true;
	}

	public function onPaymentNotification(&$statuses) {
		if( @$this->payment_params->debug ) {
			$this->writeToLog('data recieved in $_POST : ');
			$this->writeToLog($_POST);
			$this->writeToLog('data recieved in $_GET : ');
			$this->writeToLog($_GET);
		}



		$finalReturn = isset($_GET['monetico_return']);
		if($finalReturn) {
			$order_id = (int)@$_GET['orderId'];
		} else {
			if($_SERVER["REQUEST_METHOD"] == "GET")
				$reference = @$_GET['reference'];

			if($_SERVER["REQUEST_METHOD"] == "POST")
				$reference = @$_POST['reference'];
			$db = JFactory::getDBO();
			$db->setQuery('SELECT order_id FROM '.hikashop_table('order').' WHERE order_number='.$db->Quote($reference).';');
			$order_id = (int)$db->loadResult();
		}

		if(empty($order_id)) {
			$this->sendNotifResponse(false,'1');
		}

		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);

		if($finalReturn) {
			return $this->onPaymentUserReturn($dbOrder);
		}

		if($_SERVER["REQUEST_METHOD"] == "GET")
			$vars = $_GET;

		if($_SERVER["REQUEST_METHOD"] == "POST")
			$vars = $_POST;

		if($this->payment_params->debug){
			$this->writeToLog('data recieved in $vars : ')."\r\n\r\n";
			$this->writeToLog($vars)."\r\n\r\n";
		}

		if(empty($dbOrder)) {
			$this->sendNotifResponse(false, 'POST[reference] invalid ("'.$vars['reference'].'")');
		}

		if(empty($vars['MAC'])) {
			$this->sendNotifResponse(false, 'POST[MAC] not present');
		}

		$tpe = $vars['TPE'];
		if(empty($tpe)) {$tpe = $vars['?TPE'];}

		if($tpe != $this->payment_params->tpe ) {
			$this->sendNotifResponse(false, 'POST[TPE] invalid ("' . htmlentities($tpe) . '" != "' . $this->payment_params->tpe . '")');
		}

		$mac = $vars['MAC'];
		unset($vars['MAC']);
		unset($vars['TPE']);
		unset($vars['?TPE']);
		unset($vars['notif_payment']);
		unset($vars['ctrl']);
		unset($vars['task']);
		unset($vars['option']);
		unset($vars['tmpl']);

		ksort($vars);
		$vars = array_reverse($vars);
		$vars['TPE'] = $tpe;
		$vars = array_reverse($vars);

		if( @$this->payment_params->debug )
			$this->writeToLog($vars);
		$processedHash = $this->generateHash($vars, $this->payment_params);

		if(strtolower($mac) != $processedHash) {
			$this->sendNotifResponse(false, 'POST[MAC] invalid ("' . htmlentities($mac) . '" != "' . $processedHash . '")');
		}

		$history = new stdClass();
		$history->notified = 0;
		$history->amount = $vars['montant'];
		$history->data = $vars['numauto'] . "\r\n" . ob_get_clean();

		$var_name = 'paiement';
		if( $this->payment_params->sandbox ) {
			$var_name = 'payetest';
		}

		$completed = ($vars['code-retour'] == $var_name);

		if(!$completed && substr($vars['code-retour'], 0, 11) == 'paiement_pf') {
			$email = new stdClass();
			$email->subject = JText::sprintf('PAYMENT_NOTIFICATION', $this->name, 'recurrent');
			$email->body = str_replace('<br/>', "\r\n", JText::sprintf('PAYMENT_NOTIFICATION_STATUS', $this->name, $dbOrder->order_status)) .
				"\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $dbOrder->order_number, HIKASHOP_LIVE) .
				"\r\n".str_replace('<br/>', "\r\n", JText::sprintf('ACCESS_ORDER_WITH_LINK', HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id=' . (int)$dbOrder->order_id));
			$o = false;
			$this->modifyOrder($o, null, null, $email);

			$this->sendNotifResponse(true, '2', true);
		}

		if($completed) {
			$order_status = $this->payment_params->verified_status;
			$history->notified = 1;
		} else {
			$order_status = $this->payment_params->invalid_status;
			$order_text = $vars['motifrefus'];
		}

		$email = true;
		if(!empty($order_text))
			$email = $order_text;
		$this->modifyOrder($order_id, $order_status, $history, $email);

		$this->sendNotifResponse(true, '3');
	}

	protected function onPaymentUserReturn($dbOrder) {
		$vars = array(
			'reference' => @$_GET['orderId']
		);

		if(empty($dbOrder)){
			$msg = ob_get_clean();
			echo 'Could not load any order for your notification '.$vars['reference'];
			return false;
		}

		$return_url = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id=' . $dbOrder->order_id . $this->url_itemid;
		$cancel_url = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id=' . $dbOrder->order_id . $this->url_itemid;

		if($dbOrder->order_status != $this->payment_params->verified_status) {
			$this->app->enqueueMessage(JText::_('TRANSACTION_DECLINED'));
			$this->app->redirect($cancel_url);
		}

		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM '. hikashop_table('history') .' WHERE history_order_id='. $dbOrder->order_id.' AND history_new_status='.$db->Quote($this->payment_params->verified_status).' ORDER BY history_created DESC;');
		$histories = $db->loadObjectList();
		foreach($histories as $history) {
			$data = $history->history_data;
			if(strpos($data, "\n--\n") !== false) {
				$data = trim(substr($data, 0, strpos($data, "\n--\n")));
				$this->app->enqueueMessage($data);
				break;
			}
		}
		$this->app->redirect($return_url);
	}

	private function sendNotifResponse($confirm = true, $msg = '', $exit = true) {
		$data = ob_get_clean();

		if($confirm) {
			echo "version=2\ncdr=0\n";
		} else {
			echo "version=2\ncdr=1\n";
		}

		if(!empty($this->payment_params) && !empty($this->payment_params->debug)) {
			if(!empty($msg))
				$data .= "\r\n" . $msg;
			$this->writeToLog($data);
		}

		if($exit)
			exit;
	}

	private function generateHash($vars, $params) {
		$sUsableKey = $this->_getUsableKey($params->key);
		array_walk($vars, function(&$a, $b) { $a = "$b=$a"; });
		$sData = implode('*',$vars);

		return strtolower(hash_hmac("sha1", $sData, $sUsableKey));
	}

	private function _getUsableKey($oEpt){
		$hexStrKey  = substr($oEpt, 0, 38);
		$hexFinal   = "" . substr($oEpt, 38, 2) . "00";

		$cca0=ord($hexFinal);

		if ($cca0>70 && $cca0<97)
			$hexStrKey .= chr($cca0-23) . substr($hexFinal, 1, 1);
		else {
			if (substr($hexFinal, 1, 1)=="M")
				$hexStrKey .= substr($hexFinal, 0, 1) . "0";
			else
				$hexStrKey .= substr($hexFinal, 0, 2);
		}

		return pack("H*", $hexStrKey);
	}

}
