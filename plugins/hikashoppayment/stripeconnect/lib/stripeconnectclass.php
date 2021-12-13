<?php
class StripeConnectClass {
	protected $client_id = null;
	protected $api_key = null;

	public function __construct($client_id, $api_key) {
		if(!empty($client_id))
			$this->setClientId($client_id);
		if(!empty($api_key))
			$this->setApiKey($api_key);
		\Stripe\Stripe::setAppInfo('HikaShop Stripe Connect', '1.0.6', 'https://www.hikashop.com/', 'pp_partner_EeHZlYRrkR2iA6');
	}

	public function setClientId($client_id) {
		$this->client_id = $client_id;
		\Stripe\Stripe::setClientId($this->client_id );
	}
	public function setApiKey($api_key) {
		$this->api_key = $api_key;
		\Stripe\Stripe::setApiKey($this->api_key);
		\Stripe\Stripe::setApiVersion('2019-03-14');
	}

	public function createCharge($params, $connect = null) {
		try {
			if($connect === null)
				return \Stripe\Charge::create($params);
			return \Stripe\Charge::create($params, $connect);
		} catch(\Stripe\Error\Card $e) {
			$ret = new stdClass();
			$ret->decline_code = $e->getDeclineCode();
			$ret->failure_message = $e->getMessage();
			return $ret;
		} catch (Exception $e) {
			hikashop_writeToLog('Create Charge Error'."\r\n".$e->getMessage(), 'StripeConnect');
		}
		return false;
	}


	public function updateCharge($identifier, $params) {
		if(empty($params))
			return true;

		try {
			$charge = \Stripe\Charge::retrieve($identifier);
		} catch (Exception $e) {
			hikashop_writeToLog('Retrieve Charge Error'."\r\n".$e->getMessage(), 'StripeConnect');
		}
		if(empty($charge))
			return false;

		$u = false;
		foreach($params as $k => $v) {
			if(!in_array($k, array('description', 'metadata', 'customer', 'receipt_email', 'shipping', 'transfer_group', 'fraud_details')))
				continue;
			$charge->$k = $v;
			$u = true;
		}
		if(!$u)
			return false;

		try {
			$ret = $charge->save();
			return $ret;
		} catch (Exception $e) {
			hikashop_writeToLog('Update Charge Error'."\r\n".$e->getMessage(), 'StripeConnect');
		}
		return false;
	}

	public function captureCharge($identifier) {
		try {
			$charge = \Stripe\Charge::retrieve($identifier);
		} catch (Exception $e) {
			hikashop_writeToLog('Retrieve Charge Error'."\r\n".$e->getMessage(), 'StripeConnect');
		}

		if(empty($charge))
			return false;

		try {
			$ret = $charge->capture();
			return $ret;
		} catch (Exception $e) {
			hikashop_writeToLog('Capture Charge Error'."\r\n".$e->getMessage(), 'StripeConnect');
		}
		return false;
	}

	public function createSource($data) {
		try {
			$source = \Stripe\Source::create( $data );
		} catch (Exception $e) {
			hikashop_writeToLog('Create Source Error'."\r\n".$e->getMessage(), 'StripeConnect');
		}
		if(empty($source))
			return false;
		return $source;
	}

	public function createPaymentIntent($data, $connect = null) {
		try {
			if($connect===null)
				$intent = \Stripe\PaymentIntent::create( $data );
			else
				$intent = \Stripe\PaymentIntent::create( $data, $connect );
		} catch (Exception $e) {
			hikashop_writeToLog('Create Payment Intent Error'."\r\n".$e->getMessage(), 'StripeConnect');
		}
		if(empty($intent))
			return false;
		return $intent;
	}

	public function updatePaymentIntent($identifier, $data) {
		try {
			$intent = \Stripe\PaymentIntent::update($identifier, $data);
		} catch (Exception $e) {
			hikashop_writeToLog('Update Payment Intent Error'."\r\n".$e->getMessage(), 'StripeConnect');
		}
		if(empty($intent))
			return false;
		return $intent;
	}

