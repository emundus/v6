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
class plgHikashoppaymentPaypalcheckout extends hikashopPaymentPlugin
{

	var $bncode = 'HIKARISOFTWARE_Cart_PPCP';
	var $liveMerchantId = 'FSXMPYPPVVVMG';
	var $sandboxMerchantId = 'NZFJZCZ2WRXPN';
	var $livePartnerClientId = 'AUnDFJMShyffM9evNWx7OD7J6qyklq-f37FENcc_7D_6RHg3TGtHgH9x_yuDiRGXpSOSGYKPP7hdfxCT';
	var $sandboxPartnerClientId = 'AcTqgpujxMEGpk8rKeEu9LOG6EgskJyK-AtCMdQBtZjN4zt51HLetLv3Y9plTUGrFCont2uzaZVi4aqe';

	var $accepted_currencies = array(
		'AUD','BRL','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD','SEK',
		'DKK','PLN','NOK','HUF','CZK','MXN','MYR','PHP','TWD','THB','ILS','TRY',
	);

	var $rounding = array('TWD' => 0, 'MYR' => 0, 'JPY' => 0, 'HUF' => 0);

	var $multiple = true;
	var $name = 'paypalcheckout';
	var $doc_form = 'paypalcheckout';

	var $pluginConfig = array(
		'sandbox' => array('HIKA_SANDBOX', 'boolean', 0),
		'connect' => array('PAYPAL_CHECKOUT_CONNECT', 'html'),
		'client_id' => array('PAYPAL_CHECKOUT_CLIENT_ID', 'input'),
		'client_secret' => array('PAYPAL_CHECKOUT_CLIENT_SECRET', 'input'),
		'merchant_id' => array('PAYPAL_CHECKOUT_MERCHANT_ID', 'input'),
		'brand_name' => array('PAYPAL_CHECKOUT_MERCHANT_NAME', 'input'),
		'capture' => array('INSTANTCAPTURE', 'boolean','1'),
		'landing_page' => array('PAYPAL_CHECKOUT_LANDING_PAGE', 'list', array(
				'LOGIN' =>'PAYPAL_CHECKOUT_LOGIN_PAGE',
				'BILLING' => 'PAYPAL_CHECKOUT_CREDIT_CARD_PAGE',
				'NO_PREFERENCE' => 'PAYPAL_CHECKOUT_NO_PREFERENCE',
			),
		),
		'disable_funding' => array(
			'PAYPAL_CHECKOUT_DISABLE_FUNDING',
			'checkbox',
			array(
				'card' =>'Credit or debit cards',
				'credit' => 'PayPal Credit (US, UK)',
				'paylater' => 'Pay Later (US, UK), Pay in 4 (AU), 4X PayPal (France), Paga en 3 plazos (Spain), Paga in 3 rate (Italy), Später Bezahlen (Germany)',
				'venmo' => 'Venmo',
				'bancontact' => 'Bancontact',
				'blik' => 'BLIK',
				'eps' => 'eps',
				'giropay' => 'giropay',
				'ideal' => 'iDEAL',
				'mercadopago' => 'Mercado Pago',
				'mybank' => 'MyBank',
				'p24' => 'Przelewy24',
				'sepa' => 'SEPA-Lastschrift',
				'sofort' => 'Sofort',
			),
			'tooltip' => 'PAYPAL_CHECKOUT_DISABLE_FUNDING_TOOLTIP',
		),
		'funding' => array(
			'PAYPAL_CHECKOUT_ENABLE_FUNDING',
			'checkbox',
			array(
				'card' =>'Credit or debit cards',
				'credit' => 'PayPal Credit (US, UK)',
				'paylater' => 'Pay Later (US, UK), Pay in 4 (AU), 4X PayPal (France), Paga en 3 plazos (Spain), Paga in 3 rate (Italy), Später Bezahlen (Germany)',
				'venmo' => 'Venmo',
				'bancontact' => 'Bancontact',
				'blik' => 'BLIK',
				'eps' => 'eps',
				'giropay' => 'giropay',
				'ideal' => 'iDEAL',
				'mercadopago' => 'Mercado Pago',
				'mybank' => 'MyBank',
				'p24' => 'Przelewy24',
				'sepa' => 'SEPA-Lastschrift',
				'sofort' => 'Sofort',
			),
			'tooltip' => 'PAYPAL_CHECKOUT_ENABLE_FUNDING_TOOLTIP',
		),
		'layout' => array('PAYPAL_CHECKOUT_BUTTON_LAYOUT', 'list', array(
				'vertical' => 'VERTICAL',
				'horizontal' => 'HORIZONTAL',
			),
		),
		'color' => array('PAYPAL_CHECKOUT_BUTTON_COLOR', 'list', array(
				'gold' => 'PAYPAL_CHECKOUT_GOLD',
				'blue' => 'PAYPAL_CHECKOUT_BLUE',
				'silver' => 'PAYPAL_CHECKOUT_SILVER',
				'white' => 'PAYPAL_CHECKOUT_WHITE',
				'black' => 'PAYPAL_CHECKOUT_BLACK',
			),
		),
		'shape' => array('PAYPAL_CHECKOUT_BUTTON_SHAPE', 'list', array(
				'rect' => 'PAYPAL_CHECKOUT_RECTANGLE',
				'pill' => 'PAYPAL_CHECKOUT_PILL',
			),
		),
		'label' => array('PAYPAL_CHECKOUT_BUTTON_LABEL', 'list', array(
				'paypal' => 'PayPal',
				'checkout' => 'PayPal Checkout',
				'buynow' => 'PayPal Buy Now',
				'pay' => 'Pay with PayPal',
			),
		),
		'tagline' => array('PAYPAL_CHECKOUT_BUTTON_TAGLINE', 'boolean', 1),
		'listing_position' => array('PAYPAL_CHECKOUT_PAY_LATER_MESSAGING_ON_PRODUCT_LISTINGS', 'list', array(
				'' => 'HIKASHOP_NO',
				'top' => 'HIKA_TOP',
				'middle' => 'HIKA_MIDDLE',
				'bottom' => 'HIKA_BOTTOM',
			),
		),
		'product_page_position' => array('PAYPAL_CHECKOUT_PAY_LATER_MESSAGING_ON_PRODUCT_PAGE', 'list', array(
				'' => 'HIKASHOP_NO',
				'topBegin' => 'TOP_BEGIN',
				'topEnd' => 'TOP_END',
				'leftBegin' => 'LEFT_BEGIN',
				'leftEnd' => 'LEFT_END',
				'rightBegin' => 'RIGHT_BEGIN',
				'rightMiddle' => 'RIGHT_MIDDLE',
				'rightEnd' => 'RIGHT_END',
				'bottomBegin' => 'BOTTOM_BEGIN',
				'bottomMiddle' => 'BOTTOM_MIDDLE',
				'bottomEnd' => 'BOTTOM_END',
			),
		),
		'cart_page_position' => array('PAYPAL_CHECKOUT_PAY_LATER_MESSAGING_ON_CART_PAGE', 'list', array(
				'' => 'HIKASHOP_NO',
				'bottom' => 'HIKA_BOTTOM',
			),
		),
		'checkout_page_position' => array('PAYPAL_CHECKOUT_PAY_LATER_MESSAGING_ON_CHECKOUT_CART', 'list', array(
				'' => 'HIKASHOP_NO',
				'top' => 'HIKA_TOP',
				'bottom' => 'HIKA_BOTTOM',
			),
		),
		'paylater_messaging_color' => array('PAYPAL_CHECKOUT_PAY_LATER_MESSAGING_COLOR', 'list', array(
				'black' => 'PAYPAL_CHECKOUT_BLACK',
				'white' => 'PAYPAL_CHECKOUT_WHITE',
				'monochrome' => 'PAYPAL_CHECKOUT_MONOCHROME',
				'grayscale' => 'PAYPAL_CHECKOUT_GRAYSCALE',
			),
		),
		'debug' => array('DEBUG', 'boolean', 0),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
	);

