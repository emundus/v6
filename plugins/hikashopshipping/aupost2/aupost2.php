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
class plgHikashopshippingAupost2 extends hikashopShippingPlugin {
	var $multiple = true;
	var $name = 'aupost2';
	var $doc_form = 'aupost2';
	var $use_cache = true;
	var $pluginConfig = array(
		'api_key' => array('FEDEX_API_KEY', 'input'),
		'post_code' => array('POST_CODE', 'input'),
		'services' => array('SHIPPING_SERVICES', 'checkbox',array(
			'AUS_PARCEL_REGULAR' => 'Parcel Post (your own packaging)',
			'AUS_PARCEL_REGULAR_SATCHEL_500G' => 'Parcel Post Small Satchel',
			'AUS_PARCEL_REGULAR_SATCHEL_3KG' => 'Parcel Post Medium Satchel',
			'AUS_PARCEL_REGULAR_SATCHEL_5KG' => 'Parcel Post Large Satchel',
			'AUS_PARCEL_EXPRESS' => 'Express Post (your own packaging)',
			'AUS_PARCEL_EXPRESS_SATCHEL_500G' => 'Express Post Small Satchel',
			'AUS_PARCEL_EXPRESS_SATCHEL_3KG' => 'Express Post Medium (3Kg) Satchel',
			'AUS_PARCEL_EXPRESS_SATCHEL_5KG' => 'Express Post Large (5Kg) Satchel',
			'INT_PARCEL_STD_OWN_PACKAGING' => 'International parcel standard (your own packaging)',
			'INTL_SERVICE_ECI_PLATINUM' => 'Express Courier International Platinum',
			'INTL_SERVICE_ECI_M' => 'Express Courier International Merchandise',
			'INTL_SERVICE_ECI_D' => 'Express Courier International Documents',
			'INTL_SERVICE_EPI' => 'Express Post International',
			'INTL_SERVICE_PTI' => 'Pack and Track International',
			'INTL_SERVICE_RPI' => 'Registered Post International',
			'INTL_SERVICE_AIR_MAIL' => 'Air Mail',
			'INTL_SERVICE_SEA_MAIL' => 'Sea Mail',
		)),
		'reverse_order' => array('Reverse order of services', 'boolean','0'),
		'shipping_group' => array('Group products together', 'boolean','0'),
	);
	var $shipping_types = array(
		'AUS_PARCEL_REGULAR' => 1,
		'AUS_PARCEL_REGULAR_SATCHEL_500G' => 2,
		'AUS_PARCEL_EXPRESS' => 3,
		'AUS_PARCEL_EXPRESS_SATCHEL_500G' => 4,
		'INTL_SERVICE_ECI_PLATINUM' => 5,
		'INTL_SERVICE_ECI_M' => 6,
		'INTL_SERVICE_ECI_D' => 7,
		'INTL_SERVICE_EPI' => 8,
		'INTL_SERVICE_PTI' => 9,
		'INTL_SERVICE_RPI' => 10,
		'INTL_SERVICE_AIR_MAIL' => 11,
		'INTL_SERVICE_SEA_MAIL' => 12,
		'AUS_PARCEL_REGULAR_SATCHEL_3KG' => 13,
		'AUS_PARCEL_REGULAR_SATCHEL_5KG' => 14,
		'AUS_PARCEL_EXPRESS_SATCHEL_3KG' => 15,
		'AUS_PARCEL_EXPRESS_SATCHEL_5KG' => 16,
		'INT_PARCEL_STD_OWN_PACKAGING' => 17
		);

	var $shipping_names = array(
		'AUS_PARCEL_REGULAR' => 'Parcel Post (your own packaging)',
		'AUS_PARCEL_REGULAR_SATCHEL_500G' => 'Parcel Post Small Satchel',
		'AUS_PARCEL_REGULAR_SATCHEL_3KG' => 'Parcel Post Medium Satchel',
		'AUS_PARCEL_REGULAR_SATCHEL_5KG' => 'Parcel Post Large Satchel',
		'AUS_PARCEL_EXPRESS' => 'Express Post (your own packaging)',
		'AUS_PARCEL_EXPRESS_SATCHEL_500G' => 'Express Post Small Satchel',
		'AUS_PARCEL_EXPRESS_SATCHEL_3KG' => 'Express Post Medium (3Kg) Satchel',
		'AUS_PARCEL_EXPRESS_SATCHEL_5KG' => 'Express Post Large (5Kg) Satchel',
		'INT_PARCEL_STD_OWN_PACKAGING' => 'International parcel standard (your own packaging)',
		'INTL_SERVICE_ECI_PLATINUM' => 'Express Courier International Platinum',
		'INTL_SERVICE_ECI_M' => 'Express Courier International Merchandise',
		'INTL_SERVICE_ECI_D' => 'Express Courier International Documents',
		'INTL_SERVICE_EPI' => 'Express Post International',
		'INTL_SERVICE_PTI' => 'Pack and Track International',
		'INTL_SERVICE_RPI' => 'Registered Post International',
		'INTL_SERVICE_AIR_MAIL' => 'Air Mail',
		'INTL_SERVICE_SEA_MAIL' => 'Sea Mail',
		);

