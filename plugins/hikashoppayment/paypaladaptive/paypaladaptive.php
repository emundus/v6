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
class plgHikashoppaymentPaypalAdaptive extends hikashopPaymentPlugin
{
	public $accepted_currencies = array(
		'AUD','BRL','CAD','CHF','CZK','DKK','EUR','GBP','HKD','HUF','ILS','JPY',
		'MYR','MXN','NOK','NZD','PHP','PLN','SGD','SEK','TWD','THB','TRY','USD'
	);

	public $multiple = true;
	public $name = 'paypaladaptive';
	public $doc_form = 'paypaladaptive';

	public $market_support = true;

	private $payment_urls = array(
		'production' => 'https://svcs.paypal.com/AdaptivePayments/Pay',
		'sandbox' => 'https://svcs.sandbox.paypal.com/AdaptivePayments/Pay'
	);
	private $return_urls = array(
		'redirect' => array(
			'production' => 'https://www.paypal.com/webscr?cmd=_ap-payment',
			'sandbox' => 'https://www.sandbox.paypal.com/webscr?cmd=_ap-payment',
		),
		'popup' => array(
			'production' => 'https://www.paypal.com/webapps/adaptivepayment/flow/pay',
			'sandbox' => 'https://www.sandbox.paypal.com/webapps/adaptivepayment/flow/pay'
		)
	);
	private $communication_formats = array(
		'nv' => 'NV',
		'xml' => 'XML',
		'json' => 'JSON'
	);
	private $fees_formats = array(
		'each' => 'EACHRECEIVER', // default
		'sender' => 'SENDER',
		'primary' => 'PRIMARYRECEIVER', // chained payment only
		'secondary' => 'SECONDARYONLY' // chained payment only
	);
	private $display_modes = array(
		'redirect',
		'popup'
	);

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

	private function getOrderVendor(&$order) {
		$vendor_id = 0;
		if(empty($order->products))
			return $vendor_id;

		$config = hikamarket::config();
		$cart_restriction = (int)$config->get('vendors_in_cart', 0);
		foreach($order->products as $product) {
			if(isset($product->product_vendor_id) && (int)$product->product_vendor_id >= 1) {
				if($cart_restriction > 0)
					$vendor_id = (int)$product->product_vendor_id;
				elseif($vendor_id == 0)
					$vendor_id = (int)$product->product_vendor_id;
				else
					$vendor_id = -1;
			}
		}
		return $vendor_id;
	}

	private function CheckAllVendorsHaveEmail(&$order) {
		if(empty($order->products))
			return true;
		$vendor_ids = array();
		foreach($order->products as $product) {
			if(isset($product->product_vendor_id) && (int)$product->product_vendor_id > 1) {
				$vendor_ids[(int)$product->product_vendor_id] = (int)$product->product_vendor_id;
			}
		}
		if(empty($vendor_ids))
			return true;
		$db = JFactory::getDBO();
		$db->setQuery('SELECT vendor_id, vendor_params FROM '.hikamarket::table('vendor').' WHERE vendor_id IN ('.implode(',', $vendor_ids).')');
		$vendors = $db->loadObjectList();
		foreach($vendors as $vendor) {
			if(empty($vendor->vendor_params))
				return false;
			$vendor_params = unserialize($vendor->vendor_params);
			if(empty($vendor_params->paypal_email))
				return false;
		}
		return true;
	}