	function __construct(&$subject, $config) {
		return parent::__construct($subject, $config);
	}

	public function onHikashopBeforeDisplayView(&$view) {
		$app = JFactory::getApplication();
		if(version_compare(JVERSION,'4.0','<'))
			$admin = $app->isAdmin();
		else
			$admin = $app->isClient('administrator');
		if($admin)
			return;
		$viewName = $view->getName();
		$layout = $view->getLayout();

		if($viewName == 'product' && $layout == 'listing' && hikaInput::get()->getVar('hikashop_front_end_main', 0) && hikaInput::get()->getVar('task') == 'listing') {
			$this->processListing($view);
		}
		if($viewName == 'product' && $layout == 'show') {
			$this->processDetailsPage($view);
		}
	}

	public function onHikashopAfterDisplayView(&$view) {
		$app = JFactory::getApplication();
		if(version_compare(JVERSION,'4.0','<'))
			$admin = $app->isAdmin();
		else
			$admin = $app->isClient('administrator');
		if($admin)
			return;
		$viewName = $view->getName();
		$layout = $view->getLayout();

		if($viewName == 'cart' && $layout == 'show') {
			$this->processCart($view);
		}
	}
	public function onBeforeCheckoutViewDisplay($layout, &$view) {
		if($layout != 'cart')
			return;

		$method = $this->getPaymentMethod();
		if(!$method) {
			return;
		}
		$position = $this->getPosition($method, 'listing');
		if(empty($position))
			return;

		$data = $this->getMessagingHTML(0, 'payment', $method);

		if(!isset($view->extraData))
			$view->extraData = array();

		if(!isset($view->extraData[$view->module_position]))
			$view->extraData[$view->module_position] = new stdClass();

		if(!isset($view->extraData[$view->module_position]->$position))
			$view->extraData[$view->module_position]->$position = array();
		array_push($view->extraData[$view->module_position]->$position, $data);
	}

	private function processListing(&$view) {

		$method = $this->getPaymentMethod();
		if(!$method) {
			return;
		}
		$position = $this->getPosition($method, 'listing');
		if(empty($position))
			return;

		$data = $this->getMessagingHTML(0, 'category', $method);

		if(!isset($view->element->extraData))
			$view->element->extraData = new stdClass();

		if(!isset($view->element->extraData->$position))
			$view->element->extraData->$position = array();
		array_push($view->element->extraData->$position, $data);
	}

	private function processCart(&$view) {
		if(empty($view->cart->total->prices)) {
			return;
		}

		$method = $this->getPaymentMethod();
		if(!$method) {
			return;
		}
		$position = $this->getPosition($method, 'cart');
		if(empty($position))
			return;

		$price_value = 'price_value';
		if($view->config->get('price_with_tax')) {
			$price_value = 'price_value_with_tax';
		}
		$mainPrice = reset($view->cart->total->prices);

		if(empty($mainPrice->$price_value) && $mainPrice->$price_value > 0) {
			return;
		}

		$data = $this->getMessagingHTML($amount, 'cart', $method);

		echo $data;
	}

