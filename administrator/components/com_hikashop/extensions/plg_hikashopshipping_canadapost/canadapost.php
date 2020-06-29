<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashopshippingCANADAPOST extends hikashopShippingPlugin {
	var $multiple = true;
	var $name = 'canadapost';
	var $doc_form = 'canadapost';

	var $canadapost_methods = array(
		array('key' => 1, 'name' => 'Priority Courier', 'countries' => 'CANADA'),
		array('key' => 2, 'name' => 'Xpresspost', 'countries' => 'CANADA'),
		array('key' => 3, 'name' => 'Regular', 'countries' => 'CANADA'),
		array('key' => 4, 'name' => 'Priority Worldwide USA', 'countries' => 'USA'),
		array('key' => 5, 'name' => 'Xpresspost USA', 'countries' => 'USA'),
		array('key' => 6, 'name' => 'Small Packets Air', 'countries' => 'USA'),
		array('key' => 7, 'name' => 'Small Packets Surface', 'countries' => 'USA'),
		array('key' => 8, 'name' => 'Priority Worldwide INTL', 'countries' => 'ALL'),
		array('key' => 9, 'name' => 'XPressPost International', 'countries' => 'ALL'),
		array('key' => 10, 'name' => 'Small Packets Air', 'countries' => 'ALL'),
		array('key' => 11, 'name' => 'Parcel Surface', 'countries' => 'ALL'),
		array('key' => 12, 'name' => 'Small Packets Surface', 'countries' => 'ALL'),
		array('key' => 13, 'name' => 'Expedited', 'countries' => 'ALL'),
	);
	var $convertUnit = array(
		'kg' => 'KGS',
		'lb' => 'LBS',
		'cm' => 'CM',
		'in' => 'IN',
		'kg2' => 'kg',
		'lb2' => 'lb',
		'cm2' => 'cm',
		'in2' => 'in'
	);
	var $limits = array(
		'CA' => array(
			'default' => array(
				'x' => 2,
				'y' => 2,
				'z' => 2,
				'length_girth' => 3,
				'w' => 30
			)
		),
		'US' => array(
			'Priority Worldwide USA' => 'default',
			'Expedited' => array(
				'x' => 2,
				'y' => 2,
				'z' => 2,
				'length_girth' => 2.74,
				'w' => 30
			),
			'Xpresspost USA' => array(
				'x' => 1.5,
				'y' => 1.5,
				'z' => 1.5,
				'length_girth' => 2.74,
				'w' => 30
			),
			'Small Packets Air' => array(
				'x' => 0.6,
				'y' => 0.6,
				'z' => 0.6,
				'length_width_height' => 0.9,
				'w' => 1
			),
			'default' => array(
				'x' => 2,
				'y' => 2,
				'z' => 2,
				'length_girth' => 3,
				'w' => 30
			)
		),
		'default' => array(
			'default' => array(
				'x' => 2,
				'y' => 2,
				'z' => 2,
				'length_girth' => 3,
				'w' => 30
			),
			'medium' => array(
				'x' => 1.5,
				'y' => 1.5,
				'z' => 1.5,
				'length_girth' => 3,
				'w' => 30
			),
			'small' => array(
				'x' => 0.6,
				'y' => 0.6,
				'z' => 0.6,
				'length_width_height' => 0.9,
				'w' => 2
			),
			'Priority Worldwide INTL' => 'default',
			'Expedited' => 'default',
			'XPressPost International' => 'medium',
			'Parcel Surface' => 'medium',
			'Small Packets Air' => 'small',
			'Small Packets Surface' => 'small',
		),
	);
	var $nbpackage = 0;

	function shippingMethods(&$main) {
		$methods = array();
		if(!empty($main->shipping_params->methodsList)) {
			$main->shipping_params->methods = hikashop_unserialize($main->shipping_params->methodsList);
		}
		if(!empty($main->shipping_params->methods)) {
			foreach($main->shipping_params->methods as $key => $value) {
				$selected = null;
				foreach($this->canadapost_methods as $canadapost) {
					if($canadapost['name'] == $key)
						$selected = $canadapost;
				}
				if($selected)
					$methods[$main->shipping_id . '-' . $selected['key']] = $selected['name'];
			}
		}
		return $methods;
	}

	function onShippingDisplay(&$order, &$dbrates, &$usable_rates, &$messages) {
		if(empty($order->shipping_address))
			return true;
		$local_usable_rates = array();
		$local_messages = array();
		$ret = parent::onShippingDisplay($order, $dbrates, $local_usable_rates, $local_messages);
		if($ret === false)
			return false;

		$currentShippingZone = null;
		$currentCurrencyId = null;
		$found = true;
		$usableWarehouses = array();
		$zoneClass = hikashop_get('class.zone');
		$currencyClass = hikashop_get('class.currency');
		$zones = $zoneClass->getOrderZones($order);
		if(!function_exists('curl_init')) {
			$app = JFactory::getApplication();
			$app->enqueueMessage('The CANADAPOST shipping plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.', 'error');
			return false;
		}

		if(empty($local_usable_rates))
			return;

		if($order->weight <= 0 || $order->volume <= 0) {
			return true;
		}

		$this->shipping_currency_id = hikashop_getCurrency();
		$db = JFactory::getDBO();
		$query = 'SELECT currency_code FROM ' . hikashop_table('currency') . ' WHERE currency_id IN (' . $this->shipping_currency_id . ')';
		$db->setQuery($query);
		$this->shipping_currency_code = $db->loadResult();
		$cartClass = hikashop_get('class.cart');
		$null = null;
		$cartClass->loadAddress($null, $order->shipping_address->address_id, 'object', 'shipping');

		foreach($local_usable_rates as $k => $rate) {
			if(!empty($rate->shipping_params->warehousesList)) {
				$rate->shipping_params->warehouses = hikashop_unserialize($rate->shipping_params->warehousesList);
			} else {
				$messages['no_warehouse_configured'] = 'No warehouse configured in the CANADA POST shipping plugin options';
				continue;
			}

			foreach($rate->shipping_params->warehouses as $warehouse) {
				if((empty($warehouse->zone) && $warehouse->zip != '-') || (!empty($warehouse->zone) && in_array($warehouse->zone, $zones) && $warehouse->zip != '-')) {
					$usableWarehouses[] = $warehouse;
				}
			}

			if(empty($usableWarehouses)) {
				$messages['no_warehouse_configured'] = 'No available warehouse found for your location';
				continue;
			}

			if(!empty($rate->shipping_params->methodsList)) {
				$rate->shipping_params->methods = hikashop_unserialize($rate->shipping_params->methodsList);
			} else {
				$messages['no_shipping_methods_configured'] = 'No shipping methods configured in the CANADA POST shipping plugin options';
				continue;
			}

			$data = null;
			if(empty($order->shipping_address)) {
				$messages['no_shipping_address_found'] = 'No shipping address entered';
				continue;
			}

			$receivedMethods = $this->_getBestMethods($rate, $order, $usableWarehouses, $null);

			if(empty($receivedMethods)) {
				$messages['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
				continue;
			}

			foreach($receivedMethods as $method) {
				$r = clone($rate);
				$r->shipping_price += $method['value'];
				$selected_method = '';
				$name = '';
				$description = '';
				foreach($this->canadapost_methods as $canadapost_method) {
					if($canadapost_method['name'] == $method['name']) {
						$name = $canadapost_method['name'];
						$selected_method = $canadapost_method['key'];
					}
				}
				$typeKey = str_replace(' ','_', strtoupper($name));
				$shipping_name = JText::_($typeKey);

				if($shipping_name != $typeKey)
					$name = $shipping_name;
				else
					$name = $name;

				$shipping_description = JText::_($typeKey.'_DESCRIPTION');
				if($shipping_description != $typeKey.'_DESCRIPTION')
					$description .= $shipping_description;

				$r->shipping_name = $name;
				if($description != '')
					$r->shipping_description .= $description;

				if(!empty($selected_method))
					$r->shipping_id .= '-' . $selected_method;

				if($method['deliveryDate'] != 'www.canadapost.ca') {
					if(is_numeric($method['deliveryDate'])) {
						$timestamp = strtotime($method['deliveryDate']);
						$time = parent::displayDelaySECtoDAY($timestamp - strtotime('now'), 2);
						$r->shipping_description .= '<br/>Estimated delivery date:  ' . $time;
					} else {
						$time = $method['deliveryDate'];
						$r->shipping_description .= '<br/>' .JText::sprintf('SHIPPING_DELIVERY_DELAY',$time);
					}
				} else {
					$r->shipping_description .= ' ' . JText::_('NO_ESTIMATED_TIME_AFTER_SEND');
				}

				if($rate->shipping_params->group_package == 1 && $this->nbpackage > 1)
					$r->shipping_description .= '<br/>' . JText::sprintf('X_PACKAGES', $this->nbpackage);

				if(isset($r->shipping_price_orig) || isset($r->shipping_currency_id_orig)){
					if($r->shipping_currency_id_orig == $r->shipping_currency_id)
						$r->shipping_price_orig = $r->shipping_price;
					else
						$r->shipping_price_orig = $currencyClass->convertUniquePrice($r->shipping_price, $r->shipping_currency_id, $r->shipping_currency_id_orig);
				}

				$usable_rates[$r->shipping_id] = $r;
			}
		}
	}

	function getShippingDefaultValues(&$element) {
		$element->shipping_name = 'CANADA POST';
		$element->shipping_description = '';
		$element->group_package = 0;
		$element->shipping_images = 'canadapost';
		$element->shipping_params->post_code = '';
		$element->shipping_currency_id = $this->main_currency;
	}

	function onShippingConfiguration(&$element) {
		$app = JFactory::getApplication();
		$config = hikashop_config();

		$this->main_currency = $config->get('main_currency', 1);

		$currencyClass = hikashop_get('class.currency');

		$main_currency = $currencyClass->get($this->main_currency);
		$this->currencyCode = $main_currency->currency_code;
		$this->currencySymbol = $main_currency->currency_symbol;

		$this->canadapost = hikaInput::get()->getCmd('name', 'canadapost');

		$this->categoryType = hikashop_get('type.categorysub');
		$this->categoryType->type = 'tax';
		$this->categoryType->field = 'category_id';

		parent::onShippingConfiguration($element);

		$elements = array($element);
		$key = key($elements);

		if(!empty($elements[$key]->shipping_params->warehousesList)) {
			$elements[$key]->shipping_params->warehouse = hikashop_unserialize($elements[$key]->shipping_params->warehousesList);
		}
		if(!empty($elements[$key]->shipping_params->methodsList)) {
			$elements[$key]->shipping_params->methods = hikashop_unserialize($elements[$key]->shipping_params->methodsList);
		}
		if(empty($elements[$key]->shipping_params->merchant_ID)){
			$app->enqueueMessage(JText::sprintf('ENTER_INFO', 'Canada POST', JText::_('ATOS_MERCHANT_ID')));
		}
		if(empty($elements[$key]->shipping_params->warehouse[0]->zip)){
			$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD', JText::_('POST_CODE')), 'notice');
		}

		$js = '
function deleteRow(divName,inputName,rowName){
	var d = document.getElementById(divName);
	var olddiv = document.getElementById(inputName);
	if(d && olddiv) {
		d.removeChild(olddiv);
		document.getElementById(rowName).style.display = "none";
	}
	return false;
}
function deleteZone(zoneName) {
	var d = document.getElementById(zoneName);
	if(d)  d.innerHTML = "";
	return false;
}
function checkAllBox(id, type) {
	var toCheck = document.getElementById(id).getElementsByTagName("input");
	for(i = 0 ; i < toCheck.length ; i++) {
		if(toCheck[i].type != "checkbox")
			continue;
		toCheck[i].checked = (type == "check");
	}
}
';

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( "<!--\n".$js."\n//-->\n" );
	}

	function onShippingConfigurationSave(&$element) {
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$cats = array();
		$methods = array();

		$warehouses = hikaInput::get()->get('warehouse', array(), 'array');
		$formData = hikaInput::get()->get('data', array(), 'array');

		if(isset($formData['shipping_methods'])) {
			foreach($formData['shipping_methods'] as $method) {
				foreach($this->canadapost_methods as $canadapostMethod) {
					if($canadapostMethod['name'] == $method['name']) {
						$methods[strip_tags($method['name'])] = '';
					}
				}
			}
		} else {
			$app->enqueueMessage(JText::sprintf('CHOOSE_SHIPPING_SERVICE'));
		}

		$element->shipping_params->methodsList = serialize($methods);

		if(!empty($warehouses)) {
			$zone_keys = array();
			foreach($warehouses as $id => $warehouse) {
				if(!empty($warehouse['zone']))
					$zone_keys[] = 'zone_namekey = ' . $db->Quote($warehouse['zone']);
			}
			if(!empty($zone_keys)) {
				$query = 'SELECT zone_namekey, zone_id, zone_name_english FROM ' . hikashop_table('zone') . ' WHERE (' . implode(') OR (', $zone_keys) . ')';
				$db->setQuery($query);
				$zones = $db->loadObjectList();
			}

			foreach($warehouses as $id => $warehouse) {
				$warehouse['zone_name'] = '';
				if(!empty($zones)) {
					foreach($zones as $zone) {
						if($zone->zone_namekey == $warehouse['zone'])
							$warehouse['zone_name'] = $zone->zone_id . ' ' . $zone->zone_name_english;
					}
				}

				if(isset($warehouse['zip']) && $warehouse['zip'] != '-' && !empty($warehouse['zip'])) {
					$obj = new stdClass();
					$obj->name = strip_tags($warehouse['name']);
					$obj->zip = strip_tags($warehouse['zip']);
					$obj->zone = @strip_tags($warehouse['zone']);
					$obj->zone_name = $warehouse['zone_name'];
					$obj->units = strip_tags($warehouse['units']);
					$cats[] = $obj;
				}
			}
			$element->shipping_params->warehousesList = serialize($cats);
		}

		if(empty($cats)) {
			$obj = new stdClass();
			$obj->name = '';
			$obj->zip = '';
			$obj->zone = '';
			$void = array($obj);
			$element->shipping_params->warehousesList = serialize($void);
		}

		return true;
	}

	function _getBestMethods(&$rate, &$order, &$usableWarehouses, $null) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$usableMethods = array();
		$query = 'SELECT zone_id, zone_code_2 FROM ' . hikashop_table('zone') . ' WHERE zone_id = 38';
		$db->setQuery($query);
		$warehouses_namekey = $db->loadObjectList();

		if(empty($usableWarehouses))
			return false;

		$method_available = array_keys($rate->shipping_params->methods);
		$bestPrice = 99999999;
		$bestWarehouse = null;

		foreach($usableWarehouses as $id => &$warehouse) {
			foreach($warehouses_namekey as $zone) {
				if($zone->zone_id == 38) {
					$warehouse->country_ID = $zone->zone_code_2;
				}
			}

			$warehouse->methods = $this->_getShippingMethods($rate, $order, $warehouse, $null);

			if(empty($warehouse->methods))
				continue;

			foreach($warehouse->methods as $i => $method) {
				if(!in_array($method['name'], $method_available)) {
					unset($warehouse->methods[$i]);
					continue;
				}
				if($method['value'] < $bestPrice) {
					$bestPrice = $method['value'];
					$bestWarehouse = $id;
				}
			}
		}
		unset($warehouse);

		if($bestWarehouse === null) {
			$app->enqueueMessage('There is no warehouse usable for that location');
			return false;
		}
		return $usableWarehouses[$bestWarehouse]->methods;
	}

	function _getShippingMethods(&$rate, &$order, &$warehouse, $null) {
		$data = array(
			'merchant_ID' => $rate->shipping_params->merchant_ID,
			'turnaround_time' => $rate->shipping_params->turnaround_time,
			'destCity' => $null->shipping_address->address_city,
			'destState' => $null->shipping_address->address_state,
			'destZip' => $null->shipping_address->address_post_code,
			'destCountry' => $null->shipping_address->address_country->zone_code_2,
			'units' => $warehouse->units,
			'zip' => $warehouse->zip,
			'destType' => '',
			'XMLpackage' => '<?xml version="1.0" ?>'."\r\n".
				'<eparcel>'."\r\n".
				'<language>EN</language>'."\r\n".
				'<ratesAndServicesRequest>'."\r\n".
				'<merchantCPCID>' . $rate->shipping_params->merchant_ID . '</merchantCPCID>'."\r\n".
				'<fromPostalCode>' . $warehouse->zip . '</fromPostalCode>'."\r\n".
				(!empty($rate->shipping_params->turnaround_time) ? ('<turnAroundTime>' . $rate->shipping_params->turnaround_time . '</turnAroundTime>' . "\r\n") : '') .
				(!empty($order->total->prices[0]->price_value) ? ('<itemsPrice>' . $order->total->prices[0]->price_value . '</itemsPrice>' . "\r\n") : '') .
				'<lineItems>'."\r\n",
			'weight' => 0,
			'height' => 0,
			'length' => 0,
			'width' => 0,
			'price' => 0,
			'quantity' => 0,
			'name' => '',
		);

		$totalPrice = 0;

		if(isset($this->limits[ $null->shipping_address->address_country->zone_code_2 ]))
			$zone_limit = $this->limits[ $null->shipping_address->address_country->zone_code_2 ];
		else
			$zone_limit = $this->limits['default'];

		$limit = null;
		foreach($zone_limit as $key => $value) {
			if($limit === null && isset($rate->shipping_params->methods[$key]))
				$limit = $value;
		}

		if(is_string($limit)) {
			if(strpos($limit, ':') === false) {
				$limit = $zone_limit[ $limit ];
			} else {
				list($zone, $key) = explode(':', $limit, 2);
				$limit = $this->limits[$zone][$key];
			}
		}

		if($limit === null)
			$limit = $zone_limit['default'];

		if(!$rate->shipping_params->group_package || $rate->shipping_params->group_package == 0)
			$limit['unit'] = 1;

		$packages = $this->getOrderPackage($order, array('weight_unit' => 'kg', 'volume_unit' => 'm', 'limit' => $limit, 'required_dimensions' => array('w','x','y','z')));

		if(empty($packages))
			return true;

		$this->package_added = 0;
		$this->nbpackage = 0;

		if(!empty($rate->shipping_params->group_package) && $rate->shipping_params->group_package > 0) {
			$data['name'] = 'grouped package';
		}

		if(isset($packages['w']) && isset($packages['x']) && isset($packages['y']) && isset($packages['z'])) {
			$this->nbpackage++;
			$data['weight'] = $packages['w'];
			$data['height'] = $packages['z'];
			$data['length'] = $packages['y'];
			$data['width'] = $packages['x'];
			$data['quantity'] = 1;
			$data['XMLpackage'] .= $this->_createPackage($data, $rate, $order);
		} else {
			foreach($packages as $package){
				if(!isset($package['w']) || $package['w'] == 0 || !isset($package['x']) || $package['x'] == 0 || !isset($package['y']) || $package['y'] == 0 || !isset($package['z']) || $package['z'] == 0)
					continue;
				$this->nbpackage++;
				$data['weight'] = $package['w'];
				$data['height'] = $package['z'];
				$data['length'] = $package['y'];
				$data['width'] = $package['x'];
				$data['quantity'] = 1;
				$data['XMLpackage'] .= $this->_createPackage($data, $rate, $order);
			}
		}
		$data['XMLpackage'] .= '</lineItems>' .
			'<city>' . $data['destCity'] . '</city>' .
			'<provOrState>' . $data['destState']->zone_name . '</provOrState>' .
			'<country>' . $data['destCountry'] . '</country>' .
			'<postalCode>' . $data['destZip'] . '</postalCode>' .
			'</ratesAndServicesRequest>'.
			'</eparcel>';
		$usableMethods = $this->_RequestMethods($data, $data['XMLpackage']);

		if(empty($usableMethods))
			return false;

		$db = JFactory::getDBO();
		$query = 'SELECT currency_id FROM '. hikashop_table('currency') .' WHERE currency_code = "CAD"';
		$db->setQuery($query);
		$currencyID = $db->loadResult();
		foreach($usableMethods as $i => $method) {
			$usableMethods[$i]['currency_id'] = (int)$currencyID;
		}

		$usableMethods = parent::_currencyConversion($usableMethods, $order);

		return $usableMethods;
	}

	function processPackageLimit($limit_key, $limit_value, $product, $qty, $package, $units) {
		switch($limit_key) {
			case 'length_width_height':
				$divide = $product['x'] + $product['y'] + $product['z'];
				if(!$divide || $divide > $limit_value)
					return false;
				$current_limit_value = max(0.0, $limit_value - (float)($package['z'] + $package['y'] + $package['x']));
				return (int)floor($current_limit_value / $divide);
				break;
			case 'length_girth':
				$max_qty = (($limit_value - $product['z']) / 2 - $product['y']) / $product['x'];
				if(!$max_qty || $max_qty < 1)
					return false;
				return (int)floor($max_qty);
				break;
		}
		return parent::processPackageLimit($limit_key, $limit_value , $product, $qty, $package, $units);
	}

	function _createPackage(&$data, &$rate, &$order) {

		if(!empty($rate->shipping_params->weight_approximation)) {
			$data['weight'] = $data['weight'] + ($data['weight'] * $rate->shipping_params->weight_approximation / 100);
		}
		if($data['weight'] < 1)
			$data['weight'] = 1;

		if(!empty($rate->shipping_params->dim_approximation)) {
			$data['height'] = $data['height'] + ($data['height'] * $rate->shipping_params->dim_approximation / 100);
			$data['length'] = $data['length'] + ($data['length'] * $rate->shipping_params->dim_approximation / 100);
			$data['width'] = $data['width'] + ($data['width'] * $rate->shipping_params->dim_approximation / 100);
		}
		if($data['name'] == '')
			$data['name'] = ' ';
		$xml = '<item>' .
			'<quantity>' . $data['quantity'] . '</quantity>' .
			'<weight>' . $data['weight'] . '</weight>' .
			'<length>' . $data['width'] . '</length>' .
			'<width>' . $data['length'] . '</width>' .
			'<height>' . $data['height'] . '</height>' .
			'<description>' . $data['name'] . '</description>';
		if ($rate->shipping_params->readyToShip)
			$xml .= '<readyToShip/>';
		$xml .= '</item>';
		return $xml;
	}

	function _RequestMethods($data, $xml) {
		$app = JFactory::getApplication();
		$session = curl_init("cybervente.postescanada.ca");
		curl_setopt($session, CURLOPT_HEADER, 1);
		curl_setopt($session, CURLOPT_POST, 1);
		curl_setopt($session, CURLOPT_PORT, 30000);
		curl_setopt($session, CURLOPT_TIMEOUT, 30);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($session, CURLOPT_POSTFIELDS, $xml);
		$result = curl_exec($session);
		$error = curl_errno($session);
		$error_message = curl_error($session);
		curl_close($session);

		if($error) {
			$app->enqueueMessage('An error occurred. The connection to the Canada Post server could not be established: ' . $error_message);
			return false;
		}
		$xml_data = strstr($result, '<?');
		$xml = simplexml_load_string($xml_data);
		if(isset($xml->ratesAndServicesResponse->statusCode) && $xml->ratesAndServicesResponse->statusCode != 1) {
			$app->enqueueMessage('Error while sending XML to CANADA POST. Error code: ' . $xml->ratesAndServicesResponse->statusCode . '. Message: ' . $xml->ratesAndServicesResponse->statusMessage . '', 'error');
			return false;
		}
		if(isset($xml->error->statusCode) && $xml->error->statusCode != 1) {
			$app->enqueueMessage('Error while sending XML to CANADA POST. Error code: ' . $xml->error->statusCode . '. Message: ' . $xml->error->statusMessage . '', 'error');
			return false;
		}

		$handling = 0.0;
		if(isset($xml->ratesAndServicesResponse->handling))
			$handling = hikashop_toFloat($xml->ratesAndServicesResponse->handling->__toString());

		$i = 1;
		$shipment = array();
		foreach($xml->ratesAndServicesResponse->product as $rate) {
			$shipment[$i++] = array(
				'value' => hikashop_toFloat($rate->rate->__toString()) + $handling,
				'name' => $rate->name->__toString(),
				'shippingDate' => $rate->shippingDate->__toString(),
				'deliveryDate' => $rate->deliveryDate->__toString(),
				'deliveryDayOfWeek' => $rate->deliveryDayOfWeek->__toString(),
				'nextDayAM' => $rate->nextDayAM->__toString(),
				'status_code' => $xml->ratesAndServicesResponse->statusCode->__toString(),
				'status_message' => $xml->ratesAndServicesResponse->statusMessage->__toString(),
				'currency_code' => 'CAD',
				'old_currency_code' => 'CAD',
			);
		}

		return $shipment;
	}
}