	public function checkPaymentDisplay(&$method, &$order) {
		if(!function_exists('curl_init')) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('CURL_NOT_FOUND'), 'error');
			return false;
		}
		if(empty($method->payment_params->require_paypal_email_for_all))
			return true;
		if(!$this->initMarket())
			return false;
		$config = hikamarket::config();
		$limit_vendors_in_cart = $config->get('vendors_in_cart', 0);
		if(!empty($method->payment_params->classical)) {
			$vendor_id = $this->getOrderVendor($order);
			if($vendor_id > 1) {
				$vendorClass = hikamarket::get('class.vendor');
				$vendor = $vendorClass->get($vendor_id);
				if(empty($vendor->vendor_params->paypal_email))
					return false;
			}
		} else {
			return $this->CheckAllVendorsHaveEmail($order);
		}
		return true;
	}

	public function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		if(!empty($this->payment_params->classical)) {
			return $this->afterOrderConfirm_Classical($order, $methods, $method_id);
		}
		return $this->afterOrderConfirm_Adaptive($order, $methods, $method_id);
	}

	private function afterOrderConfirm_Adaptive(&$order, &$methods, $method_id) {
		if(empty($this->payment_params->username) || empty($this->payment_params->password) || empty($this->payment_params->signature) || empty($this->payment_params->email)) {
			$this->app->enqueueMessage('Please check your &quot;paypal adaptive&quot; plugin configuration');
			return false;
		}

		if($this->currency->currency_locale['int_frac_digits'] > 2)
			$this->currency->currency_locale['int_frac_digits'] = 2;

		if(!isset($this->payment_params->sandbox) && isset($this->payment_params->debug))
			$this->payment_params->sandbox = $this->payment_params->debug;

		$ip = hikashop_getIp();
		if(empty($ip)) $ip = '127.0.0.1';
		if(strpos($ip, ':') !== false)
			$ip = '';

		$url = $this->payment_urls[ $this->payment_params->sandbox ? 'sandbox' : 'production' ];
			$format = 'nv';
		if(empty($this->payment_params->fee_mode))
			$this->payment_params->fee_mode = 'each';
		if(empty($this->payment_params->payment_mode))
			$this->payment_params->payment_mode = 'chained';
		if(empty($this->payment_params->store_secondary))
			$this->payment_params->store_secondary = false;

		$display_mode = @$this->payment_params->display_mode;
		if(!in_array($display_mode, $this->display_modes))
			$display_mode = reset($this->display_modes);

		$notify_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&order_id='.$order->order_id.'&tmpl=component&lang='.$this->locale . $this->url_itemid;
		if($display_mode == 'redirect') {
			$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id . $this->url_itemid;
			$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id . $this->url_itemid;
		} else {
			$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&mode=popup&subtask=after_end&order_id='.$order->order_id.'&tmpl=component&lang='.$this->locale . $this->url_itemid;
			$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&mode=popup&subtask=cancel_order&order_id='.$order->order_id.'&tmpl=component&lang='.$this->locale . $this->url_itemid;
		}

		if($this->payment_params->sandbox)
			$this->payment_params->applicationid = 'APP-80W284485P519543T';

		if(!isset($this->fees_formats[ $this->payment_params->fee_mode ]) || ($this->payment_params->payment_mode != 'chained' && in_array($this->payment_params->payment_mode, array('primary','secondary'))))
			$this->payment_params->fee_mode = 'each';
		$fee = $this->fees_formats[ $this->payment_params->fee_mode ];

		$headers = array(
			'X-PAYPAL-SECURITY-USERID: ' . $this->payment_params->username, // 'tok261_biz_api.abc.com'
			'X-PAYPAL-SECURITY-PASSWORD: ' . $this->payment_params->password, // '1244612379'
			'X-PAYPAL-SECURITY-SIGNATURE: ' . $this->payment_params->signature, // 'lkfg9groingghb4uw5'
			'X-PAYPAL-DEVICE-IPADDRESS: ' . $ip,
			'X-PAYPAL-REQUEST-DATA-FORMAT: ' . $this->communication_formats[$format],
			'X-PAYPAL-RESPONSE-DATA-FORMAT: ' . $this->communication_formats[$format],
			'X-PAYPAL-APPLICATION-ID: ' . $this->payment_params->applicationid, // 'APP-80W284485P519543T'
		);
		if(empty($ip))
			unset($headers[3]);

		$struct = array(
			'requestEnvelope' => array(
				'errorLanguage' => 'en_US'
			),
			'actionType' => 'PAY',
			'currencyCode' => $this->currency->currency_code,
			'receiverList' => array(),
			'feesPayer' => $fee,
			'trackingId' => $order->order_id.'#'.uniqid(),
			'cancelUrl' => $cancel_url,
			'returnUrl' => $return_url,
			'ipnNotificationUrl' => $notify_url,
			'reverseAllParallelPaymentsOnError' => (@$this->payment_params->reverse_all_on_error ? 'true' : 'false'),
			'clientDetails' => array(
				'applicationId' => $this->payment_params->applicationid,
				'ipAddress' => $ip,
				'customerId' => $this->user->user_id
			)
		);
		if(empty($ip))
			unset($struct['clientDetails']['ipAddress']);

		$db = JFactory::getDBO();
		$suborders = array();
		$vendors = array();
		$order_id = $order->order_id;
		if($order_id > 0 && $this->initMarket()) {
			$query = 'SELECT b.*, a.* FROM ' . hikamarket::table('shop.order') . ' AS a LEFT JOIN ' . hikamarket::table('vendor') . ' AS b ON a.order_vendor_id = b.vendor_id WHERE a.order_parent_id = ' . $order_id;
			$db->setQuery($query);
			$suborders = $db->loadObjectList();
		}

		if(!empty($suborders)) {
			$full_amount = round($order->cart->full_total->prices[0]->price_value_with_tax, (int)$this->currency->currency_locale['int_frac_digits']);
			$store_amount = $full_amount;

			if($this->payment_params->payment_mode != 'chained' || !empty($this->payment_params->store_secondary)) {
				foreach($suborders as $k => $suborder) {
					if((int)$suborder->order_vendor_id <= 1)
						continue;

					$paypal_email = $suborder->vendor_email;
					if(!empty($suborder->vendor_params))
						$suborder->vendor_params = unserialize($suborder->vendor_params);
					if(!empty($suborder->vendor_params->paypal_email))
						$paypal_email = $suborder->vendor_params->paypal_email;

					$paypal_email = trim($paypal_email);
					if(strpos($paypal_email, '@') === false)
						continue;

					$p = round(hikashop_toFloat($suborder->order_vendor_price), (int)$this->currency->currency_locale['int_frac_digits']);
					if($p >= 0) {
						$store_amount -= $p;
					} else {
						$store_amount -= round(hikashop_toFloat($suborder->order_vendor_price) + $order->cart->full_total->prices[0]->price_value_with_tax, (int)$this->currency->currency_locale['int_frac_digits']);
					}
				}
			}

			$e = array(
				'email' => $this->payment_params->email,
				'amount' => $store_amount
			);
			if($this->payment_params->payment_mode == 'chained')
				$e['primary'] = empty($this->payment_params->store_secondary) ? 'true' : 'false';
			if($store_amount > 0)
				$struct['receiverList'][] = $e;

			$vendor_primary = empty($this->payment_params->store_secondary) ? 'false' : 'true';

			foreach($suborders as $k => $suborder) {
				$price = round(hikashop_toFloat($suborder->order_vendor_price), (int)$this->currency->currency_locale['int_frac_digits']);
				if($price < 0) {
					$price = round(hikashop_toFloat($suborder->order_vendor_price) + $order->cart->full_total->prices[0]->price_value_with_tax, (int)$this->currency->currency_locale['int_frac_digits']);
				}

				if($suborder->order_vendor_price == 0.0 || (int)$suborder->order_vendor_id <= 1)
					continue;

				$paypal_email = $suborder->vendor_email;
				if(!empty($suborder->vendor_params) && is_string($suborder->vendor_params))
					$suborder->vendor_params = unserialize($suborder->vendor_params);
				if(!empty($suborder->vendor_params->paypal_email))
					$paypal_email = $suborder->vendor_params->paypal_email;

				$paypal_email = trim($paypal_email);
				if(strpos($paypal_email, '@') === false)
					continue;

				$vendors[$suborder->order_vendor_id] = array(
					'email' => $paypal_email,
					'name' => $suborder->vendor_name,
					'params' => $suborder->vendor_params
				);

				$e = array(
					'email' => $paypal_email,
					'amount' => $price
				);
				if($this->payment_params->payment_mode == 'chained') {
					$e['primary'] = $vendor_primary;
					if($vendor_primary == 'true')
						$e['amount'] = $full_amount;
				}
				$vendor_primary = 'false';
				$struct['receiverList'][] = $e;
			}

			if($vendor_primary == 'true' || count($struct['receiverList']) == 1) {
				foreach($struct['receiverList'] as $k => $receiver) {
					unset($struct['receiverList'][$k]['primary']);
				}
			}

		} else {
			$struct['receiverList'][] = array(
				'email' => $this->payment_params->email,
				'amount' => round($order->cart->full_total->prices[0]->price_value_with_tax, (int)$this->currency->currency_locale['int_frac_digits'])
			);
		}

		if(!empty($this->payment_params->debug)) {
			echo '<pre>';
			print_r($struct);
			echo '</pre>';
		}

		$res = $this->submitPaypalData($url, $headers, $struct, $format);

		if(isset($res['responseEnvelope.ack']) && strtolower($res['responseEnvelope.ack']) == 'failure') {
			$removed_vendors = array();

			do {
				$something_removed = false;
				$failure = false;
				$e = 0;
				$errCode = $res['error('.$e.').errorId'];
				if($errCode == '520009') {
					$errParameter = trim(urldecode($res['error('.$e.').parameter(0)']));
					if($errParameter == $this->payment_params->email)
						return false;

					foreach($struct['receiverList'] as $k => $receiver) {
						if($receiver['email'] == $errParameter) {
							if($struct['receiverList'][$k]['primary'] != 'true') {
								$something_removed = true;
								unset($struct['receiverList'][$k]);
							}
						}
					}

					if(!empty($vendors) && $something_removed) {
						foreach($vendors as $id => $vendor) {
							if($vendor['email'] == $errParameter) {
								if(empty($vendor['params']))
									$vendor['params'] = new stdClass();
								$vendor['params']->paypal_email = 'no paypal account';
								$params = serialize($vendor['params']);
								$db->setQuery('UPDATE '.hikamarket::table('vendor').' SET vendor_params = ' . $db->Quote($params) . ' WHERE vendor_id = ' . $id);
								$db->execute();
								unset($params);

								if(!empty($this->payment_params->notify_wrong_emails))
									$removed_vendors[$id] = $vendor;
							}
						}
					}

					$res = $this->submitPaypalData($url, $headers, $struct, $format);
					$failure = (isset($res['responseEnvelope.ack']) && strtolower($res['responseEnvelope.ack']) == 'failure');
				}
			} while($failure && $something_removed);

			if(!empty($removed_vendors)) {
				$email = new stdClass();
				$email->subject = JText::_('INCORRECT_VENDOR_PAYPAL_EMAILS');
				$email->body = str_replace('<br/>', "\r\n", JText::_('SOME_VENDORS_HAD_INCORRECT_PAYPAL_EMAILS')) . "\r\n";
				foreach($removed_vendors as $id => $removed_vendor) {
					$email->body .= $removed_vendor['name'] . ' (' . $id . ') : ' . $removed_vendor['email'] . "\r\n";
				}
				$o = false;
				$this->modifyOrder($o, null, null, $email);
			}
		}

		if(!empty($this->payment_params->debug)) {
			echo '<pre>';
			print_r($res);
			echo '</pre>';
		}

		$this->target = '';

		if(isset($res['payKey'])) {
			$this->paykey = $res['payKey'];
			$this->display_mode = $display_mode;
			if($display_mode == 'popup')
				$this->target = 'PPDGFrame';
			$this->return_url = $this->return_urls[ $this->display_mode ][ $this->payment_params->sandbox ? 'sandbox' : 'production' ];
		} else {

			if(isset($res['error(0).message'])) {
				$this->app->enqueueMessage( urldecode($res['error(0).message']) );
			}

			return false;
		}

		return $this->showPage('end');
	}

	private function afterOrderConfirm_Classical(&$order, &$methods, $method_id) {
		if($this->currency->currency_locale['int_frac_digits'] > 2)
			$this->currency->currency_locale['int_frac_digits'] = 2;

		$notify_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&order_id='.$order->order_id.'&tmpl=component&lang='.$this->locale . $this->url_itemid;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id . $this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id . $this->url_itemid;

		if(!isset($this->payment_params->sandbox) && isset($this->payment_params->debug))
			$this->payment_params->sandbox = $this->payment_params->debug;

		$tax_total = '';
		$discount_total = '';
		$debug = @$this->payment_params->debug;
		$sandbox = @$this->payment_params->sandbox;
		if(!isset($this->payment_params->no_shipping))
			$this->payment_params->no_shipping = 1;
		if(!empty($this->payment_params->rm))
			$this->payment_params->rm = 2;

		$vars = array(
			'cmd' => '_ext-enter',
			'redirect_cmd' => '_cart',
			'upload' => '1',
			'business' => $this->payment_params->email,
			'receiver_email' => $this->payment_params->email,
			'invoice' => $order->order_id,
			'currency_code' => $this->currency->currency_code,
			'return' => $return_url,
			'notify_url' => $notify_url,
			'cancel_return' => $cancel_url,
			'undefined_quantity' => '0',
			'test_ipn' => $sandbox,
			'no_shipping' => $this->payment_params->no_shipping,
			'no_note' => !@$this->payment_params->notes,
			'charset' => 'utf-8',
			'rm' => (int)@$this->payment_params->rm,
			'bn' => 'ObsidevHikaMarket_Cart_WPS'
		);

		$db = JFactory::getDBO();
		$suborders = array();
		$order_id = $order->order_id;
		if($order_id > 0 && $this->initMarket()) {
			$query = 'SELECT b.*, a.* FROM ' . hikamarket::table('shop.order') . ' AS a LEFT JOIN ' . hikamarket::table('vendor') . ' AS b ON a.order_vendor_id = b.vendor_id ' .
					' WHERE a.order_type = ' . $db->quote('subsale') . ' AND a.order_parent_id = ' . $order_id;
			$db->setQuery($query);
			$suborders = $db->loadObjectList();
		}
		if(!empty($suborders)) {
			$cpt = 0;
			$suborder_id = -1;
			foreach($suborders as $k => $suborder) {
				if($suborder->order_vendor_price == 0.0 || (int)$suborder->order_vendor_id <= 1)
					continue;
				$suborder_id = $k;
				$cpt++;
			}
			if($cpt == 1) {
				$suborder = $suborders[$suborder_id];
				$suborder_id = (int)$suborder->order_id;
				if(is_string($suborder->vendor_params) && !empty($suborder->vendor_params))
					$suborder->vendor_params = unserialize($suborder->vendor_params);
				$paypal_email = @$suborder->vendor_params->paypal_email;

				if(!empty($paypal_email)) {
					$vars['business'] = $paypal_email;
					$vars['receiver_email'] = $paypal_email;

					$order_vendor_price = (float)hikamarket::toFloat($suborder->order_vendor_price);

					if($order_vendor_price > 0) {
						$order_vendor_price = $order_vendor_price - $order->order_full_price;

						$order_payment_params = $suborder->order_payment_params;
						if(!empty($order_payment_params) && is_string($order_payment_params))
							$order_payment_params = unserialize($order_payment_params);
						else
							$order_payment_params = new stdClass();
						$order_payment_params->market_mode = 'commission';
						$order_payment_params = serialize($order_payment_params);

						$query = 'UPDATE ' . hikamarket::table('shop.order') . ' SET order_vendor_price = '.$db->Quote($order_vendor_price) . ', order_payment_params = ' . $db->Quote($order_payment_params).' WHERE order_id = '.(int)$suborder_id;
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		}

		if(!empty($this->payment_params->address_type)) {
			$address_type = $this->payment_params->address_type . '_address';
			$address = $this->app->getUserState(HIKASHOP_COMPONENT . '.' . $address_type);
			if(!empty($address)) {
				if(!isset($this->payment_params->address_override)) {
					$this->payment_params->address_override = '1';
				}

				$vars['address_override'] = $this->payment_params->address_override;
				$vars['first_name'] = @$order->cart->$address_type->address_firstname;
				$vars['last_name'] = @$order->cart->$address_type->address_lastname;
				$address1 = '';
				$address2 = '';

				if(!empty($order->cart->$address_type->address_street2))
					$address2 = substr($order->cart->$address_type->address_street2, 0, 99);

				if(!empty($order->cart->$address_type->address_street)) {
					if(strlen($order->cart->$address_type->address_street) > 100) {
						$address1 = substr($order->cart->$address_type->address_street, 0, 99);
						if(empty($address2))
							$address2 = substr($order->cart->$address_type->address_street, 99, 199);
					} else {
						$address1 = $order->cart->$address_type->address_street;
					}
				}

				$vars['address1'] = $address1;
				$vars['address2'] = $address2;
				$vars['zip'] = @$order->cart->$address_type->address_post_code;
				$vars['city'] = @$order->cart->$address_type->address_city;
				if((!isset($order->cart->$address_type->address_state->zone_code_3) || is_numeric($order->cart->$address_type->address_state->zone_code_3)) && !empty($order->cart->$address_type->address_country->zone_name)){
					$vars['state'] = @$order->cart->$address_type->address_state->zone_name;
				}else{
					$vars['state'] = @$order->cart->$address_type->address_state->zone_code_3;
				}
				$vars['country'] = @$order->cart->$address_type->address_country->zone_code_2;
				$vars['email'] = $this->user->user_email;
				$vars['night_phone_b'] = @$order->cart->$address_type->address_telephone;

			} elseif(!empty($order->cart->billing_address->address_country->zone_code_2)) {
				$vars['lc'] = $order->cart->billing_address->address_country->zone_code_2;
			}
		} elseif(!empty($order->cart->billing_address->address_country->zone_code_2)) {
			$vars['lc'] = $order->cart->billing_address->address_country->zone_code_2;
		}

		if(!empty($this->payment_params->cpp_header_image)) {
			$vars['cpp_header_image'] = $this->payment_params->cpp_header_image;
		}

		if(empty($this->payment_params->details)) {
			$vars['amount_1'] = round($order->cart->full_total->prices[0]->price_value_with_tax, (int)$this->currency->currency_locale['int_frac_digits']);
			$vars['item_name_1'] = JText::_('CART_PRODUCT_TOTAL_PRICE');
		} else {
			$i = 1;
			$tax = 0;
			$config =& hikashop_config();
			$group = $config->get('group_options',0);
			foreach($order->cart->products as $product) {
				if($group && $product->order_product_option_parent_id) continue;
				$vars['item_name_' . $i] = substr(strip_tags($product->order_product_name), 0, 127);
				$vars['item_number_' . $i] = $product->order_product_code;
				$vars['amount_'.$i] = round($product->order_product_price, (int)$this->currency->currency_locale['int_frac_digits']);
				$vars['quantity_' . $i] = $product->order_product_quantity;
				$tax += round($product->order_product_tax, (int)$this->currency->currency_locale['int_frac_digits']) * $product->order_product_quantity;
				$i++;
			}

			if(!empty($order->order_shipping_price) && bccomp($order->order_shipping_price, 0, 5)) {
				$vars['item_name_' . $i] = JText::_('HIKASHOP_SHIPPING');
				$vars['amount_' . $i] = round($order->order_shipping_price - @$order->order_shipping_tax, (int)$this->currency->currency_locale['int_frac_digits']);
				$tax += round($order->order_shipping_tax, (int)$this->currency->currency_locale['int_frac_digits']);
				$vars['quantity_' . $i] = 1;
				$i++;
			}

			if(!empty($order->order_payment_price) && bccomp($order->order_payment_price, 0, 5)) {
				$vars['item_name_' . $i] = JText::_('HIKASHOP_PAYMENT');
				$vars['amount_' . $i] = round($order->order_payment_price - @$order->order_payment_tax, (int)$this->currency->currency_locale['int_frac_digits']);
				$tax += round($order->order_payment_tax, (int)$this->currency->currency_locale['int_frac_digits']);
				$vars['quantity_' . $i] = 1;
				$i++;
			}

			if(bccomp($tax, 0, 5))
				$vars['tax_cart'] = $tax;
			if(!empty($order->cart->coupon))
				$vars['discount_amount_cart'] = round($order->order_discount_price, (int)$this->currency->currency_locale['int_frac_digits']);
		}

		if((isset($this->payment_params->validation) && $this->payment_params->validation) || (isset($this->payment_params->enable_validation) && !$this->payment_params->enable_validation)) {
			$vars['paymentaction'] = 'authorization';
		}

		if($sandbox)
			$this->return_url = 'https://www.sandbox.paypal.com/cgi-bin/websc';
		else
			$this->return_url = 'https://www.paypal.com/cgi-bin/webscr';

		$this->vars = $vars;
		return $this->showPage('end');
	}

	public function onPaymentNotification(&$statuses) {
		$order_id = 0;
		$order_text = '';

		if(!isset($this->payment_params->sandbox) && isset($this->payment_params->debug))
			$this->payment_params->sandbox = $this->payment_params->debug;

		if(hikaInput::get()->getCmd('mode', '') == 'popup' && hikaInput::get()->getCmd('subtask', '') != '') {
			$order_id = (int)@$_GET['order_id'];
			$task = hikaInput::get()->getCmd('subtask', '');
			$doc = JFactory::getDocument();

			$itemId = hikaInput::get()->getInt('Itemid', 0);
			if($itemId > 0)
				$itemId = '&Itemid'.$itemid;
			else
				$itemId = '';

			if($task == 'after_end') {
				$url = hikashop_completeLink('checkout&task=after_end&order_id=' . $order_id . $itemId);
			} else {
				$url = hikashop_completeLink('order&task=cancel_order&order_id=' . $order_id . $itemId);
			}

			$doc->addScriptDeclaration('
window.hikashop.ready(function() {
	var fct = top.validatePaypalBox || top.opener.top.validatePaypalBox;
	var dgFlow = top.dgFlow || top.opener.top.dgFlow;
	fct("'.$url.'");
	dgFlow.closeFlow();
	top.close();
});');
			return true;
		}

		$raw_data = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : file_get_contents('php://input');
		$ipndata = $this->processIPNdata($raw_data);

		$order_id = (int)@$_GET['order_id'];
		if(isset($ipndata['tracking_id']))
			$order_id = (int)substr($ipndata['tracking_id'], 0, strpos($ipndata['tracking_id'], '#'));

		$dbOrder = $this->getOrder((int)$order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);

		if(!isset($ipndata['status']) && empty($this->payment_params->classical)) {
			echo 'Paypal classic IPN, not adaptive one';
			return false;
		}

		if($this->payment_params->debug)
			echo print_r($ipndata, true) . "\r\n\r\n";

		if(empty($dbOrder)) {
			echo 'Could not load any order for your notification ' . $order_id;
			return false;
		}

		$order_text = "\r\n\r\n" . 'Order Id: ' . $order_id;

		if(!empty($this->payment_params->ips)) {
			$ip = hikashop_getIP();
			$ips = str_replace(array('.', '*', ','), array('\.', '[0-9]+', '|'), $this->payment_params->ips);
			if(!preg_match('#('.implode('|',$ips).')#', $ip)) {
				$email = new stdClass();
				$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER', 'Paypal') . ' ' . JText::sprintf('IP_NOT_VALID', $dbOrder->order_number);
				$email->body = str_replace('<br/>', "\r\n", JText::sprintf('NOTIFICATION_REFUSED_FROM_IP', 'Paypal', $ip, implode("\r\n", $this->payment_params->ips))) .
					"\r\n\r\n" . JText::sprintf('CHECK_DOCUMENTATION', HIKASHOP_HELPURL . 'payment-paypal-error#ip') . $order_text;
				$o = false;
				$this->modifyOrder($o, null, null, $email);

				if(HIKASHOP_J30) {
					throw new JAccessExceptionNotallowed(JText::_('Access Forbidden'), 403);
				} else {
					JError::raiseError(403, JText::_('Access Forbidden'));
				}
				return false;
			}
			$order_text .= "\r\n" . 'IP Address: ' . $ip;
		}

		if($this->payment_params->debug)
			echo print_r($dbOrder, true) . "\r\n\r\n";

		$ipnConfirm = $this->verifyIPN();

		if(empty($response) || !preg_match('#VERIFIED#i', $response)) {
			$notif_urls = array(
				'production' => 'https://www.paypal.com/webscr',
				'sandbox' => 'https://www.sandbox.paypal.com/webscr',
			);
			$notif_url = $notif_urls[ $this->payment_params->sandbox ? 'sandbox' : 'production' ];
			$ipnConfirm = $this->sendIPNconfirm($notif_url, $raw_data . '&cmd=_notify-validate');
		}

		if($this->payment_params->debug)
			echo $ipnConfirm;

		$db = JFactory::getDBO();
		$suborders = array();
		if($order_id > 0 && $this->initMarket()) {
			$query = 'SELECT b.*, a.* FROM ' . hikamarket::table('shop.order') . ' AS a LEFT JOIN ' . hikamarket::table('vendor') . ' AS b ON a.order_vendor_id = b.vendor_id WHERE a.order_parent_id = ' . $order_id;
			$db->setQuery($query);
			$suborders = $db->loadObjectList();
		}

		$verified = preg_match('#VERIFIED#i', $ipnConfirm);
		if(!$verified) {
			if(empty($raw_data))
				$order_text .= "\r\n" . 'Warning: Empty data received.';

			$email = new stdClass();
			if(preg_match('#INVALID#i', $ipnConfirm)) {
				$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER', 'Paypal') . ' invalid transaction';
				$email->body = JText::sprintf("Hello,\r\n A paypal notification was refused because it could not be verified by the paypal server").
					"\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION', HIKASHOP_HELPURL . 'payment-paypal-error#invalidtnx') . $order_text;
				if($this->payment_params->debug)
					echo 'invalid transaction'."\n\n\n";
			} else {
				$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER', 'Paypal') . ' invalid response';
				$email->body = JText::sprintf("Hello,\r\n A paypal notification was refused because the response from the paypal server was invalid").
					"\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION', HIKASHOP_HELPURL . 'payment-paypal-error#invalidresponse') . $order_text;

				if($this->payment_params->debug)
					echo 'invalid response'."\n\n\n";
			}
			$o = false;
			$this->modifyOrder($o, null, null, $email);
			return false;
		}

		if(!empty($this->payment_params->classical)) {
			$ipndata['status'] = $ipndata['payment_status'];
		}

		$completed = preg_match('#Completed#i', $ipndata['status']);
		$pending = preg_match('#Pending#i', $ipndata['status']);
		if(!$completed && !$pending) {
			$email = new stdClass();
			$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', 'Paypal', $ipndata['status'], $dbOrder->order_number);
			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS', 'Paypal', $ipndata['status'])) .
				' ' . JText::_('STATUS_NOT_CHANGED') . "\r\n\r\n" .
				JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-paypal-error#status') . $order_text;
			$o = false;
			$this->modifyOrder($o, null, null, $email);

			if($this->payment_params->debug)
				echo 'payment ' . $ipndata['status'] . "\r\n\r\n";
			return false;
		}

		$paypal_ids = array();
		$amount = 0;
		if(empty($this->payment_params->classical)) {
			$receiver_emails = array();
			foreach($ipndata['transaction'] as $transaction) {
				$paypal_ids[] = $transaction['id'];
				$receiver_emails[] = $transaction['receiver'];
			}
			$amount = $ipndata['transaction'][0]['amount'];
		} else {
			$paypal_ids[] = $ipndata['txn_id'];
			$amount = @$ipndata['mc_gross'] . @$ipndata['mc_currency'];
		}
		echo 'PayPal transaction id: '.implode(', ', $paypal_ids) . "\r\n\r\n";

		$history = new stdClass();
		$history->notified = 0;
		$history->amount = $amount;
		$history->data = ob_get_clean();

		if($this->currency->currency_locale['int_frac_digits'] > 2)
			$this->currency->currency_locale['int_frac_digits'] = 2;
		$price_check = round($dbOrder->order_full_price, (int)$this->currency->currency_locale['int_frac_digits']);
		if(!empty($this->payment_params->classical) && ($price_check != @$ipndata['mc_gross'] || $this->currency->currency_code != @$ipndata['mc_currency'])) {
			$email = new stdClass();
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Paypal').JText::_('INVALID_AMOUNT');
			$email->body = str_replace('<br/>', "\r\n", JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER', 'Paypal', $history->amount, $price_check . $this->currency->currency_code)) . "\r\n\r\n" . JText::sprintf('CHECK_DOCUMENTATION', HIKASHOP_HELPURL . 'payment-paypal-error#amount') . $order_text;

			$this->modifyOrder($order_id, $this->payment_params->invalid_status, $history, $email);
			return false;
		}

	 	if($completed) {
	 		$order_status = $this->payment_params->verified_status;
	 	} else {
	 		$order_status = $this->payment_params->pending_status;
	 		$order_text = JText::sprintf('CHECK_DOCUMENTATION', HIKASHOP_HELPURL . 'payment-paypal-error#pending') . "\r\n\r\n" . $order_text;
	 	}

	 	if($dbOrder->order_status == $order_status)
	 		return true;

		$config = hikashop_config();
		if($config->get('order_confirmed_status', 'confirmed') == $order_status)
			$history->notified = 1;

		$email = new stdClass();
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', 'Paypal', $ipndata['status'], $dbOrder->order_number);
		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS', 'Paypal', $ipndata['status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->mail_status)."\r\n\r\n".$order_text;

	 	$this->modifyOrder($order_id, $order_status, $history, $email);

		if(!empty($suborders) && empty($this->payment_params->classical)) {
			$suborders_id = array();
			$vendor_ids = array();
			foreach($suborders as $suborder) {
				$paypal_email = $suborder->vendor_email;
				if(!empty($suborder->vendor_params) && is_string($suborder->vendor_params))
					$suborder->vendor_params = unserialize($suborder->vendor_params);
				if(!empty($suborder->vendor_params->paypal_email))
					$paypal_email = $suborder->vendor_params->paypal_email;

				if(in_array($paypal_email, $receiver_emails)) {
					$suborders_id[] = $suborder->order_id;
					$vendor_ids[] = $suborder->order_vendor_id;
				}
			}

			if(!empty($vendor_ids)) {
				$query = 'UPDATE ' . hikamarket::table('order_transaction') . ' SET order_transaction_paid = order_id WHERE order_id = '.(int)$order_id.' AND vendor_id IN (' . implode(',', $vendor_ids) . ')';
				$db->setQuery($query);
				$db->execute();
			}
		}

		return true;
	}

	public function onPaymentConfiguration(&$element) {
		$subtask = hikaInput::get()->getCmd('subtask', '');
		if($subtask == 'ips') {
			$ips = null;
			echo implode(',', $this->getIPList($ips));
			exit;
		}

		parent::onPaymentConfiguration($element);
		$this->address = hikashop_get('type.address');

		if(empty($element->payment_params->username)) {
			$app = JFactory::getApplication();
			$lang = JFactory::getLanguage();
			$locale = strtolower(substr($lang->get('tag'), 0, 2));
			$app->enqueueMessage(JText::sprintf('ENTER_INFO_REGISTER_IF_NEEDED', 'PayPal', JText::_('HIKA_EMAIL'), 'PayPal', 'https://www.paypal.com/' . $locale . '/mrb/pal=SXL9FKNKGAEM8'));
		}

		if(!function_exists('curl_init')) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('CURL_NOT_FOUND'), 'error');
		}
	}

	public function onPaymentConfigurationSave(&$element) {
		$ret = parent::onPaymentConfigurationSave($element);
		if($ret) {
			$element->payment_params->market_support = true;
			if(!empty($element->payment_params->ips))
				$element->payment_params->ips = explode(',', $element->payment_params->ips);
		}
		return $ret;
	}

	private function convertStruct($struct, $format = 'nv') {
		if($format != 'nv')
			return '';

		$output = array();
		foreach($struct as $k => $v) {
			if(is_array($v)) {
				foreach($v as $l => $w) {
					if(is_numeric($l)) {
						foreach($w as $n => $x) {
							$output[] = $k . '.' . str_replace('List', '', $k) . '(' . $l . ').' . $n . '=' . urlencode($x);
						}
					} else {
						$output[] = $k . '.' . $l . '=' . urlencode($w);
					}
				}
			} else {
				$output[] = $k . '=' . urlencode($v);
			}
		}
		return implode('&', $output);
	}

	private function parseResponse($data, $format = 'nv') {
		if($format != 'nv')
			return $data;
		$res = array();
		$tmp = explode('&', $data);
		foreach($tmp as $t) {
			if(strpos($t, '=') === false)
				continue;
			list($k,$v) = explode('=', $t, 2);
			$res[$k] = $v;
		}
		unset($tmp);
		return $res;
	}

	private function submitPaypalData($url, $headers, $struct, $format = 'nv') {
		$data = $this->convertStruct($struct, $format);

		if(!empty($this->payment_params->use_fsock))
			return $this->submitPaypalData_socket($url, $headers, $data, $format);

		$session = curl_init();
		curl_setopt($session, CURLOPT_FRESH_CONNECT,  true);
		curl_setopt($session, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($session, CURLOPT_FAILONERROR,    true);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session, CURLOPT_USERAGENT,      'HikaMarket-Paypal-Adaptive');
		curl_setopt($session, CURLOPT_TIMEOUT,        60);
		curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($session, CURLOPT_COOKIEFILE,     '');
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($session, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($session, CURLOPT_SSLVERSION,     6);
		curl_setopt($session, CURLOPT_ENCODING,       'UTF-8');
		curl_setopt($session, CURLOPT_HEADER,         false);
		curl_setopt($session, CURLOPT_HTTPHEADER,     $headers);
		curl_setopt($session, CURLOPT_URL,            $url);
		curl_setopt($session, CURLOPT_POST,           true);
		curl_setopt($session, CURLOPT_POSTFIELDS,     $data);

		$curl_version = curl_version();
		$sslVersion = isset($curl_version['ssl_version']) ? $curl_version['ssl_version'] : '';
		if(substr($sslVersion, 0, 4) != 'NSS/') {
			curl_setopt($session, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
		}

		$result = trim(curl_exec($session));
		$error = curl_error($session);
		curl_close($session);

		$ret = $this->parseResponse($result, $format);

		if(empty($ret) && !empty($error) && !empty($this->payment_params->debug)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage($error, 'error');
		}

		return $ret;
	}

	private function submitPaypalData_socket($dest_url, $headers, $data, $format) {
		$url = parse_url($dest_url);
		if(!isset($url['query']))
			$url['query'] = '';

		if(!isset($url['port'])) {
			if(!empty($url['scheme']) && in_array($url['scheme'], array('https', 'ssl'))) {
				$url['port'] = 443;
			} else {
				$url['port'] = 80;
			}
		}

		if(!empty($url['scheme']) && in_array($url['scheme'], array('https', 'ssl'))) {
			$url['host_socket'] = 'ssl://' . $url['host'];
		} else {
			$url['host_socket'] = $url['host'];
		}

		$fp = fsockopen($url['host_socket'], $url['port'], $errno, $errstr, 30);
		if(!$fp)
			return false;

		if(empty($headers)) {
			$headers = '';
		} else {
			$headers = implode("\r\n", $headers) . "\r\n";
		}

		$uri = $url['path'] . ($url['query'] != '' ? '?' . $url['query'] : '');
		$header = 'POST '.$uri.' HTTP/1.1'."\r\n".
			'User-Agent: PHP/'.phpversion()."\r\n".
			'Server: '.$_SERVER['SERVER_SOFTWARE']."\r\n".
			'Host: '.$url['host']."\r\n".
			'Content-Type: application/x-www-form-urlencoded'."\r\n".
			'Content-Length: '.strlen($data)."\r\n".
			'Accept: */'.'*'."\r\n".$headers.
			'Connection: close'."\r\n\r\n";

		fwrite($fp, $header . $data);
		$response = '';
		while(!feof($fp)) {
			$response .= @fgets($fp, 4096);
		}
		fclose ($fp);

		$result = substr($response, strpos($response, "\r\n\r\n") + strlen("\r\n\r\n"));
		$lines = explode("\n", $result);
		if(strpos($lines[0], '&') === false)
			array_shift($lines);
		$result = reset($lines);

		return $this->parseResponse($result, $format);
	}

	private function processIPNdata($data = '') {
		if(empty($data))
			return array();
		$ret = array();
		$elements = explode('&', $data);
		foreach($elements as $element) {
			if(strpos($element, '=') === false)
				continue;

			list($k, $v) = explode('=', $element, 2);
			$k = urldecode($k);
			$v = urldecode($v);

			preg_match('/(\w+)(?:\[(\d+)\])?(?:\.(\w+))?/', $k, $parts);
			switch(count($parts)) {
				case 4:
					if(!isset($ret[ $parts[1] ]))
						$ret[ $parts[1] ] = array();
					if(!isset($ret[ $parts[1] ][ $parts[2] ]))
						$ret[ $parts[1] ][ $parts[2] ] = array();
					$ret[ $parts[1] ][ $parts[2] ][ $parts[3] ] = $v;
					break;

				case 3:
					if(!isset($ret[$parts[1]]))
						$ret[ $parts[1] ] = array();
					$ret[ $parts[1] ][ $parts[2] ] = $v;
					break;

				default:
					$ret[$k] = $v;
					break;
			}
		}

		return $ret;
	}

	private function sendIPNconfirm($notif_url, $data = '') {
		$url = parse_url($notif_url);
		if(!isset($url['query']))
			$url['query'] = '';

		if(!isset($url['port'])) {
			if(!empty($url['scheme']) && in_array($url['scheme'], array('https', 'ssl'))) {
				$url['port'] = 443;
			} else {
				$url['port'] = 80;
			}
		}

		if(!empty($url['scheme']) && in_array($url['scheme'], array('https', 'ssl'))) {
			$url['host_socket'] = 'ssl://' . $url['host'];
		} else {
			$url['host_socket'] = $url['host'];
		}

		if(!empty($this->payment_params->use_fsock))
			return $this->sendIPNconfirm_socket($url, $data);

		return $this->sendIPNconfirm_socket($url, $data);
	}

	private function sendIPNconfirm_socket($url, $data = '') {
		$fp = fsockopen($url['host_socket'], $url['port'], $errno, $errstr, 30);
		if(!$fp)
			return false;

		$uri = $url['path'] . ($url['query'] != '' ? '?' . $url['query'] : '');
		$header = 'POST '.$uri.' HTTP/1.1'."\r\n".
			'User-Agent: PHP/'.phpversion()."\r\n".
			'Referer: '.hikashop_currentURL()."\r\n".
			'Server: '.$_SERVER['SERVER_SOFTWARE']."\r\n".
			'Host: '.$url['host']."\r\n".
			'Content-Type: application/x-www-form-urlencoded'."\r\n".
			'Content-Length: '.strlen($data)."\r\n".
			'Accept: */'.'*'."\r\n".
			'Connection: close'."\r\n\r\n";

		fwrite($fp, $header . $data);
		$response = '';
		while(!feof($fp)) {
			$response .= fgets($fp, 1024);
		}
		fclose ($fp);

		$response = substr($response, strpos($response, "\r\n\r\n") + strlen("\r\n\r\n"));

		return $response;
	}

	private function sendIPNconfirm_CURL($url, $data = '') {
		if(!function_exists('curl_version')) {
			if($this->payment_params->debug)
				echo 'CURL is not available'. "\r\n\r\n";
			return false;
		}

		$uri = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query'] != '' ? '?' . $url['query'] : '');
		$ch = curl_init($uri);

		if(!$ch){
			if($this->payment_params->debug)
				echo 'CURL could not be initialized'. "\r\n\r\n";
			return false;
		}

		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
		curl_setopt($ch, CURLOPT_FAILONERROR,true);

		if($this->payment_params->debug) {
			echo print_r($data, true) . "\r\n\r\n";
		}

		$response = curl_exec($ch);

		$errno = curl_errno($ch);
		$error = curl_error($ch);

		curl_close($ch);

		if (!$response) {
			if($this->payment_params->debug)
				echo 'CURL request didn\t return any data'. "\r\n\r\n";
			return false;
		}

		if($errno){
			if($this->payment_params->debug) {
				echo 'CURL error number: '.$errno. "\r\n\r\n";
				echo 'CURL error message: '.$error. "\r\n\r\n";
			}
		}

		if($this->payment_params->debug) {
			echo print_r($response, true) . "\r\n\r\n";
		}

		return $response;
	}

	protected function verifyIPN() {
		if(!function_exists('curl_version'))
			return false;

		$raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		$myPost = array();
		foreach ($raw_post_array as $keyval) {
			$keyval = explode('=', $keyval);
			if (count($keyval) == 2) {
				if ($keyval[0] === 'payment_date') {
					if (substr_count($keyval[1], '+') === 1) {
						$keyval[1] = str_replace('+', '%2B', $keyval[1]);
					}
				}
				$myPost[$keyval[0]] = urldecode($keyval[1]);
			}
		}
		$req = 'cmd=_notify-validate';
		$get_magic_quotes_exists = false;
		if (function_exists('get_magic_quotes_gpc')) {
			$get_magic_quotes_exists = true;
		}
		foreach ($myPost as $key => $value) {
			if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
				$value = urlencode(stripslashes($value));
			} else {
				$value = urlencode($value);
			}
			$req .= "&$key=$value";
		}

		if(empty($this->payment_params->url))
			$this->payment_params->url = 'https://www.paypal.com/cgi-bin/webscr';
		if(strpos($this->payment_params->url, 'sandbox') === false) {
			$url = 'https://ipnpb.paypal.com/cgi-bin/webscr';
		} else {
			$url = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
		}

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSLVERSION, 6);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
		$res = curl_exec($ch);

		if ( ! ($res)) {
			$errno = curl_errno($ch);
			$errstr = curl_error($ch);
			curl_close($ch);
			$this->writeToLog("cURL error: [$errno] $errstr");
			return false;
		}

		$info = curl_getinfo($ch);
		$http_code = $info['http_code'];
		if ($http_code != 200) {
			$this->writeToLog("PayPal responded with http code $http_code");
			return false;
		}
		curl_close($ch);

		return $res;
	}

	public function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'PayPal';
		$element->payment_description = 'You can pay by credit card or paypal using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,PayPal';

		$element->payment_params->username = '';
		$element->payment_params->password = '';
		$element->payment_params->signature = '';
		$element->payment_params->ips = '';
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
	}

	private function getIPList(&$ipList) {
		$hosts = array(
			'www.paypal.com',
			'notify.paypal.com',
			'ipn.sandbox.paypal.com'
		);

		$ipList = array();
		foreach($hosts as $host) {
			$ips = gethostbynamel($host);
			if(empty($ips))
				continue;
			if(empty($ipList))
				$ipList = $ips;
			else
				$ipList = array_merge($ipList, $ips);
		}

		if(empty($ipList))
			return $ipList;

		$newList = array();
		foreach($ipList as $k => $ip) {
			$ipParts = explode('.', $ip);
			if(count($ipParts) == 4) {
				array_pop($ipParts);
				$ip = implode('.', $ipParts) . '.*';
			}
			if(!in_array($ip, $newList))
				$newList[] = $ip;
		}
		return $newList;
	}
}