	private function processDetailsPage(&$view) {
		if(empty($view->element->prices)) {
			return;
		}
		$method = $this->getPaymentMethod();
		if(!$method) {
			return;
		}
		$position = $this->getPosition($method, 'product_page');
		if(empty($position))
			return;

		$price_value = 'price_value';
		if($view->params->get('price_with_tax')) {
			$price_value = 'price_value_with_tax';
		}
		$mainPrice = reset($view->element->prices);

		if(empty($mainPrice->$price_value) && $mainPrice->$price_value > 0) {
			return;
		}

		$data = $this->getMessagingHTML($mainPrice->$price_value, 'product', $method);

		if(!isset($view->element->extraData))
			$view->element->extraData = new stdClass();

		if(!isset($view->element->extraData->$position))
			$view->element->extraData->$position = array();
		array_push($view->element->extraData->$position, $data);
	}

	private function getMessagingHTML($amount, $type, &$method) {
		static $currency = null;
		if($currency == null) {
			$currency_id = hikashop_getCurrency();
			$currencyClass = hikashop_get('class.currency');
			$currencyObj = $currencyClass->get($currency_id);
			$currency = $currencyObj->currency_code;
		}

		$attribs = '';
		if(!empty($method->payment_params->paylater_messaging_color)) {
			$attribs.=' data-pp-style-text-color="'.$method->payment_params->paylater_messaging_color.'"';
		}
		$url = 'https://www.paypal.com';
		$amount = number_format(round((float)$amount,2), 2, '.', '');
		return '<div data-pp-message
		data-pp-placement="'.$type.'" 
		data-pp-amount="'.$amount.'" 
		data-pp-currency="'.$currency.'" 
		'.$attribs.'
		></div>
		<script src="'.$url.'/sdk/js?client-id='.$method->payment_params->client_id.'&components=messages" data-partner-attribution-id="'.$this->bncode.'"></script>
';
	}

	private function getPosition(&$method, $type) {
		if(!empty($method->payment_params->funding)) {
			if(is_string($method->payment_params->funding)) {
				$fundings = explode(',', $method->payment_params->funding);
			} else {
				$fundings = $method->payment_params->funding;
			}
			if(!in_array('paylater', $fundings))
				return '';
		}
		$var = $type.'_position';
		if(!empty($method->payment_params->$var)) {
			return $method->payment_params->$var;
		}
		return '';
	}

	private function getPaymentMethod() {
		static $method = null;
		if(is_null($method)) {
			$class = hikashop_get('class.cart');
			$cart = $class->getFullCart();
			if(!empty($cart->usable_methods->payment)) {
				foreach($cart->usable_methods->payment as $payment) {
					if($payment->payment_type == $this->name) {
						$method = $payment;
						break;
					}
				}
			}
			if(is_null($method))
				$method = $this->loadFirstPaymentMethodFound();
		}

		if(empty($method->payment_params->client_id))
			$method = false;

		return $method;
	}

	private function loadFirstPaymentMethodFound() {
		static $result = null;
		if(!is_null($result))
			return $result;

		$db = JFactory::getDBO();
		$where = array('payment_type = '.$db->Quote($this->name),'payment_published=\'1\'');
		$currency = hikashop_getCurrency();
		if(!empty($currency)){
			$where[] = "(payment_currency IN ('','_','all') OR payment_currency LIKE '%,".intval($currency).",%')";
		}
		hikashop_addACLFilters($where,'payment_access');
		$db->setQuery('SELECT * FROM `#__hikashop_payment` WHERE '.implode(' AND ',$where).' ORDER BY payment_ordering ASC');
		$result = $db->loadObject();

		if(empty($result))
			$result = false;

		if(!empty($result->payment_params)) {
			$result->payment_params = hikashop_unserialize($result->payment_params);
		}
		if(!empty($result->payment_name))
			$result->payment_name = hikashop_translate($result->payment_name);
		if(!empty($result->payment_description))
			$result->payment_description = hikashop_translate($result->payment_description);
		return $result;
	}


	public function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$this->loadJS();

		$this->notify_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&order_id='.$order->order_id.'&tmpl=component&lang='.$this->locale . $this->url_itemid;
		$this->cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id . $this->url_itemid;
		$this->orderData = $this->getOrderData($order);

		if(!empty($this->payment_params->debug)) {
			hikashop_writeToLog($this->orderData);
			hikashop_writeToLog($this->params);
		}