	public function retrievePaymentIntent($identifier) {
		try {
			$intent = \Stripe\PaymentIntent::retrieve($identifier);
		} catch (Exception $e) {
			hikashop_writeToLog('Retrieve Payment Intent Error'."\r\n".$e->getMessage(), 'StripeConnect');
		}
		if(empty($intent))
			return false;
		return $intent;
	}

	public function confirmPaymentIntent(&$intent) {
		if(empty($intent))
			return false;
		try {
			$intent->confirm();
		} catch (Exception $e) {
			$err = $e->getMessage();
			hikashop_writeToLog('Confirm Payment Intent Error'."\r\n".$err, 'StripeConnect');
			return $err;
		}
		return true;
	}

	public function cancelPaymentIntent(&$intent) {
		if(empty($intent))
			return false;
		try {
			$intent->cancel();
		} catch (Exception $e) {
			$err = $e->getMessage();
			hikashop_writeToLog('Cancel Payment Intent Error'."\r\n".$err, 'StripeConnect');
			return $err;
		}
		return true;
	}

	public function createRefund($params, $connect = null) {
		try {
			if($connect === null)
				return \Stripe\Refund::create($params);
			return \Stripe\Refund::create($params, $connect);
		} catch (Exception $e) {
			hikashop_writeToLog('Create Refund Error'."\r\n".$e->getMessage(), 'StripeConnect');
		}
		return false;
	}

	public function retrieveRefund($id) {
		try {
			return \Stripe\Refund::retrieve($id);
		} catch (Exception $e) {
			hikashop_writeToLog('Retrieve Refund Error'."\r\n".$e->getMessage(), 'StripeConnect');
		}
		return false;
	}

	public function createTransfer($params) {
		try {
			return \Stripe\Transfer::create($params);
		} catch (Exception $e) {
			hikashop_writeToLog('Create Transfer Error'."\r\n".$e->getMessage(), 'StripeConnect');
		}
		return false;
	}

	public function getCustomer(&$user, $options = null) {
		$stripe_customer_id = !empty($user->user_params->stripeconnect_customer_id) ? $user->user_params->stripeconnect_customer_id : null;

		if(empty($stripe_customer_id))
			return $this->createCustomer($user, $options);

		try {
			$customer = \Stripe\Customer::retrieve($stripe_customer_id);
			if(empty($customer))
				return $this->createCustomer($user, $options);
		} catch (Exception $e) {
			hikashop_writeToLog('Get Customer Error'."\r\n".$e->getMessage(), 'StripeConnect');

			return $this->createCustomer($user, $options);
		}

		try {
			if(!empty($options['token'])) {
				$customer->source = $options['token'];
				$customer->save();
			}

			if(!empty($options['source'])) {
				$card = $customer->sources->create(array('source' => $options['source']));
				$customer->default_source = $card->id;
				$customer->save();
			}
		} catch (Exception $e) {
			hikashop_writeToLog('Get Customer Error'."\r\n".$e->getMessage(), 'StripeConnect');
			return false;
		}

		return $customer;
	}

	public function createCustomer(&$user, $options = null) {
		try {
			$customerData = array(
				'email' => $user->user_email
			);
			if(!empty($options['token']))
				$customerData['source'] = $options['token'];

			$customer = \Stripe\Customer::create($customerData);
		} catch(\Stripe\Error\Card $e) {
			return false;
		} catch (Exception $e) {
			hikashop_writeToLog('Create Customer Error'."\r\n".$e->getMessage(), 'StripeConnect');
			return false;
		}

		$hk_user = new stdClass();
		$hk_user->user_id = $user->user_id;
		$hk_user->user_params = $user->user_params;
		$hk_user->user_params->stripeconnect_customer_id = $customer->id;

		$userClass = hikashop_get('class.user');
		$userClass->save($hk_user);

		return $customer;
	}

