<?php
/**
 * @package    StripeConnect for Joomla! HikaShop
 * @version    1.0.6
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2020 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashoppaymentStripeconnect extends hikashopPaymentPlugin
{
	public $accepted_currencies = array(
		'AED','AFN','ALL','AMD','ANG','AOA','ARS','AUD','AWG','AZN','BAM','BBD',
		'BDT','BGN','BIF','BMD','BND','BOB','BRL','BSD','BWP','BZD','CAD','CDF',
		'CHF','CLP','CNY','COP','CRC','CVE','CZK','DJF','DKK','DOP','DZD','EEK',
		'EGP','ETB','EUR','FJD','FKP','GBP','GEL','GIP','GMD','GNF','GTQ','GYD',
		'HKD','HNL','HRK','HTG','HUF','IDR','ILS','INR','ISK','JMD','JPY','KES',
		'KGS','KHR','KMF','KRW','KYD','KZT','LAK','LBP','LKR','LRD','LSL','LTL',
		'LVL','MAD','MDL','MGA','MKD','MNT','MOP','MRO','MUR','MVR','MWK','MXN',
		'MYR','MZN','NAD','NGN','NIO','NOK','NPR','NZD','PAB','PEN','PGK','PHP',
		'PKR','PLN','PYG','QAR','RON','RSD','RUB','RWF','SAR','SBD','SCR','SEK',
		'SGD','SHP','SLL','SOS','SRD','STD','SVC','SZL','THB','TJS','TOP','TRY',
		'TTD','TWD','TZS','UAH','UGX','USD','UYI','UZS','VEF','VND','VUV','SWT',
		'XAF','XCD','XOF','XPF','YER','ZAR','ZMW'
	);

	public $multiple = true;
	public $name = 'stripeconnect';
	public $doc_form = 'stripeconnect';

	public $market_support = true;
	const EXPIRATION = 1500;

	var $pluginConfig = array(
		'credentials' => array('Credentials', 'stripe_credentials'),
		'debug' => array('DEBUG', 'boolean', '0'),
		'payment_mode' => array('Payment mode', 'list', array(
			'end' => 'End page',
		)),
		'card_method' => array('Payment Interface', 'list', array(
			'card' => 'Card (3D Secure)',
			'token' => 'Card (by Stripe Token - Legacy)',
		)),
		'preferred_charge' => array('Preferred charge mode (connect)', 'list', array(
			'destination' => 'Destination',
			'direct' => 'Direct',
		)),
		'show_price_end' => array('Show price in end page', 'boolean', '1'),
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

	protected function initLib() {
		static $init = null;
		if($init !== null)
			return $init;
		if(version_compare(PHP_VERSION, '5.4.0') < 0) {
			$app = JFactory::getApplication();
			if($app->isAdmin())
				$app->enqueueMessage('StripeConnect plugin requires PHP 5.4.0 or later', 'error');
			$init = false;
			return $init;
		}
		$this->loadLanguage('plg_hikashoppayment_stripeconnect', JPATH_ADMINISTRATOR);
		try {
			include_once(dirname(__FILE__).'/lib/stripeconnect.php');
			include_once(dirname(__FILE__).'/lib/stripeconnectclass.php');
			$init = true;
		} catch(Exception $e) {
			$app = JFactory::getApplication();
			if($app->isAdmin())
				hikashop_display($e->getMessage());
			$init = false;
		}
		return $init;
	}

	public function pluginConfigDisplay($fieldType, $data, $type, $paramsType, $key, $element) {
		if($fieldType != 'stripe_credentials')
			return;

		$value = @$element->$paramsType->$key;
		if(is_string($value))
			$value = json_decode($value, true);
		if(empty($value))
			$value = array();
		if(!isset($value['default']))
			$value['default'] = true;

		return '
<dl>
	<dt>Use global credentials</dt>
	<dd>'.JHTML::_('hikaselect.booleanlist', 'data['.$type.']['.$paramsType.']['.$key.'][default]', ' onchange="window.localPage.changeStripeCredentials(this);"', $value['default']).'</dd>

	<dt data-stripe-credentials="0">Client Id (Connect Only)</dt>
	<dd data-stripe-credentials="0">
		<input type="text" name="data['.$type.']['.$paramsType.']['.$key.'][client_id]" value="'.htmlentities(@$value['client_id'], ENT_COMPAT, 'UTF-8').'"/>
	</dd>

	<dt data-stripe-credentials="0">Secret Key</dt>
	<dd data-stripe-credentials="0">
		<input type="text" name="data['.$type.']['.$paramsType.']['.$key.'][api_key]" value="'.htmlentities(@$value['api_key'], ENT_COMPAT, 'UTF-8').'"/>
	</dd>

	<dt data-stripe-credentials="0">Publishable Key</dt>
	<dd data-stripe-credentials="0">
		<input type="text" name="data['.$type.']['.$paramsType.']['.$key.'][publish_key]" value="'.htmlentities(@$value['publish_key'], ENT_COMPAT, 'UTF-8').'"/>
	</dd>
</dl>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.changeStripeCredentials = function(el) {
	var blocks = document.querySelectorAll("[data-stripe-credentials=\"0\"]");
	if(!blocks) return;
	blocks.forEach(function(b){
		b.style.display = (el.value == "0") ? "" : "none";
	});
};
window.hikashop.ready(function(){
	window.localPage.changeStripeCredentials({"value":'.(int)$value['default'].'});
});
</script>
';
	}

	private function getStripeParameter($key, $default = null) {
		if(!isset($this->params)) {
			$plugin = JPluginHelper::getPlugin('hikashoppayment', 'stripeconnect');
			$this->params = new JRegistry(@$plugin->params);
		}
		if(empty($this->payment_params) || empty($this->payment_params->credentials))
			return $this->params->get($key, $default);
		if(is_string($this->payment_params->credentials))
			$this->payment_params->credentials = json_decode($this->payment_params->credentials, true);
		if(!isset($this->payment_params->credentials['default']) || !empty($this->payment_params->credentials['default']))
			return $this->params->get($key, $default);
		return !empty($this->payment_params->credentials[$key]) ? $this->payment_params->credentials[$key] : $default;
	}

	public function getAPI() {
		if(!empty($this->api))
			return $this->api;
		if(!$this->initLib())
			return false;
		$client_id = $this->getStripeParameter('client_id', false);
		$api_key = $this->getStripeParameter('api_key', false);
		if(empty($api_key))
			return false;
		$this->api = new StripeConnectClass($client_id, $api_key);
		return $this->api;
	}

	public function checkPaymentDisplay(&$method, &$order) {
		if(version_compare(PHP_VERSION, '5.4.0', '<'))
			return false;

		if($method->payment_params->payment_mode != 'checkout')
			return true;
		if(!hikashop_isSSL())
			return false;

		if(!$this->getAPI())
			return false;
		$vendors = $this->api->getVendors($order);
		if(!empty($vendors))
			return true;

		$checkout_custom = $this->getCheckoutCustom($method->payment_id);
		$card_set = false;

		$now = time();
		if(!empty($checkout_custom)) {
			if(!empty($checkout_custom['stripeToken']) || !empty($checkout_custom['stripeSource'])) {
				if(!empty($checkout_custom['timestamp']) && ($checkout_custom['timestamp'] + self::EXPIRATION) < $now)
					$checkout_custom = null;
				else
					$card_set = true;
			}
			if(!empty($checkout_custom['stripeIntent'])) {
				$this->getAPI();
				$intent = $this->api->retrievePaymentIntent( $checkout_custom['stripeIntent'] );
				if(!empty($intent) && !empty($intent->status)) {
					if($intent->status != 'requires_confirmation') {
						$intent->cancel();
						$intent = null;
						$checkout_custom = null;
					}
				}
				if(!empty($intent) && !empty($intent->payment_method)) {
					$card_set = true;
				}
			}
		}
		if($card_set) {
			$this->getCustomHtmlConfirm($method, $order, $checkout_custom);
			return true;
		}
		if(!empty($method->payment_params->card_method) && $method->payment_params->card_method == 'token') {
			$this->getCustomHtmlFormToken($method, $order);
		} else {
			$this->getCustomHtmlFormPayIntent($method, $order);
		}
		return true;
	}

	private function getCustomHtmlFormToken(&$method, &$order) {
		static $js_added = false;
		$publishable_key = $this->getStripeParameter('publish_key', false);

		$mm = JText::_('CC_MM');
		if($mm == 'CC_MM')
			$mm = JText::_('MM');

		$method->custom_html = '
<p id="hk_co_p_c_STRIPEC_container_'.$method->payment_id.'">
	<div class="form-row">
		<div id="hk_co_p_c_STRIPEC_elements_'.$method->payment_id.'"></div>
		<div id="hk_co_p_c_STRIPEC_errors_'.$method->payment_id.'" role="alert"></div>
	</div>
</p>
<input type="hidden" name="checkout[payment][custom]['.$method->payment_id.'][stripeToken]" id="hk_co_p_c_STRIPEC_TOK_'.$method->payment_id.'" value="" />
<input type="hidden" name="checkout[payment][custom]['.$method->payment_id.'][last4]" id="hk_co_p_c_STRIPEC_L4_'.$method->payment_id.'" value="" />
';

		$script_internal = JURI::base(true).'/media/plg_stripeconnect/stripeconnect.js?v=1-0-6';
		$script_main = 'https://js.stripe.com/v3/';
		$doc = JFactory::getDocument();
		if(!$js_added && hikaInput::get()->getCmd('ctrl') == 'checkout' && hikaInput::get()->getCmd('task') == 'show') {
			$doc->addScript($script_internal);
			$doc->addScript($script_main);
			$js_added = true;
		}

		$additional_data = array(
			'name' => @$order->billing_address->address_lastname . ' ' . @$order->billing_address->address_firstname,
			'address_line1' => @$order->billing_address->address_street,
			'address_city' => @$order->billing_address->address_city,
			'address_state' => @$order->billing_address->address_state->zone_name,
			'address_zip' => @$order->billing_address->address_post_code,
			'address_country' => @$order->billing_address->address_country->zone_code_2
		);
		if(!empty($order->billing_address->address_street2))
			$additional_data['address_line2'] = $order->billing_address->address_street2;
		foreach($additional_data as $k => $v) {
			if(empty($v))
				unset($additional_data[$k]);
		}

		$method->custom_html .= '
<script type="text/javascript">
if(typeof(Stripe) == "undefined" || typeof(window.stripeConnect) == "undefined") {
	var d = document, s = d.createElement("script");
	s.setAttribute("src", "'.$script_internal.'"); d.head.appendChild(s);
	s = d.createElement("script"); s.setAttribute("src", "'.$script_main.'"); d.head.appendChild(s); }
window.hikashop.ready(function(){var init = function(){
	if(!window.stripeConnect || typeof(Stripe) == "undefined") return setTimeout(init, 200);
	window.stripeConnect.init('.$method->payment_id.', '.json_encode(array(
		'authData' => array('pub' => $publishable_key),
		'additional' => $additional_data
	)).');
}; init(); });
</script>
';
	}

	private function getCustomHtmlFormPayIntent(&$method, &$order) {
		static $js_added = false;
		$publishable_key = $this->getStripeParameter('publish_key', false);

		$mm = JText::_('CC_MM');
		if($mm == 'CC_MM')
			$mm = JText::_('MM');

		$method->custom_html = '
<p id="hk_co_p_c_STRIPEC_container_'.$method->payment_id.'">
	<div class="form-row">
		<div id="hk_co_p_c_STRIPEC_elements_'.$method->payment_id.'"></div>
		<div id="hk_co_p_c_STRIPEC_errors_'.$method->payment_id.'" role="alert"></div>
	</div>
</p>
<input type="hidden" name="checkout[payment][custom]['.$method->payment_id.'][payment_method_id]" id="hk_co_p_c_STRIPEC_MET_'.$method->payment_id.'" value="" />
<input type="hidden" name="checkout[payment][custom]['.$method->payment_id.'][3ds]" id="hk_co_p_c_STRIPEC_3DS_'.$method->payment_id.'" value="" />
<input type="hidden" name="checkout[payment][custom]['.$method->payment_id.'][last4]" id="hk_co_p_c_STRIPEC_L4_'.$method->payment_id.'" value="" />
';

		$script_internal = JURI::base(true).'/media/plg_stripeconnect/stripeconnect.js';
		$script_main = 'https://js.stripe.com/v3/';
		$doc = JFactory::getDocument();
		if(!$js_added && hikaInput::get()->getCmd('ctrl') == 'checkout' && hikaInput::get()->getCmd('task') == 'show') {
			$doc->addScript($script_internal);
			$doc->addScript($script_main);
			$js_added = true;
		}

		$additional_data = array(
			'billing_details' => array(
				'name' => @$order->billing_address->address_lastname . ' ' . @$order->billing_address->address_firstname,
				'email' => null,
				'address' => array(
					'city' => @$order->billing_address->address_city,
					'country' => @$order->billing_address->address_country->zone_code_2,
					'line1' => @$order->billing_address->address_street,
					'postal_code' => @$order->billing_address->address_post_code,
					'state' => @$order->billing_address->address_state->zone_name,
				)
			),
		);
		if(!empty($order->billing_address->address_street2))
			$additional_data['billing_details']['address']['line2'] = $order->billing_address->address_street2;
		foreach($additional_data as $k => $v) {
			if(empty($v))
				unset($additional_data[$k]);
		}

		$method->custom_html .= '
<script type="text/javascript">
if(typeof(Stripe) == "undefined" || typeof(window.stripeConnect) == "undefined") {
	var d = document, s = d.createElement("script");
	s.setAttribute("src", "'.$script_internal.'"); d.head.appendChild(s);
	s = d.createElement("script"); s.setAttribute("src", "'.$script_main.'"); d.head.appendChild(s); }
window.hikashop.ready(function(){var init = function(){
	if(!window.stripeConnect || typeof(Stripe) == "undefined") return setTimeout(init, 200);
	window.stripeConnect.init('.$method->payment_id.', '.json_encode(array(
		'authData' => array('pub' => $publishable_key),
		'mode' => 'method',
		'additional' => $additional_data
	)).');
}; init(); });
</script>
';
	}

	private function getCustomHtmlConfirm(&$method, &$order, $data) {
		$last4 = !empty($data['last4']) ? $data['last4'] : 'xxxx';
		if(strlen($last4) != 4)
			$last4 = 'xxxx';

		$method->custom_html_no_btn = true;
		$method->custom_html = '
<dl class="hika_options large" id="hk_co_p_c_ADN_summary_'.$method->payment_id.'">
	<dt><label for="hk_co_p_c_n_'.$method->payment_id.'">'.JText::_('CREDIT_CARD_NUMBER').'</label></dt>
	<dd>
		<span class="hikashop_checkout_payment_card_details">XXXX-XXXX-XXXX-'.$last4.'</span>
	</dd>
</dl>
<div class="hikashop_checkout_payment_submit">
	<button class="hikabtn hikabtn-warning hikabtn_checkout_payment_submit" id="hikabtn_checkout_payment_submit_p4" onclick="return window.checkout.submitBlock(\'payment\', this, null, {\'checkout[payment][id]\':'.$method->payment_id.',\'checkout[payment][custom]['.$method->payment_id.'][reset]\':1});">'.JText::_('RESET').'</button>
</div>
<script type="text/javascript">
if(window.stripeConnect.data['.$method->payment_id.'])
	window.stripeConnect.data['.$method->payment_id.'].hasData = true;
if(window.stripeConnect.formSubmit && window.checkout.onFormSubmit && window.checkout.onFormSubmit(null))
	document.getElementById("hikashop_checkout_form").submit();
</script>
';
	}

	public function onPaymentCustomSave(&$cart, &$method, $formData) {
		if(!empty($formData['reset']) && (int)$formData['reset'] == 1) {
			if(!empty($formData['stripeIntent']))
				$intent = $this->api->retrievePaymentIntent( $formData['stripeIntent'] );
			if(!empty($intent))
				$intent->cancel();
			return array();
		}
		if(!empty($formData['stripeToken']) || !empty($formData['stripeSource']) || !empty($formData['payment_method_id']))
			$formData['timestamp'] = time();
		if(!empty($formData['payment_method_id'])) {
			$intent = $this->createPaymentIntent($cart, $formData['payment_method_id']);
			if(!empty($intent)) {
				$formData['stripeIntent'] = $intent->id;
				unset($formData['payment_method_id']);
			}
		}
		return $formData;
	}

	public function onBeforeOrderCreate(&$order, &$do) {
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		$payment_mode = $this->payment_params->payment_mode;
		if(empty($payment_mode) || $payment_mode == 'js')
			$payment_mode = 'end';

		if(!$this->getAPI())
			return false;
		if($payment_mode != 'end') {
			$vendors = $this->api->getVendors($order);
			if(!empty($vendors))
				$payment_mode = 'end';
		}

		if($payment_mode == 'checkout')
			return $this->onBeforeOrderCreate_Checkout($order, $do);
		return true;
	}

	private function onBeforeOrderCreate_Checkout(&$order, &$do) {
		$app = JFactory::getApplication();

		$method_id = (int)$order->order_payment_id;
		$checkout_custom = $this->getCheckoutCustom();
		$data = !empty($checkout_custom[$method_id]) ? $checkout_custom[$method_id] : null;

		$this->loadLanguage('plg_hikashoppayment_stripeconnect', JPATH_ADMINISTRATOR);

		if(empty($data) || (empty($data['stripeToken']) && empty($data['stripeSource']) && empty($data['stripeIntent'])) ) {
			$app->enqueueMessage(JText::_('STRIPE_CONNECT_MISSING_DATA'), 'error');
			$do = false;
			return false;
		}

		$now = time();

		if(!empty($data['stripeIntent'])) {
			$app->enqueueMessage(JText::_('Stripe - Mode not available yet'), 'error');
			$do = false;
			return false;
		} else {
			if(!empty($data['timestamp']) && $data['timestamp'] + self::EXPIRATION < $now) {
				$checkout_custom[$method_id] = null;
				$app->setUserState(HIKASHOP_COMPONENT.'.checkout_custom', json_encode($checkout_custom));
				$app->enqueueMessage(JText::_('STRIPE_CONNECT_EXPIRED_DATA'), 'error');
				$do = false;
				return false;
			}
			$token = (!empty($data['stripeToken'])) ? $data['stripeToken'] : $data['stripeSource'];
			$ret = $this->processChargeToken($order, $token);
		}

		unset($data['stripeToken']);
		unset($data['stripeSource']);
		$checkout_custom[$method_id] = $data;
		$app->setUserState(HIKASHOP_COMPONENT.'.checkout_custom', json_encode($checkout_custom));

		if($ret === true)
			return true;

		$do = false;
		if(is_array($ret) && isset($ret['error'])) {
			$app->enqueueMessage($ret['error'], 'error');
		} else {
			$app->enqueueMessage(JText::_('PAYMENT_REFUSED'), 'error');
		}
		return false;
	}

	public function onAfterOrderCreate(&$order) {
	}

	public function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$notify_url = HIKASHOP_LIVE.$this->name.'_'.$method_id.'.php?order_id='.$order->order_id . $this->url_itemid;
		$this->loadLanguage('plg_hikashoppayment_stripeconnect', JPATH_ADMINISTRATOR);

		if(empty($order->user))
			$order->user = $this->user;

		$payment_mode = $this->payment_params->payment_mode;
		if(empty($payment_mode) || $payment_mode == 'js')
			$payment_mode = 'end';

		if(!$this->getAPI())
			return false;
		if($payment_mode != 'end') {
			$vendors = $this->api->getVendors($order);
			if(!empty($vendors))
				$payment_mode = 'end';
		}

		if($payment_mode == 'checkout') {
			return $this->onAfterOrderConfirm_Checkout($order, $methods, $method_id);
		}

		$this->payment_params->publishable_key = $this->getStripeParameter('publish_key', false);

		if($payment_mode == 'end') {
			return $this->onAfterOrderConfirm_End($order, $methods, $method_id);
		}
	}

	private function onAfterOrderConfirm_Checkout(&$order, &$methods, $method_id) {
		if(!empty($this->payment_params->card_method) && $this->payment_params->card_method == 'token') {
			$vendors = $this->api->getVendors($order);
			if(!empty($vendors)) {
				$payment_params = isset($order->order_payment_params) ? $order->order_payment_params : $order->old->order_payment_params;
				if(empty($payment_params))
					$payment_params = new stdClass();
				if(is_string($payment_params))
					$payment_params = hikashop_unserialize($payment_params);

				$charge_id = $payment_params->stripe_transaction_id;

				$this->processPaymentVendors($order, $vendors, $charge_id, $payment_params);
			}
		} else {

		}
		return $this->showPage('thankyou');
	}

	private function onAfterOrderConfirm_End(&$order, &$methods, $method_id) {
		if(!empty($this->payment_params->card_method) && $this->payment_params->card_method == 'token') {
			$this->additional_data = array(
				'name' => @$order->cart->billing_address->address_lastname . ' ' . @$order->cart->billing_address->address_firstname,
				'address_line1' => @$order->cart->billing_address->address_street,
				'address_city' => @$order->cart->billing_address->address_city,
				'address_state' => @$order->cart->billing_address->address_state->zone_name,
				'address_zip' => @$order->cart->billing_address->address_post_code,
				'address_country' => @$order->cart->billing_address->address_country->zone_code_2
			);
			if(!empty($order->cart->billing_address->address_street2))
				$this->additional_data['address_line2'] = $order->cart->billing_address->address_street2;
			foreach($this->additional_data as $k => $v) {
				if(empty($v))
					unset($this->additional_data[$k]);
			}
			$view = 'end';
		} else {
			$this->method_id = $methods[$method_id]->payment_id;
			$this->publishable_key = $this->getStripeParameter('publish_key', false);

			$this->additional_data = array(
				'billing_details' => array(
					'name' => @$order->cart->billing_address->address_lastname . ' ' . @$order->cart->billing_address->address_firstname,
					'email' => @$order->customer->user_email,
					'address' => array(
						'city' => @$order->cart->billing_address->address_city,
						'country' => @$order->cart->billing_address->address_country->zone_code_2,
						'line1' => @$order->cart->billing_address->address_street,
						'postal_code' => @$order->cart->billing_address->address_post_code,
						'state' => @$order->cart->billing_address->address_state->zone_name,
					)
				),
			);
			if(!empty($order->cart->billing_address->address_street2))
				$this->additional_data['billing_details']['address']['line2'] = $order->cart->billing_address->address_street2;
			foreach($this->additional_data as $k => $v) {
				if(empty($v))
					unset($this->additional_data[$k]);
			}
			$view = 'end_intent';
		}
		$this->order_total = null;
		if(!empty($this->payment_params->show_price_end)) {
			if(empty($this->currencyClass))
				$this->currencyClass = hikashop_get('class.currency');
			$total = $this->getOrderTotal($order);
			$currency = $this->getOrderCurrencyObj($order);
			$this->order_total = $this->currencyClass->format($total, $currency->currency_id);
		}
		$this->notifyurl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&tmpl=component&order_id='.$order->order_id.'&lang='.$this->locale.$this->url_itemid;
		$this->notifyurl_js = $this->notifyurl . '&' . hikashop_getFormToken() . '=1';
		$this->order =& $order;
		return $this->showPage($view);
	}

	public function onPaymentNotification(&$statuses) {
		$order_id = hikaInput::get()->getInt('order_id', (int)@$_REQUEST['order_id']);
		if(empty($order_id))
			return false;
		$dbOrder = $this->getOrder($order_id);

		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params)) {
			echo 'The system can\'t load the payment params';
			return false;
		}
		$this->loadOrderData($dbOrder);

		$confirm_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id=' . $order_id . $this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id=' . $order_id . $this->url_itemid;

		$token = hikaInput::get()->post->getVar('stripeToken', @$_POST['stripeToken']);
		if(!empty($token) && JSession::checkToken()) {
			$app = JFactory::getApplication();

			$ret = $this->processChargeToken($dbOrder, $token);
			if($ret === true) {
				$app->redirect($confirm_url);
				return true;
			}

			if(is_array($ret) && !empty($ret['error']))
				$app->enqueueMessage($ret['error'], 'error');

			if(is_array($ret) && !empty($ret['url'])) {
				$app->redirect($ret['url']);
			} else {
				$app->redirect($cancel_url);
			}
			return false;
		}

		$paymentMethod = hikaInput::get()->post->getVar('paymentMethod', @$_POST['paymentMethod']);
		if(!empty($paymentMethod) && JSession::checkToken('request')) {
			$this->getAPI();
			$intent_id = $this->getOrderIntent($dbOrder);
			if(!empty($intent_id)) {
				$intent = $this->api->retrievePaymentIntent( $intent_id );
				if(!empty($intent))
					$this->api->cancelPaymentIntent($intent);
			}
			$intent = $this->createPaymentIntent($dbOrder, $paymentMethod);
			if(!empty($intent)) {
				$payment_params = isset($dbOrder->order_payment_params) ? $dbOrder->order_payment_params : null;
				if(empty($payment_params))
					$payment_params = new stdClass();
				if(is_string($payment_params))
					$payment_params = hikashop_unserialize($payment_params);
				$payment_params->stripe_payment_intent = $intent->id;
				$this->modifyOrder($order_id, null, null, null, $payment_params);

				$ret = $this->api->confirmPaymentIntent($intent);
				if($ret !== true) {
					echo json_encode(array(
						'error' => $ret,
						'status' => $intent->status
					));
					exit;
				}
				$status = $intent->status;

				if($status == 'requires_source_action')
					$status = 'requires_action';

				hikashop_cleanBuffers();

				if($status == 'requires_action' && $intent->next_action->type == 'use_stripe_sdk') {
					echo json_encode(array(
						'requires_action' => true,
						'payment_intent_client_secret' => $intent->client_secret
					));
				} else if($status == 'succeeded') {
					echo json_encode(array(
						'success' => true,
						'url' => hikashop_completeLink($confirm_url)
					));
					if(ob_get_level())
						ob_end_flush();
					ob_start(); ob_start();
					$this->modifyOrder($order_id, $this->payment_params->verified_status, true, true);
					if(ob_get_level())
						@ob_end_clean();
				} else {
					echo json_encode(array(
						'error' => $intent->last_payment_error,
						'status' => $status
					));
				}
			} else {
				echo json_encode(array('error' => JText::_('INVALID_DATA')));
			}
			exit;
		}

		$paymentIntent = hikaInput::get()->post->getVar('paymentIntent', @$_POST['paymentIntent']);
		if(!empty($paymentIntent) && JSession::checkToken('request')) {
			$this->getAPI();
			$intent = null;
			$intent_id = $this->getOrderIntent($dbOrder);
			if($intent_id == $paymentIntent) {
				$intent = $this->api->retrievePaymentIntent( $intent_id );
			}
			hikashop_cleanBuffers();
			if(!empty($intent)) {
				$ret = $this->api->confirmPaymentIntent($intent);
				if($ret === true && $intent->status == 'succeeded') {
					echo json_encode(array(
						'success' => true,
						'url' => hikashop_completeLink($confirm_url)
					));
					if(ob_get_level())
						ob_end_flush();
					ob_start();
					$this->modifyOrder($order_id, $this->payment_params->verified_status, true, true);
					ob_end_clean();
				} else {
					echo json_encode(array(
						'error' => $intent->last_payment_error,
						'status' => $intent->status
					));
				}
			} else {
				echo json_encode(array('error' => JText::_('INVALID_DATA')));
			}
			exit;
		}

		return false;
	}

	private function getCheckoutCustom($method_id = null) {
		$app = JFactory::getApplication();
		$checkout_custom = $app->getUserState(HIKASHOP_COMPONENT.'.checkout_custom', null);
		if(!empty($checkout_custom))
			$checkout_custom = json_decode(base64_decode($checkout_custom), true);
		if($method_id === null)
			return $checkout_custom;
		if(!empty($checkout_custom[$method_id]))
			return $checkout_custom[$method_id];
		return null;
	}

	private function createPaymentIntent(&$dbOrder, $payment_method_id) {
		$this->getAPI();
		$total = $this->getOrderTotal($dbOrder);
		$currency = $this->getOrderCurrencyObj($dbOrder);
		$amount = round($total, 2) * 100;
		if((int)$currency->currency_locale['int_frac_digits'] == 0)
			$amount = round($total, 0);
		$connectData = null;
		$paymentData = array(
			'amount' => $amount,
			'currency' => $currency->currency_code,
			'payment_method_types' => ['card'],
			'payment_method' => $payment_method_id,
			'confirmation_method' => 'manual', // Need: confirm();
		);
		if(!empty($this->payment_params->order_desc)) {
			$paymentData['description'] = JText::sprintf($this->payment_params->order_desc, $dbOrder->order_number);
		} else {
			$paymentData['description'] = JText::_('ORDER_NUMBER') . ': ' . $dbOrder->order_number;
		}
		if($this->initMarket())
			$this->prepareIntentVendors($dbOrder, $paymentData, $connectData);
		$intent = $this->api->createPaymentIntent($paymentData, $connectData);
		return $intent;
	}

	private function updatePaymentIntent(&$dbOrder) {
		$this->getAPI();
		$id = $this->getOrderIntent($dbOrder);
		$total = $this->getOrderTotal($dbOrder);
		$currency = $this->getOrderCurrencyObj($dbOrder);
		$amount = round($total, 2) * 100;
		if((int)$currency->currency_locale['int_frac_digits'] == 0)
			$amount = round($total, 0);
		return $this->api->updatePaymentIntent($id, array(
			'amount' => $amount,
			'currency' => $currency->currency_code,
		));
	}

	protected function getOrderTotal($dbOrder) {
		if(isset($dbOrder->full_total)) {
			$p = $dbOrder->full_total->prices[0];
			return isset($p->price_value_with_tax) ? $p->price_value_with_tax : $p->price_value;
		}
		if(isset($dbOrder->order_full_price)) {
			$p = hikashop_toFloat($dbOrder->order_full_price);
			return $p;
		}
		return 0;
	}
	protected function getOrderCurrencyObj($dbOrder) {
		$currency_id = isset($dbOrder->cart_currency_id) ? (int)$dbOrder->cart_currency_id : (int)@$dbOrder->order_currency_id;
		if(empty($currency_id))
			return null;
		$currencyClass = hikashop_get('class.currency');
		$null = null;
		$currencies = $currencyClass->getCurrencies($currency_id, $null);
		if(isset($currencies[$currency_id]))
			return $currencies[$currency_id];
		return null;
	}
	protected function getOrderIntent($dbOrder) {
		$payment_params = isset($dbOrder->order_payment_params) ? $dbOrder->order_payment_params : null;
		if(empty($payment_params))
			$payment_params = new stdClass();
		if(is_string($payment_params))
			$payment_params = hikashop_unserialize($payment_params);
		if(isset($payment_params->stripe_payment_intent))
			return $payment_params->stripe_payment_intent;
		return null;
	}

	private function processChargeToken(&$dbOrder, $token) {
		$this->getAPI();

		$order_id = null;
		$desc = '';
		$order_text = '';

		if(!empty($dbOrder->order_id)) {
			$order_id = (int)$dbOrder->order_id;
			$desc = JText::sprintf('ORDER_NUMBER').' : '.$order_id;

			$order = (int)$dbOrder->order_id;
		} else {
			$order =& $dbOrder;
		}

		$currency = $this->currency->currency_code;
		$amount = round($dbOrder->order_full_price, 2) * 100;
		if((int)$this->currency->currency_locale['int_frac_digits'] == 0)
			$amount = round($dbOrder->order_full_price, 0);

		$chargeData = array(
			'amount' => $amount, // amount in cents, again
			'currency' => $currency,
			'description' => $desc,
			'source' => $token,
		);
		if(!empty($order_id)) {
			$chargeData['metadata'] = array('order_id' => $order_id);
		}

		$customer = $this->api->getCustomer($this->user, array('token' => $token));
		if(!empty($customer)) {
			$chargeData['customer'] = $customer->id;
			unset($chargeData['source']);
		}

		$vendors = false;
		$chargeConnect = null;

		if($this->initMarket())
			$vendors = $this->prepareChargeVendors($dbOrder, $chargeData, $chargeConnect, $customer);
		$charge = $this->api->createCharge($chargeData, $chargeConnect);

		if(!empty($this->payment_params->debug)) {
			$this->writeToLog('Create Charge'."\r\n".'<pre>'.print_r($charge, true).'</pre>');
		}

		if(empty($charge) || empty($charge->id) || (isset($charge->paid) && empty($charge->paid)) || (isset($charge->status) && $charge->status == 'failed')) {
			$this->modifyOrder($order, $this->payment_params->invalid_status);

			$ret = false;
			if(!empty($charge->decline_code) && !empty($charge->failure_message)) {
				$ret = array('error' => $charge->failure_message);
			}
			return $ret;
		}

		$payment_params = $dbOrder->order_payment_params;
		if(empty($payment_params))
			$payment_params = new stdClass();
		$payment_params->stripe_transaction_id = $charge->id;
		if(!empty($charge->source->last4))
			$payment_params->last4 = $charge->source->last4;

		if(!empty($charge->outcome) && isset($charge->outcome->type) && in_array($charge->outcome->type, array('blocked', 'invalid', 'issuer_declined'))) {
			$history = new stdClass();
			$history->notified = 0;
			$history->amount = round($dbOrder->order_full_price, 2);
			$history->data = $charge->failure_message . "\r\n" . ob_get_clean();

			$this->modifyOrder($order, $this->payment_params->invalid_status, $history, null, $payment_params);
			return false;
		}

		$order_status = ($charge->status == 'succeeded') ? $this->payment_params->verified_status : $this->payment_params->pending_status;
		$extra_data = '';

		if((isset($charge->status) && $charge->status == 'pending') || (isset($charge->outcome->type) && $charge->outcome->type != 'authorized') || (isset($charge->outcome->risk_level) && $charge->outcome->risk_level != 'normal')) {
			$order_status = $this->payment_params->pending_status;
			if(!empty($charge->outcome->type))
				$extra_data .= 'Return: '.$charge->outcome->type."\r\n<br/>";
			if(!empty($charge->outcome->risk_level))
				$extra_data .= 'Risk Level: ' . $charge->outcome->risk_level."\r\n<br/>";
			if(!empty($charge->failure_message))
				$extra_data .= $charge->failure_message . "\r\n<br/>";
			if(!empty($charge->outcome->reason))
				$extra_data .= $charge->outcome->reason . "\r\n<br/>";
		}

		if(!empty($vendors)) {
			$this->processPaymentVendors($dbOrder, $vendors, $charge->id, $payment_params);
		}

		$history = new stdClass();
		$history->notified = 1;
		$history->amount = round($dbOrder->order_full_price, 2);
		$history->data = $extra_data . ob_get_clean();

		if(!empty($order_id)) {
			$email = new stdClass();
			$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', 'Stripe Connect', $order_status, $dbOrder->order_number);
			$email->body = str_replace('<br/>', "\r\n", JText::sprintf('PAYMENT_NOTIFICATION_STATUS', 'Stripe Connect', $order_status)).' '.JText::sprintf('ORDER_STATUS_CHANGED', $order->mail_status) . "\r\n\r\n" . $order_text;
		} else {
			$history->notified = 0;
			$email = null;
		}

		$this->modifyOrder($order, $order_status, $history, $email, $payment_params);
		return true;
	}

	private function prepareChargeVendors(&$dbOrder, &$chargeData, &$chargeConnect, &$customer) {
		$vendors = $this->api->getVendors($dbOrder);
		if(empty($vendors))
			return false;

		if(count($vendors) == 2) {
			$main_vendor = $vendors[0];
			$other_vendor = end($vendors);

			$chargeData['application_fee'] = round($main_vendor['sum'], 2) * 100;

			if(!empty($this->payment_params->preferred_charge) && $this->payment_params->preferred_charge == 'direct') {
				$chargeConnect = array(
					'stripe_account' => $other_vendor['stripe_id'],
				);
			} else {
				$chargeData['destination'] = array(
					'account' => $other_vendor['stripe_id'],
				);
			}
		}

		if(empty($customer))
			return $vendors;

		if(!empty($this->payment_params->preferred_charge) && $this->payment_params->preferred_charge == 'direct') {
			$other_vendor = end($vendors);
			$chargeData['source'] = $this->api->createToken(array('customer' => $customer->id), array('stripe_account' => $other_vendor['stripe_id']));
			unset($chargeData['customer']);
		}
		return $vendors;
	}

	private function prepareIntentVendors(&$dbOrder, &$intentData, &$intentConnect) {
		$vendors = $this->api->getVendors($dbOrder);
		if(empty($vendors))
			return false;
		if(count($vendors) == 2) {
			$main_vendor = $vendors[0];
			$other_vendor = end($vendors);

			$intentData['application_fee_amount'] = round($main_vendor['sum'], 2) * 100;
			if(empty($intentData['application_fee_amount']))
				unset($intentData['application_fee_amount']);

			if(!empty($this->payment_params->preferred_charge) && $this->payment_params->preferred_charge == 'direct') {
				$intentConnect = array(
					'stripe_account' => $other_vendor['stripe_id'],
				);
			} else {
				$intentData['on_behalf_of'] = $other_vendor['stripe_id'];
				$intentData['transfer_data'] = array(
					'destination' => $other_vendor['stripe_id'],
				);
			}
		}
		return $vendors;
	}

	private function processPaymentVendors(&$dbOrder, $vendors, $payment_id, &$payment_params) {
		if(empty($vendors) || count($vendors) <= 1)
			return;

		$order_id = isset($dbOrder->order_id) ? (int)$dbOrder->order_id : false;
		if(empty($order_id)) {
			return;
		}

		if(count($vendors) > 2) {
			$payment_params->stripe_transfers = array();

			foreach($vendors as $k => &$vendor) {
				if($k == 0)
					continue;
				$vendor_amount = round($vendor['sum'], 2) * 100;
				if((int)$this->currency->currency_locale['int_frac_digits'] == 0)
					$vendor_amount = round($vendor['sum'], 0);
				$transfer = $this->api->createTransfer(array(
					'amount' => $vendor_amount,
					'currency' => $this->currency->currency_code,
					'source_transaction' => $payment_id,
					'destination' => $vendor['stripe_id'],
				));
				if(!empty($transfer) && !empty($transfer->id)) {
					$vendor['transfer'] = $transfer->id;
					$payment_params->stripe_transfers[$k] = $transfer->id;
				}
			}
			unset($vendor);
		}

		$db = JFactory::getDBO();
		$query = 'UPDATE `#__hikamarket_order_transaction` SET order_transaction_paid = order_id '.
				' WHERE order_id = '.(int)$order_id.' AND vendor_id IN (1,'.implode(',', array_keys($vendors)).')';
		$db->setQuery($query);
		$db->execute();
	}

	public function onPaymentConfiguration(&$element) {
		parent::onPaymentConfiguration($element);

		if(version_compare(PHP_VERSION, '5.4.0', '<')) {
			$app = JFactory::getApplication();
			$app->enqueueMessage('To work correctly, StripeConnect requires PHP 5.4.0 or higher', 'error');
		}
	}

	public function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'Stripe';
		$element->payment_description = '';
		$element->payment_images = 'MasterCard,VISA,Credit_card';

		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
	}

	public function onPaymentConfigurationSave(&$element) {
		$ret = parent::onPaymentConfigurationSave($element);
		if(isset($element->payment_params->credentials) && is_array($element->payment_params->credentials)) {
			$v = $element->payment_params->credentials;
			if(empty($v['default']) && empty($v['client_id']) && empty($v['api_key']) && empty($v['publish_key']))
				$element->payment_params->credentials['default'] = 1;
			$element->payment_params->credentials = json_encode($element->payment_params->credentials);
		}

		$app = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.path');
		$lang = JFactory::getLanguage();
		$locale = strtolower(substr($lang->get('tag'),0,2));

		$content = '<'.'?'.'php
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
		JFile::write(JPATH_ROOT.DS.$this->name.'_'.$element->payment_id.'.php', $content);

		return $ret;
	}
}