		return $this->showPage('end');

	}

	public function onPaymentConfiguration(&$element) {
		parent::onPaymentConfiguration($element);

		if(empty($element->payment_params->client_id) || empty($element->payment_params->client_secret)) {
			if(empty($element->payment_params->sandbox)) {
				$element->payment_params->sandbox = 0;
			}			
			$this->pluginConfig['connect'][2] = $this->getConnectButtonHTML($element->payment_params->sandbox);
		} else {
			$this->pluginConfig['connect'][2] = $this->checkMerchantStatus($element);
			$this->pluginConfig['connect'][2] .= $this->getChangeLinkedAccountHTML();
		}
		$config = hikashop_config();
		$round_calculations = $config->get('round_calculations');
		if(empty($round_calculations)){
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('PAYPAL_CHECKOUT_ROUND_PRICES_WARNING'));
		}

	}

	public function checkMerchantStatus(&$element) {
		if(empty($element->payment_params->merchant_id)) {
			return '';
		}
		$url = 'https://api-m.paypal.com';
		$merchantId = $this->liveMerchantId;

		if(empty($element->payment_params->sandbox)) {
			$element->payment_params->sandbox = 0;
		}
		if(!empty($element->payment_params->sandbox)) {
			$url = 'https://api-m.sandbox.paypal.com';
			$merchantId = $this->sandboxMerchantId;
		}

		$curl = curl_init();
		$post = "grant_type=client_credentials";
    	curl_setopt_array($curl, array(
			CURLOPT_URL => $url."/v1/oauth2/token",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			CURLOPT_USERPWD => $element->payment_params->client_id.":".$element->payment_params->client_secret,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $post,
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/x-www-form-urlencoded",
				"PayPal-Partner-Attribution-Id: ".$this->bncode,
    		),
			CURLOPT_CAINFO => __DIR__.'/cacert.pem',
			CURLOPT_CAPATH => __DIR__.'/cacert.pem',
			CURLINFO_HEADER_OUT => true,
		));
		$curl_result = curl_exec($curl);
		if(empty($curl_result)) {
			hikashop_writeToLog(curl_getinfo($curl));
			hikashop_writeToLog($post);
			hikashop_writeToLog($curl_result);
			return hikashop_display('Returned data from Access Token Request not valid','error',true);
		}
		$array = json_decode($curl_result, true);
		if(empty($array)) {
			hikashop_writeToLog(curl_getinfo($curl));
			hikashop_writeToLog($post);
			hikashop_writeToLog($curl_result);
			return hikashop_display('Returned data from Access Token Request not valid','error',true);
		}
		if(empty($array['access_token'])) {
			hikashop_writeToLog($array);
			return hikashop_display($array['error_description'],'error',true);
		}
		$token = $array['access_token'];

		$curl = curl_init();
    	curl_setopt_array($curl, array(
			CURLOPT_URL => $url."/v1/customer/partners/".$merchantId."/merchant-integrations/".$element->payment_params->merchant_id,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json",
				"Authorization: Bearer ".$token,
				"PayPal-Partner-Attribution-Id: ".$this->bncode,
    		),
			CURLOPT_CAINFO => __DIR__.'/cacert.pem',
			CURLOPT_CAPATH => __DIR__.'/cacert.pem',
			CURLINFO_HEADER_OUT => true,
		));
		$curl_result = curl_exec($curl);
		$array = json_decode($curl_result, true);
		if(empty($array['primary_email_confirmed'])) {
			$msg = JText::_('PAYPAL_CHECKOUT_PRIMARY_EMAIL_NOT_CONFIRMED');
			if(!empty($array['message'])) {
				$msg = $array['message'] .'<br/>'. $msg;
			}
			return hikashop_display($msg,'error',true);
		}

		if(empty($array['payments_receivable'])) {
			$msg = JText::_('PAYPAL_CHECKOUT_PAYMENTS_NOT_RECEIVABLE');
			if(!empty($array['message'])) {
				$msg = $array['message'] .'<br/>'. $msg;
			}
			return hikashop_display($msg,'error',true);
		}
		return hikashop_display(JText::_('PAYPAL_CHECKOUT_SUCCESSFULLY_CONNECTED'),'success',true);
	}

	private function getChangeLinkedAccountHTML() {
		return '
		<script>
			window.hikashop.changeLinkedAccount = function() {
				var clientId = document.getElementById(\'data_payment_payment_params_client_id\');
				var clientSecret = document.getElementById(\'data_payment_payment_params_client_secret\');
				var merchantId = document.getElementById(\'data_payment_payment_params_merchant_id\');
				clientId.value = \'\';
				clientSecret.value = \'\';
				merchantId.value = \'\';

				Joomla.submitbutton(\'apply\');
			}
		</script>
		<a href="#" onclick="window.hikashop.changeLinkedAccount();return false;" class="hikabtn hikabtn-primary">'.JText::_('PAYPAL_CHECKOUT_CHANGE_LINKED_ACCOUNT').'</a>
		';
	}

	public function getConnectButtonHTML($sandbox = false, $callback = '') {
		hikashop_loadJslib('notify');
		$nonce = substr(hash('sha512',mt_rand()),17,70);
		$AJAX_URL = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&onboarding=1&nonce='.urlencode($nonce).'&tmpl=component';
		$partnerClientId = $this->livePartnerClientId;
		$merchantId = $this->liveMerchantId;
		$url = 'https://www.paypal.com';
		if(empty($sandbox)) {
			$sandbox = 0;
		}
		if(!empty($sandbox)) {
			$partnerClientId = $this->sandboxPartnerClientId;
			$merchantId = $this->sandboxMerchantId;
			$url = 'https://www.sandbox.paypal.com';
		}

		$closeURL = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&closepopup=1&tmpl=component';
		$params = array(
			'partnerId' => $merchantId,
			'product' => 'ppcp',
			'integrationType' => 'FO',
			'features' => 'PAYMENT',
			'partnerClientId' => $partnerClientId,
			'partnerLogoUrl' => 'https://www.hikashop.com/images/branding/hikashop_logo1.png',
			'displayMode' => 'minibrowser',
			'sellerNonce' => $nonce,
		);
		if(empty($callback)) {
			$js = 'Joomla.submitbutton(\'apply\');';
			$selector = '#data_payment_payment_params_sandbox input';
		} else {
			$js = $callback.'();';
			$selector = '#sanbox input';
		}
		$html = '
		<a
			id="paypal_connect"
			class="direct"
			target="_blank"
			data-paypal-onboard-complete="onboardedCallback"
			href="'.$url.'/bizsignup/partner/entry?'.http_build_query($params).'"
			data-paypal-button="PPLtBlue"
			>
			'.JText::_('PAYPAL_CHECKOUT_CONNECT_TO_PAYPAL_CHECKOUT').'
		</a>
		<script id="paypal-js" src="https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js"></script>
		<script>
			function onboardedCallback(authCode, sharedId) {
				var callbackURL = \''.$AJAX_URL.'\';
				console.log(window.hikashop.isPayPalSandbox);
				if(window.hikashop.isPayPalSandbox==1) {
					callbackURL += \'&sandbox=1\';
				}
				callbackURL += \'&authCode=\' + authCode + \'&sharedId=\' + sharedId;
				window.Oby.xRequest(callbackURL,  {mode:\'POST\'}, function(xhr){
					var resp = window.Oby.evalJSON(xhr.responseText);
					if(resp && resp.error) {
						console.log(callbackURL);
						console.log(resp);
						jQuery(document.getElementById(\'paypal_connect\')).notify({title:resp.errorTitle, text:resp.errorMessage, image:\'<i class=\"fas fa-3x fa-file-invoice\"></i>\', globalPosition:\'top right\'},{style:"metro",className:\'error\', autoHide: false, arrowShow:true});
					} else {
						var clientId = document.getElementById(\'data_payment_payment_params_client_id\');
						var clientSecret = document.getElementById(\'data_payment_payment_params_client_secret\');
						var merchantId = document.getElementById(\'data_payment_payment_params_merchant_id\');
						clientId.value = resp.clientId;
						clientSecret.value = resp.clientSecret;
						merchantId.value = resp.merchantId;

						'.$js.'
					}
				});
			}
			window.hikashop.changeSandbox = function(sandbox) {
				window.hikashop.isPayPalSandbox = sandbox;

				var src = \'https://www.paypal\';
				var dst = \'https://www.paypal\';
				if(sandbox==1) {
					dst = \'https://www.sandbox.paypal\';
				} else {
					src = \'https://www.sandbox.paypal\';
				}
				document.getElementById(\'paypal_connect\').href = document.getElementById(\'paypal_connect\').href.replace(src, dst);

				if(sandbox) {
					src = \'&sandbox=0\';
					dst = \'&sandbox=1\';
				} else {
					src = \'&sandbox=1\';
					dst = \'&sandbox=0\';
				}
				document.getElementById(\'paypal_connect\').href = document.getElementById(\'paypal_connect\').href.replace(src, dst);

				if(sandbox) {
					src = \''.$this->livePartnerClientId.'\';
					dst = \''.$this->sandboxPartnerClientId.'\';
				} else {
					src = \''.$this->sandboxPartnerClientId.'\';
					dst = \''.$this->livePartnerClientId.'\';
				}
				document.getElementById(\'paypal_connect\').href = document.getElementById(\'paypal_connect\').href.replace(src, dst);

				if(sandbox) {
					src = \''.$this->liveMerchantId.'\';
					dst = \''.$this->sandboxMerchantId.'\';
				} else {
					src = \''.$this->sandboxMerchantId.'\';
					dst = \''.$this->liveMerchantId.'\';
				}
				document.getElementById(\'paypal_connect\').href = document.getElementById(\'paypal_connect\').href.replace(src, dst);


			}
			window.hikashop.setSandboxFlag = function() {
				document.querySelectorAll(\''.$selector.'\').forEach((elem) => {
					if(elem.checked) {
						window.hikashop.changeSandbox(elem.value);
					}
				});
			}

			window.hikashop.ready( function() {
				window.hikashop.isPayPalSandbox = '.(int)empty($sandbox).';
				window.hikashop.setSandboxFlag();
				document.querySelectorAll(\''.$selector.'\').forEach((elem) => {
					elem.addEventListener("click", function(event) {
						window.hikashop.changeSandbox(event.target.value);
					});
				  });
			});
		</script>
		';
		return $html;
	}

	private function processOnboarding() {
		$result = new stdClass();
		$result->errorTitle = JText::_('PAYPAL_CHECKOUT_ERROR_OCCURRED');
		$result->errorMessage = '';
		$result->error = false;

		$nonce = hikaInput::get()->getString('nonce');
		if(empty($nonce)) {
			$result->error = true;
			$result->errorMessage = 'Nonce is missing';
			return $result;
		}
		$authCode = hikaInput::get()->getString('authCode');
		if(empty($authCode)) {
			$result->error = true;
			$result->errorMessage = 'authCode is missing';
			return $result;
		}
		$sharedId = hikaInput::get()->getString('sharedId');
		if(empty($sharedId)) {
			$result->error = true;
			$result->errorMessage = 'sharedId is missing';
			return $result;
		}
		$sharedId = base64_encode($sharedId.':');

		$sandbox = hikaInput::get()->getInt('sandbox');
		$url = 'https://api-m.paypal.com';
		$merchantId = $this->liveMerchantId;
		if($sandbox) {
			$url = 'https://api-m.sandbox.paypal.com';
			$merchantId = $this->sandboxMerchantId;
		}

		$curl = curl_init();
		$post = "grant_type=authorization_code&code=".urlencode($authCode)."&code_verifier=".urlencode($nonce);
    	curl_setopt_array($curl, array(
			CURLOPT_URL => $url."/v1/oauth2/token",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $post,
			CURLOPT_HTTPHEADER => array(
				"Content-Type: text/plain",
				"Authorization: Basic ".$sharedId,
				"PayPal-Partner-Attribution-Id: ".$this->bncode,
    		),
			CURLOPT_CAINFO => __DIR__.'/cacert.pem',
			CURLOPT_CAPATH => __DIR__.'/cacert.pem',
			CURLINFO_HEADER_OUT => true,
		));
		$curl_result = curl_exec($curl);
		$array = json_decode($curl_result, true);
		if(empty($array)) {
			$result->error = true;
			$result->errorMessage = 'Returned data from Acquire Seller Access not valid';
			hikashop_writeToLog(curl_getinfo($curl));
			hikashop_writeToLog($post);
			hikashop_writeToLog($curl_result);
			return $result;
		}
		if(empty($array['access_token'])) {
			$result->error = true;
			$result->errorMessage = 'Returned data from Acquire Seller Access does not contain access token';
			hikashop_writeToLog(curl_getinfo($curl));
			hikashop_writeToLog($post);
			hikashop_writeToLog($array);
			return $result;
		}
		$token = $array['access_token'];

		$curl = curl_init();
    	curl_setopt_array($curl, array(
			CURLOPT_URL => $url."/v1/customer/partners/".$merchantId."/merchant-integrations/credentials",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json",
				"Authorization: Bearer ".$token,
				"PayPal-Partner-Attribution-Id: ".$this->bncode,
    		),
			CURLOPT_CAINFO => __DIR__.'/cacert.pem',
			CURLOPT_CAPATH => __DIR__.'/cacert.pem',
			CURLINFO_HEADER_OUT => true,
		));
		$curl_result = curl_exec($curl);
		$array = json_decode($curl_result, true);
		if(empty($array)) {
			$result->error = true;
			$result->errorMessage = 'Returned data from Get Seller Credentials not valid';
			hikashop_writeToLog(curl_getinfo($curl));
			hikashop_writeToLog($curl_result);
			return $result;
		}
		if(empty($array['client_id'])) {
			$result->error = true;
			$result->errorMessage = 'Returned data from Get Seller Credentials does not contain client ID';
			hikashop_writeToLog(curl_getinfo($curl));
			hikashop_writeToLog($array);
			return $result;
		}
		if(empty($array['client_secret'])) {
			$result->error = true;
			$result->errorMessage = 'Returned data from Get Seller Credentials does not contain client secret';
			hikashop_writeToLog(curl_getinfo($curl));
			hikashop_writeToLog($array);
			return $result;
		}
		if(empty($array['payer_id'])) {
			$result->error = true;
			$result->errorMessage = 'Returned data from Get Seller Credentials does not contain payer ID';
			hikashop_writeToLog(curl_getinfo($curl));
			hikashop_writeToLog($array);
			return $result;
		}

		$result->clientId = $array['client_id'];
		$result->clientSecret = $array['client_secret'];
		$result->merchantId = $array['payer_id'];

		return $result;
	}

	public function onPaymentNotification(&$statuses) {
		$onboarding = hikaInput::get()->getInt('onboarding');
		if($onboarding) {
			echo json_encode($this->processOnboarding());
			exit;
		}
		$closepopup = hikaInput::get()->getInt('closepopup');
		if($closepopup) {
			echo "
			<script>
			window.close();
			</script>
			";
			exit;
		}

		$order_id = hikaInput::get()->getInt('order_id');
		$paypal_id = hikaInput::get()->getString('paypal_id');

		if(empty($paypal_id)) {
			hikashop_writeToLog('paypal_id missing !');
			hikashop_writeToLog($order_id);
			return false;
		}

		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;

		$this->loadOrderData($dbOrder);

		if($this->payment_params->debug) {
			hikashop_writeToLog($dbOrder);
			hikashop_writeToLog($paypal_id);
		}

		try {

			require __DIR__ . '/vendor/autoload.php';

			if($this->payment_params->sandbox) {
				$env = new PayPalCheckoutSdk\Core\SandboxEnvironment($this->payment_params->client_id, $this->payment_params->client_secret);
			} else {
				$env = new PayPalCheckoutSdk\Core\ProductionEnvironment($this->payment_params->client_id, $this->payment_params->client_secret);
			}

			$client = new PayPalCheckoutSdk\Core\PayPalHttpClient($env);
			$request = new PayPalCheckoutSdk\Orders\OrdersGetRequest($paypal_id);
			$request->headers['PayPal-Partner-Attribution-Id'] = $this->bncode;
			$response = $client->execute($request);

			$ok = $this->checkResponse($response, $dbOrder);
		} catch(Exception $e) {
			hikashop_writeToLog($e->getMessage());
		}


		if(!empty($ok)) {
			$history = new stdClass();
			$history->notified = 1;
			$history->amount = @$ok->amount->value.@$ok->amount->currency_code;
			$history->data = 'PayPal transaction id:'.$paypal_id;
			$this->modifyOrder($order_id, $this->payment_params->verified_status, $history, true);
		} else {
			hikashop_writeToLog($paypal_id);
			hikashop_writeToLog($response);
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);
		}

		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$dbOrder->order_id . $this->url_itemid;
		$app = JFactory::getApplication();
		$app->redirect($return_url);
	}

	private function checkResponse(&$response, &$dbOrder) {
		if($response->result->status != 'COMPLETED') {
			return false;
		}

		if(!isset($response->result->purchase_units) || !count($response->result->purchase_units))
			return false;
		$purchaseUnit = reset($response->result->purchase_units);

		if($this->currency->currency_locale['int_frac_digits'] > 2)
			$this->currency->currency_locale['int_frac_digits'] = 2;
		$rounding = 2;
		if(isset($this->rounding[$this->currency->currency_code]))
			$rounding = $this->rounding[$this->currency->currency_code];
		if($purchaseUnit->amount->value < round($dbOrder->order_full_price, $rounding)) {
			return false;
		}

		if($purchaseUnit->amount->currency_code != $this->currency->currency_code) {
			return false;
		}

		return $purchaseUnit;
	}

	public function getPaymentDefaultValues(&$element) {
		$element->payment_name='PayPal Checkout';
		$element->payment_description='You can pay with PayPal Checkout using this payment method';
		$element->payment_images='PayPal';

		$element->payment_params->instant_capture = 1;
		$element->payment_params->landing_page='NO_PREFERENCE';
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->verified_status='confirmed';
		$element->payment_params->funding = 'paylater';
		$element->payment_params->layout = 'vertical';
		$element->payment_params->color = 'gold';
		$element->payment_params->label = 'paypal';
		$element->payment_params->shape = 'rect';
		$element->payment_params->tagline = '1';
		$element->payment_params->listing_position = 'bottom';
		$element->payment_params->product_page_position = 'rightMiddle';
		$element->payment_params->cart_page_position = 'bottom';
		$element->payment_params->checkout_page_position = 'bottom';
		$element->payment_params->paylater_messaging_color = 'black';
	}

	private function getOrderData(&$order) {
		if($this->currency->currency_locale['int_frac_digits'] > 2)
			$this->currency->currency_locale['int_frac_digits'] = 2;

		$rounding = 2;
		if(isset($this->rounding[$this->currency->currency_code]))
			$rounding = $this->rounding[$this->currency->currency_code];

		$orderData = new stdClass();
		if(!empty($this->payment_params->capture)) {
			$orderData->intent = 'CAPTURE';
		} else {
			$orderData->intent = 'AUTHORIZE';
		}
		$orderData->application_context = new stdClass();
		if(empty($order->order_shipping_id)) {
			$orderData->application_context->shipping_preference = 'NO_SHIPPING';
		}elseif(!empty($order->cart->billing_address)) {
			$orderData->application_context->shipping_preference = 'SET_PROVIDED_ADDRESS';
		}
		if(!empty($this->payment_params->brand_name))
			$orderData->application_context->brand_name = mb_substr($this->payment_params->brand_name, 0, 127);
		$orderData->application_context->cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id . $this->url_itemid;
		if(!empty($this->payment_params->landing_page)) {
			$orderData->application_context->landing_page = $this->payment_params->landing_page;
		}

		$orderData->payer = new stdClass();
		$orderData->payer->email_address = $this->user->user_email;
		if(!empty($order->cart->billing_address->address_firstname) && !empty($order->cart->billing_address->address_lastname)) {
			$orderData->payer->name = new stdClass();
			$orderData->payer->name->given_name = $order->cart->billing_address->address_firstname;
			$orderData->payer->name->surname = $order->cart->billing_address->address_lastname;
		}
		if(!empty($order->cart->billing_address)) {
			$orderData->payer->address = new stdClass();
			if(!empty($order->cart->billing_address->address_street)) {
				$orderData->payer->address->address_line_1 = $order->cart->billing_address->address_street;
			}
			if(!empty($order->cart->billing_address->address_street2)) {
				$orderData->payer->address->address_line_2 = $order->cart->billing_address->address_street2;
			}
			if(!empty($order->cart->billing_address->address_city)) {
				$orderData->payer->address->admin_area_2 = $order->cart->billing_address->address_city;
			}
			if(!empty($order->cart->billing_address->address_post_code)) {
				$orderData->payer->address->postal_code = $order->cart->billing_address->address_post_code;
			}
			if(!empty($order->cart->billing_address->address_state->zone_name)) {
				$orderData->payer->address->admin_area_1 = $order->cart->billing_address->address_state->zone_name;
			}
			if(!empty($order->cart->billing_address->address_country->zone_code_2)) {
				$orderData->payer->address->country_code = $order->cart->billing_address->address_country->zone_code_2;
			}
		}
		$purchaseUnit = new stdClass();
		$purchaseUnit->invoice_id = $order->order_id;
		$purchaseUnit->description = mb_substr(JText::_('ORDER_NUMBER').' '.$order->order_number,0,127);
		$purchaseUnit->items = [];
		$config = hikashop_config();
		$group = $config->get('group_options',0);
		$item_total = 0;
		$tax_total = 0;
		foreach($order->cart->products as $product) {
			if($group && $product->order_product_option_parent_id) continue;
			if(empty($product->order_product_quantity)) continue;

			$item = new stdClass();
			$item->name = mb_substr(strip_tags($product->order_product_name),0,127);
			$item->quantity = $product->order_product_quantity;
			$item->sku = mb_substr($product->order_product_code,0,127);
			$item->unit_amount = new stdClass();
			$item->unit_amount->value = number_format(round($product->order_product_price, (int)$this->currency->currency_locale['int_frac_digits']), $rounding, '.', '');
			$item->unit_amount->currency_code = $this->currency->currency_code;
			$item->tax = new stdClass();
			$item->tax->value = number_format(round($product->order_product_tax, (int)$this->currency->currency_locale['int_frac_digits']), $rounding, '.', '');
			$item->tax->currency_code = $this->currency->currency_code;
			$purchaseUnit->items[] = $item;
			$item_total += round($product->order_product_price, $rounding) * $product->order_product_quantity;
			$tax_total += round($product->order_product_tax, $rounding) * $product->order_product_quantity;
		}
		if(!empty($order->cart->additional)){
			foreach($order->cart->additional as $product) {
				if(empty($product->order_product_price) || $product->order_product_price == 0) continue;
				$item = new stdClass();
				$item->name =  mb_substr($JText::_(strip_tags($product->order_product_name)),0,127);
				$item->quantity = 1;
				$item->sku = mb_substr($product->order_product_code,0,127);
				$item->unit_amount = new stdClass();
				$item->unit_amount->value = number_format(round($product->order_product_price, (int)$this->currency->currency_locale['int_frac_digits']), $rounding, '.', '');
				$item->unit_amount->currency_code = $this->currency->currency_code;
				$item->tax = new stdClass();
				$item->tax->value = number_format(round($product->order_product_tax, (int)$this->currency->currency_locale['int_frac_digits']), $rounding, '.', '');
				$item->tax->currency_code = $this->currency->currency_code;
				$purchaseUnit->items[] = $item;
				$item_total += round($product->order_product_price, $rounding);
				$tax_total += round($product->order_product_tax, $rounding);
			}
		}
		if(!empty($order->order_payment_price) && bccomp(sprintf('%F',$order->order_payment_price), 0, 5)) {
			$item = new stdClass();
			$item->name = mb_substr(JText::_('HIKASHOP_PAYMENT'),0,127);
			$item->quantity = 1;
			$item->sku = 'payment_fee';
			$item->unit_amount = new stdClass();
			$item->unit_amount->value = number_format(round($order->order_payment_price-$order->order_payment_tax, (int)$this->currency->currency_locale['int_frac_digits']), $rounding, '.', '');
			$item->unit_amount->currency_code = $this->currency->currency_code;
			$item->tax = new stdClass();
			$item->tax->value = number_format(round($order->order_payment_tax, (int)$this->currency->currency_locale['int_frac_digits']), $rounding, '.', '');
			$item->tax->currency_code = $this->currency->currency_code;
			$purchaseUnit->items[] = $item;
			$item_total += round($order->order_payment_price-$order->order_payment_tax, $rounding);
			$tax_total += round($order->order_payment_tax, $rounding);
		}

		if(!empty($order->cart->shipping_address)) {
			$purchaseUnit->shipping = new stdClass();
			if(!empty($order->cart->shipping_address->address_firstname) && !empty($order->cart->shipping_address->address_lastname)) {
				$purchaseUnit->shipping->name = new stdClass();
				$purchaseUnit->shipping->name->full_name = $order->cart->shipping_address->address_firstname. ' ' . $order->cart->shipping_address->address_lastname;
			}
			$purchaseUnit->shipping->address = new stdClass();
			if(!empty($order->cart->shipping_address->address_street)) {
				$purchaseUnit->shipping->address->address_line_1 = $order->cart->shipping_address->address_street;
			}
			if(!empty($order->cart->shipping_address->address_street2)) {
				$purchaseUnit->shipping->address->address_line_2 = $order->cart->shipping_address->address_street2;
			}
			if(!empty($order->cart->shipping_address->address_city)) {
				$purchaseUnit->shipping->address->admin_area_2 = $order->cart->shipping_address->address_city;
			}
			if(!empty($order->cart->shipping_address->address_post_code)) {
				$purchaseUnit->shipping->address->postal_code = $order->cart->shipping_address->address_post_code;
			}
			if(!empty($order->cart->shipping_address->address_state->zone_name)) {
				$purchaseUnit->shipping->address->admin_area_1 = $order->cart->shipping_address->address_state->zone_name;
			}
			if(!empty($order->cart->shipping_address->address_country->zone_code_2)) {
				$purchaseUnit->shipping->address->country_code = $order->cart->shipping_address->address_country->zone_code_2;
			}
		}
		$purchaseUnit->amount = new stdClass();
		$purchaseUnit->amount->value = number_format(round($order->cart->full_total->prices[0]->price_value_with_tax, (int)$this->currency->currency_locale['int_frac_digits']), $rounding, '.', '');
		$purchaseUnit->amount->currency_code = $this->currency->currency_code;
		$purchaseUnit->amount->breakdown = new stdClass();
		$purchaseUnit->amount->breakdown->item_total = new stdClass();
		$purchaseUnit->amount->breakdown->item_total->value = number_format($item_total, $rounding, '.', '');
		$purchaseUnit->amount->breakdown->item_total->currency_code = $this->currency->currency_code;
		$purchaseUnit->amount->breakdown->tax_total = new stdClass();
		$purchaseUnit->amount->breakdown->tax_total->value = number_format($tax_total, $rounding, '.', '');
		$purchaseUnit->amount->breakdown->tax_total->currency_code = $this->currency->currency_code;
		if(!empty($order->cart->coupon) && bccomp(sprintf('%F',$order->order_discount_price), 0, 5)){
			$purchaseUnit->amount->breakdown->discount = new stdClass();
			$purchaseUnit->amount->breakdown->discount->value = number_format(round($order->order_discount_price, (int)$this->currency->currency_locale['int_frac_digits']), $rounding, '.', '');
			$purchaseUnit->amount->breakdown->discount->currency_code = $this->currency->currency_code;
		}

		if(!empty($order->order_shipping_price) && bccomp(sprintf('%F',$order->order_shipping_price), 0, 5)) {
			$purchaseUnit->amount->breakdown->shipping = new stdClass();
			$purchaseUnit->amount->breakdown->shipping->value = number_format(round($order->order_shipping_price, (int)$this->currency->currency_locale['int_frac_digits']), $rounding, '.', '');
			$purchaseUnit->amount->breakdown->shipping->currency_code = $this->currency->currency_code;
		}
		$orderData->purchase_units = [$purchaseUnit];

		return $orderData;
	}
	private function loadJS() {
		$this->params = [
			'client-id' => $this->payment_params->client_id,
			'integration-date' => '2022-07-11',
			'currency' => $this->currency->currency_code,
			'components' => 'buttons,funding-eligibility,messages',
		];
		if(!empty($this->payment_params->disable_funding)) {
			if(!is_string($this->payment_params->disable_funding)) {
				$this->payment_params->disable_funding = implode(',', $this->payment_params->disable_funding);
			}
			$this->params['disable-funding'] = $this->payment_params->disable_funding;
		}
		if(!empty($this->payment_params->funding)) {
			if(!is_string($this->payment_params->funding)) {
				$this->payment_params->funding = implode(',', $this->payment_params->funding);
			}
			$this->params['enable-funding'] = $this->payment_params->funding;
		}

		if(!empty($this->payment_params->capture)) {
			$this->params['intent'] = 'capture';
		} else {
			$this->params['intent'] = 'authorize';
		}
		if(!empty($this->payment_params->debug)) {
			$this->params['debug'] = 'true';
		}

		if(empty($this->payment_params->layout)) {
			$this->payment_params->layout = 'vertical';
		}
		if(empty($this->payment_params->color)) {
			$this->payment_params->color = 'gold';
		}
		if(empty($this->payment_params->label)) {
			$this->payment_params->label = 'paypal';
		}
		if(empty($this->payment_params->shape)) {
			$this->payment_params->shape = 'rect';
		}
		if($this->payment_params->layout == 'horizontal') {
			if(!isset($this->payment_params->tagline)) {
				$this->payment_params->tagline = '1';
			}
		} else {
			$this->payment_params->tagline = false;
		}
	}
}