	public function createToken($params, $connect = null) {
		try {
			if($connect === null)
				return \Stripe\Token::create($params);
			return \Stripe\Token::create($params, $connect);
		} catch (Exception $e) {
			hikashop_writeToLog('Create Token Error'."\r\n".$e->getMessage(), 'StripeConnect');
		}
		return false;
	}

	public function getVendors(&$dbOrder) {
		if(!defined('HIKAMARKET_COMPONENT'))
			return false;

		if(!empty($dbOrder->order_id))
			return $this->getVendorsByOrderId($dbOrder);

		$orderMarketClass = hikamarket::get('class.order');
		$products = $orderMarketClass->getProductVendorAttribution($dbOrder);

		$vendor_ids = array();
		foreach($products as $product) {
			$vendor_ids[ $product['vendor'] ] = $product['vendor'];
		}

		if(count($vendor_ids) == 1 && isset($vendor_ids[0]))
			return false;

		$feeClass = hikamarket::get('class.fee');
		$allFees = $feeClass->getProducts($products, $vendor_ids);
	}

	private function getVendorsByOrderId(&$dbOrder) {
		$db = JFactory::getDBO();
		$order_id = (int)$dbOrder->order_id;

		$vendors = array();
		$total = 0.0;

		$query = 'SELECT o.*, v.vendor_name, v.vendor_alias, v.vendor_email, v.vendor_published, v.vendor_params '.
				' FROM ' . hikamarket::table('shop.order') . ' AS o '.
				' LEFT JOIN ' . hikamarket::table('vendor') . ' AS v ON o.order_vendor_id = v.vendor_id '.
				' WHERE o.order_type = '. $db->Quote('subsale') .' AND o.order_parent_id = ' . (int)$order_id;
		$db->setQuery($query);
		$suborders = $db->loadObjectList('order_id');
		foreach($suborders as $suborder) {
			$vendor_params = !empty($suborder->vendor_params) ? hikamarket::unserialize($suborder->vendor_params) : null;
			if($vendor_params === null)
				$vendor_params = new stdClass();

			if(empty($vendor_params->stripe_account_id))
				continue;

			$vendor_id = (int)$suborder->order_vendor_id;

			if($vendor_id <= 1)
				continue;

			$price = (float)hikamarket::toFloat($suborder->order_vendor_price);
			$currency = (int)$suborder->order_currency_id;

			$vendors[ $vendor_id ] = array(
				'sum' => $price,
				'currency' => $currency,
				'stripe_id' => $vendor_params->stripe_account_id,
			);

			$total += $price;
		}

		$full_price = (float)hikamarket::toFloat($dbOrder->order_full_price);

		$vendors[ 0 ] = array(
			'sum' => $full_price - $total,
			'currency' => $dbOrder->order_currency_id,
			'stripe_id' => null
		);

		ksort($vendors);

		return $vendors;
	}

	public function getVendor(&$vendor) {
		$vendor_params = !empty($vendor->vendor_params) ? hikamarket::unserialize($vendor->vendor_params) : null;
		if($vendor_params === null)
			$vendor_params = new stdClass();

		if(empty($vendor_params->stripe_account_id))
			return false;

		return $vendor_params->stripe_account_id;
	}

	public function getOAuthAuthorizeUrl($params = null, $opts = null) {
		return\Stripe\OAuth::authorizeUrl($params, $opts);
	}

	public function oAuthToken($params = null, $opts = null) {
		$ret = false;
		try {
		    $ret = \Stripe\OAuth::token($params, $opts);
		} catch (\Stripe\Error\OAuth\OAuthBase $e) {
			hikashop_writeToLog('OAuth Token'."\r\n".$e->getMessage(), 'StripeConnect');
		} catch(Exception $e) {
		    hikashop_writeToLog('OAuth Token'."\r\n".$e->getMessage(), 'StripeConnect');
		}
		return $ret;
	}


}
