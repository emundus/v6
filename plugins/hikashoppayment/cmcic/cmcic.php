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
class plgHikashoppaymentCmcic extends hikashopPaymentPlugin {
	public $accepted_currencies = array('EUR','USD','GBP','CHF');
	public $multiple = true;
	public $name = 'cmcic';
	public $doc_form = 'cmcic';

	public $pluginConfig = array(
		'tpe' => array('TPE', 'input'),
		'key' => array('Key', 'input'),
		'societe' => array('Societe', 'input'),
		'bank' => array('Bank', 'list', array(
			'cm' => 'Credit Mutuel',
			'cic' => 'Groupe CIC',
			'obc' => 'OBC',
			'mp' => 'Monetico Paiement')
		),
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
		'language' => array('Default language', 'list', array(
			'fr' => 'FR',
			'en' => 'EN',
			'de' => 'DE',
			'it' => 'IT',
			'es' => 'ES',
			'nl' => 'NL',
			'pt' => 'PT')
		),
		'debug' => array('DEBUG', 'boolean','0'),
		'sandbox' => array('SANDBOX', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus', 'cancelled'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus', 'confirmed')
	);

	public function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'CMCIC';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
		$element->payment_params->language = 'fr';
	}

	public function onBeforeOrderCreate(&$order,&$do){
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if(empty($this->payment_params->tpe) || empty($this->payment_params->societe) || empty($this->payment_params->key)) {
			$this->app->enqueueMessage('Please check your &quot;CM-CIC&quot; plugin configuration');
			$do = false;
		}
	}

	public function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$notify_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=cmcic&tmpl=component&orderId='.$order->order_id.'&lang='.$this->locale. $this->url_itemid;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=cmcic&tmpl=component&cmcic_return=1&orderId='.$order->order_id.'&lang='.$this->locale. $this->url_itemid;

		if(empty($this->payment_params->language))
			$this->payment_params->language = 'FR';
		$localeCM = $this->payment_params->language;
		if( in_array($this->locale, array('fr','en','de','it','es','nl','pt')) ) {
			$localeCM = strtoupper($this->locale);
		}
		if(!empty($this->payment_params->locale)) {
			$localeCM = strtoupper($this->payment_params->locale);
		}

		if(@$this->payment_params->sandbox) {
			$urls = array(
				'cm' => 'https://paiement.creditmutuel.fr/test/paiement.cgi',
				'cic' => 'https://ssl.paiement.cic-banques.fr/test/paiement.cgi',
				'obc' => 'https://ssl.paiement.banque-obc.fr/test/paiement.cgi',
				'mp' => 'https://p.monetico-services.com/test/paiement.cgi',
			);
		} else {
			$urls = array(
				'cm' => 'https://paiement.creditmutuel.fr/paiement.cgi',
				'cic' => 'https://ssl.paiement.cic-banques.fr/paiement.cgi',
				'obc' => 'https://ssl.paiement.banque-obc.fr/paiement.cgi',
				'mp' => 'https://p.monetico-services.com/paiement.cgi',
			);
		}
		if(!isset($this->payment_params->bank) || !isset($urls[$this->payment_params->bank]) ) {
			$this->payment_params->bank = 'cm';
		}
		$this->url = $urls[$this->payment_params->bank];

		$this->vars = array(
			'TPE' => trim($this->payment_params->tpe),
			'date' => date('d/m/Y:H:i:s'),
			'montant' => number_format($order->cart->full_total->prices[0]->price_value_with_tax, 2, '.', '') . $this->currency->currency_code,
			'reference' => $order->order_number,
			'texte-libre' => '',
			'version' => '3.0',
			'lgue' => $localeCM,
			'societe' => trim($this->payment_params->societe),
			'mail' => $this->user->user_email,
		);

		$this->vars['MAC'] = $this->generateHash($this->vars, $this->payment_params->key, 19);

		if( @$this->payment_params->debug ) {
			echo 'Data sent<pre>' . var_export($this->vars, true) . '</pre>';
		}

		$this->vars['url_retour'] = HIKASHOP_LIVE . 'index.php?option=com_hikashop';
		$this->vars['url_retour_ok'] = $return_url;
		$this->vars['url_retour_err'] = $return_url;

		$this->showPage('end');
		return true;
	}

	public function onPaymentNotification(&$statuses) {
		$finalReturn = isset($_GET['cmcic_return']);
		if($finalReturn) {
			$order_id = (int)@$_GET['orderId'];
		} else {
			$reference = @$_POST['reference'];
			$db = JFactory::getDBO();
			$db->setQuery('SELECT order_id FROM '.hikashop_table('order').' WHERE order_number='.$db->Quote($reference).';');
			$order_id = (int)$db->loadResult();
		}
		if(empty($order_id)) {
			$this->sendNotifResponse(false);
		}

		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);

		if($finalReturn) {
			return $this->onPaymentUserReturn($dbOrder);
		}

		$vars = array(
			'TPE' => $this->payment_params->tpe,
			'date' => @$_POST['date'],
			'montant' => @$_POST['montant'],
			'reference' => @$_POST['reference'],
			'texte-libre' => @$_POST['texte-libre'],
			'version' => '3.0',
			'code-retour' => @$_POST['code-retour'],
			'cvx' => @$_POST['cvx'],
			'vld' => @$_POST['vld'],
			'brand' => @$_POST['brand'],
			'status3ds' => @$_POST['status3ds'],
			'numauto' => @$_POST['numauto'],
			'motifrefus' => @$_POST['motifrefus'],
			'originecb' => @$_POST['originecb'],
			'bincb' => @$_POST['bincb'],
			'hpancb' => @$_POST['hpancb'],
			'ipclient' => @$_POST['ipclient'],
			'originetr' => @$_POST['originetr']
		);

		if($this->payment_params->debug){
			echo print_r($vars,true)."\r\n\r\n";
		}

		if(empty($dbOrder)) {
			$this->sendNotifResponse(false, 'POST[reference] invalid ("'.$vars['reference'].'")');
		}

		if(empty($_POST['MAC'])) {
			$this->sendNotifResponse(false, 'POST[MAC] not present');
		}

		if($_POST['TPE'] != $this->payment_params->tpe) {
			$this->sendNotifResponse(false, 'POST[TPE] invalid ("' . htmlentities($_POST['TPE']) . '" != "' . $this->payment_params->tpe . '")');
		}

		$processedHash = $this->generateHash($vars, $this->payment_params->key, 21);
		if(strtolower($_POST['MAC']) != $processedHash) {
			$this->sendNotifResponse(false, 'POST[MAC] invalid ("' . htmlentities($_POST['MAC']) . '" != "' . $processedHash . '")');
		}

		$history = new stdClass();
		$history->notified = 0;
		$history->amount = $vars['montant'];
		$history->data = $vars['numauto'] . "\r\n" . ob_get_clean();

		if( $this->payment_params->sandbox ) {
			$completed = ($vars['code-retour'] == 'payetest');
		} else {
			$completed = ($vars['code-retour'] == 'paiement');
		}

		if(!$completed && substr($vars['code-retour'], 0, 11) == 'paiement_pf') {
			$email = new stdClass();
			$email->subject = JText::sprintf('PAYMENT_NOTIFICATION', $this->name, 'recurrent');
			$email->body = str_replace('<br/>', "\r\n", JText::sprintf('PAYMENT_NOTIFICATION_STATUS', $this->name, $dbOrder->order_status)) .
				"\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $dbOrder->order_number, HIKASHOP_LIVE) .
				"\r\n".str_replace('<br/>', "\r\n", JText::sprintf('ACCESS_ORDER_WITH_LINK', HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id=' . (int)$dbOrder->order_id));
			$o = false;
			$this->modifyOrder($o, null, null, $email);

			$this->sendNotifResponse(true);
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

		$this->sendNotifResponse(true);
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
			if(!empty($data) && !empty($msg))
				$data .= "\r\n" . $msg;
			$this->writeToLog($data);
		}

		if($exit)
			exit;
	}

	private function generateHash($vars, $key, $nb) {
		$str = implode('*',$vars);
		$l = $nb - count($vars);
		$str .= str_pad('', $l, '*');

		$hexStrKey = substr($key, 0, 38);
		$hexFinal = '' . substr($key, 38, 2) . '00';
		$cca0 = ord($hexFinal);
		if($cca0 > 70 && $cca0 < 97) {
			$hexStrKey .= chr($cca0-23) . substr($hexFinal, 1, 1);
		} elseif(substr($hexFinal, 1, 1) == 'M')  {
			$hexStrKey .= substr($hexFinal, 0, 1) . '0';
		} else {
			$hexStrKey .= substr($hexFinal, 0, 2);
		}
		$hKey = pack('H*', $hexStrKey);

		return strtolower($this->hmacsha1($str, $hKey));
	}

	private function hmacsha1($data,$key) {
		if(function_exists('hash_hmac'))
			return hash_hmac('sha1', $data, $key);

		if(!function_exists('sha1'))
			die('SHA1 function is not present');

		if(strlen($key) > 64)
			$key = pack('H*',sha1($key));

		$key  = str_pad($key, 64, chr(0x00));
		$ipad = str_pad('', 64, chr(0x36));
		$opad = str_pad('', 64, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;

		return sha1($k_opad.pack('H*',sha1($k_ipad.$data)));
	}
}