	var $local_shipping_types = array(
		'AUS_PARCEL_REGULAR',
		'AUS_PARCEL_REGULAR_SATCHEL_500G',
		'AUS_PARCEL_REGULAR_SATCHEL_3KG',
		'AUS_PARCEL_REGULAR_SATCHEL_5KG',
		'AUS_PARCEL_EXPRESS',
		'AUS_PARCEL_EXPRESS_SATCHEL_500G',
		'AUS_PARCEL_EXPRESS_SATCHEL_3KG',
		'AUS_PARCEL_EXPRESS_SATCHEL_5KG'
	);

	var $world_shipping_types = array(
		'INTL_SERVICE_ECI_PLATINUM',
		'INTL_SERVICE_ECI_M',
		'INTL_SERVICE_ECI_D',
		'INTL_SERVICE_EPI',
		'INTL_SERVICE_PTI',
		'INTL_SERVICE_RPI',
		'INTL_SERVICE_AIR_MAIL',
		'INTL_SERVICE_SEA_MAIL',
		'INT_PARCEL_STD_OWN_PACKAGING'
	);

	var $world_shipping_new_types = array(
		'INTL_SERVICE_ECI_PLATINUM' => 'INT_PARCEL_COR_OWN_PACKAGING',
		'INTL_SERVICE_ECI_M' => 'INT_PARCEL_EXP_OWN_PACKAGING',
		'INTL_SERVICE_ECI_D' => 'INT_PARCEL_EXP_OWN_PACKAGING',
		'INTL_SERVICE_EPI' => 'INT_PARCEL_STD_OWN_PACKAGING',
		'INTL_SERVICE_PTI' => 'INT_PARCEL_STD_OWN_PACKAGING',
		'INTL_SERVICE_RPI' => 'INT_PARCEL_STD_OWN_PACKAGING',
		'INTL_SERVICE_AIR_MAIL' => 'INT_PARCEL_AIR_OWN_PACKAGING',
		'INTL_SERVICE_SEA_MAIL' => 'INT_PARCEL_SEA_OWN_PACKAGING'
	);
	function processPackageLimit($limit_key, $limit_value, $product, $qty, $package, $units) {
		switch ($limit_key) {
			case 'volume':
				$divide = (float)($product['x'] * $product['y'] * $product['z']);
				if(empty($divide) || $divide > $limit_value)
					return false;
				$current_limit_value = max(0.0, $limit_value - (float)($package['x'] * $package['y'] * $package['z']));
				return (int)floor($current_limit_value / $divide);
				break;
			case 'girth':
				$divide = (float)(($product['x'] + $product['y']) * 2);
				if(empty($divide) || $divide > $limit_value)
					return false;
				$current_limit_value = max(0.0, $limit_value - (float)(($package['x'] + $package['y']) * 2));
				return (int)floor($current_limit_value / $divide);
				break;
		}
		return parent::processPackageLimit($limit_key, $limit_value , $product, $qty, $package, $units);
	}

