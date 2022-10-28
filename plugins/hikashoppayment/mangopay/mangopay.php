<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashoppaymentMangopay extends hikashopPaymentPlugin
{
	public $accepted_currencies = array(
		'EUR','USD','GBP','CHF',
		'SEK','NOK','DKK','PLN',
	);

	public $multiple = true;
	public $name = 'mangopay';
	public $doc_form = 'mangopay';

	public $market_support = true;

	var $pluginConfig = array(
		'client_id' => array('ClientId', 'html', 'Please configure credentials in the Joomla plugin manager side'),
		'payment_mode' => array('Payment Mode', 'list', array(
			'web' => 'Web'
		)),
		'debug' => array('DEBUG', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus', 'cancelled'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus', 'created'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus', 'confirmed'),
	);

	protected $api = null;

	private function initMarket() {
		static $init = null;
		if($init !== null)
			return $init;

		$init = defined('HIKAMARKET_COMPONENT');
		if(!$init) {
			$filename = rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikamarket'.DS.'helpers'.DS.'helper.php';
			if(file_exists($filename)) {
				include_once($filename);
				$init = defined('HIKAMARKET_COMPONENT');
			}
		}
		return $init;
	}

	public function getAPI() {
		if(!empty($this->api))
			return $this->api;

		if(version_compare(PHP_VERSION, '5.3', '<'))
			return false;
		require_once dirname(__FILE__).DS.'mangolib'.DS.'mangoPayApi.inc';
		$this->api = new MangoPay\MangoPayApi();

		if(!isset($this->params)) {
			$plugin = JPluginHelper::getPlugin('hikashoppayment', 'mangopay');
			$this->params = new JRegistry(@$plugin->params);
		}

		if($this->params->get('clientid', false)) {
			$this->api->Config->ClientId = $this->params->get('clientid');
			$this->api->Config->ClientPassword = $this->params->get('clientpassword');

			if((int)$this->params->get('sandbox', 0)) {
				$this->api->Config->BaseUrl = 'https://api.sandbox.mangopay.com';
				$this->api->Config->TemporaryFolder = dirname(__FILE__).DS.'tmp'.DS.'debug'.DS;
			} else {
				$this->api->Config->BaseUrl = 'https://api.mangopay.com';
				$this->api->Config->TemporaryFolder = dirname(__FILE__).DS.'tmp'.DS.'prod'.DS;
			}
			if(!empty($this->payment_params->debug)) {
				$this->api->Config->DebugMode = true;
			}

			jimport('joomla.filesystem.file');
			jimport('joomla.filesystem.folder');
			if(!JFolder::exists($this->api->Config->TemporaryFolder))
				JFolder::create($this->api->Config->TemporaryFolder);
			$content = 'deny from all';
			if(!JFile::exists($this->api->Config->TemporaryFolder.'.htaccess'))
				JFile::write($this->api->Config->TemporaryFolder.'.htaccess', $content);
		}

		return $this->api;
	}

	public function checkPaymentDisplay(&$method, &$order) {
		if(version_compare(PHP_VERSION, '5.3', '<'))
			return false;
		return true;
	}

	public function onBeforeOrderCreate(&$order, &$do) {
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		$this->getAPI();
		if(empty($order->user))
			$order->user = $this->user;
		$mangoUser = $this->getUser($order);
		if(!empty($mangoUser->Id))
			return true;

		$do = false;
		$this->app->enqueueMessage('Error during the creation of your MangoPay customer profile', 'error');
		return false;
	}

	public function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$notify_url = HIKASHOP_LIVE.'mangopay_'.$method_id.'.php?order_id='.$order->order_id . $this->url_itemid;

		$this->getAPI();

		if(empty($order->user))
			$order->user = $this->user;

		$mangoUser = $this->getUser($order);

		if(empty($this->payment_params->payment_mode) || !in_array($this->payment_params->payment_mode, array('direct', 'web')))
			$this->payment_params->payment_mode = 'web';

		$update_order = new stdClass();
		$update_order->order_id = $order->order_id;
		$update_order->order_payment_params = @$order->order_payment_params;

		if(empty($update_order->order_payment_params))
			$update_order->order_payment_params = new stdClass();
		if(empty($order->order_payment_params->mangopay))
			$update_order->order_payment_params->mangopay = new stdClass();

		if($this->payment_params->payment_mode == 'direct') {
			$cardRegister = new MangoPay\CardRegistration();
			$cardRegister->UserId = $mangoUser->Id;
			$cardRegister->Currency = strtoupper($this->currency->currency_code);

			try {

				$createdCardRegister = $this->api->CardRegistrations->Create($cardRegister);

			}
			catch (MangoPay\ResponseException $e) { $this->mangoDebug('CardRegistrations Create', $e, true); }
			catch (MangoPay\Exception $e) { $this->mangoDebug('CardRegistrations Create', $e, false); }
			catch (Exception $e) {}

			$update_order->order_payment_params->mangopay->cardRegisterId = $createdCardRegister->Id;

			$this->return_url = $notify_url;
			$this->createdCardRegister = $createdCardRegister;

			$db = JFactory::getDBO();
			$query = 'UPDATE ' .  hikashop_table('order') .
					' SET order_payment_params = ' . $db->Quote(serialize($update_order->order_payment_params)) .
					' WHERE order_id = ' . (int)$update_order->order_id;
			$db->setQuery($query);
			$db->execute();

			return $this->showPage('end');
		}

		if($this->payment_params->payment_mode == 'web') {
			$wallet = new MangoPay\Wallet();
			$wallet->Owners = array( $mangoUser->Id );
			$wallet->Currency = strtoupper($this->currency->currency_code);
			$wallet->Description = $order->order_number;
			$wallet->Tag = 'order#' . $order->order_id;

			$createdWallet = false;
			try {

				$createdWallet = $this->api->Wallets->Create($wallet);

			}
			catch (MangoPay\ResponseException $e) { $this->mangoDebug('Wallets Create', $e, true); $createdWallet=null; }
			catch (MangoPay\Exception $e) { $this->mangoDebug('Wallets Create', $e, false); $createdWallet=null; }
			catch (Exception $e) { $createdWallet=null; }
			$lang = JFactory::getLanguage();
			$locale = strtoupper(substr($lang->get('tag'), 0, 2));

			$PayIn = new MangoPay\PayIn();
			$PayIn->CreditedWalletId = $createdWallet->Id;
			$PayIn->AuthorId = $mangoUser->Id;
			$PayIn->PaymentType = 'CARD';
			$PayIn->PaymentDetails = new MangoPay\PayInPaymentDetailsCard();
			$PayIn->PaymentDetails->CardType = 'CB_VISA_MASTERCARD';
			$PayIn->DebitedFunds = new MangoPay\Money();
			$PayIn->DebitedFunds->Currency = strtoupper($this->currency->currency_code);
			$PayIn->DebitedFunds->Amount = round($order->order_full_price * 100);
			$PayIn->Fees = new MangoPay\Money();
			$PayIn->Fees->Currency = strtoupper($this->currency->currency_code);
			$PayIn->Fees->Amount = 0;
			$PayIn->ExecutionType = 'WEB';
			$PayIn->ExecutionDetails = new MangoPay\PayInExecutionDetailsWeb();
			$PayIn->ExecutionDetails->ReturnURL = $notify_url;
			$PayIn->ExecutionDetails->Culture = $locale;
			$PayIn->Tag = 'order#' . $order->order_id;

			$createdPayIn = null;
			try {

				$createdPayIn = $this->api->PayIns->Create($PayIn);

			}
			catch (MangoPay\ResponseException $e) { $this->mangoDebug('PayIns Create', $e, true); $createdPayIn=null; }
			catch (MangoPay\Exception $e) { $this->mangoDebug('PayIns Create', $e, false); $createdPayIn=null; }
			catch (Exception $e) { $createdPayIn=null; }

			$update_order->order_payment_params->mangopay->payInId = $createdPayIn->Id;
			$update_order->order_payment_params->mangopay->walletId = $createdWallet->Id;

			$this->return_url = $createdPayIn->ExecutionDetails->RedirectURL;
			$this->createdPayIn = $createdPayIn;

			$db = JFactory::getDBO();
			$query = 'UPDATE ' . hikashop_table('order') .
					' SET order_payment_params = ' . $db->Quote(serialize($update_order->order_payment_params)) .
					' WHERE order_id = ' . (int)$update_order->order_id;
			$db->setQuery($query);
			$db->execute();

			$app = JFactory::getApplication();
			$app->redirect( $createdPayIn->ExecutionDetails->RedirectURL );
		}
	}

	public function onAfterOrderUpdate(&$order) {
		if(empty($order->order_status))
			return;

		$order_payment_method = !empty($order->order_payment_method) ? $order->order_payment_method : @$order->old->order_payment_method;
		if($order_payment_method != 'mangopay')
			return;

		$order_type = !empty($order->order_type) ? $order->order_type : @$order->old->order_type;
		if($order_type != 'sale')
			return;

		$order_payment_params = !empty($order->order_payment_params) ? $order->order_payment_params : $order->old->order_payment_params;
		if(is_string($order_payment_params))
			$order_payment_params = unserialize($order_payment_params);
		if(!empty($order_payment_params->mangopay->paid))
			return;

		if(!isset($this->params)) {
			$plugin = JPluginHelper::getPlugin('hikashoppayment', 'mangopay');
			$this->params = new JRegistry(@$plugin->params);
		}
		$confirmed = explode(',', trim($this->params->get('order_status', ''), ','));
		if(count($confirmed) == 1 && empty($confirmed[0]))
			$confirmed = array();

		$shopConfig = hikashop_config();
		if(empty($confirmed)) {
			$confirmed = explode(',', trim($shopConfig->get('invoice_order_statuses'), ','));
			if(empty($confirmed))
				$confirmed = array('confirmed','shipped');
		}

		$refund_statuses = explode(',', trim($shopConfig->get('cancelled_order_status'), ','));

		if(in_array($order->order_status, $confirmed)) {
			$this->payVendors($order);
		}
		return;
	}

	public function checkOrderStatuses(&$messages) {
		return;
	}

	public function onPaymentNotification(&$statuses) {
		$order_id = (int)@$_GET['order_id'];
		$transaction_id = (int)@$_GET['transactionId'];

		if(!empty($order_id) && !empty($transaction_id))
			return $this->paymentNotificationSecureReturn($transaction_id, $order_id, $statuses);
		if(!empty($order_id))
			return $this->paymentNotificationCardReturn($order_id, $statuses);

		return false;
	}

	protected function paymentNotificationCardReturn($order_id, &$statuses) {
		$dbOrder = $this->getOrder((int)$order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);

		if(is_string($dbOrder->order_payment_params))
			$dbOrder->order_payment_params = unserialize($dbOrder->order_payment_params);

		$cancel_url = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id=' . $dbOrder->order_id . $this->url_itemid;
		$confirm_url = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id=' . $dbOrder->order_id . $this->url_itemid;

		if(empty($dbOrder->order_payment_params->mangopay->cardRegisterId) && !empty($dbOrder->order_payment_params->mangopay->walletId)) {
			if($dbOrder->order_status == $this->payment_params->invalid_status) {
				$this->app->redirect($cancel_url);
				exit;
			}
			$this->app->redirect($confirm_url);
			exit;
		}

		if(empty($dbOrder->order_payment_params->mangopay->cardRegisterId))
			die('Invalid order');

		$payment_id = (int)$dbOrder->order_payment_id;

		$this->getAPI();

		try {

			$cardRegister = $this->api->CardRegistrations->Get($dbOrder->order_payment_params->mangopay->cardRegisterId);
			$cardRegister->RegistrationData = isset($_GET['data']) ? 'data=' . $_GET['data'] : 'errorCode=' . $_GET['errorCode'];
			$updatedCardRegister = $this->api->CardRegistrations->Update($cardRegister);

		}
		catch (MangoPay\ResponseException $e) { $this->mangoDebug('CardRegistrations Get/Update', $e, true); $updatedCardRegister=null; }
		catch (MangoPay\Exception $e) { $this->mangoDebug('CardRegistrations Get/Update', $e, false); $updatedCardRegister=null; }
		catch (Exception $e) { $updatedCardRegister=null; }

		if(!isset($updatedCardRegister) || $updatedCardRegister->Status != 'VALIDATED' || !isset($updatedCardRegister->CardId))
			die('Cannot create virtual card. Payment has not been created.');

		$card = false;
		try {

			$card = $this->api->Cards->Get($updatedCardRegister->CardId);

		}
		catch (MangoPay\ResponseException $e) { $this->mangoDebug('Cards Get', $e, true); $card=null; }
		catch (MangoPay\Exception $e) { $this->mangoDebug('Cards Get', $e, false); $card=null; }
		catch (Exception $e) { $card=null; }

		if(empty($card))
			die('An error occured, please contact the administrator in order to have more details. (Card Get)');

		$wallet = new MangoPay\Wallet();
		$wallet->Owners = array( $updatedCardRegister->UserId );
		$wallet->Currency = strtoupper($this->currency->currency_code);
		$wallet->Description = $dbOrder->order_number;
		$wallet->Tag = 'order#' . $dbOrder->order_id;

		$createdWallet = false;
		try {

			$createdWallet = $this->api->Wallets->Create($wallet);

		}
		catch (MangoPay\ResponseException $e) { $this->mangoDebug('Wallets Create', $e, true); $createdWallet=false; }
		catch (MangoPay\Exception $e) { $this->mangoDebug('Wallets Create', $e, false); $createdWallet=false; }
		catch (Exception $e) { $createdWallet=false; }

		if(empty($createdWallet))
			die('An error occured, please contact the administrator in order to have more details. (Wallet Creation)');

		$payIn = new MangoPay\PayIn();
		$payIn->Tag = 'order#' . (int)$dbOrder->order_id;
		$payIn->CreditedWalletId = $createdWallet->Id;
		$payIn->AuthorId = $updatedCardRegister->UserId;
		$payIn->DebitedFunds = new MangoPay\Money();
		$payIn->DebitedFunds->Amount = round($dbOrder->order_full_price * 100);
		$payIn->DebitedFunds->Currency = strtoupper($this->currency->currency_code);
		$payIn->Fees = new MangoPay\Money();
		$payIn->Fees->Amount = 0;
		$payIn->Fees->Currency = strtoupper($this->currency->currency_code);

		$payIn->PaymentDetails = new MangoPay\PayInPaymentDetailsCard();
		$payIn->PaymentDetails->CardType = $card->CardType;

		$payIn->ExecutionDetails = new MangoPay\PayInExecutionDetailsDirect();
		$payIn->ExecutionDetails->CardId = $card->Id;
		$payIn->ExecutionDetails->SecureModeReturnURL = HIKASHOP_LIVE.'mangopay_'.$payment_id.'.php';

		$createdPayIn = null;
		try {

			$createdPayIn = $this->api->PayIns->Create($payIn);

		}
		catch (MangoPay\ResponseException $e) { $this->mangoDebug('PayIns Create', $e, true); $createdPayIn=null; }
		catch (MangoPay\Exception $e) { $this->mangoDebug('PayIns Create', $e, false); $createdPayIn=null; }
		catch (Exception $e) { $createdPayIn=null; }

		if(empty($createdPayIn))
			die('An error occured, please contact the administrator in order to have more details. (PayIns Create)');

		$update_order = new stdClass();
		$update_order->order_id = (int)$dbOrder->order_id;
		$update_order->order_number = $dbOrder->order_number;
		$update_order->order_payment_params = $dbOrder->order_payment_params;

		$update_order->order_payment_params->mangopay->cardRegisterId = null;
		$update_order->order_payment_params->mangopay->payInId = $createdPayIn->Id;
		$update_order->order_payment_params->mangopay->walletId = $createdWallet->Id;

		$email = false;
		$history = new stdClass();
		$history->notified = 0;
		$history->amount = $dbOrder->order_full_price;
		$history->data = 'MangoPay Status: '.$createdPayIn->Status;

		if(isset($createdPayIn->SecureModeNeeded) && isset($createdPayIn->Status) && !empty($createdPayIn->ExecutionDetails->SecureModeNeeded) && $createdPayIn->Status != 'FAILED') {
			$order_status = $this->payment_params->pending_status;
			$redirect_url = $createdPayIn->ExecutionDetails->SecureModeRedirectURL;
		} if(isset($createdPayIn->Status) && $createdPayIn->Status == 'SUCCEEDED') {
			$history->notified = 1;
			$email = true;
			$order_status = $this->payment_params->verified_status;
			$redirect_url = $confirm_url;
		} else {
			$order_status = $this->payment_params->invalid_status;
			$email = true;
			$redirect_url = $cancel_url;
		}

		if($this->payment_params->debug) {
			$dbg = 'MangoPay - PayIn' . "\r\n".
				'<pre>'.print_r($createdPayIn, true).'</pre>';
			$this->writeToLog($dbg);
		}

		$this->modifyOrder($update_order, $order_status, $history, true);
		$orderClass = hikashop_get('class.order');
		$orderClass->save($update_order);

		$this->app->redirect($redirect_url);
	}

	protected function paymentNotificationSecureReturn($transaction_id, $order_id, &$statuses) {
		$notif_id = (int)$_GET['notif_id'];

		if($this->pluginParams($notif_id))
			$this->payment_params =& $this->plugin_params;

		$this->getAPI();

		$payIn = null;
		try {

			$payIn = $this->api->PayIns->Get($transaction_id);

		}
		catch (MangoPay\ResponseException $e) { $this->mangoDebug('PayIns Get', $e, true); $payIn=null; }
		catch (MangoPay\Exception $e) { $this->mangoDebug('PayIns Get', $e, false); $payIn=null; }
		catch (Exception $e) { $payIn=null; }

		if($payIn === null) {
			$this->writeToLog('Invalid payIn: ' . $transaction_id);
			return false;
		}

		$tag_order_id = (int)str_replace('order#', '', $payIn->Tag);
		if($tag_order_id > 0 && $tag_order_id != $order_id) {
			$this->writeToLog('Wrong payIn (' . $transaction_id .') : '. $payIn->Tag);
			return false;
		} else if($tag_order_id == 0) {
		}


		$redirect_url = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id=' . (int)$order_id;
		if(isset($payIn->Status) && $payIn->Status == 'SUCCEEDED') {

			$dbOrder = $this->getOrder((int)$order_id);


			$order_status = $this->payment_params->verified_status;
			$history = new stdClass();
			$history->notified = 1;
			$history->data = 'MangoPay Secure Return: '.$payIn->Status;

			$this->modifyOrder($order_id, $order_status, $history, true);
			$redirect_url = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id=' . $dbOrder->order_id;
		} elseif(isset($payIn->Status) && $payIn->Status == 'FAILED') {
			$order_status = $this->payment_params->invalid_status;
			$history = new stdClass();
			$history->notified = 0;
			$history->data = 'MangoPay Secure Return: '.$payIn->Status;
			$this->modifyOrder($order_id, $order_status, $history, true);
		}
		if(empty($this->app))
			$this->app = JFactory::getApplication();
		$this->app->redirect($redirect_url);
	}

	protected function payVendors(&$order) {
		$db = JFactory::getDBO();
		$suborders = array();
		$vendors = array();

		$order_id = (int)$order->order_id;
		if(empty($order_id))
			return false;

		$order_payment_params = !empty($order->order_payment_params) ? $order->order_payment_params : $order->old->order_payment_params;
		if(is_string($order_payment_params))
			$order_payment_params = unserialize($order_payment_params);

		if(empty($order_payment_params->mangopay->walletId))
			return false;

		if(!$this->initMarket() || !$this->getAPI())
			return false;

		$this->currency = 0;
		$currency_id = (isset($order->order_currency_id) ? (int)$order->order_currency_id : (int)$order->old->order_currency_id);
		if(!empty($currency_id)) {
			$currencyClass = hikashop_get('class.currency');
			$currencies = null;
			$currencies = $currencyClass->getCurrencies($currency_id, $currencies);
			$this->currency = $currencies[$currency_id];
		}
		$currency_code = strtolower($this->currency->currency_code);

		$query = 'SELECT v.*, o.* FROM ' . hikamarket::table('shop.order') . ' AS o '.
			' LEFT JOIN ' . hikamarket::table('vendor') . ' AS v ON o.order_vendor_id = v.vendor_id '.
			' WHERE o.order_parent_id = ' . $order_id;
		$db->setQuery($query);
		$suborders = $db->loadObjectList();
		$suborders_id = array();
		$transaction_vendors = array();

		$userClass = hikamarket::get('shop.class.user');
		$order_user_id = isset($order->order_user_id) ? (int)$order->order_user_id : (int)$order->old->order_user_id;
		$order->user = $userClass->get($order_user_id);
		$mangoCustomerId = $order->user->user_params->mangopay_id;

		$full_price = isset($order->order_full_price) ? $order->order_full_price : $order->old->order_full_price;

		if(!empty($suborders)) {
			$vendors = array();
			$total = 0.0;

			foreach($suborders as $k => $suborder) {
				$price = round(hikamarket::toFloat($suborder->order_vendor_price), (int)$this->currency->currency_locale['int_frac_digits']);
				if($price < 0)
					$price = round(hikamarket::toFloat($suborder->order_vendor_price) + hikamarket::toFloat($full_price), (int)$this->currency->currency_locale['int_frac_digits']);

				if($price == 0)
					continue;


				$total += (float)hikamarket::toFloat($suborder->order_full_price);

				$suborders_id[] = (int)$suborder->order_id;
				$transaction_vendors[ (int)$suborder->order_vendor_id ] = (int)$suborder->order_vendor_id;

				$mango_id = null;
				$wallets = null;
				if(!empty($suborder->vendor_params))
					$suborder->vendor_params = unserialize($suborder->vendor_params);
				if(!empty($suborder->vendor_params->mangopay_id))
					$mango_id = (int)$suborder->vendor_params->mangopay_id;
				if(!empty($suborder->vendor_params->mangopay_wallets))
					$wallets = $suborder->vendor_params->mangopay_wallets;

				if(empty($mango_id)) {
					$mango_user = $this->createVendor($suborder);
					$mango_id = $mango_user->Id;
					$suborder->vendor_params->mangopay_id = (int)$mango_id;
				}
				if(empty($wallets) || empty($wallets[$currency_code])) {
					if(empty($wallets))
						$wallets = array();
					$wallets[$currency_code] = $this->getVendorWallet($suborder, $currency_code);
				}

				$vendors[ (int)$suborder->order_vendor_id ] = array(
					'price' => $price,
					'full_price' => (float)hikamarket::toFloat($suborder->order_full_price),
					'mango_id' => (int)$mango_id,
					'wallet' => (int)$wallets[$currency_code],
				);
			}

			foreach($vendors as $k => $v) {
				$this->transfert(
					$mangoCustomerId, // $v['mango_id']
					$order_payment_params->mangopay->walletId,
					$v['wallet'],
					$v['full_price'],
					$v['full_price'] - $v['price'],
					$currency_code
				);
			}

			$order_full_price = (float)hikamarket::toFloat($full_price);
			if($order_full_price - $total > 0) {
				$vendorClass = hikamarket::get('class.vendor');
				$mainVendor = $vendorClass->get(1);
				$mainVendorWallet = $this->getVendorWallet($mainVendor);

				$this->transfert(
					$mangoCustomerId, // $mainVendor->vendor_params->mangopay_id,
					$order_payment_params->mangopay->walletId,
					$mainVendorWallet,
					(float)($order_full_price - $total),
					0,
					$currency_code
				);
			}

		} else {
			$vendorClass = hikamarket::get('class.vendor');
			$mainVendor = $vendorClass->get(1);
			$mainVendorWallet = $this->getVendorWallet($mainVendor);

			$this->transfert(
				$mangoCustomerId, // $mainVendor->vendor_params->mangopay_id,
				$order_payment_params->mangopay->walletId,
				$mainVendorWallet,
				(float)hikamarket::toFloat($full_price),
				0,
				$currency_code
			);
		}

		$new_order_payment_params = hikamarket::cloning($order_payment_params);
		unset($new_order_payment_params->mangopay->walletId);
		$new_order_payment_params->mangopay->paid = true;

		$query = 'UPDATE ' .  hikashop_table('order') .
				' SET order_payment_params = ' . $db->Quote(serialize($new_order_payment_params)) .
				' WHERE order_id = ' . (int)$order->order_id;
		$db->setQuery($query);
		$db->execute();
		if(!empty($transaction_vendors)) {
			$query = 'UPDATE '.hikamarket::table('order_transaction').
					' SET order_transaction_paid = order_id '.
					' WHERE order_id = '.(int)$order->order_id.' AND vendor_id IN ('.implode(',', $transaction_vendors).')';
			$db->setQuery($query);
			$db->execute();
		}
	}

	protected function getUser(&$order, $update = true) {
		if(!$this->getAPI())
			return false;

		if(empty($order->user)) {
			if(empty($this->user)) {
				hikashop_loadUser(true, true);
				$this->user = hikashop_loadUser(true);
			}
			$order->user = $this->user;
		}

		if(empty($order->user->user_params->mangopay_id))
			return $this->createUser($order);

		$mangoUser_id = (int)$order->user->user_params->mangopay_id;
		$mangoUser = null;
		try {

			$mangoUser = $this->api->Users->Get($mangoUser_id);

		}
		catch (MangoPay\ResponseException $e) { $this->mangoDebug('Users Get', $e, true); $mangoUser=null; }
		catch (MangoPay\Exception $e) { $this->mangoDebug('Users Get', $e, false); $mangoUser=null; }
		catch (Exception $e) { $mangoUser=null; }

		if(empty($mangoUser))
			return $this->createUser($order);

		if($update) {
			$billing_address = $order->cart->billing_address;
			$mangoUser->FirstName = substr($billing_address->address_firstname, 0, 99);
			$mangoUser->LastName = substr($billing_address->address_lastname, 0, 99);
			$mangoUser->Email = $order->user->user_email;
			$mangoUser->Address = substr($billing_address->address_street . ' ' . $billing_address->address_city, 0, 254);
			$mangoUser->CountryOfResidence = $billing_address->address_country->zone_code_2;

			try {

				$this->api->Users->Update($mangoUser);

			}
			catch (MangoPay\ResponseException $e) { $this->mangoDebug('Users Update', $e, true); }
			catch (MangoPay\Exception $e) { $this->mangoDebug('Users Update', $e, false); }
			catch (Exception $e) {}
		}

		return $mangoUser;
	}

	protected function createUser(&$order) {
		if(!$this->getAPI())
			return false;

		$billing_address = $order->cart->billing_address;

		$user = new MangoPay\UserNatural();
		$user->FirstName = substr($billing_address->address_firstname, 0, 99);
		$user->LastName = substr($billing_address->address_lastname, 0, 99);
		$user->Email = $order->user->user_email;
		$user->Address = substr($billing_address->address_street . ' ' . $billing_address->address_city, 0, 254);
		$user->CountryOfResidence = $billing_address->address_country->zone_code_2;
		$user->tag = 'hkId:' . (!empty($order->order_user_id) ? $order->order_user_id : @$order->user->user_id);

		$user->Nationality = $billing_address->address_country->zone_code_2;
		$user->Birthday = time();

		$mangoUser = null;

		try {

			$mangoUser = $this->api->Users->Create($user);

		}
		catch (MangoPay\ResponseException $e) { $this->mangoDebug('Users Create', $e, true); $mangoUser=null; }
		catch (MangoPay\Exception $e) { $this->mangoDebug('Users Create', $e, false); $mangoUser=null; }
		catch (Exception $e) { $mangoUser=null; }

		if(!empty($mangoUser->Id)) {
			$hkUser = new stdClass();

			$hkUser->user_params = new stdClass();
			if(!empty($order->user->user_params) && is_object($order->user->user_params))
				$hkUser->user_params = $order->user->user_params;

			if(isset($order->user) && isset($order->user->user_params) && is_object($order->user->user_params))
				$order->user->user_params->mangopay_id = $mangoUser->Id;
			if(isset($this->user) && isset($this->user->user_params) && is_object($this->user->user_params))
				$this->user->user_params->mangopay_id = $mangoUser->Id;

			$hkUser->user_id = $order->user->user_id;
			$hkUser->user_params->mangopay_id = $mangoUser->Id;
			$userClass = hikashop_get('class.user');
			$userClass->save($hkUser);
		}

		return $mangoUser;
	}

	public function getUserCards(&$user) {
		if(!empty($user->user_params) && is_string($user->user_params))
			$user->user_params = unserialize($user->user_params);

		if(empty($user->user_params->mangopay_id))
			return false;

		if(empty($this->api))
			$this->getAPI();

		$cards = array();

		try {

			$cards = $this->api->Users->getCards($user->user_params->mangopay_id);

		}
		catch (MangoPay\ResponseException $e) { $this->mangoDebug('Users getCards', $e, true); }
		catch (MangoPay\Exception $e) { $this->mangoDebug('Users getCards', $e, false); }
		catch (Exception $e) {}

		return $cards;
	}

	public function getVendor(&$vendor, $update = false) {
		if(empty($vendor->vendor_params->mangopay_id))
			return $this->createVendor($vendor);

		if(empty($this->api))
			$this->getAPI();

		$mangoUser_id = (int)$vendor->vendor_params->mangopay_id;
		$mangoUser = null;
		try {

			$mangoUser = $this->api->Users->Get($mangoUser_id);

		}
		catch (MangoPay\ResponseException $e) { $this->mangoDebug('Vendor:Users Get', $e, true); }
		catch (MangoPay\Exception $e) { $this->mangoDebug('Vendor:Users Get', $e, false); }
		catch (Exception $e) {}

		if(empty($mangoUser))
			return $this->createVendor($vendor);
		return $mangoUser;
	}

	protected function createVendor(&$vendor) {
		if(empty($this->api))
			$this->getAPI();

		$user = new MangoPay\UserLegal();
		$user->Name = $vendor->vendor_name;
		$user->LegalPersonType = 'BUSINESS';
		$user->Email = $vendor->vendor_email;
		$user->HeadquartersAddress = substr($vendor->vendor_address_street . ' ' . $vendor->vendor_address_city, 0, 254);
		$user->tag = 'hkVendorId:'.$vendor->vendor_id;

		$billing_address = null;
		$app = JFactory::getApplication();
		if(!empty($vendor->vendor_admin_id) && (int)$vendor->vendor_admin_id > 0) {
			$addressClass = hikamarket::get('shop.class.address');
			$addresses = $addressClass->loadUserAddresses((int)$vendor->vendor_admin_id);
			$billing_address = reset($addresses);
		} else if(hikashop_isClient('administrator') && $vendor->vendor_id == 1) {
			$addressClass = hikamarket::get('shop.class.address');
			$current_user_id = hikashop_loadUser();
			$addresses = $addressClass->loadUserAddresses($current_user_id);
			$billing_address = reset($addresses);
		}

		$user->LegalRepresentativeFirstName = substr(@$billing_address->address_firstname, 0, 99);
		$user->LegalRepresentativeLastName = substr(@$billing_address->address_lastname, 0, 99);
		$user->LegalRepresentativeAdress = substr(trim(@$billing_address->address_street . ' ' . @$billing_address->address_city), 0, 254);
		$user->LegalRepresentativeCountryOfResidence = @$billing_address->address_country->zone_code_2;
		$user->LegalRepresentativeNationality = @$billing_address->address_country->zone_code_2;
		$user->LegalRepresentativeBirthday = time();

		if(empty($user->Email)) {
			$shopConfig = hikamarket::config(false);
			$user->Email = $shopConfig->get('from_email');
		}
		if(empty($user->LegalRepresentativeNationality)) $user->LegalRepresentativeNationality = 'US';
		if(empty($user->LegalRepresentativeCountryOfResidence)) $user->LegalRepresentativeCountryOfResidence = 'US';

		$mangoUser = null;

		try {

			$mangoUser = $this->api->Users->Create($user);

		}
		catch (MangoPay\ResponseException $e) { $this->mangoDebug('Vendor:Users Create', $e, true); }
		catch (MangoPay\Exception $e) { $this->mangoDebug('Vendor:Users Create', $e, false); }
		catch (Exception $e) {}

		if(!empty($mangoUser->Id)) {
			$hkVendor = new stdClass();

			$hkVendor->vendor_params = new stdClass();
			if(!empty($vendor->vendor_params) && is_object($vendor->vendor_params))
				$hkVendor->vendor_params = $vendor->vendor_params;

			$hkVendor->vendor_id = (int)$vendor->vendor_id;
			$hkVendor->vendor_params->mangopay_id = $mangoUser->Id;
			$vendorClass = hikamarket::get('class.vendor');
			$vendorClass->save($hkVendor);
		}

		return $mangoUser;
	}

	public function getVendorWallet(&$vendor, $currency = null, $full = false) {
		if(!empty($vendor->vendor_params) && is_string($vendor->vendor_params))
			$vendor->vendor_params = unserialize($vendor->vendor_params);

		if(empty($vendor->vendor_params->mangopay_id)) {
			$mango_user = $this->createVendor($vendor);
			$vendor->vendor_params->mangopay_id = (int)$mango_user->Id;
		}

		if(empty($currency)) {
			$currencyClass = hikamarket::get('shop.class.currency');
			if(!empty($vendor->vendor_params->vendor_currency_id)) {
				$vendor_currency = $currencyClass->get($vendor->vendor_params->vendor_currency_id);
				if(!empty($vendor_currency))
					$currency = strtolower($vendor_currency->currency_code);
			}
			if(empty($currency)) {
				$shopConfig = hikamarket::config(false);
				$main_currency_id = $shopConfig->get('main_currency', 1);
				$main_currency = $currencyClass->get($main_currency_id);
				$currency = strtolower($main_currency->currency_code);
			}
		}

		if(!empty($vendor->vendor_params->mangopay_wallets[$currency]))
			return $vendor->vendor_params->mangopay_wallets[$currency];

		if(empty($this->api))
			$this->getAPI();

		$wallet_id = null;
		$retWallet = null;

		$userWallets = null;
		try {
			$userWallets = $this->api->Users->GetWallets( (int)$vendor->vendor_params->mangopay_id );
		}
		catch (MangoPay\ResponseException $e) { $this->mangoDebug('Users GetWallets', $e, true); }
		catch (MangoPay\Exception $e) { $this->mangoDebug('Users GetWallets', $e, false); }
		catch (Exception $e) {}

		if(!empty($userWallets)) {
			foreach($userWallets as $userWallet) {
				if(strtoupper($userWallet->Currency) == strtoupper($currency)) {
					$wallet_id = $userWallet->Id;
					$retWallet = $userWallet;
				}
			}
		}

		if(empty($wallet_id)) {
			$wallet = new MangoPay\Wallet();
			$wallet->Owners = array( (int)$vendor->vendor_params->mangopay_id );
			$wallet->Currency = strtoupper($currency);
			$wallet->Description = $vendor->vendor_name;
			$wallet->Tag = 'vendor#'.$vendor->vendor_id;

			try {

				$retWallet = $this->api->Wallets->Create($wallet);

			}
			catch (MangoPay\ResponseException $e) { $this->mangoDebug('Wallets Create', $e, true); }
			catch (MangoPay\Exception $e) { $this->mangoDebug('Wallets Create', $e, false); }
			catch (Exception $e) {}

			if(empty($retWallet->Id))
				return null;
			$wallet_id = $retWallet->Id;
		}

		if(!empty($wallet_id)) {
			$hkVendor = new stdClass();

			$hkVendor->vendor_params = new stdClass();
			if(!empty($vendor->vendor_params) && is_object($vendor->vendor_params))
				$hkVendor->vendor_params = $vendor->vendor_params;

			$hkVendor->vendor_id = (int)$vendor->vendor_id;
			if(empty($hkVendor->vendor_params->mangopay_wallets))
				$hkVendor->vendor_params->mangopay_wallets = array();
			$hkVendor->vendor_params->mangopay_wallets[$currency] = $wallet_id;
			$vendorClass = hikamarket::get('class.vendor');
			$vendorClass->save($hkVendor);
		}

		if($full)
			return $retWallet;
		return $wallet_id;
	}

	public function getVendorWallets(&$vendor) {
		if(!empty($vendor->vendor_params) && is_string($vendor->vendor_params))
			$vendor->vendor_params = unserialize($vendor->vendor_params);

		if(empty($vendor->vendor_params->mangopay_id)) {
			$mango_user = $this->createVendor($vendor);
			$vendor->vendor_params->mangopay_id = (int)$mango_user->Id;
		}

		if(empty($vendor->vendor_params->mangopay_wallets))
			return array();

		if(empty($this->api))
			$this->getAPI();


		$wallets = array();
		foreach($vendor->vendor_params->mangopay_wallets as $currency => $wallet_id) {
			try {
				$wallet = $this->api->Wallets->Get( (int)$wallet_id );

				if(in_array($vendor->vendor_params->mangopay_id, $wallet->Owners))
					$wallets[$currency] = $wallet;
			}
			catch (MangoPay\ResponseException $e) { $this->mangoDebug('Wallets Get', $e, true); }
			catch (MangoPay\Exception $e) { $this->mangoDebug('Wallets Get', $e, false); }
			catch (Exception $e) {}
		}
		return $wallets;
	}

	public function transfert($author, $src_wallet, $dst_wallet, $full_price, $fee, $currency_code) {
		if(empty($author))
			return false;

		$currency_code = strtoupper($currency_code);

		$transfer = new MangoPay\Transfer();
		$transfer->AuthorId = $author;

		$transfer->DebitedFunds = new MangoPay\Money();
		$transfer->DebitedFunds->Currency = $currency_code;
		$transfer->DebitedFunds->Amount = $full_price * 100;

		$transfer->Fees = new MangoPay\Money();
		$transfer->Fees->Currency = $currency_code;
		$transfer->Fees->Amount = $fee * 100;

		$transfer->DebitedWalletID = $src_wallet;
		$transfer->CreditedWalletId = $dst_wallet;

		try {

			$result = $this->api->Transfers->Create($transfer);

		}
		catch (MangoPay\ResponseException $e) { $this->mangoDebug('Transfers Create', $e, true); }
		catch (MangoPay\Exception $e) { $this->mangoDebug('Transfers Create', $e, false); }
		catch (Exception $e) {}

		if(empty($result) || empty($result->Id))
			return false;
		return $result->Id;
	}

	public function getCountryList() {
		return array(
			'AD','AE','AF','AG','AI','AL','AM','AO','AQ','AR','AS','AT','AU','AW','AX','AZ','BA','BB','BD','BE','BF','BG','BH','BI','BJ','BL','BM','BN','BO',
			'BQ','BR','BS','BT','BV','BW','BY','BZ','CA','CC','CD','CF','CG','CH','CI','CK','CL','CM','CN','CO','CR','CU','CV','CW','CX','CY','CZ','DE','DJ',
			'DK','DM','DO','DZ','EC','EE','EG','EH','ER','ES','ET','FI','FJ','FK','FM','FO','FR','GA','GB','GD','GE','GF','GG','GH','GI','GL','GM','GN','GP',
			'GQ','GR','GS','GT','GU','GW','GY','HK','HM','HN','HR','HT','HU','ID','IE','IL','IM','IN','IO','IQ','IR','IS','IT','JE','JM','JO','JP','KE','KG',
			'KH','KI','KM','KN','KP','KR','KW','KY','KZ','LA','LB','LC','LI','LK','LR','LS','LT','LU','LV','LY','MA','MC','MD','ME','MF','MG','MH','MK','ML',
			'MM','MN','MO','MP','MQ','MR','MS','MT','MU','MV','MW','MX','MY','MZ','NA','NC','NE','NF','NG','NI','NL','NO','NP','NR','NU','NZ','OM','PA','PE',
			'PF','PG','PH','PK','PL','PM','PN','PR','PS','PT','PW','PY','QA','RE','RO','RS','RU','RW','SA','SB','SC','SD','SE','SG','SH','SI','SJ','SK','SL',
			'SM','SN','SO','SR','SS','ST','SV','SX','SY','SZ','TC','TD','TF','TG','TH','TJ','TK','TL','TM','TN','TO','TR','TT','TV','TW','TZ','UA','UG','UM',
			'US','UY','UZ','VA','VC','VE','VG','VI','VN','VU','WF','WS','YE','YT','ZA','ZM','ZW'
		);
	}

	public function mangoDebug($ctx, $e, $r = false) {
		ob_start();
		if($r) {
			MangoPay\Logs::Debug('MangoPay\ResponseException Code', $e->GetCode());
			MangoPay\Logs::Debug('Message', $e->GetMessage());
			MangoPay\Logs::Debug('Details', $e->GetErrorDetails());
		} else {
			MangoPay\Logs::Debug('MangoPay\Exception Message', $e->GetMessage());
		}
		$dbg = ob_get_clean();
		$this->writeToLog($ctx . '<br/>' . $dbg);
		return;
	}

	public function onHikashopCronTrigger(&$messages) {
		if(!$this->cronCheck())
			return;
		$this->checkOrderStatuses($messages);
	}
	public function onPaymentConfiguration(&$element) {
		parent::onPaymentConfiguration($element);

		if(version_compare(PHP_VERSION, '5.3', '<')) {
			$app = JFactory::getApplication();
			$app->enqueueMessage('To work correctly, MangoPay API requires PHP 5.3 or higher', 'error');
		}
	}

	public function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'MangoPay';
		$element->payment_description = '';
		$element->payment_images = 'MasterCard,VISA,Credit_card';

		$element->payment_params->clientid = '';
		$element->payment_params->clientpassword = '';
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
	}

	public function onPaymentConfigurationSave(&$element) {
		$ret = parent::onPaymentConfigurationSave($element);

		jimport('joomla.filesystem.file');
		$lang = JFactory::getLanguage();
		$locale = strtolower(substr($lang->get('tag'), 0, 2));

		$opts = array(
			'option' => 'com_hikashop',
			'tmpl' => 'component',
			'ctrl' => 'checkout',
			'task' => 'notify',
			'notif_payment' => $this->name,
			'format' => 'html',
			'local' => $locale,
			'notif_id' => $element->payment_id,
		);
		$content = '<?php' . "\r\n";
		foreach($opts as $k => $v) {
			$v = str_replace(array('\'','\\'), '', $v);
			$content .= '$_GET[\''.$k.'\']=\''.$v.'\';'."\r\n".
						'$_REQUEST[\''.$k.'\']=\''.$v.'\';'."\r\n";
		}
		$content .= 'include(\'index.php\');'."\r\n";
		JFile::write(JPATH_ROOT.DS.$this->name.'_'.$element->payment_id.'.php', $content);

		$app = JFactory::getApplication();
		if(!$this->initMarket()) {
			$app->enqueueMessage('HikaMarket is not present in your website ; you need HikaMarket to use the MangoPay plugin', 'error');
		} else {
			$url = 'https://api.mangopay.com/v2/oauth/token';
			$h = curl_init($url);
			curl_setopt($h, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($h, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($h, CURLOPT_SSL_VERIFYHOST, 2);
			$result = curl_exec($h);
			if($result === false && curl_errno($h) != 0)
				$app->enqueueMessage('Testing API MangoPay. cURL error: ' . curl_error($h), 'error');
			$responseCode = (int)curl_getinfo($h, CURLINFO_HTTP_CODE);
			if($responseCode!=200)
				$app->enqueueMessage('Testing API MangoPay. HTTP return code: ' . $responseCode, 'warning');
			curl_close($h);
			$api = $this->getAPI();
			$vendorClass = hikamarket::get('class.vendor');
			$vendor = $vendorClass->get(1);
			$already_exist = !empty($vendor->vendor_params->mangopay_id);
			$main_vendor = $this->GetVendor($vendor);
			if(empty($main_vendor)) {
				$app->enqueueMessage('Failed to get/create the legal user for your main vendor.<br/>Please check that you right set your MangoPay credentials in the plugin (Joomla site)<br/>Please also check that your account have a valid published address', 'error');
			} else if(!$already_exist) {
				$app->enqueueMessage('We create a MangoPay legal user for your main vendor ; you should finish the configuration of that user in the HikaMarket front-end interface', 'message');
			}

			if(!empty($this->api)) {
				jimport('joomla.filesystem.file');
				jimport('joomla.filesystem.folder');
				if(!JFolder::exists($this->api->Config->TemporaryFolder)) {
					$app->enqueueMessage('The MangoPay API configuration folder does not exists', 'error');
				}
			} else {
				$app->enqueueMessage('MangoPay API Not available', 'error');
			}
		}

		return $ret;
	}
}
