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
class plgHikashoppaymentPaypalcheckout extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'AUD','BRL','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD','SEK',
		'DKK','PLN','NOK','HUF','CZK','MXN','MYR','PHP','TWD','THB','ILS','TRY',
	);

	var $rounding = array('TWD' => 0, 'MYR' => 0, 'JPY' => 0, 'HUF' => 0);

	var $multiple = true;
	var $name = 'paypalcheckout';
	var $doc_form = 'paypalcheckout';

	var $pluginConfig = array(
		'client_id' => array('Client ID', 'input'),
		'client_secret' => array('Client secret', 'input'),
		'brand_name' => array('Merchant name', 'input'),
		'capture' => array('INSTANTCAPTURE', 'boolean','1'),
		'landing_page' => array('Landing page', 'list', array(
				'LOGIN' =>'Login page',
				'BILLING' => 'Credit card page',
				'NO_PREFERENCE' => 'No preference',
			),
		),
		'disable_funding' => array(
			'Disable Funding',
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
			'tooltip' => 'Select the payment methods you would like to NOT be available to your customers.',
		),
		'funding' => array(
			'Enable Funding',
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
			'tooltip' => 'Select the payment methods you would like to be available to your customers. Note that even if selected, they will only appear based on your eligibility and the eligibility of your customer to these payment methods.',
		),
		'debug' => array('DEBUG', 'boolean', 0),
		'sandbox' => array('HIKA_SANDBOX', 'boolean', 0),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
	);

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

		$config = hikashop_config();
		$round_calculations = $config->get('round_calculations');
		if(empty($round_calculations)){
			$app = JFactory::getApplication();
			$app->enqueueMessage('The "Round prices during calculations" setting is deactivated in the HikaShop configuration. This can sometimes lead to rounding differences between the total calculated by PayPal and the total calculated by HikaShop, resulting in an "AMOUNT_MISMATCH" error at the end of the checkout with this payment method.');
		}

	}

	public function onPaymentNotification(&$statuses) {
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
			$response = $client->execute(new PayPalCheckoutSdk\Orders\OrdersGetRequest($paypal_id));

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
	}
}
