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
class plgHikashopshippingFedEx extends hikashopShippingPlugin {
	var $multiple = true;
	var $name = 'fedex';
	var $doc_form = 'fedex';
	var $packages;    // array of packages
	var $packageCount;    // number of packages in this shipment
	var $fedex_methods = array(
		array('key'=>1,'code' => 'FEDEX_GROUND', 'name' => 'FedEx Ground', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172') , 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key'=>2,'code' => 'FEDEX_2_DAY', 'name' => 'FedEx 2 Day', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key'=>3,'code' => 'FEDEX_EXPRESS_SAVER', 'name' => 'FedEx Express Saver', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key'=>4,'code' => 'FIRST_OVERNIGHT', 'name' => 'FedEx First Overnight', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key'=>5,'code' => 'GROUND_HOME_DELIVERY', 'name' => 'FedEx Ground (Home Delivery)', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key'=>6,'code' => 'PRIORITY_OVERNIGHT', 'name' => 'FedEx Priority Overnight', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key'=>7,'code' => 'SMART_POST', 'name' => 'FedEx Smart Post', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key'=>8,'code' => 'STANDARD_OVERNIGHT', 'name' => 'FedEx Standard Overnight', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key'=>9,'code' => 'FEDEX_GROUND', 'name' => 'FedEx International Ground'),
		array('key'=>10,'code' => 'INTERNATIONAL_ECONOMY', 'name' => 'FedEx International Economy'),
		array('key'=>11,'code' => 'INTERNATIONAL_ECONOMY_DISTRIBUTION', 'name' => 'FedEx International Economy Distribution'),
		array('key'=>12,'code' => 'INTERNATIONAL_FIRST', 'name' => 'FedEx International First'),
		array('key'=>13,'code' => 'INTERNATIONAL_PRIORITY', 'name' => 'FedEx International Priority'),
		array('key'=>14,'code' => 'INTERNATIONAL_PRIORITY_DISTRIBUTION', 'name' => 'FedEx International Priority Distribution'),
		array('key'=>15,'code' => 'EUROPE_FIRST_INTERNATIONAL_PRIORITY', 'name' => 'FedEx Europe First')
	);
	var $convertUnit=array(
		'kg' => 'KGS',
		'lb' => 'LBS',
		'cm' => 'CM',
		'in' => 'IN',
		'kg2' => 'kg',
		'lb2' => 'lb',
		'cm2' => 'cm',
		'in2' => 'in',
	);
	public $nbpackage = 0;

	function shippingMethods(&$main){
		$methods = array();
		if(!empty($main->shipping_params->methodsList)) {
			$main->shipping_params->methods = hikashop_unserialize($main->shipping_params->methodsList);
		}
		if(!empty($main->shipping_params->methods)) {
			foreach($main->shipping_params->methods as $method){
				$selected = null;
				foreach($this->fedex_methods as $fedex) {
					if($fedex['code'] == $method) {
						$selected = $fedex;
						break;
					}
				}

				if($selected)
					$methods[$main->shipping_id . '-' . $selected['key']] = $selected['name'];
				if($selected['key'] == 1)
					$methods[$main->shipping_id . '-9'] = $this->fedex_methods[8]['name'];
			}
		}
		return $methods;
	}

	function onShippingDisplay(&$order,&$dbrates,&$usable_rates,&$messages){
		if(empty($order->shipping_address))
			return true;

		$local_usable_rates = array();
		$local_messages = array();
		$ret = parent::onShippingDisplay($order, $dbrates, $local_usable_rates, $local_messages);
		if($ret === false)
			return false;
		$currentShippingZone = null;
		$currentCurrencyId = null;
		$currencyClass = hikashop_get('class.currency');
		foreach($local_usable_rates as $k => $rate){
			if(empty($rate->shipping_params->methodsList)) {
				$messages['no_shipping_methods_configured'] = 'No shipping methods configured in the FedEx shipping plugin options';
				continue;
			}
			$rate->shipping_params->methods = hikashop_unserialize($rate->shipping_params->methodsList);
			if($order->weight <= 0 || ($order->volume <= 0 && @$rate->shipping_params->use_dimensions == 1))
				continue;

			$this->freight = false;
			$this->classicMethod = false;
			$heavyProduct = false;
			$weightTotal = 0;
			if(!empty($rate->shipping_params->methods)) {
				foreach($rate->shipping_params->methods as $method) {
					if($method=='TDCB' || $method=='TDA' || $method=='TDO' || $method=='308' || $method=='309' || $method=='310')
						$this->freight = true;
					else
						$this->classicMethod = true;
				}
			}

			$data = null;
			if(empty($order->shipping_address)) {
				$messages['no_shipping_methods_configured'] = 'No shipping address is configured.';
				return true;
			}

			$this->shipping_currency_id = $currency = hikashop_getCurrency();
			$db = JFactory::getDBO();
			$query = 'SELECT currency_code FROM '. hikashop_table('currency') .' WHERE currency_id IN ('. $this->shipping_currency_id .')';
			$db->setQuery($query);
			$this->shipping_currency_code = $db->loadResult();
			$cart = hikashop_get('class.cart');
			$null = null;
			$cart->loadAddress($null,$order->shipping_address->address_id,'object', 'shipping');
			$currency = hikashop_get('class.currency');

			$receivedMethods = $this->_getRates($rate, $order, $heavyProduct, $null);

			if(empty($receivedMethods)) {
				$messages['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
				continue;
			}

			$i = 0;
			$local_usable_rates = array();
			foreach($receivedMethods as $method) {
				$usableMethods[] = $method;
				$local_usable_rates[$i] = clone($rate);
				$local_usable_rates[$i]->shipping_price += round($method['value'], 2);
				$selected_method = '';
				$name = '';
				$description = '';

				foreach($this->fedex_methods as $fedex_method) {
					if($fedex_method['code'] == $method['code'] && ($method['old_currency_code'] == 'CAD' || !isset($fedex_method['double']))) {
						$name = $fedex_method['name'];
						$selected_method = $fedex_method['key'];

						$typeKey = str_replace(' ','_', strtoupper($fedex_method['name']));
						$shipping_name = JText::_($typeKey);

						if($shipping_name != $typeKey)
							$name = $shipping_name;
						else
							$name = $fedex_method['name'];

						$shipping_description = JText::_($typeKey.'_DESCRIPTION');
						if($shipping_description != $typeKey.'_DESCRIPTION')
							$description .= $shipping_description;
						break;
					}
				}
				$local_usable_rates[$i]->shipping_name = $name;

				if($description != '')
					$local_usable_rates[$i]->shipping_description .= $description;

				if(!empty($selected_method))
					$local_usable_rates[$i]->shipping_id .= '-' . $selected_method;
				$sep = '';
				if(@$rate->shipping_params->show_eta) {
					if(@$rate->shipping_params->show_eta_delay) {
						if($method['delivery_delay'] != -1 && $method['day'] > 0)
							$local_usable_rates[$i]->shipping_description .= $sep . JText::sprintf( 'ESTIMATED_TIME_AFTER_SEND', $method['delivery_delay']);
						else
							$local_usable_rates[$i]->shipping_description .= $sep . JText::_( 'NO_ESTIMATED_TIME_AFTER_SEND');
					} else {
						if($method['delivery_day'] != -1 && $method['day'] > 0)
							$local_usable_rates[$i]->shipping_description .= $sep . JText::sprintf( 'ESTIMATED_TIME_AFTER_SEND', $method['delivery_day']);
						else
							$local_usable_rates[$i]->shipping_description .= $sep . JText::_( 'NO_ESTIMATED_TIME_AFTER_SEND');
					}
					$sep = '<br/>';
					if($method['delivery_time']!= -1 && $method['day'] > 0) {
						if(@$rate->shipping_params->show_eta_format == '12')
							$local_usable_rates[$i]->shipping_description .= $sep . JText::sprintf( 'DELIVERY_HOUR', date('h:i:s a', strtotime($method['delivery_time'])));
						else
							$local_usable_rates[$i]->shipping_description .= $sep . JText::sprintf( 'DELIVERY_HOUR', $method['delivery_time']);
					} else {
						$local_usable_rates[$i]->shipping_description .= $sep . JText::_( 'NO_DELIVERY_HOUR');
					}
				}
				if(@$rate->shipping_params->show_notes && !empty($method['notes'])) {
					foreach($method['notes'] as $note){
						if($note->Code != '820' && $note->Code != '819' && !empty($note->LocalizedMessage)) {
							$local_usable_rates[$i]->shipping_description .= $sep . implode('<br/>', $note->LocalizedMessage);
							$sep = '<br/>';
						}
					}
				}
				if($rate->shipping_params->group_package && $this->nbpackage > 0)
					$local_usable_rates[$i]->shipping_description .= '<br/>' . JText::sprintf('X_PACKAGES', $this->nbpackage);
				$i++;
			}

			foreach($local_usable_rates as $i => $finalRate) {
				if(isset($finalRate->shipping_price_orig) || isset($finalRate->shipping_currency_id_orig)){
					if($finalRate->shipping_currency_id_orig == $finalRate->shipping_currency_id)
						$finalRate->shipping_price_orig = $finalRate->shipping_price;
					else
						$finalRate->shipping_price_orig = $currencyClass->convertUniquePrice($finalRate->shipping_price, $finalRate->shipping_currency_id, $finalRate->shipping_currency_id_orig);
				}
				$usable_rates[$finalRate->shipping_id] = $finalRate;
			}
		}
	}
	function getShippingDefaultValues(&$element) {
		$element->shipping_name = 'FedEx';
		$element->shipping_description = '';
		$element->group_package = 0;
		$element->debug = 0;
		$element->shipping_images = 'fedex';
		$element->shipping_params->post_code = '';
		$element->shipping_currency_id = $this->main_currency;
		$element->shipping_params->pickup_type = '01';
		$element->shipping_params->destination_type = 'auto';
		$element->shipping_params->use_dimensions = 1;
		$element->shipping_params->show_eta_delay = 1;
		$element->shipping_params->show_eta = 1;
		$element->shipping_params->show_notes = 1;
	}
	function onShippingConfiguration(&$element){
		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency', 1);
		$currencyClass = hikashop_get('class.currency');
		$currency = hikashop_get('class.currency');
		$this->currencyCode = $currency->get($this->main_currency)->currency_code;
		$this->currencySymbol = $currency->get($this->main_currency)->currency_symbol;
		$this->fedex = hikaInput::get()->getCmd('name','fedex');
		$this->categoryType = hikashop_get('type.categorysub');
		$this->categoryType->type = 'tax';
		$this->categoryType->field = 'category_id';
		$this->nameboxType = hikashop_get('type.namebox');

		parent::onShippingConfiguration($element);

		$js = "
			function checkAllBox(id, type){
				var toCheck = document.getElementById(id).getElementsByTagName('input');
				for (i = 0 ; i < toCheck.length ; i++) {
					if (toCheck[i].type == 'checkbox') {
						if(type == 'check'){
							toCheck[i].checked = true;
						}else{
							toCheck[i].checked = false;
						}
					}
				}
			}";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( "<!--\n".$js."\n//-->\n" );
	}

	function onShippingConfigurationSave(&$element){
		$app = JFactory::getApplication();
		$methods = array();
		if(empty($element->shipping_params->account_number)
			|| empty($element->shipping_params->origination_postcode)
			|| empty($element->shipping_params->meter_id)
			|| empty($element->shipping_params->api_key)
			|| empty($element->shipping_params->api_password)
			|| empty($element->shipping_params->sender_company)
			|| empty($element->shipping_params->sender_phone)
			|| empty($element->shipping_params->sender_address)
			|| empty($element->shipping_params->sender_city)
			|| empty($element->shipping_params->sender_state)
			|| empty($element->shipping_params->sender_country)
			|| empty($element->shipping_params->sender_postcode)
		) {
			$app->enqueueMessage(JText::sprintf('ENTER_INFO', 'FedEx', JText::_('SENDER_INFORMATIONS').' ('. JText::_( 'FEDEX_ORIGINATION_POSTCODE' ).', '.JText::_( 'FEDEX_ACCOUNT_NUMBER' ).', '.JText::_( 'FEDEX_METER_ID' ).', '.JText::_( 'FEDEX_API_KEY' ).', '.JText::_( 'HIKA_PASSWORD' ).', '.JText::_( 'COMPANY' ).', '.JText::_( 'TELEPHONE' ).', '.JText::_( 'ADDRESS' ).', '.JText::_( 'CITY' ).', '.JText::_( 'COUNTRY' ).', '.JText::_( 'POST_CODE' ).')'));
		}
		if(isset($_REQUEST['data']['shipping_methods'])) {
			foreach($_REQUEST['data']['shipping_methods'] as $method) {
				foreach($this->fedex_methods as $fedexMethod) {
					$name = strtolower($fedexMethod['name']);
					$name = str_replace(' ','_', $name);
					if($name == $method['name']) {
						$obj = new stdClass();
						$methods[strip_tags($method['name'])] = strip_tags($fedexMethod['code']);
					}
				}
			}
		} else {
			$app->enqueueMessage(JText::sprintf('CHOOSE_SHIPPING_SERVICE'));
		}
		$element->shipping_params->methodsList = serialize($methods);
		return true;
	}


	function _getRates(&$rate, &$order, $heavyProduct, $null){
		$db = JFactory::getDBO();
		$total_price = 0;
		foreach($order->products as $k=>$v) {
			foreach($v->prices as $price) {
				$total_price = $total_price + $price->price_value;
			}
		}

		$data['fedex_account_number'] = @$rate->shipping_params->account_number;
		$data['fedex_meter_number'] = @$rate->shipping_params->meter_id;
		$data['fedex_api_key'] = @$rate->shipping_params->api_key;
		$data['fedex_api_password'] = @$rate->shipping_params->api_password;
		$data['show_eta'] = @$rate->shipping_params->show_eta;
		$data['show_eta_format'] = @$rate->shipping_params->show_eta_format;
		$data['packaging_type'] = @$rate->shipping_params->packaging_type;
		$data['include_price'] = @$rate->shipping_params->include_price;
		$data['currency_code'] = $this->shipping_currency_code;
		$data['weight_approximation'] = @$rate->shipping_params->weight_approximation;
		$data['use_dimensions'] = (isset($rate->shipping_params->use_dimensions)) ? $rate->shipping_params->use_dimensions : 0;
		$data['dim_approximation_l'] = @$rate->shipping_params->dim_approximation_l;
		$data['dim_approximation_w'] = @$rate->shipping_params->dim_approximation_w;
		$data['dim_approximation_h'] = @$rate->shipping_params->dim_approximation_h;
		$data['methods'] = @$rate->shipping_params->methods;
		$data['destZip'] = @$null->shipping_address->address_post_code;
		$data['destCountry'] = @$null->shipping_address->address_country->zone_code_2;
		$data['zip'] = @$rate->shipping_params->origination_postcode;
		$data['total_insured'] = @$total_price;
		$data['sender_company'] = @$rate->shipping_params->sender_company;
		$data['sender_phone'] = @$rate->shipping_params->sender_phone;
		$data['sender_address'] = @$rate->shipping_params->sender_address;
		$data['sender_city'] = @$rate->shipping_params->sender_city;
		$data['weight'] = 0;
		$data['height'] = 0;
		$data['length'] = 0;
		$data['width'] = 0;
		if(isset($order->full_total->prices[0]))
			$data['price'] = $order->full_total->prices[0]->price_value_with_tax;
		else
			$data['price'] = 0;

		$state_zone = '';
		$state_zone = @$rate->shipping_params->sender_state;
		$query = "SELECT zone_id, zone_code_2, zone_code_3 FROM ".hikashop_table('zone')." WHERE zone_namekey IN (".$db->Quote($state_zone).")";
		$db->setQuery($query);
		$state = $db->loadObject();
		$data['sender_state'] = '';
		if(isset($state->zone_code_2) && strlen($state->zone_code_2) == 2)
			$data['sender_state'] = $state->zone_code_2;
		elseif(isset($state->zone_code_3) && strlen($state->zone_code_3) == 2)
			$data['sender_state'] = $state->zone_code_3;

		$data['sender_postcode'] = $rate->shipping_params->sender_postcode;
		$data['recipient'] = $null->shipping_address;

		$czone_code = '';
		$czone_code = @$rate->shipping_params->sender_country;
		$query = "SELECT zone_id, zone_code_2 FROM ".hikashop_table('zone')." WHERE zone_namekey IN (".$db->Quote($czone_code).")";
		$db->setQuery($query);
		$czone = $db->loadObject();
		$data['country'] = $czone->zone_code_2;

		$data['XMLpackage'] = '';
		$data['pickup_type'] = @$rate->shipping_params->pickup_type;
		$this->nbpackage = 0;

		$ground_limit = array(
			'FEDEX_GROUND',
			'FEDEX_2_DAY',
			'FIRST_OVERNIGHT',
			'STANDARD_OVERNIGHT',
			'SMART_POST',
			'PRIORITY_OVERNIGHT'
		);
		$international_limit = array(
			'INTERNATIONAL_GROUND',
			'INTERNATIONAL_ECONOMY',
			'INTERNATIONAL_ECONOMY_DISTRIBUTION',
			'INTERNATIONAL_FIRST',
			'EUROPE_FIRST_INTERNATIONAL_PRIORITY',
			'INTERNATIONAL_FIRST',
			'INTERNATIONAL_PRIORITY',
			'INTERNATIONAL_PRIORITY_DISTRIBUTION'
		);

		$limit = array(
			'y' => 119,
			'w' => 150,
			'length_girth' => 165
		);
		if(!empty($rate->shipping_params->methods)) {
			foreach($rate->shipping_params->methods as $k => $service_name) {
				$l_lenght = 0; $l_weight = 0; $l_dimension = 0;
				if($service_name == 'FEDEX_GROUND')
					$limit['y'] = 108;
				if($service_name == 'GROUND_HOME_DELIVERY')
					$limit['w'] = 70;
				if(in_array($service_name,$international_limit))
					$limit['length_girth'] = 130;
				}
			}

		if(!$rate->shipping_params->group_package || $rate->shipping_params->group_package == 0)
			$limit['unit'] = 1;


		$required_dimensions = array('w');
		if(@$rate->shipping_params->use_dimensions == 1) {
			$required_dimensions = array('w','x','y','z');
		} else {
			$limit = array('w' => $limit['w']);
		}

		$packages = $this->getOrderPackage($order, array('weight_unit' => 'lb', 'volume_unit' => 'in', 'limit' => $limit, 'required_dimensions' => $required_dimensions));

		if(empty($packages))
			return false;

		$this->package_added = 0;
		$this->nbpackage = 0;

		if(isset($packages['w']) && isset($packages['x']) && isset($packages['y']) && isset($packages['z'])) {
			$this->nbpackage++;
			$data['weight'] = $packages['w'];
			$data['height'] = $packages['z'];
			$data['length'] = $packages['y'];
			$data['width'] = $packages['x'];
			$data['weight_unit'] = 'LB';
			$data['dimension_unit'] = 'IN';
			$data['quantity'] = 1;

			if(($this->freight==true && $this->classicMethod==false) || ($heavyProduct==true && $this->freight==true) || @$rate->shipping_params->use_dimensions != 1)
				$data['XMLpackage'].= $this->_createPackage($data, $rate, $order);
			else
				$data['XMLpackage'].= $this->_createPackage($data, $rate, $order, true);
		} else {
			foreach($packages as $package){
				if(!isset($package['w']) || $package['w'] == 0)
					continue;
				if(@$rate->shipping_params->use_dimensions == 1) {
					if(!isset($package['x']) || $package['x'] == 0 || !isset($package['y']) || $package['y'] == 0 || !isset($package['z']) || $package['z'] == 0)
						continue;
				}
				$this->nbpackage++;
				$data['weight'] = $package['w'];
				$data['height'] = $package['z'];
				$data['length'] = $package['y'];
				$data['width'] = $package['x'];
				$data['weight_unit'] = 'LB';
				$data['dimension_unit'] = 'IN';
				$data['quantity'] = 1;

				if(($this->freight==true && $this->classicMethod==false) || ($heavyProduct==true && $this->freight==true) || @$rate->shipping_params->use_dimensions != 1)
					$data['XMLpackage'].= $this->_createPackage($data, $rate, $order);
				else
					$data['XMLpackage'].= $this->_createPackage($data, $rate, $order, true);
			}
		}

		$usableMethods = $this->_FEDEXrequestMethods($data,$rate);

		if(empty($usableMethods))
			return false;

		$currencies = array();
		foreach($usableMethods as $method){
			$currencies[$method['currency_code']] = '"'. $method['currency_code'] .'"';
		}

		$db = JFactory::getDBO();
		$query = 'SELECT currency_code, currency_id FROM '. hikashop_table('currency') .' WHERE currency_code IN ('. implode(',',$currencies) .')';
		$db->setQuery($query);
		$currencyList = $db->loadObjectList();
		$currencyList = reset($currencyList);
		foreach($usableMethods as $i => $method) {
			$usableMethods[$i]['currency_id'] = $currencyList->currency_id;
		}

		$usableMethods = parent::_currencyConversion($usableMethods, $order);

		return $usableMethods;
	}

	function _createPackage(&$data, &$rate, &$order, $includeDimension=false){
		if(!empty($rate->shipping_params->weight_approximation))
			$data['weight'] = $data['weight']+$data['weight']*$rate->shipping_params->weight_approximation/100;

		if(@$data['weight'] < 1)
			$data['weight'] = 1;

		if(!empty($rate->shipping_params->dim_approximation_h) && @$rate->shipping_params->use_dimensions == 1)
			$data['height'] = $data['height'] + $data['height']*($rate->shipping_params->dim_approximation_h / 100);

		if(!empty($rate->shipping_params->dim_approximation_l) && @$rate->shipping_params->use_dimensions == 1)
			$data['length'] = $data['length'] + $data['length']*($rate->shipping_params->dim_approximation_l / 100);

		if(!empty($rate->shipping_params->dim_approximation_w) && @$rate->shipping_params->use_dimensions == 1){
			$data['width'] = $data['width'] + $data['width']*($rate->shipping_params->dim_approximation_w / 100);
		}
		$options = '';
		$dimension = '';
		if(@$rate->shipping_params->include_price) {
			$options = '<PackageServiceOptions>
						<InsuredValue>
							<CurrencyCode>'.$data['currency_code'].'</CurrencyCode>
							<MonetaryValue>'.$data['price'].'</MonetaryValue>
						</InsuredValue>
					</PackageServiceOptions>';
		}
		if($includeDimension) {
			if($data['height'] != '' && $data['height'] != 0 && $data['height'] != '0.00') {
				$dimension = '<Dimensions>
							<UnitOfMeasurement>
								<Code>'.$data['dimension_unit'].'</Code>
							</UnitOfMeasurement>
							<Length>'.$data['length'].'</Length>
							<Width>'.$data['width'].'</Width>
							<Height>'.$data['height'].'</Height>
						</Dimensions>';
			}
		}
		static $id = 0;
		$xml = '<Package'. $id .'>
				<PackagingType>
					<Code>02</Code>
				</PackagingType>
				<Description>Shop</Description>
				'. $dimension .'
				<PackageWeight>
					<UnitOfMeasurement>
						<Code>'. $data['weight_unit'] .'</Code>
					</UnitOfMeasurement>
					<Weight>'. $data['weight']. '</Weight>
				</PackageWeight>
				'. $options .'
			</Package'. $id .'>';
		$id++;
		return $xml;
	}
	function _FEDEXrequestMethods($data,$rate) {
		global $fedex_methods;

		$environment = 'fedex_rate';
		if(!empty($rate->shipping_params->environment) && $rate->shipping_params->environment=='test') {
			$environment .= '_test';
		}

		$path_to_wsdl = dirname(__FILE__).DS.$environment.'.wsdl';

		ini_set("soap.wsdl_cache_enabled","0");
		if(!class_exists('SoapClient')) {
			$app = JFactory::getApplication();
			$app->enqueueMessage('The FEDEX shipping plugin needs the SOAP library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}
		$client = new SoapClient($path_to_wsdl, array('exceptions' => false));


		$shipment= array();
		foreach($data['methods'] as $k=>$v) {
			$request['WebAuthenticationDetail'] = array(
				'UserCredential' => array(
					'Key' => $data['fedex_api_key'],
					'Password' => $data['fedex_api_password']
				)
			);
			$request['ClientDetail'] = array(
				'AccountNumber' => $data['fedex_account_number'],
				'MeterNumber' => $data['fedex_meter_number']
			);
			$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Request v10 using PHP ***');
			$request['Version'] = array(
				'ServiceId' => 'crs',
				'Major' => '10',
				'Intermediate' => '0',
				'Minor' => '0'
			);

			$request['ReturnTransitAndCommit'] = true;
			$request['RequestedShipment']['DropoffType'] = 'REGULAR_PICKUP'; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
			$request['RequestedShipment']['ShipTimestamp'] = date('c');
			$request['RequestedShipment']['ServiceType'] = $v; // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
			$request['RequestedShipment']['PackagingType'] = $data['packaging_type']; // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
			$request['RequestedShipment']['TotalInsuredValue'] = array('Ammount'=>$data['total_insured'],'Currency'=>'USD');
			$request['RequestedPackageDetailType'] = 'PACKAGE_SUMMARY';

			$shipper = array(
				'Contact' => array(
					'PersonName' => $data['sender_company'],
					'CompanyName' => $data['sender_company'],
					'PhoneNumber' => $data['sender_phone']),
				'Address' => array(
					'StreetLines' => array($data['sender_address']),
					'City' => $data['sender_city'],
					'StateOrProvinceCode' => $data['sender_state'],
					'PostalCode' => $data['sender_postcode'],
					'CountryCode' => $data['country'])
			);

			$recipient_StateOrProvinceCode = '';
			if(isset($data['recipient']->address_state->zone_code_2) && strlen($data['recipient']->address_state->zone_code_2) == 2)
				$recipient_StateOrProvinceCode = $data['recipient']->address_state->zone_code_2;
			elseif(isset($data['recipient']->address_state->zone_code_3) && strlen($data['recipient']->address_state->zone_code_3) == 2)
				$recipient_StateOrProvinceCode = $data['recipient']->address_state->zone_code_3;
			$recipient = array(
				'Contact' => array(
					'PersonName' => $data['recipient']->address_title." ".$data['recipient']->address_firstname." ".$data['recipient']->address_lastname,
					'CompanyName' => $data['recipient']->address_company,
					'PhoneNumber' => $data['recipient']->address_telephone
				),
				'Address' => array(
					'StreetLines' => array($data['recipient']->address_street),
					'City' => $data['recipient']->address_city,
					'StateOrProvinceCode' => $recipient_StateOrProvinceCode,
					'PostalCode' => $data['recipient']->address_post_code,
					'CountryCode' => $data['recipient']->address_country->zone_code_2,
					'Residential' => true)
			);
			if(@$rate->shipping_params->destination_type == 'res') {
				$recipient['Address']['Residential'] = true;
			}
			if(@$rate->shipping_params->destination_type=='com' || (@$rate->shipping_params->destination_type=='auto' && $v == 'FEDEX_GROUND')) {
				$recipient['Address']['Residential'] = false;
			}
			$shippingChargesPayment = array(
				'PaymentType' => 'SENDER', // valid values RECIPIENT, SENDER and THIRD_PARTY
				'Payor' => array(
					'AccountNumber' => $data['fedex_account_number'],
					'CountryCode' => $data['country'])
			);

			$pkg_values = $this->xml2array('<root>'. $data['XMLpackage'] .'</root>');
			$pkg_values = $pkg_values['root'];
			$pkg_count = count($pkg_values);

			$request['RequestedShipment']['Shipper'] = $shipper;
			$request['RequestedShipment']['Recipient'] = $recipient;
			$request['RequestedShipment']['ShippingChargesPayment'] = $shippingChargesPayment;
			$request['RequestedShipment']['RateRequestTypes'] = 'ACCOUNT';
			if(empty($rate->shipping_params->rate_types) || $rate->shipping_params->rate_types != 'ACCOUNT'){
				$request['RequestedShipment']['RateRequestTypes'] = 'LIST';
			}
			$request['RequestedShipment']['PackageCount'] = $pkg_count;
			$request['RequestedShipment']['RequestedPackageLineItems'] = $this->addPackageLineItem($pkg_values);

			$ctrl = hikaInput::get()->getString('ctrl','');
			if(@$rate->shipping_params->debug && $ctrl == 'checkout') {
				echo "<br/> Request $v : <br/>";
				echo '<pre>' . var_export($request, true) . '</pre>';
			}


				$response = $client->getRates($request);


			if(isset($response->HighestSeverity) && $response->HighestSeverity == "ERROR") {
				static $notif = false;
				if(!$notif && isset($response->Notifications->Message) && $response->Notifications->Message == 'Authentication Failed') {
					$app = JFactory::getApplication();
					$app->enqueueMessage('FEDEX Authentication Failed');
					$notif = true;
				}
				if(!$notif && !empty($response->Notifications->Message) && strpos($response->Notifications->Message,'Service is not allowed') === FALSE) {
					$app = JFactory::getApplication();
					$app->enqueueMessage('The FedEx request failed with the message : ' . $response->Notifications->Message);
				}
			}
			if(@$rate->shipping_params->debug && $ctrl == 'checkout') {
				echo "<br/> Response $v : <br/>";
				echo '<pre>' . var_export($response, true) . '</pre>';
			}
			if(!empty($response->HighestSeverity) && ($response->HighestSeverity == "SUCCESS" || $response->HighestSeverity == "NOTE" || $response->HighestSeverity == "WARNING")) {
				$code = '';
				$notes = array();
				if($response->HighestSeverity == "NOTE" || $response->HighestSeverity == "WARNING") {
					$notes = $response->Notifications;
				}

				foreach($this->fedex_methods as $k=>$v) {
					if($v['code'] == $response->RateReplyDetails->ServiceType){
						$code = $v['code'];
					}
				}
				$delayType = hikashop_get('type.delay');
				if(!empty($response->RateReplyDetails->DeliveryTimestamp))
					$timestamp = strtotime($response->RateReplyDetails->DeliveryTimestamp);
				else {
					$timestamp = 0;
					$response->RateReplyDetails->DeliveryTimestamp = 0;
				}
				$totalNetPrice = 0;
				$discountAmount = 0;
				if(is_array($response->RateReplyDetails->RatedShipmentDetails)){
					$totalNetPrice = $response->RateReplyDetails->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount;

					if($request['RequestedShipment']['RateRequestTypes'] != 'ACCOUNT'){
						$discountAmount = $response->RateReplyDetails->RatedShipmentDetails[0]->ShipmentRateDetail->TotalFreightDiscounts->Amount;
					}
					$shipment[] = array(
						'value' => $totalNetPrice + $discountAmount,
						'code' => $code,
						'delivery_timestamp' => $timestamp,
						'day' => $response->RateReplyDetails->DeliveryTimestamp,
						'delivery_day' => date("m/d/Y", $timestamp),
						'delivery_delay' => parent::displayDelaySECtoDAY($timestamp - strtotime('now'),2),
						'delivery_time' => date("H:i:s", $timestamp),
						'currency_code' => $response->RateReplyDetails->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Currency,
						'old_currency_code' => $response->RateReplyDetails->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Currency,
						'notes' => $notes
					);

				} else if(is_object($response->RateReplyDetails->RatedShipmentDetails)){
					$totalNetPrice = $response->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Amount;

					if($request['RequestedShipment']['RateRequestTypes'] != 'ACCOUNT'){
						$discountAmount = $response->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail->TotalFreightDiscounts->Amount;
					}
					$shipment[] = array(
						'value' => $totalNetPrice + $discountAmount,
						'code' => $code,
						'delivery_timestamp' => $timestamp,
						'day' => $response->RateReplyDetails->DeliveryTimestamp,
						'delivery_day' => date("m/d/Y", $timestamp),
						'delivery_delay' => parent::displayDelaySECtoDAY($timestamp - strtotime('now'),2),
						'delivery_time' => date("H:i:s", $timestamp),
						'currency_code' => $response->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Currency,
						'old_currency_code' => $response->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Currency,
						'notes' => $notes
					);
				}
			} else if(!empty($response->HighestSeverity) && ($response->HighestSeverity == "ERROR")) {
				static $errorsDisplayed = array();

				if(!empty($response->Notifications)) {
					foreach($response->Notifications as $notif) {
						if(!is_object($notif) || !isset($notif->Code))
							continue;
						$errorCode = $notif->Code;

						if(!isset($errorsDisplayed[$errorCode])) {
							$app = JFactory::getApplication();
							$app->enqueueMessage($notif->Message);
						}
						$errorsDisplayed[$errorCode] = true;
					}

				}
			}
		}

		return $shipment;
	}

	function processPackageLimit($limit_key, $limit_value, $product, $qty, $package, $units) {
		switch($limit_key) {
			case 'length_girth':
				$max_qty = (($limit_value - $product['z']) / 2 - $product['y']) / $product['x'];
				if(!$max_qty || $max_qty < 1)
					return false;
				return (int)floor($max_qty);
				break;
		}
		return parent::processPackageLimit($limit_key, $limit_value , $product, $qty, $package, $units);
	}

	function printSuccess($client, $response) {
		echo '<h2>Transaction Successful</h2>';
		echo "\n";
		printRequestResponse($client);
	}
	function printRequestResponse($client){
		echo '<h2>Request</h2>' . "\n";
		echo '<pre>' . htmlspecialchars($client->__getLastRequest()). '</pre>';
		echo "\n";

		echo '<h2>Response</h2>'. "\n";
		echo '<pre>' . htmlspecialchars($client->__getLastResponse()). '</pre>';
		echo "\n";
	}

	function printFault($exception, $client) {
		echo '<h2>Fault</h2>' . "<br>\n";
		echo "<b>Code:</b>{$exception->faultcode}<br>\n";
		echo "<b>String:</b>{$exception->faultstring}<br>\n";
		writeToLog($client);
	}

	function writeToLog($client){
		if (!$logfile = fopen(TRANSACTIONS_LOG_FILE, "a")) {
			error_func("Cannot open " . TRANSACTIONS_LOG_FILE . " file.\n", 0);
			exit(1);
		}

		fwrite($logfile, sprintf("\r%s:- %s",date("D M j G:i:s T Y"), $client->__getLastRequest(). "\n\n" . $client->__getLastResponse()));
	}

	function getProperty($var){
		if($var == 'check') Return true;
		if($var == 'shipaccount') Return 'XXX';
		if($var == 'billaccount') Return 'XXX';
		if($var == 'dutyaccount') Return 'XXX';
		if($var == 'accounttovalidate') Return 'XXX';
		if($var == 'meter') Return 'XXX';
		if($var == 'key') Return 'XXX';
		if($var == 'password') Return '';
		if($var == 'shippingChargesPayment') Return 'SENDER';
		if($var == 'internationalPaymentType') Return 'SENDER';
		if($var == 'readydate') Return '2010-05-31T08:44:07';
		if($var == 'readytime') Return '12:00:00-05:00';
		if($var == 'closetime') Return '20:00:00-05:00';
		if($var == 'closedate') Return date("Y-m-d");
		if($var == 'pickupdate') Return date("Y-m-d", mktime(8, 0, 0, date("m")  , date("d")+1, date("Y")));
		if($var == 'pickuptimestamp') Return mktime(8, 0, 0, date("m")  , date("d")+1, date("Y"));
		if($var == 'pickuplocationid') Return 'XXX';
		if($var == 'pickupconfirmationnumber') Return '00';
		if($var == 'dispatchdate') Return date("Y-m-d", mktime(8, 0, 0, date("m")  , date("d")+1, date("Y")));
		if($var == 'dispatchtimestamp') Return mktime(8, 0, 0, date("m")  , date("d")+1, date("Y"));
		if($var == 'dispatchlocationid') Return 'XXX';
		if($var == 'dispatchconfirmationnumber') Return '00';
		if($var == 'shiptimestamp') Return mktime(10, 0, 0, date("m"), date("d")+1, date("Y"));
		if($var == 'tag_readytimestamp') Return mktime(10, 0, 0, date("m"), date("d")+1, date("Y"));
		if($var == 'tag_latesttimestamp') Return mktime(15, 0, 0, date("m"), date("d")+1, date("Y"));
		if($var == 'trackingnumber') Return 'XXX';
		if($var == 'trackaccount') Return 'XXX';
		if($var == 'shipdate') Return '2010-06-06';
		if($var == 'account') Return 'XXX';
		if($var == 'phonenumber') Return '1234567890';
		if($var == 'closedate') Return '2010-05-30';
		if($var == 'expirationdate') Return '2011-06-15';
		if($var == 'hubid') Return '5531';
		if($var == 'begindate') Return '2011-05-20';
		if($var == 'enddate') Return '2011-05-31';
		if($var == 'address1') Return array('StreetLines' => array('10 Fed Ex Pkwy'),
			'City' => 'Memphis',
			'StateOrProvinceCode' => 'TN',
			'PostalCode' => '38115',
			'CountryCode' => 'US');
		if($var == 'address2') Return array('StreetLines' => array('13450 Farmcrest Ct'),
			'City' => 'Herndon',
			'StateOrProvinceCode' => 'VA',
			'PostalCode' => '20171',
			'CountryCode' => 'US');
		if($var == 'locatoraddress') Return array(array('StreetLines'=>'240 Central Park S'),
			'City'=>'Austin',
			'StateOrProvinceCode'=>'TX',
			'PostalCode'=>'78701',
			'CountryCode'=>'US');
		if($var == 'recipientcontact') Return array('ContactId' => 'arnet',
			'PersonName' => 'Recipient Contact',
			'PhoneNumber' => '1234567890');
		if($var == 'freightaccount') Return 'XXX';
		if($var == 'freightbilling') Return array(
			'Contact'=>array(
				'ContactId' => 'freight1',
				'PersonName' => 'Big Shipper',
				'Title' => 'Manager',
				'CompanyName' => 'Freight Shipper Co',
				'PhoneNumber' => '1234567890'
			),
			'Address'=>array(
				'StreetLines'=>array('1202 Chalet Ln', 'Do Not Delete - Test Account'),
				'City' =>'Harrison',
				'StateOrProvinceCode' => 'AR',
				'PostalCode' => '72601-6353',
				'CountryCode' => 'US'
			)
		);
	}

	function setEndpoint($var){
		if($var == 'changeEndpoint') Return false;
		if($var == 'endpoint') Return '';
	}

	function printNotifications($notes){
		foreach($notes as $noteKey => $note){
			if(is_string($note)){
				echo $noteKey . ': ' . $note . Newline;
			} else{
				printNotifications($note);
			}
		}
		echo Newline;
	}

	function printError($client, $response){
		echo '<h2>Error returned in processing transaction</h2>';
		echo "\n";
		printNotifications($response->Notifications);
		printRequestResponse($client, $response);
	}

	function addPackageLineItem($pkg_values){
		$packageLineItem[] = array();
		$x = 0;
		foreach($pkg_values as $pkg) {
			if($pkg['PackageWeight']['UnitOfMeasurement']['Code'] == "LBS"){
				$uom = "LB";
			} else {
				$uom = $pkg["PackageWeight"]["UnitOfMeasurement"]['Code'];
			}
			$dimensions = array(
				'Length' => 0,
				'Width' => 0,
				'Height' => 0,
				'Units' => 'IN'
			);
			if(isset($pkg['Dimensions']) && is_array($pkg['Dimensions'])) {
				$dimensions = array(
					'Length' => $pkg['Dimensions']['Length'],
					'Width' => $pkg['Dimensions']['Width'],
					'Height' => $pkg['Dimensions']['Height'],
					'Units' => $pkg['Dimensions']['UnitOfMeasurement']['Code']
				);
			}

			$packageLineItem[$x] = array(
				'SequenceNumber' => $x + 1,
				'GroupPackageCount' => 1,
				'Weight' => array(
					'Value' => $pkg['PackageWeight']['Weight'],
					'Units' => $uom
				),
				'Dimensions' => $dimensions
			);
			$x++;
		}

		return $packageLineItem;
	}

	function xml2array($contents, $get_attributes = 1, $priority = 'tag') {
		if (!function_exists('xml_parser_create')) {
			return array ();
		}
		$parser = xml_parser_create('');

		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xml_values);
		xml_parser_free($parser);
		if (!$xml_values)
			return; //Hmm...
		$xml_array = array ();
		$parents = array ();
		$opened_tags = array ();
		$arr = array ();
		$current = & $xml_array;
		$repeated_tag_index = array ();
		foreach ($xml_values as $data) {
			unset ($attributes, $value);
			extract($data);
			$result = array ();
			$attributes_data = array ();
			if (isset ($value)) {
				if ($priority == 'tag')
					$result = $value;
				else
					$result['value'] = $value;
			}
			if (isset ($attributes) and $get_attributes) {
				foreach ($attributes as $attr => $val) {
					if ($priority == 'tag')
						$attributes_data[$attr] = $val;
					else
						$result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
				}
			}
			if ($type == "open") {
				$parent[$level -1] = & $current;
				if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
					$current[$tag] = $result;
					if ($attributes_data)
						$current[$tag . '_attr'] = $attributes_data;
					$repeated_tag_index[$tag . '_' . $level] = 1;
					$current = & $current[$tag];
				} else {
					if (isset ($current[$tag][0])) {
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						$repeated_tag_index[$tag . '_' . $level]++;
					} else {
						$current[$tag] = array (
							$current[$tag],
							$result
						);
						$repeated_tag_index[$tag . '_' . $level] = 2;
						if (isset ($current[$tag . '_attr'])) {
							$current[$tag]['0_attr'] = $current[$tag . '_attr'];
							unset ($current[$tag . '_attr']);
						}
					}
					$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
					$current = & $current[$tag][$last_item_index];
				}
			} elseif ($type == "complete") {
				if (!isset ($current[$tag])) {
					$current[$tag] = $result;
					$repeated_tag_index[$tag . '_' . $level] = 1;
					if ($priority == 'tag' and $attributes_data)
						$current[$tag . '_attr'] = $attributes_data;
				} else {
					if (isset ($current[$tag][0]) and is_array($current[$tag])) {
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						if ($priority == 'tag' and $get_attributes and $attributes_data) {
							$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
						}
						$repeated_tag_index[$tag . '_' . $level]++;
					} else {
						$current[$tag] = array (
							$current[$tag],
							$result
						);
						$repeated_tag_index[$tag . '_' . $level] = 1;
						if ($priority == 'tag' and $get_attributes) {
							if (isset ($current[$tag . '_attr'])) {
								$current[$tag]['0_attr'] = $current[$tag . '_attr'];
								unset ($current[$tag . '_attr']);
							}
							if ($attributes_data) {
								$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
							}
						}
						$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
					}
				}
			} elseif ($type == 'close') {
				$current = & $parent[$level -1];
			}
		}
		return ($xml_array);
	}
}
