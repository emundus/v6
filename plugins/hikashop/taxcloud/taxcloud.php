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
class plgHikaShopTaxcloud extends JPlugin {
	protected $soap = null;
	protected $debug = false;
	protected $errors = array();

	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		$app = JFactory::getApplication();
		$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.address_hash', '');
	}


	 public function onHikashopBeforeDisplayView(&$view){
	 	$app = JFactory::getApplication();
	 	if(!hikashop_isClient('administrator')) return true;

	 	$viewName = $view->getName();
	 	$layoutName = $view->getLayout();
		if($viewName!='order' || $layoutName!='show') return true;
		$_SESSION['order_products'][$view->order->order_id] = $view->order->products;
	 }


	public function onAfterOrderUpdate(&$order,&$send_email){
		$full_order = null;
		if(!isset($order->order_type)){
			$orderClass = hikashop_get('class.order');
			$full_order = $orderClass->loadFullOrder($order->order_id,false ,false);
			if($full_order->order_type!='sale'){
				return;
			}
		}elseif($order->order_type!='sale'){
			return;
		}
		if(!$this->loadOptions()){
			return false;
		}

		$config =& hikashop_config();
		$app = JFactory::getApplication();

		$confirmed_statuses = explode(',', trim($config->get('invoice_order_statuses','confirmed,shipped'), ','));
		$cancelled_statuses = explode(',', trim($config->get('cancelled_order_status','cancelled'), ','));

		if(empty($confirmed_statuses))
			$confirmed_statuses = array('confirmed','shipped');
		if(empty($cancelled_statuses))
			$cancelled_statuses = array('cancelled');

		if(!empty($_SESSION['order_products'][$order->order_id])){
			if(!isset($full_order)){
				$orderClass = hikashop_get('class.order');
				$full_order = $orderClass->loadFullOrder($order->order_id,false ,false);
			}
			if($this->partialReturn($full_order, false))
				return;
		}

		if(!empty($order->order_status)){
			if(in_array($order->order_status,$confirmed_statuses) && (empty($order->old->order_status) || !in_array($order->old->order_status,$confirmed_statuses))) {//if the actual status is confirmed and the old status wasn't confirmed
				if(!isset($full_order)){
					$orderClass = hikashop_get('class.order');
					$full_order = $orderClass->loadFullOrder($order->order_id,false ,false);
				}
				$this->AuthorizedWithCaptured($full_order);
				return;
			}

			if(in_array($order->order_status,$cancelled_statuses) && in_array($order->old->order_status,$confirmed_statuses)){//if the changed status is cancelled and the old status was confirmed
				if(!isset($full_order)){
					$orderClass = hikashop_get('class.order');
					$full_order = $orderClass->loadFullOrder($order->order_id,false ,false);
				}
				$error = '';
				$error=$this->Returned($full_order);
				if($error != '')
					$app->enqueueMessage('TaxCloud Error : '.$error);
				return;
			}
		}
	}

	public function partialReturn($order, $all){

		static $soapCache = array();
		$app = JFactory::getApplication();

		$new_products_qty = array();
		if(empty($order->order_user_id)){
			$orderClass = hikashop_get('class.order');
			$dbOrder = $orderClass->get($order->order_id);
			$order->order_user_id = $dbOrder->order_user_id;
		}
		$user_id = $order->order_user_id;


		if($all == false){
			foreach($order->products as $product) {
				$new_products_qty[$product->order_product_id] = $product->order_product_quantity;
			}
		}

		$ids = array();
		if($all==false){
			foreach($_SESSION['order_products'][$order->order_id] as $product) {//getting the id of the product to get its tic
				if($product->product_id) $ids[$product->order_product_id] = (int)$product->product_id; //make sure that the product_id is set in the product (it might be a product manually added to the order)
			}
		}else{
			foreach($order->products as $product) {//getting the id of the product to get its tic
				if($product->product_id) $ids[$product->order_product_id] = (int)$product->product_id; //make sure that the product_id is set in the product (it might be a product manually added to the order)
			}
		}

		$db = JFactory::getDBO();
		if(empty($ids) || !count($ids))
			return false;

		$product_query = 'SELECT product_id, product_taxability_code, product_parent_id FROM ' . hikashop_table('product') . ' WHERE product_id IN (' . implode(',', $ids) . ') AND product_access=\'all\' AND product_published=1';
		$db->setQuery($product_query);
		$products = $db->loadObjectList();

		if(empty($products))
			return false;

		$tics = array();
		$parents_ids = array();
		foreach($products as $product){//associating the tics with the right ItemIds
			if(empty($product->product_taxability_code)){
				if(!empty($product->product_parent_id)){
					$parents_ids[$product->product_id] = $product->product_parent_id;
				}
				continue;
			}
			$tics[$product->product_id] = $product->product_taxability_code;
		}

		if(count($parents_ids)){
			$product_query = 'SELECT product_id, product_taxability_code FROM ' . hikashop_table('product') . ' WHERE product_id IN (' . implode(',', $parents_ids) . ') AND product_access=\'all\' AND product_published=1 AND product_type=\'main\'';
			$db->setQuery($product_query);
			$parents = $db->loadObjectList('product_id');

			if(!empty($parents)){
				foreach($products as $product){
					if(!empty($tics[$product->product_id]))
						continue;
					if(empty($parents_ids[$product->product_id]))
						continue;
					if(empty($parents[$parents_ids[$product->product_id]]))
						continue;
					$tics[$product->product_id] = $parents[$parents_ids[$product->product_id]]->product_taxability_code;
				}
			}
		}
		$return_items = array();
		$all_returned = true;
		$i = 0;
		if($all == false){
			foreach($_SESSION['order_products'][$order->order_id] as $old_product_values){

				$old_product_id= $old_product_values->order_product_id;
				$old_product_qty=  $old_product_values->order_product_quantity;

				if(!empty($old_product_values->order_product_params->taxcloud->taxcloud_id))
					$id = $old_product_values->order_product_params->taxcloud->taxcloud_id;
				else
					$id = $old_product_values->order_product_id;

				if(empty($new_products_qty[$old_product_id]) && $old_product_qty!= '0'){
					$old_product_values->order_product_quantity = 0;

					$tic = (int)$this->plugin_options['default_tic'];
					if(isset($ids[$old_product_values->order_product_id]) && isset($tics[$ids[$old_product_values->order_product_id]])){
						if((int)$tics[$ids[$old_product_values->order_product_id]] != '-1' && (int)$tics[$ids[$old_product_values->order_product_id]] !=='')
							$tic = (int)$tics[$ids[$old_product_values->order_product_id]];
					}

					$return_items[] = array(
						'Index' => $id,
						'ItemID' => $old_product_values->order_product_code,
						'TIC' => $tic,
						'Price' => $old_product_values->order_product_price,
						'Qty' => $old_product_qty
					);
					unset($old_product_values);
				}
				else if(@$new_products_qty[$old_product_id] < $old_product_qty){
					$old_product_values->order_product_quantity = $new_products_qty[$old_product_id];

					$tic = (int)$this->plugin_options['default_tic'];
					if(isset($ids[$old_product_values->order_product_id]) && isset($tics[$ids[$old_product_values->order_product_id]])){
						if((int)$tics[$ids[$old_product_values->order_product_id]] != '-1' && (int)$tics[$ids[$old_product_values->order_product_id]] !=='')
							$tic = (int)$tics[$ids[$old_product_values->order_product_id]];
					}
					else {$all_returned = false;}

					$return_items[] = array(
						'Index' => $id,
						'ItemID' => $old_product_values->order_product_code,
						'TIC' => $tic,
						'Price' => $old_product_values->order_product_price,
						'Qty' => $old_product_qty - $new_products_qty[$old_product_id]
					);
				}
			}
		}else{
			foreach($order->products as $product){
				if(!isset($ids[$product->order_product_id]) || !isset($tics[$product->product_id])){
					continue;
				}
				if(!empty($product->order_product_params->taxcloud->taxcloud_id))
					$id = $product->order_product_params->taxcloud->taxcloud_id;
				else
					$id = $product->order_product_id;
				$product_id= $product->order_product_id;
				$product_qty=  $product->order_product_quantity;

				$tic = (int)$this->plugin_options['default_tic'];
				if(isset($ids[$old_product_values->order_product_id]) && isset($tics[$ids[$old_product_values->order_product_id]])){
					if((int)$tics[$ids[$product->order_product_id]] != '-1' && (int)$tics[$ids[$product->order_product_id]] !=='')
						$tic = (int)$tics[$ids[$product->order_product_id]];
				}else{$all_returned = false;}

				$return_items[] = array(
					'Index' => $id,
					'ItemID' => $product->order_product_code,
					'TIC' => $tic,
					'Price' => $product->order_product_price,
					'Qty' => $product_qty
				);
			}

			foreach($order->order_shipping_params->prices as $shipping){

				$return_items[] = array(
					'Index' => $shipping->taxcloud_index,
					'ItemID' => $shipping->taxcloud_itemId,
					'TIC' => (int)$this->plugin_options['shipping_tic'],
					'Price' => $shipping->taxcloud_price,
					'Qty' => 1
				);
			}

		}
		if(!$all_returned)
			$app->enqueueMessage('TaxCloud error : Some item(s) could not be returned. Please return it manually.', 'error');
		if(empty($return_items))
			return false;

		$parameters = array(
			'apiLoginID' => $this->plugin_options['api_id'],
			'apiKey' => $this->plugin_options['api_key'],
			'customerID' => $user_id,
			'orderID' => $order->order_id,
			'cartItems' => $return_items,
			'returnedDate' => date('c'),
		);

		if(!$this->initSoap())
			return true;

		try {
			$soapRet = $this->soap->__soapCall('Returned', array($parameters));
			$ret = $soapRet->ReturnedResult;
		} catch(Exception $e) {
			hikashop_display($e->getMessage());
			$ret = false;
		}
		if(@$ret->Messages->ResponseMessage->ResponseType == "Error"){
			if(empty($ret->Messages->ResponseMessage->Message))
				$ret->Messages->ResponseMessage->Message = 'Unkown error';
			$this->display_errors(@$ret->Messages->ResponseMessage->Message);
		}
		return true;
	}

	public function Returned($order){
		if(!$this->initSoap())
			return false;
		static $soapCache = array();
		$app = JFactory::getApplication();

		if(!$this->loadOptions()){
			return false;
		}

		$cart_items = array();

		$parameters = array(
			'apiLoginID' => $this->plugin_options['api_id'],
			'apiKey' => $this->plugin_options['api_key'],
			'orderID' => $order->order_id,
			'returnedDate' => date('c')
		);

		try {
			$soapRet = $this->soap->__soapCall('Returned', array($parameters));
			$ret = $soapRet->ReturnedResult;
			if(@$ret->Messages->ResponseMessage->ResponseType == "Error"){
				$this->partialReturn($order, true);
			}

		} catch(Exception $e) {
			hikashop_display($e->getMessage());
			$ret = false;
		}

		if($this->debug) {
			var_dump($ret);
		}

	}

	private function AuthorizedWithCaptured(&$order){
		if(!$this->initSoap())
			return false;

		if(!$this->loadOptions()){
			return false;
		}
		$parameters = array(
			'apiLoginID' => $this->plugin_options['api_id'],
			'apiKey' => $this->plugin_options['api_key'],
			'customerID' => $order->order_user_id,
			'cartID' => $order->order_id,
			'orderID' => $order->order_id,
			'dateAuthorized' => date('c'),
			'dateCaptured' => date('c')
		);

		static $soapCache = array();
		$app = JFactory::getApplication();

		try {
			$soapRet = $this->soap->__soapCall('AuthorizedWithCapture', array($parameters));
			$ret = $soapRet->AuthorizedWithCaptureResult;
		} catch(Exception $e) {
			hikashop_display($e->getMessage());
			$ret = false;
		}

		if($this->debug) {
			var_dump($ret);
		}
	}

	public function onAfterOrderCreate(&$order,&$send_email){
		if($order->order_type!='sale')
			return;

		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator'))
			return;
		$this->lookupAfterOrderCreate($order);

		if(!empty($order->order_status)){
			$config =& hikashop_config();
			$confirmed_statuses = explode(',', trim($config->get('invoice_order_statuses','confirmed,shipped'), ','));
			if(empty($confirmed_statuses))
				$confirmed_statuses = array('confirmed','shipped');

			if(in_array($order->order_status,$confirmed_statuses) && (empty($order->old->order_status) || !in_array($order->old->order_status,$confirmed_statuses))) {//if the actual status is confirmed and the old status wasn't confirmed
				$this->AuthorizedWithCaptured($order);
				return;
			}
		}
	}

	private function init() {
		static $init = null;
		if($init !== null)
			return $init;

		$init = defined('HIKASHOP_COMPONENT');
		if(!$init) {
			$filename = rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php';
			if(file_exists($filename)) {
				include_once($filename);
				$init = defined('HIKASHOP_COMPONENT');
			}
		}
		return $init;
	}


	 protected function lookupAfterOrderCreate(&$order) {
		if(!$this->initSoap())
			return false;
		$cart = $order->cart;

		$address = $this->loadAddress();
		if(empty($address)){
			$address = $order->cart->shipping_address;
		}

		$app = JFactory::getApplication();
		if(!$this->loadOptions()){
			return false;
		}
		$db = JFactory::getDBO();

		$parameters = array(
			'uspsUserID' => $this->plugin_options['usps_id'],
			'address1' => $address->address_street,
			'address2' => $address->address_street2,
			'city' => $address->address_city,
			'state' => $address->address_state->zone_code_3,
			'zip5' => $address->address_post_code,
			'zip4' => ''
		);

		$ret = $this->soap->__soapCall('verifyAddress', array($parameters));
		try {
			$ret = $this->soap->__soapCall('verifyAddress', array($parameters));
		} catch(Exception $e) {
			$ret = false;
		}

		if(!empty($ret) && !empty($ret->VerifyAddressResult)) {
			$errNumber = $ret->VerifyAddressResult->ErrNumber;
			if($errNumber === '0') {
				$usps_address = array(
					'Address1' => $ret->VerifyAddressResult->Address1,
					'Address2' => @$ret->VerifyAddressResult->Address2,
					'City' => $ret->VerifyAddressResult->City,
					'State' => $ret->VerifyAddressResult->State,
					'Zip5' => $ret->VerifyAddressResult->Zip5,
					'Zip4' => $ret->VerifyAddressResult->Zip4
				);
			} else if($errNumber === '97'){//if the address is incorrect, use the original address anyway
				$usps_address = array(
					'Address1' => $address->address_street,
					'Address2' => $address->address_street2,
					'City' => $address->address_city,
					'State' => $address->address_state->zone_code_3,
					'Zip5' => $address->address_post_code,
					'Zip4' => ''
					);
			}
			else{
				$option = hikaInput::get()->getCmd('option', '');
				$ctrl = hikaInput::get()->getCmd('ctrl', '');
				if($option == 'com_hikashop' && $ctrl == 'checkout') {
					$app->enqueueMessage(JText::_('WRONG_SHIPPING_ADDRESS'), 'error');
				}
			}
		}

		$user_id = $order->order_user_id;
		$cart_items = array();


		$id = 0;
		foreach($cart->products as &$product) {
			$tic = $this->getCode($product, $cart);
			if(!$tic)
				$tic = (int)$this->plugin_options['default_tic'];

			$id++;
			if(!isset($product->order_product_params))
				$product->order_product_params = new stdClass();
			if(!isset($product->order_product_params->taxcloud))
				$product->order_product_params->taxcloud = new stdClass();
			$product->order_product_params->taxcloud->taxcloud_id = $id;
			$cart_items[] = array(
				'Index' => $id,
				'ItemID' => $product->order_product_code,
				'TIC' => $tic,
				'Price' => $product->order_product_price,
				'Qty' => $product->order_product_quantity
			);

			$product_query = 'UPDATE '. hikashop_table('order_product') .' SET order_product_params = '.$db->Quote(json_encode($product->order_product_params)).'  WHERE order_product_id = '.(int)$product->order_product_id;
			$db->setQuery($product_query);
			$db->execute();
		}
		unset($product);

		$j=0;
		$db = JFactory::getDBO();
		if(!empty($order->order_shipping_params->prices)){
			foreach($order->order_shipping_params->prices as $key => $shipping) {
				$id++;
				$order->order_shipping_params->prices[$key]->taxcloud_index = $id;
				$order->order_shipping_params->prices[$key]->taxcloud_itemId = "Shipping".$j;
				$order->order_shipping_params->prices[$key]->taxcloud_tic = $this->plugin_options['shipping_tic'];
				$order->order_shipping_params->prices[$key]->taxcloud_price = $shipping->price_with_tax - $shipping->tax;


				$cart_items[] = array(
					'Index' => $id,
					'ItemID' => "Shipping".$j,
					'TIC' => $this->plugin_options['shipping_tic'],
					'Price' => $order->order_shipping_params->prices[$key]->taxcloud_price,
					'Qty' => '1'
				);
				$j++;
			}
			$product_query = 'UPDATE '. hikashop_table('order') .' SET order_shipping_params = '.$db->Quote(serialize($order->order_shipping_params)).'  WHERE order_id = '.(int)$order->order_id;
			$db->setQuery($product_query);
			$db->execute();
		}


		$parameters = array(
			'apiLoginID' => $this->plugin_options['api_id'],
			'apiKey' => $this->plugin_options['api_key'],
			'customerID' => $user_id,
			'cartID' => $order->order_id,//important change here ! Not cart_id but order_id
			'cartItems' => $cart_items,
			'origin' => array(
				'Address1' => $this->plugin_options['origin_address1'],
				'Address2' => $this->plugin_options['origin_address2'],
				'City' => $this->plugin_options['origin_city'],
				'State' => $this->plugin_options['origin_state'],
				'Zip5' => $this->plugin_options['origin_zip5'],
				'Zip4' => $this->plugin_options['origin_zip4']
			),
			'destination' => $usps_address,
			'deliveredBySeller' => false,
			'exemptCert' => null
		);

		static $soapCache = array();

		try {
			$soapRet = $this->soap->__soapCall('Lookup', array($parameters));
			$ret = $soapRet->LookupResult;
		} catch(Exception $e) {
			$ret = false;
		}

		if($this->debug) {
			hikashop_display($ret->ResponseType);
			if($ret->ResponseType == 'OK')
				hikashop_display(($ret->CartItemsResponse->CartItemResponse));
			else
				var_dump($ret);
		}
	}

	protected function whitelistCheck() {
		$config = hikashop_config();
		$whitelist = $config->get('taxcloud_whitelist');
		if(empty($whitelist))
			return true;


		$debug = $config->get('taxcloud_whitelist_debug', 0);

		$current_url = hikashop_currentURL();

		$allowedURLs = explode(';;', $whitelist);
		foreach($allowedURLs as $allowed) {
			$parts = explode('||', $allowed);
			$url = $parts[0];
			$url_ok = false;
			if(strpos($current_url, $url)!== false)
				$url_ok = true;

			$params_ok = true;
			if(!empty($parts[1])) {
				$parameters = explode('&', $parts[1]);
				foreach($parameters as $parameter) {
					$param_parts = explode('=', $parameter);
					if(count($param_parts) == 2) {
						$name = $param_parts[0];
						$value = $param_parts[1];
						if(!isset($_POST[$name]) || $_POST[$name] != $value) {
							$params_ok = false;
							break;
						}
					}
				}
			}
			if($params_ok && $url_ok) {
				return true;
			}
		}
		if($debug) {
			hikashop_writeToLog('TaxCloud request was blocked for URL '.$current_url);
		}
		return false;
	}

	public function onAfterCartProductsLoad(&$cart) {
		if(!$this->whitelistCheck())
			return;
		$verify_address = $this->verifyAddress();
		$this->lookup($cart);
	}

	public function onHikashopBeforeCheckDB(&$createTable, &$custom_fields, &$structure, &$helper){
		$structure['#__hikashop_product']['product_taxability_code'] = '`product_taxability_code` INT(10) NOT NULL DEFAULT 0';
	}


	public function onProductFormDisplay(&$product, &$html) {
		if($product->product_type == 'variant')
			return false;
		$db = JFactory::getDBO();
		$current = $db->getTableColumns(hikashop_table('product'));

		if(!isset($current['product_taxability_code'])) {
			$databaseHelper = hikashop_get('helper.database');
			$databaseHelper->addColumns('product','`product_taxability_code` INT(10) NOT NULL DEFAULT 0');
		}


		$html[] = '
<tr>
	<td class="key">
		<label for="">'.JText::_('TAXABILITY_CODE').'</label>
	</td>
	<td>
		<input type="text" name="data[product][product_taxability_code]" value="'.@$product->product_taxability_code.'" id="hikashop_data_product_taxability_code">
		<input type="hidden" name="product_taxability_code_field" value="1"/>
	</td>
</tr>
		';
	}


	public function onAfterProductCreate(&$product) {
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator')) {
			$this->productFormSave($product);
		}
	}

	public function onAfterProductUpdate(&$product) {
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator')) {
			$this->productFormSave($product);
		}
	}

	protected function productFormSave(&$product) {
		$field = hikaInput::get()->getInt('product_taxability_code_field', '0');
		if(!empty($field) && empty($product->product_taxability_code)) {
			$product->product_taxability_code = (int)@$this->plugin_options['default_tic'];
		}
	}

	public function onAfterProcessShippings(&$usable_rates) {
		if(!$this->whitelistCheck())
			return;

		$verify_address = $this->verifyAddress();
		if($verify_address == 2 )
			return;

		if(!$this->initSoap())
			return false;

		$app = JFactory::getApplication();
		if(!$this->loadOptions()){
			return false;
		}
		$user_id = hikashop_loadUser(false);
		if(empty($user_id)){
			return false;
		}

		$usps_address = $app->getUserState(HIKASHOP_COMPONENT.'.taxcloud.full_address', null);
		if(empty($usps_address)){
			$address = $this->loadAddress();
			if(empty($address)) return false;

			$usps_address = array(
				'Address1' => $address->address_street,
				'Address2' => $address->address_street2,
				'City' => $address->address_city,
				'State' => $address->address_state->zone_code_3,
				'Zip5' => $address->address_post_code,
				'Zip4' => ''
			);
		}
		$shipping_tic = (int)@$this->plugin_options['shipping_tic'];
		$done = array();
		$i=0;
		foreach($usable_rates as $k => $method) {
			if(isset($method->shipping_price_with_tax)) unset($usable_rates[$k]->shipping_price_with_tax);
			$group_key =$method->shipping_id;
			if(!empty($method->shipping_warehouse_id)){
				$group_key .= '_';
				if(is_array($method->shipping_warehouse_id)){
					foreach($method->shipping_warehouse_id as $key => $val){
						$group_key .= $key.$val;
					}
				}else{
					$group_key .= $method->shipping_warehouse_id;
				}
			}
			if(isset($done[$group_key])){
				$usable_rates[$k]->taxcloud_id = $done[$group_key];
				continue;
			}
			$i++;
			$done[$group_key] = $i;
			$usable_rates[$k]->taxcloud_id = $i;
			$cart_items[] = array(
				'Index' => $i,
				'ItemID' => 'shipping_rate_'.$group_key,
				'TIC' => $shipping_tic,
				'Price' => $method->shipping_price,
				'Qty' => 1
			);
		}

		$parameters = array(
			'apiLoginID' => $this->plugin_options['api_id'],
			'apiKey' => $this->plugin_options['api_key'],
			'customerID' => $user_id,
			'cartID' => 'sp0',
			'cartItems' => $cart_items,
			'origin' => array(
				'Address1' => $this->plugin_options['origin_address1'],
				'Address2' => $this->plugin_options['origin_address2'],
				'City' => $this->plugin_options['origin_city'],
				'State' => $this->plugin_options['origin_state'],
				'Zip5' => $this->plugin_options['origin_zip5'],
				'Zip4' => $this->plugin_options['origin_zip4']
			),
			'destination' => $usps_address,
			'deliveredBySeller' => true,
			'exemptCert' => null
		);

		static $soapCache = array();

		$hash = md5(serialize($parameters));
		$session_hash = $app->getUserState(HIKASHOP_COMPONENT.'.taxcloud.shipping_cache_hash', '');
		if($hash == $session_hash) {
			$ret = $app->getUserState(HIKASHOP_COMPONENT.'.taxcloud.shipping_cache', '');
			if(!empty($ret) && !empty($ret->ResponseType)) {
				$useCache = true;
				if(!isset($soapCache[$hash]))
					$soapCache[$hash] = $ret;
			} else {
				unset($ret);
			}
		} else {
			$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.shipping_cache_hash', '');
			$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.shipping_cache', null);
		}

		if(!isset($soapCache[$hash])) {
			try {
				$soapRet = $this->soap->__soapCall('Lookup', array($parameters)); //, array('uri' => 'http://taxcloud.net','soapaction' => ''));
				$soapCache[$hash] = $soapRet->LookupResult;
				$ret = $soapRet->LookupResult;
			} catch(Exception $e) {
				$ret = false;
			}

			if($ret !== false) {
				$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.shipping_cache_hash', $hash);
				$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.shipping_cache', $ret);
			}

			if($this->debug) {
				var_dump($ret->ResponseType);
				if($ret->ResponseType == 'OK')
					var_dump($ret->CartItemsResponse->CartItemResponse);
				else
					var_dump($ret);
			}
		} else {
			$ret = $soapCache[$hash];
			$useCache = true;
		}

		$rates = array();


		if(!empty($ret) && $ret->ResponseType == 'OK') {
			if(!is_array($ret->CartItemsResponse->CartItemResponse))
				$ret->CartItemsResponse->CartItemResponse = array($ret->CartItemsResponse->CartItemResponse);

			foreach($ret->CartItemsResponse->CartItemResponse as $item) {
				foreach($usable_rates as &$method) {
					if($method->taxcloud_id == $item->CartItemIndex) {

						$tic = (int)@$this->plugin_options['shipping_tic'];
						if(!empty($method->shipping_taxability_code))
							$tic = (int)$method->shipping_taxability_code;

						$price_value = $method->shipping_price;
						$new_price = $price_value + $item->TaxAmount;

						$t = new stdClass();
						if($price_value != '0'){
							$t->tax_rate = round(($new_price / $price_value) - 1, 4);
							$t->tax_amount = $item->TaxAmount;
							$t->tax_namekey = $this->taxName($t->tax_rate); // JText::sprintf('TAXCLOUD_TAX', $t->tax_rate);

							$method->shipping_price_with_tax = $new_price;
							$method->taxes = array($t->tax_namekey => $t);

							if(!isset($rates[$tic])) {
								$rates[$tic] = new stdClass();
								$rates[$tic]->amount = 0.0;
							}
							$rates[$tic]->amount += $item->TaxAmount;

							if($this->debug && empty($useCache))
								var_dump($method);
						}
					}
				}
				unset($method);
			}
		}
		$this->errors = array();
	}

	protected function initSoap() {
		if($this->soap !== null)
			return true;

		if(!extension_loaded('soap') && !class_exists('SoapClient')){
			static $done = false;
			if(!$done){
				$app = JFactory::getApplication();
				$app->enqueueMessage('The HikaShop TaxCloud plugin requires the SOAP PHP extension to be installed and activated on your web server. Please contact your hosting company for help on installing/activating it as we detected that it is not or deactivate the TaxCloud plugin.');
				$done = true;
			}
			return false;
		}
		$wsdl = dirname(__FILE__).DIRECTORY_SEPARATOR.'taxcloud.wsdl';
		try {
			$this->soap = new SoapClient($wsdl, array('trace' => true, 'exceptions' => true));
		} catch(Exception $e) {
			var_dump($e);
			unset($this->soap);
			$this->soap = null;
			return false;
		}
		return true;
	}

	protected function taxName($rate) {
		$key = 'TAXCLOUD_TAX';
		if(JText::_($key) == $key)
			$key = 'Tax (%s)';
		$rate = round($rate * 100, 2) . '%';
		$ret = JText::sprintf($key, $rate);
		return $ret;
	}

	protected function loadOptions() {
		if(!empty($this->plugin_options)){
			if(empty($this->plugin_options['api_id']) || empty($this->plugin_options['api_key'])){
				return false;
			}
			return true;
		}

		$this->plugin_options = array(
			'api_id' => '',
			'api_key' => '',
			'usps_id' => '',
			'default_tic' => '0',
			'shipping_tic' => '0',
			'origin_address1' => '',
			'origin_address2' => '',
			'origin_city' => '',
			'origin_state' => '',
			'origin_zip5' => '',
			'origin_zip4' => ''
		);

		if(!isset($this->params)) {
			$pluginsClass = hikashop_get('class.plugins');
			$plugin = $pluginsClass->getByName('hikashop', 'taxcloud');

			foreach($this->plugin_options as $key => &$value) {
				if(!empty($plugin->params[$key])) $value = $plugin->params[$key];
			}
			unset($value);
		} else {
			foreach($this->plugin_options as $key => &$value) {
				$value = $this->params->get($key, $value);
			}
			unset($value);
		}
		if(empty($this->plugin_options['api_id']) || empty($this->plugin_options['api_key'])){
			$app = JFactory::getApplication();
			if(hikashop_isClient('administrator')){
				$app->enqueueMessage('Please configure your TaxCloud plugin via the Joomla plugins manager');
			}
			return false;
		}
		return true;
	}

	protected function verifyAddress() {
		if(!hikashop_loadUser(false))
			return false;
		$app = JFactory::getApplication();

		if(!$this->loadOptions()){
			return false;
		}

		$address = $this->loadAddress();
		if(empty($address)) return false;

		$address_hash = md5(serialize($address));

		$taxcloud_checkaddress = $app->getUserState(HIKASHOP_COMPONENT.'.taxcloud.address_hash', '');
		if($taxcloud_checkaddress == $address_hash) {
			return (int)$app->getUserState(HIKASHOP_COMPONENT.'.taxcloud.address_result', 0);
		}

		$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.address_hash', $address_hash);
		$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.address_result', 0);
		$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.full_address', null);

		if(!$this->initSoap())
			return false;

		if($address->address_country->zone_code_3 != 'USA') {
			$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.address_result', 2);
			return 2;
		}


		$parameters = array(
			'uspsUserID' => $this->plugin_options['usps_id'],
			'address1' => $address->address_street,
			'address2' => $address->address_street2,
			'city' => $address->address_city,
			'state' => $address->address_state->zone_code_3,
			'zip5' => $address->address_post_code,
			'zip4' => ''
		);
		try {
			$ret = $this->soap->__soapCall('verifyAddress', array($parameters)); //, array('uri' => 'http://taxcloud.net','soapaction' => ''));
		} catch(Exception $e) {
			$ret = false;
		}

		if(!empty($ret) && !empty($ret->VerifyAddressResult)) {
			$errNumber = $ret->VerifyAddressResult->ErrNumber;
			if($errNumber === '0') {
				$usps_address = array(
					'Address1' => $ret->VerifyAddressResult->Address1,
					'Address2' => @$ret->VerifyAddressResult->Address2,
					'City' => $ret->VerifyAddressResult->City,
					'State' => $ret->VerifyAddressResult->State,
					'Zip5' => $ret->VerifyAddressResult->Zip5,
					'Zip4' => $ret->VerifyAddressResult->Zip4
				);

				$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.full_address', $usps_address);
				$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.address_result', 1);
				return 1;
			} else {
				$option = hikaInput::get()->getCmd('option', '');
				$ctrl = hikaInput::get()->getCmd('ctrl', '');
				if($option == 'com_hikashop' && $ctrl == 'checkout') {
					$app->enqueueMessage(JText::_('WRONG_SHIPPING_ADDRESS'), 'error');
				}
			}
		}
		return 0;
	}

	protected function loadAddress(){
		$app = JFactory::getApplication();

		$shipping_method = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_method');
		if(empty($shipping_method))
			$shipping_method = array();
		if(!is_array($shipping_method))
			$shipping_method = array($shipping_method);

		$currentShipping = array();
		if(count($shipping_method)==1){
			foreach($shipping_method as $method){
				$method = explode('@',$method);
				$method = $method[0];
				$currentShipping[] = hikashop_import('hikashopshipping', $method);
			}
		}
		$override = false;
		foreach($currentShipping as $selectedMethod){
			if(!empty($selectedMethod) && method_exists($selectedMethod, 'getShippingAddress')) {
				$override = $selectedMethod->getShippingAddress();
			}
		}
		if($override){
			if(@$this->plugin_options['use_origin_address_when_override']){
				$address = new stdClass();
				$address->address_street = $this->plugin_options['origin_address1'];
				$address->address_street2 = $this->plugin_options['origin_address2'];
				$address->address_city = $this->plugin_options['origin_city'];
				$address->address_state = new stdClass();
				$address->address_state->zone_code_3 = $this->plugin_options['origin_state'];
				$address->address_post_code = $this->plugin_options['origin_zip5'].$this->plugin_options['origin_zip4'];
				return $address;
			}
			return false;
		}

		$shipping_address = (int)$app->getUserState(HIKASHOP_COMPONENT.'.shipping_address', 0);
		if(empty($shipping_address))
			$shipping_address = (int)$app->getUserState(HIKASHOP_COMPONENT.'.billing_address', 0);

		if(empty($shipping_address))
			return false;

		$addressClass = hikashop_get('class.address');
		$address = $addressClass->get($shipping_address);
		if(empty($address)) {
			return false;
		}

		$array = array(&$address);
		$addressClass->loadZone($array,'object');

		if(empty($address->address_country)) {
			$address->address_country = new stdClass();
			$address->address_country->zone_code_3 = 'USA';
		}

		return $address;
	}

	protected function getCode(&$product, &$cart){
		if(!empty($product->product_taxability_code) && $product->product_taxability_code!= '-1')
			return (int)$product->product_taxability_code;

		if(!empty($cart->full_products)) {
			$fp =& $cart->full_products;
			if(isset($fp[$product->cart_product_id]) && !empty($fp[$product->cart_product_id]->product_taxability_code) && $fp[$product->cart_product_id]->product_taxability_code != '-1')
				return (int)$fp[$product->cart_product_id]->product_taxability_code;
		}

		$p =& $cart->products;
		if(!empty($product->cart_product_parent_id) && isset($p[$product->cart_product_parent_id])) {
			if(!empty($p[$product->cart_product_parent_id]->product_taxability_code) && $p[$product->cart_product_parent_id]->product_taxability_code != '-1')
				return (int)$p[$product->cart_product_parent_id]->product_taxability_code;
			if(isset($fp) && isset($fp[$p[$product->cart_product_parent_id]->cart_product_id]) && !empty($fp[$p[$product->cart_product_parent_id]->cart_product_id]->product_taxability_code) && $fp[$p[$product->cart_product_parent_id]->cart_product_id]->product_taxability_code != '-1')
				return (int)$fp[$p[$product->cart_product_parent_id]->cart_product_id]->product_taxability_code;
		}

		if(isset($product->product_taxability_code) || empty($product->product_id))
			return;

		$productClass = hikashop_get('class.product');
		$data = $productClass->get($product->product_id);

		if(!empty($data->product_taxability_code) && $data->product_taxability_code!= '-1')
			return $data->product_taxability_code;

		if(!empty($data->product_parent_id)) {
			$data = $productClass->get($product->product_parent_id);
			if(!empty($data->product_taxability_code) && $data->product_taxability_code!= '-1')
				return $data->product_taxability_code;
		}

		return false;
	}

	protected function lookup(&$cart) {
		if(!hikashop_loadUser(false))
			return false;

		if(!$this->initSoap())
			return false;

		if(!$this->loadOptions()){
			return false;
		}

		$address = $this->loadAddress();
		if(empty($address)) return false;

		if($address->address_country->zone_code_3 != 'USA')
			return true;

		$app = JFactory::getApplication();

		$user_id = hikashop_loadUser(false);
		if(empty($user_id)){
			return false;
		}

		$usps_address = $app->getUserState(HIKASHOP_COMPONENT.'.taxcloud.full_address', null);

		if(empty($usps_address)){
			$usps_address = array(
				'Address1' => $address->address_street,
				'Address2' => $address->address_street2,
				'City' => $address->address_city,
				'State' => $address->address_state->zone_code_3,
				'Zip5' => $address->address_post_code,
				'Zip4' => ''
			);
		}


		$cart_items = array();
		$tics = array();
		$i = 0;
		foreach($cart->products as $product) {
			if(empty($product->cart_product_quantity))
				continue;
			$i++;
			$tic = $this->getCode($product, $cart);
			if(!$tic)
				$tic = (int)$this->plugin_options['default_tic'];

			if(!isset($tics[$tic])) {
				$cart_items[] = array(
					'Index' => -$i,
					'ItemID' => 'tic_rate_'.$tic,
					'TIC' => $tic,
					'Price' => 1,
					'Qty' => 1
				);
				$tics[$tic] = $i;
			}
		}
		$i=0;
		foreach($cart->products as $k => $product) {
			if(empty($product->cart_product_quantity))
				continue;
			$i++;
			$tic = $this->getCode($product, $cart);
			if(!$tic)
				$tic = (int)$this->plugin_options['default_tic'];
			$cart->products[$k]->taxcloud_id = $i;

			if(isset($product->prices[0]->unit_price->price_value))
				$price = $product->prices[0]->unit_price->price_value;
			else if (isset($product->prices[0]->price_value))
				$price = $product->prices[0]->price_value;
			else
				$price = 0;

			if($price<=0.0)
				continue;

			$cart_items[] = array(
				'Index' => $i,
				'ItemID' => $product->product_code,
				'TIC' => $tic,
				'Price' => $price,
				'Qty' => $product->cart_product_quantity
			);
		}

		$parameters = array(
			'apiLoginID' => $this->plugin_options['api_id'],
			'apiKey' => $this->plugin_options['api_key'],
			'customerID' => $user_id,
			'cartID' => $cart->cart_id,
			'cartItems' => $cart_items,
			'origin' => array(
				'Address1' => $this->plugin_options['origin_address1'],
				'Address2' => $this->plugin_options['origin_address2'],
				'City' => $this->plugin_options['origin_city'],
				'State' => $this->plugin_options['origin_state'],
				'Zip5' => $this->plugin_options['origin_zip5'],
				'Zip4' => $this->plugin_options['origin_zip4']
			),
			'destination' => $usps_address,
			'deliveredBySeller' => false,
			'exemptCert' => null
		);


		static $soapCache = array();

		$hash = md5(serialize($parameters));

		$session_hash = $app->getUserState(HIKASHOP_COMPONENT.'.taxcloud.cache_hash', '');
		if($hash == $session_hash) {
			$ret = $app->getUserState(HIKASHOP_COMPONENT.'.taxcloud.cache', '');
			if(!empty($ret) && !empty($ret->ResponseType)) {
				$useCache = true;
				if(!isset($soapCache[$hash]))
					$soapCache[$hash] = $ret;
			} else {
				unset($ret);
			}
		} else {
			$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.cache_hash', '');
			$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.cache', null);
		}

		if(!isset($soapCache[$hash])) {
			try {
				$soapRet = $this->soap->__soapCall('Lookup', array($parameters)); //, array('uri' => 'http://taxcloud.net','soapaction' => ''));
				$soapCache[$hash] = $soapRet->LookupResult;
				$ret = $soapRet->LookupResult;
			} catch(Exception $e) {
				$ret = false;
			}
			if($ret !== false && @$ret->ResponseType == 'OK') {
				$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.cache_hash', $hash);
				$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.cache', $ret);
			}

			if($this->debug) {
				var_dump($ret->ResponseType);
				if($ret->ResponseType == 'OK')
					var_dump($ret->CartItemsResponse->CartItemResponse);
				else
					var_dump($ret);
			}
		} else {
			$ret = $soapCache[$hash];
			$useCache = true;
		}

		$rates = array();

		if(!empty($ret) && $ret->ResponseType == 'OK') {
			foreach($cart->products as &$product) {
				if(empty($product->cart_product_quantity))
					continue;
				if(isset($product->prices[0])){
					$product->prices[0]->price_value_with_tax = $product->prices[0]->price_value;
					$product->prices[0]->taxes = array();
				}
			}
			unset($product);
			if(!is_array($ret->CartItemsResponse->CartItemResponse))
				$ret->CartItemsResponse->CartItemResponse = array($ret->CartItemsResponse->CartItemResponse);

			foreach($ret->CartItemsResponse->CartItemResponse as $item) {
				foreach($cart->products as &$product) {
					if(empty($product->cart_product_quantity))
						continue;
					if($item->CartItemIndex <= 0) {
						$tic = $this->getCode($product, $cart);
						if(!$tic)
							$tic = (int)$this->plugin_options['default_tic'];
					if(!isset($rates[$tic]) ) {
						$r = new stdClass();
						$r->rate = $item->TaxAmount;
						$r->amount = 0.0;
						$rates[	$tic ] = $r;
					} else {
						$rates[$tic]->rate = $item->TaxAmount;
					}
					continue;
				}

					if((int)$product->taxcloud_id == $item->CartItemIndex) {
						if(!isset($product->prices[0]))
							continue;
						$tic = $this->getCode($product, $cart);
						if(!$tic)
							$tic = (int)$this->plugin_options['default_tic'];

						$price_value = $product->prices[0]->price_value;
						$new_price = $price_value + $item->TaxAmount;

						$t = new stdClass();
						$t->tax_rate = round(($new_price / $price_value) - 1, 4);
						$t->tax_amount = $item->TaxAmount;
						$t->tax_namekey = $this->taxName($t->tax_rate);

						$product->prices[0]->price_value_with_tax = $new_price;
						$product->prices[0]->taxes[$t->tax_namekey] = $t;

						if(!isset($rates[$tic])) {
							$rates[$tic] = new stdClass();
							$rates[$tic]->amount = 0.0;
						}
						$rates[$tic]->amount += $item->TaxAmount;

						if($this->debug && empty($useCache))
							var_dump($product->prices[0]);
						if(!empty($product->prices[0]->unit_price)) {
							$tu = hikashop_copy($t);
							$tu->tax_amount = $tu->tax_amount/$product->cart_product_quantity;
							$product->prices[0]->unit_price->taxes[$tu->tax_namekey] = $tu;
							$product->prices[0]->unit_price->price_value_with_tax = $product->prices[0]->unit_price->price_value + $tu->tax_amount;
						}
					}
				}
				unset($product);
			}

			$cart->total->prices[0]->taxes = array();
			foreach($rates as $k => $rate) {
				$key = $this->taxName($rate->rate);
				if(!isset($cart->total->prices[0]->taxes[$key])) {
					$t = new stdClass();
					$t->tax_amount = 0.0;
					$t->tax_rate = $rate->rate;
					$t->tax_namekey = $this->taxName($t->tax_rate); // JText::sprintf('TAXCLOUD_TAX', $t->tax_rate);
					$cart->total->prices[0]->taxes[$key] = $t;
				}
				$cart->total->prices[0]->taxes[$key]->tax_amount += $rate->amount;
			}

			$total_taxes = 0;
			foreach($cart->total->prices[0]->taxes as &$tax) {
				$total_taxes += $tax->tax_amount;
			}
			unset($tax);
			$cart->total->prices[0]->price_value_with_tax = $cart->total->prices[0]->price_value + $total_taxes;

			if($this->debug && empty($useCache))
				var_dump($cart->total->prices[0]);
		} else {
			if(empty($ret->Messages->ResponseMessage->Message)){
				$ret->Messages->ResponseMessage->Message = "Unknown error. The tax calculation service is probably down.";
			}
			$this->display_errors(@$ret->Messages->ResponseMessage->Message);
		}

	}

	public function display_errors($error){
		$app = JFactory::getApplication();
		if(!isset($this->errors[$error])){
			$app->enqueueMessage('TaxCloud error : '.@$error, 'error');
			$this->errors[$error]=1;
		}
	}


	public function check_address() {
		JToolBarHelper::title('TaxCloud' , 'plugin.png' );

		if(!$this->init())
			return;

		if(!$this->initSoap())
			return false;

		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->getByName('hikashop', 'taxcloud');

		$url = JRoute::_('index.php?option=com_plugins&view=plugin&layout=edit&extension_id='.$plugin->extension_id);

		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton('Link', 'cancel', JText::_('HIKA_CANCEL'), $url);

		if(!$this->loadOptions()){
			return false;
		}
		$parameters = array(
			'uspsUserID' => $this->plugin_options['usps_id'],
			'address1' => $this->plugin_options['origin_address1'],
			'address2' => $this->plugin_options['origin_address2'],
			'city' => $this->plugin_options['origin_city'],
			'state' => $this->plugin_options['origin_state'],
			'zip5' => $this->plugin_options['origin_zip5'],
			'zip4' => $this->plugin_options['origin_zip4']
		);
		try{
			$ret = $this->soap->__soapCall('verifyAddress', array($parameters));
			if(!empty($ret) && !empty($ret->VerifyAddressResult)) {
				$errNumber = $ret->VerifyAddressResult->ErrNumber;
				if($errNumber === '0') {
					echo '<fieldset><h1>Check Address</h1><table width="100%" style="width:100%"><thead><tr>'.
						'<th>Name</th>'.
						'<th>Original value</th>'.
						'<th>Processed value</th>'.
						'</thead><tbody>'.
						'<tr><td>Address 1</td><td>'.$this->plugin_options['origin_address1'].'</td><td>'.@$ret->VerifyAddressResult->Address1.'</td></tr>'.
						'<tr><td>Address 2</td><td>'.$this->plugin_options['origin_address2'].'</td><td>'.@$ret->VerifyAddressResult->Address2.'</td></tr>'.
						'<tr><td>City</td><td>'.$this->plugin_options['origin_city'].'</td><td>'.@$ret->VerifyAddressResult->City.'</td></tr>'.
						'<tr><td>State</td><td>'.$this->plugin_options['origin_state'].'</td><td>'.@$ret->VerifyAddressResult->State.'</td></tr>'.
						'<tr><td>Zip5</td><td>'.$this->plugin_options['origin_zip5'].'</td><td>'.@$ret->VerifyAddressResult->Zip5.'</td></tr>'.
						'<tr><td>Zip4</td><td>'.$this->plugin_options['origin_zip4'].'</td><td>'.@$ret->VerifyAddressResult->Zip4.'</td></tr>'.
						'</tbody></table></fieldset>';
				} else {
					echo '<fieldset><h1>Check Address Error</h1><p>'.$ret->VerifyAddressResult->ErrDescription.'</p></fieldset>';
				}
			} else {
				echo '<fieldset><h1>Check Address Error</h1><p>';
				var_dump($ret);
				echo '</p></fieldset>';
			}
		}catch(Exception $e){
			echo '<fieldset><h1>Check Address Error</h1><p><pre>';
			var_dump($e);
			echo '</pre></p></fieldset>';
		}
	}

	public function browse_tic() {
		JToolBarHelper::title('TaxCloud' , 'plugin.png' );

		if(!$this->init())
			return;

		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->getByName('hikashop', 'taxcloud');

		$url = JRoute::_('index.php?option=com_plugins&view=plugin&layout=edit&extension_id='.$plugin->extension_id);

		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton('Link', 'cancel', JText::_('HIKA_CANCEL'), $url);

		echo '<fieldset><h1>Browse TIC</h1><div><input type="text" value="" id="taxability_code"/></div></fieldset>';
	}
}