	function onShippingDisplay(&$order,&$dbrates,&$usable_rates,&$messages) {

		if($this->loadShippingCache($order, $usable_rates, $messages))
			return true;

		$local_usable_rates = array();
		$local_messages = array();
		$ret = parent::onShippingDisplay($order, $dbrates, $local_usable_rates, $local_messages);
		if($ret === false)
			return false;

		if(!function_exists('curl_init')) {
			$app = JFactory::getApplication();
			$app->enqueueMessage('The AUPOST shipping plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}
		$cache_usable_rates = array();
		$cache_messages = array();

		$currentCurrencyId = null;
		foreach($local_usable_rates as $rate) {
			if(!empty($rate->shipping_zone_namekey)) {
				if(empty($rate->shipping_params->SEA) && empty($rate->shipping_params->AIR) && !empty($order->shipping_address->address_country)) {
					$db = JFactory::getDBO();
					if(is_array($order->shipping_address->address_country)) {
						$address_country = reset($order->shipping_address->address_country);
					} else {
						$address_country = $order->shipping_address->address_country;
					}
					$db->setQuery('SELECT * FROM '.hikashop_table('zone').' WHERE zone_namekey='.$db->Quote($address_country));
					$currentShippingZone = $db->loadObject();

					if($currentShippingZone->zone_code_3 != 'AUS') {
						$messages['no_shipping_to_your_zone'] = JText::_('NO_SHIPPING_TO_YOUR_ZONE');
						continue;
					}
				}
			}

			$check = false;

			if(empty($order->shipping_address->address_post_code)) {
				$check = true;
				$message = 'The Australia Post shipping plugin requires the user to enter a postal code when goods are shipped within Australia. Please go to "Display->Custom fields" and set the post code field to required.';
			} elseif(!preg_match('#[0-9]{4}#',$order->shipping_address->address_post_code)) {
				$check = true;
				$message = 'The post code entered is not valid';
				$order->shipping_address->address_post_code = preg_replace('#[^0-9A-Z]#','',$order->shipping_address->address_post_code);
			}
			if($check) {
				$zoneClass=hikashop_get('class.zone');
				$zones = $zoneClass->getOrderZones($order);
				$db = JFactory::getDBO();
				$db->setQuery('SELECT zone_namekey FROM '.hikashop_table('zone').' WHERE zone_code_3='.$db->Quote('AUS'));
				$australia_zone = $db->loadResult();
				if(in_array($australia_zone,$zones)) {
					$cache_messages['post_code_missing'] = $message;
					continue;
				}
			}
			if(empty($order->shipping_address_full)) {
				$cart = hikashop_get('class.cart');
				if(isset($order->shipping_address->address_id))
					$cart->loadAddress($order->shipping_address_full, $order->shipping_address->address_id, 'object', 'shipping');
			}
			$rates = array();

			$this->getRates($rate, $order, $rates, $cache_messages);

			if(!empty($rate->shipping_params->reverse_order)) {
				$rates=array_reverse($rates,true);
			}
			foreach($rates as $finalRate) {
				$usable_rates[$finalRate->shipping_id]=$finalRate;
				$cache_usable_rates[$finalRate->shipping_id] = $finalRate;
			}
		}
		$this->setShippingCache($order, $cache_usable_rates, $cache_messages);

		if(!empty($cache_messages)) {
			foreach($cache_messages as $k => $msg) {
				$messages[$k] = $msg;
			}
		}
		return true;
	}
	function getRates($rate, $order, &$rates, &$messages) {
		$weightClass=hikashop_get('helper.weight');
		$volumeClass=hikashop_get('helper.volume');
		$limit = array();
		if(!empty($order->shipping_address_full->shipping_address->address_country_code_2)) {
			$country = $order->shipping_address_full->shipping_address->address_country_code_2;
		} elseif(!empty($order->shipping_address_full->shipping_address->address_country->zone_code_2)) {
			$country = $order->shipping_address_full->shipping_address->address_country->zone_code_2;
		} else {
			$country='AU';
		}
		$domestic = 1;
		if($country == 'AU') {
			$limit['w'] = 22;
			$limit['volume'] = 250000;
			$limit['x'] = 105;
		} else {
			$domestic = 0;
			$limit['w'] = 20;
			$limit['girth'] = 140;
			$limit['x'] = 105;
		}
		if(!isset($rate->shipping_params->shipping_group) || !$rate->shipping_params->shipping_group)
			$limit['unit'] = 1;

		if(empty($currentCurrencyId)) {
			$query = 'SELECT currency_id FROM '.hikashop_table('currency').' WHERE currency_code=\'AUD\'';
			$db = JFactory::getDBO();
			$db->setQuery($query);
			$currentCurrencyId = $db->loadResult();
		}
		$parcel=new stdClass();
		$parcel->Weight = 0;
		$parcels = array($parcel);
		$i=0;

		$packages = $this->getOrderPackage($order, array('weight_unit' => 'kg', 'volume_unit' => 'cm', 'limit' => $limit, 'required_dimensions' => array('w','x','y','z')));

		if(empty($packages))
			return true;

		if((!$domestic && isset($packages['w'])) || ($domestic && isset($packages['x']) && isset($packages['y']) && isset($packages['z']))) {
			if(empty($parcels[$i]))
				$parcels[$i] = new stdClass();
			$parcels[$i]->Weight = $packages['w'];
			if($domestic) {
				$parcels[$i]->Width = $packages['z'];
				$parcels[$i]->Height = $packages['y'];
				$parcels[$i]->Length = $packages['x'];

				if($parcels[$i]->Length<1.5)$parcels[$i]->Length=1.5;
				if($parcels[$i]->Width<1.5)$parcels[$i]->Width=1.5;
				if($parcels[$i]->Height<0.1)$parcels[$i]->Height=0.1;
			}

			if($parcels[$i]->Weight<0.001) $parcels[$i]->Weight=0.001;

			$i++;
		} else {
			foreach($packages as $package) {
				if((!$domestic && isset($package['w'])) || ($domestic && isset($package['x']) && isset($package['y']) && isset($package['z']))) {
					if(empty($parcels[$i]))
						$parcels[$i] = new stdClass();
					$parcels[$i]->Weight = $package['w'];
					if($domestic) {
						$parcels[$i]->Width = $package['z'];
						$parcels[$i]->Height = $package['y'];
						$parcels[$i]->Length = $package['x'];

						if($parcels[$i]->Length<1.5)$parcels[$i]->Length=1.5;
						if($parcels[$i]->Width<1.5)$parcels[$i]->Width=1.5;
						if($parcels[$i]->Height<0.1)$parcels[$i]->Height=0.1;
					}

					if($parcels[$i]->Weight<0.001) $parcels[$i]->Weight=0.001;

					$i++;
				}
			}
		}

		foreach($parcels as $parcel) {
			$parcel->country_code = $country;
			$service_types = array();
			if($domestic) {
				$parcel->from_postcode = substr(trim(@$rate->shipping_params->post_code),0,4);
				$parcel->to_postcode = substr(trim($order->shipping_address->address_post_code),0,4);
			}

			$this->addRate($rates,$parcel,$rate,$currentCurrencyId, $i, $messages);
		}
	}
	function onShippingConfigurationSave(&$element) {
		$app = JFactory::getApplication();

		if(empty($element->shipping_params->post_code))
			$app->enqueueMessage(JText::sprintf('ENTER_INFO', 'Australia POST', JText::_('POST_CODE')));
		if(empty($element->shipping_params->api_key))
			$app->enqueueMessage(JText::sprintf('ENTER_INFO', 'Australia POST', JText::_('FEDEX_API_KEY')));
		if (!isset($element->shipping_params->services))
			$app->enqueueMessage(JText::sprintf('CHOOSE_SHIPPING_SERVICE'));

		foreach($this->shipping_types as $service_name => $service_id){
			$element->shipping_params->$service_name = isset($element->shipping_params->services) && in_array($service_name,$element->shipping_params->services);
		}

		parent::onShippingConfigurationSave($element);
	}
	function getShippingDefaultValues(&$element) {
		$element->shipping_name = 'Australia Post';
		$element->shipping_description = '';
		$element->shipping_images = 'aupost';

		foreach($this->shipping_types as $service_name => $service_id){
			$element->shipping_params->$service_name = $service_name;
		}
		$element->shipping_params->post_code = '';
		$element->shipping_params->api_key = '';
		$elements = array($element);
	}
	function onShippingConfiguration(&$element) {
		$this->aupost2 = hikaInput::get()->getCmd('name','aupost2');
		$this->categoryType = hikashop_get('type.categorysub');
		$this->categoryType->type = 'tax';
		$this->categoryType->field = 'category_id';

		parent::onShippingConfiguration($element);
	}
	function addRate(&$rates,$parcel,&$rate,$currency, $nb_package, &$messages) {
		if(empty($nb_package))
			$nb_package = 1;

		$service_types = array();
		$app = JFactory::getApplication();

		if($parcel->country_code=='AU') {
			foreach($this->local_shipping_types as $local_shipping_type){
				if(!empty($rate->shipping_params->$local_shipping_type))
					$service_types[] = $local_shipping_type;
			}

			$queryParams = array(
				"from_postcode" => $parcel->from_postcode,
				"to_postcode" => $parcel->to_postcode,
				"length" => $parcel->Length,
				"width" => $parcel->Width,
				"height" => $parcel->Height,
				"weight" => $parcel->Weight
			);

			$urlPrefix = 'digitalapi.auspost.com.au/postage';
			$postageTypesURL = 'https://' . $urlPrefix . '/parcel/domestic/service.json?' . http_build_query($queryParams);
		} else {
			foreach($this->world_shipping_types as $world_shipping_type){
				if(!empty($rate->shipping_params->$world_shipping_type)){
					$service_types[] = $world_shipping_type;
					if(!in_array($this->world_shipping_new_types[$world_shipping_type],$service_types))
						$service_types[] = $this->world_shipping_new_types[$world_shipping_type];
				}
			}

			$queryParams = array(
				"country_code" => $parcel->country_code,
				"weight" => $parcel->Weight
			);

			$urlPrefix = 'digitalapi.auspost.com.au/postage';
			$postageTypesURL = 'https://' . $urlPrefix . '/parcel/international/service.json?' . http_build_query($queryParams);
		}

		$session = curl_init();
		curl_setopt($session, CURLOPT_URL, $postageTypesURL);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($session, CURLOPT_HTTPHEADER, array('AUTH-KEY: ' . $rate->shipping_params->api_key));
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($session, CURLOPT_SSL_VERIFYHOST,  2);
		$rawBody = curl_exec($session);

		if(!$rawBody) {
			$app->enqueueMessage('Error: "' . curl_error($session) . '" - Code: ' . curl_errno($session));
			return false;
		}

		$serviceTypesJSON = json_decode($rawBody);

		if(isset($serviceTypesJSON->services->service) && !empty($serviceTypesJSON->services->service)) {
			$data = array();
			$available_service = array();
			if(is_object($serviceTypesJSON->services->service))
				$available_service[] = $serviceTypesJSON->services->service;
			else
				$available_service = $serviceTypesJSON->services->service;

			foreach($available_service as $key => $service) {
				if(isset($service->code) && in_array($service->code, $service_types) && isset($service->price)) {
					$service_code = $service->code;
					foreach($this->world_shipping_new_types as $old_type => $new_type){
						if($service->code == $new_type && in_array($old_type, $service_types)){
							$service_code = $old_type;
							break;
						}
					}

					if(empty($rates[$service_code])) {
						$info = new stdClass();
						$info = clone($rate);
						$shipping_name = JText::_($service_code.'_NAME');
						if($shipping_name != $service_code.'_NAME')
							$info->shipping_name .=' '.$shipping_name;
						else
							$info->shipping_name .=' '.$service->name;
						if (!empty($rate->shipping_description))
							$info->shipping_description = $rate->shipping_description . ' ';
						else {
							$shipping_description = JText::_($service_code.'_DESCRIPTION');
							if($shipping_description != $service_code.'_DESCRIPTION')
								$info->shipping_description .= $shipping_description;
						}
						$info->shipping_id .= '-' . $this->shipping_types[$service_code];
						$rates[$service_code]=$info;
					} else {
						$shipping_description = JText::_($service_code.'_DESCRIPTION');
						if($shipping_description == $service_code.'_DESCRIPTION'){ $shipping_description = ''; }
						if(empty($shipping_description)){ $shipping_description = $rate->shipping_description; }
						if(!empty($shipping_description)){ $shipping_description .= '<br/>'; }
						if($nb_package > 1 && (isset($rate->shipping_params->shipping_group) && $rate->shipping_params->shipping_group)) $rates[$service_code]->shipping_description = $shipping_description . JText::sprintf('X_PACKAGES', $nb_package);
						else $rates[$service_code]->shipping_description = $shipping_description;
					}
					if(@$rates[$service_code]->shipping_tax_id) {
						$currencyClass = hikashop_get('class.currency');
						$service->price = $currencyClass->getUntaxedPrice($service->price,hikashop_getZone(),$rates[$service_code]->shipping_tax_id);
					}
					$rates[$service_code]->shipping_price += $service->price;
					$rates[$service_code]->shipping_currency_id = $currency;
				}
			}
		} else {
			if(isset($serviceTypesJSON->error->errorMessage) && !empty($serviceTypesJSON->error->errorMessage)) {
				$messages['aupostv2_error_message'] = $serviceTypesJSON->error->errorMessage;
				return false;
			}
		}
	}
	function shippingMethods(&$main) {
		$methods = array();

		foreach($this->shipping_types as $value => $key){
			if(!empty($main->shipping_params->$value))
				$methods[$main->shipping_id.'-'.$key] = $this->shipping_names[$value];
		}

		return $methods;
	}
}
