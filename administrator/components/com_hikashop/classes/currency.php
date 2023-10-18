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
class hikashopCurrencyClass extends hikashopClass{
	var $tables = array('currency');
	var $pkeys = array('currency_id');
	var $namekeys = array('');
	var $toggle = array('currency_published'=>'currency_id','currency_displayed'=>'currency_id');
	var $taxRates = null;

	var $_loadedVariants = array();

	function round($price, $round = 2, $increment = 0, $force = false) {
		$config =& hikashop_config();
		if(!$config->get('round_calculations', 0) && !$force)
			return $price;

		if(is_array($round)) {
			$increment = $round['increment'];
			$round = $round['rounding'];
		}
		if($increment) {
			return $this->roundByIncrement($price, $increment);
		}
		return round($price, $round);
	}

	function _round($price, $round = 2, $increment = 0) {
		return $this->round($price, $round, $increment);
	}

	function roundByIncrement($number, $increment) {
		$increment = (float)(1 / $increment);
		return (round((float)$number * $increment) / $increment);
	}

	function get($element, $default = '') {
		if(is_numeric($element)) {
			$data = parent::get($element);
		} else {
			$db = JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('currency').' WHERE currency_code = '.$db->Quote($element);
			$db->setQuery($query);
			$data = $db->loadObject();
		}
		$this->checkLocale($data);
		return $data;
	}

	function getTaxedPrice(&$price, $zone_id, $tax_category_id, $round = 2, $float = null) {
		if(is_array($tax_category_id)) {
			$this->taxRates = $tax_category_id;
		} else {
			$this->taxRates = array();
			$taxRate = (float)$this->getTax($zone_id, $tax_category_id);
			if(empty($this->taxRates))
				return $this->round($price,$round);
		}

		$tax = 0.0;
		if(is_null($float)) {
			$config =& hikashop_config();
			$float = $config->get('floating_tax_prices', 0);
		}
		if(!$float) {
			$float_price = (float)$price;
			if(!empty($this->taxRates)) {
				foreach($this->taxRates as $k => $rate) {
					if(empty($rate->tax_ratio))
						$rate->tax_ratio = 1;
					$this->taxRates[$k]->tax_amount = $this->round($float_price * $rate->tax_ratio * floatval($rate->tax_rate), $round);
					$this->taxRates[$k]->amount = $this->round($float_price * $rate->tax_ratio, $round);
					$tax += $this->taxRates[$k]->tax_amount;
				}
			}
			$taxedPrice = $this->round($float_price + $this->round($tax, $round), $round);
		} else {
			$taxedPrice = (float)$price;
			$float_price = $taxedPrice;
			if(!empty($this->taxRates)) {
				foreach($this->taxRates as $k => $rate) {
					if(empty($rate->tax_ratio))
						$rate->tax_ratio = 1;
					$this->taxRates[$k]->tax_amount = $this->round($float_price * $rate->tax_ratio * floatval($rate->tax_rate) / (1.00000 + floatval($rate->tax_rate)), $round);
					$this->taxRates[$k]->amount = $this->round($float_price * $rate->tax_ratio, $round);
					$tax += $this->taxRates[$k]->tax_amount;
				}
			}
			$price = $this->round($float_price - $this->round($tax, $round), $round);
		}
		return $taxedPrice;
	}

	function getUntaxedPrice(&$price, $zone_id, $tax_category_id, $round = 2) {
		if(is_array($tax_category_id)) {
			$this->taxRates = $tax_category_id;
		} else {
			$this->taxRates = array();
			$taxRate = (float)$this->getTax($zone_id, $tax_category_id);
			if(empty($taxRate))
				return $this->round($price, $round);
		}

		$float_price = (float)$price;
		$tax = 0.0;
		if(!empty($this->taxRates)) {
			foreach($this->taxRates as $k => $rate) {
				if(empty($rate->tax_ratio))
					$rate->tax_ratio = 1;
				$this->taxRates[$k]->tax_amount = $this->round($float_price * $rate->tax_ratio * floatval($rate->tax_rate) / (1.00000 + floatval($rate->tax_rate)), $round);
				$this->taxRates[$k]->amount = $this->round($float_price * $rate->tax_ratio - $this->taxRates[$k]->tax_amount, $round);
				$tax += $this->taxRates[$k]->tax_amount;
			}
		}
		$untaxedPrice = $this->round($float_price - $this->round($tax, $round), $round);

		return $untaxedPrice;
	}

	function getTaxType($reset = false) {
		static $taxType = '';
		if(!empty($taxType) && empty($reset))
			return $taxType;

		$config =& hikashop_config();
		$taxType = $config->get('default_type','individual');

		$addressClass = hikashop_get('class.address');
		$user_address = $addressClass->getCurrentUserAddress($config->get('tax_zone_type', 'shipping'), null);
		if(empty($user_address))
			return $taxType;

		$address = is_object($user_address) ? $user_address : $addressClass->get($user_address);

		if(!empty($address->address_company)) {
			$taxType = 'company_without_vat_number';
		}
		if(!empty($address->address_vat)) {
			$vatHelper = hikashop_get('helper.vat');
			if($vatHelper->isValid($address))
				$taxType = 'company_with_vat_number';
		}

		return $taxType;
	}

	function getTax($zone_id, $tax_category_id, $type = '') {
		static $calculated = array();
		static $calculatedFullInfo = array();

		if(empty($tax_category_id))
			return 0;

		if(empty($zone_id)) {
			$zone_id = $this->mainTaxZone();
			if(empty($zone_id)) {
				return 0;
			}
		}

		if(empty($type)) {
			$type = $this->getTaxType();
		}

		$taxPlans = array();
		while ( empty( $taxPlans) && !empty( $tax_category_id)) {
			$key = $zone_id.'_'.$tax_category_id.'_'.$type;
			if(!isset($calculated[$key])){
				$filter = '';
				switch($type){
					default:
						$filter = '(taxation_type = '.$this->database->Quote($type).' OR taxation_type LIKE \'%'.hikashop_getEscaped($type,true).'%\')';
					case '':
						$typeFilter = 'taxation_type = \'\'';
						if(!empty($filter)){
							$typeFilter = '( '.$typeFilter.' OR '.$filter.' )';
						}
						break;
				}
				$filters = array(
					'a.category_id = '.(int)$tax_category_id,
					'b.taxation_published=1',
					$typeFilter,
					'b.taxation_date_start <= '.time(),
					'(b.taxation_date_end = 0 OR b.taxation_date_end > '.time().')'
				);
				hikashop_addACLFilters($filters,'taxation_access','b');
				$query = 'SELECT b.*,c.* FROM '.hikashop_table('category'). ' AS a '.
						'LEFT JOIN '.hikashop_table('taxation').' AS b ON a.category_namekey=b.category_namekey '.
						'LEFT JOIN '.hikashop_table('tax').' AS c ON b.tax_namekey=c.tax_namekey WHERE '.implode(' AND ',$filters).' ORDER BY b.taxation_id ASC';
				$this->database->setQuery($query);
				$taxPlans = $this->database->loadObjectList('taxation_id');
				if ( empty( $taxPlans)) {
					$query = 'SELECT category_parent_id FROM '.hikashop_table('category').' WHERE category_id = '.(int)$tax_category_id;
					$this->database->setQuery($query);
					$category_parent_id = $this->database->loadResult();
					if ( !empty( $category_parent_id)) {
						$tax_category_id = $category_parent_id;
					}
					else {
						break;
					}
				}
			}
			else {
				break;
			}
		}

		if(!isset($calculated[$key])){
			$query = 'SELECT * FROM '.hikashop_table('zone').' WHERE zone_id = '.(int)$zone_id;
			$this->database->setQuery($query);
			$zone = $this->database->loadObject();

			$quotedTaxNamekeys = array();
			$this->taxRates = array();
			$tax = 0;
			if(!empty($taxPlans) && !empty($zone)){
				$matches = array();
				$cumulative = false;
				$already = array($zone->zone_id=>$zone);
				foreach($taxPlans as $taxPlan){
					if(empty($taxPlan->zone_namekey)) continue;

					$taxPlan->zone_namekey = explode(',',$taxPlan->zone_namekey);
					foreach($taxPlan->zone_namekey as $zone_namekey){
						$quotedTaxNamekeys[]=$this->database->Quote($zone_namekey);
					}
					if(in_array($zone->zone_namekey,$taxPlan->zone_namekey) && $this->_matchPostCode($taxPlan)){
						$taxPlan->zone_type = $zone->zone_type;
						$matches[$taxPlan->taxation_id]=$taxPlan;
						if(!empty($taxPlan->taxation_cumulative)) $cumulative = true;
					}
				}

				if(count($quotedTaxNamekeys) && (count($matches)==0 || $cumulative)){
					$childs = array($this->database->Quote($zone->zone_namekey));
					$this->_getParents($childs,$matches,$already,$quotedTaxNamekeys,$taxPlans);
				}

				JPluginHelper::importPlugin('hikashop');
				$app = JFactory::getApplication();
				$obj =& $this;
				$app->triggerEvent('onHikashopGetTax', array( &$obj, $zone_id, $tax_category_id, $type, &$matches, &$taxPlans));

				if(count($matches)!=0){
					$type = 'state';
					$types=array('country','tax');
					$found=false;
					while(!$found){
						foreach($matches as $match){
							if($match->zone_type==$type){
								$tax += floatval(@$match->tax_rate);
								$this->taxRates[sprintf('%04d', $match->taxation_ordering).'_'.sprintf('%08d', $match->taxation_id)]=$match;
								if(empty($match->taxation_cumulative)){
									$found = true;
									break;
								}
							}
						}
						if(!$found){
							if(empty($types)){
								$found = true;
								break;
							}
							$type = array_shift($types);
						}
					}

				}else{
					foreach($taxPlans as $taxPlan){
						if(!empty($taxPlan->zone_namekey)) continue;
						if(!$this->_matchPostCode($taxPlan)) continue;
						if($taxPlan->taxation_cumulative){
							$this->taxRates[sprintf('%04d', $taxPlan->taxation_ordering).'_'.sprintf('%08d', $taxPlan->taxation_id)]=$taxPlan;
							$tax += floatval(@$taxPlan->tax_rate);
						}else{
							$this->taxRates=array(sprintf('%04d', $taxPlan->taxation_ordering).'_'.sprintf('%08d', $taxPlan->taxation_id) => $taxPlan);
							$tax = floatval(@$taxPlan->tax_rate);
						}
					}
				}
			}
			ksort($this->taxRates);
			$calculated[$key]=$tax;
			$calculatedFullInfo[$key]=array();
			foreach($this->taxRates as $k => $taxRate){
				$calculatedFullInfo[$key][$k]=clone($taxRate);
			}
		}else{
			$this->taxRates=array();
			foreach($calculatedFullInfo[$key] as $k => $taxRate){
				$this->taxRates[$k]=clone($taxRate);
			}
		}
		return $calculated[$key];
	}

	function _matchPostCode(&$taxPlan) {
		if(empty($taxPlan->taxation_post_code))
			return true;

		$config =& hikashop_config();
		$addressClass = hikashop_get('class.address');

		$address_type = $config->get('tax_zone_type', 'shipping');
		$user_address = $addressClass->getCurrentUserAddress($address_type, null);
		if(empty($user_address))
			return false;

		$address = $addressClass->get($user_address);
		if(empty($address->address_post_code))
			return false;

		if(!preg_match('#' . preg_replace('#[^a-z0-9 \-\*\[\]\?\{\}]#', '', $taxPlan->taxation_post_code) . '#i', $address->address_post_code))
			return false;
		return true;
	}

	function mainTaxZone() {
		static $main_tax_zone = 0;
		if(!$main_tax_zone){
			$config =& hikashop_config();
			$main_tax_zone = explode(',', $config->get('main_tax_zone', ''));
			if(count($main_tax_zone)) {
				$main_tax_zone = array_shift($main_tax_zone);
			}
		}
		return $main_tax_zone;
	}

	function getTaxCategory() {
		static $found = null;
		if($found !== null)
			return $found;
		$this->database->setQuery('SELECT category_id FROM '.hikashop_table('category').' WHERE category_type=\'tax\' AND category_published=1 AND category_namekey!=\'tax\' ORDER BY category_id ASC');
		$found = (int) $this->database->loadResult();
		return $found;
	}

	function displayPrices($prices, $value_field = 'price_value', $currency_id_field = 'price_currency_id') {
		if(empty($prices))
			return '';
		$p = array();
		foreach($prices as $price) {
			if((int)@$price->price_min_quantity > 1)
				continue;
			if(is_array($currency_id_field)){
				if(is_string($price->{$currency_id_field[0]})){
					$params = hikashop_unserialize($price->{$currency_id_field[0]});
				}else{
					$params = $price->{$currency_id_field[0]};
				}
				$currency_id = $params->{$currency_id_field[1]};
			}else{
				$currency_id = $price->$currency_id_field;
			}
			$p[] = $this->format($price->$value_field, $currency_id);
		}
		if(!count($p)){
			foreach($prices as $price) {
				$p[] = $this->format($price->$value_field, $price->$currency_id_field).JText::sprintf('PER_UNIT_AT_LEAST_X_BOUGHT',$price->price_min_quantity);
			}
		}
		return implode(' / ', $p);
	}

	function _getParents(&$childs,&$matches,&$already,&$quotedTaxNamekeys,&$taxPlans){
		$namekeys = array();
		foreach($already as $zone){
			$namekeys[] = $this->database->Quote($zone->zone_namekey);
		}
		$query = 'SELECT b.* FROM '.hikashop_table('zone_link').' AS a '.
				' LEFT JOIN '.hikashop_table('zone').' AS b ON a.zone_parent_namekey=b.zone_namekey '.
				' WHERE a.zone_child_namekey IN ('.implode(',',$childs).') AND a.zone_parent_namekey NOT IN ('.implode(',',$namekeys).') AND (b.zone_type IN(\'state\',\'country\') OR ( b.zone_type=\'tax\' AND b.zone_namekey IN ('.implode(',',$quotedTaxNamekeys).') ))';
		$this->database->setQuery($query);
		$parents = $this->database->loadObjectList('zone_id');
		$childs = array();
		$already = array_merge($already,$parents);
		foreach($parents as $parent){
			$found = false;
			foreach($taxPlans as $taxPlan){
				if(empty($taxPlan->zone_namekey) || !in_array($parent->zone_namekey,$taxPlan->zone_namekey)) continue;

				if(!isset($matches[$taxPlan->taxation_id]) && $this->_matchPostCode($taxPlan)){
					$taxPlan->zone_type = $parent->zone_type;
					$matches[$taxPlan->taxation_id]=$taxPlan;
				}
				$found = true;
			}
			if(!$found){
				$childs[]=$this->database->Quote($parent->zone_namekey);
			}
		}
		if(!empty($childs)){
			$this->_getParents($childs,$matches,$already,$quotedTaxNamekeys,$taxPlans);
		}
	}



	function saveForm(){
		$currency = new stdClass();
		$currency->currency_id = hikashop_getCID('currency_id');
		$formData = hikaInput::get()->get('data', array(), 'array' );
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
		foreach($formData['currency'] as $column => $value){
			hikashop_secureField($column);
			if($column=='currency_locale'){
				$tmp = array();

				foreach($value as $key => $val){
					$key = hikashop_secureField($key);
					if($key=='mon_grouping'){
						$tmp[$key] = preg_replace('#[^0-9,]#','',$val);
					}elseif($key=='rounding_increment'){
						$tmp[$key] = (!empty($val)? hikashop_toFloat($val):'');
					}elseif(!in_array($key,array('mon_thousands_sep','mon_decimal_point','negative_sign','positive_sign'))){
						$tmp[$key] = intval($val);
					}else{
						$tmp[$key] = (!empty($val)? $val[0]:'');
					}
				}
				$currency->$column = serialize($tmp);
			}elseif(in_array($column,array('currency_rate','currency_flat_fee','currency_percent_fee'))){
				$currency->$column = hikashop_toFloat($value);
			}else{
				$currency->$column = $safeHtmlFilter->clean($value, 'string');
				if(!in_array($column, array('currency_symbol', 'currency_format'))){
					$currency->$column = strip_tags($currency->$column);
				}
			}
		}

		$status = $this->save($currency);

		if(!$status){
			$currency=new stdClass();
			foreach($formData['currency'] as $column => $value){
				$currency->$column = $value;
			}
			hikaInput::get()->set( 'fail', $currency  );
		}

		return $status;
	}

	function getNamekey($element) {
		return false;
	}

	function mainCurrency() {
		$config =& hikashop_config();
		return $config->get('main_currency', 1);
	}

	function publishedCurrencies() {
		static $list = null;
		if($list !== null)
			return $list;

		$config =& hikashop_config();
		$query ='SELECT currency_id FROM '.hikashop_table('currency').' WHERE currency_published=1 OR currency_id = '.(int) $config->get('main_currency',1);
		$this->database->setQuery($query);
		$list = $this->database->loadColumn();
		return $list;
	}

	function getListingPrices(&$rows, $zone_id, $currency_id, $price_display_type = 'all', $user_id = 0) {
		return $this->getProductsPrices($rows, array('zone_id' => $zone_id, 'currency_id' => $currency_id, 'price_display_type' => $price_display_type, 'user_id' => $user_id));
	}

	function getProductsPrices(&$rows, $options = array()) {
		if(!empty($options['zone_id'])){
			$zone_id = (int)$options['zone_id'];
		}else{
			$zone_id = hikashop_getZone();
		}
		if(!empty($options['currency_id'])){
			$currency_id = (int)$options['currency_id'];
		}else{
			$currency_id = hikashop_getCurrency();
		}
		$price_display_type = 'all';
		if(!empty($options['price_display_type'])){
			$price_display_type = $options['price_display_type'];
		}
		$user_id = 0;
		if(!empty($options['user_id'])){
			$user_id = (int)$options['user_id'];
		}
		$loadDiscounts = !isset($options['no_discounts']) || !empty($options['no_discounts']);

		$ids = array();
		foreach($rows as $key => $row){
			if(!is_null($row->product_id))
				$ids[] = (int)$row->product_id;
		}

		if(empty($ids))
			return true;

		$all_ids = $ids;

		$app = JFactory::getApplication();
		$config = hikashop_config();

		$variant_ids = $this->_loadProductVariants($rows, $ids);
		$all_ids = array_merge($ids, $variant_ids);

		if($loadDiscounts) {
			$product_matches = array('discount_product_id IN (\'\',\'0\',\''.implode('\',\'', $ids) . '\')');
			foreach($all_ids  as $id) {
				$product_matches[] = 'discount_product_id LIKE \'%,'.(int)$id.',%\'';
			}
			$filters = array(
				'discount_type' => 'discount_type=\'discount\'',
				'discount_published' => 'discount_published=1',
				'discount_quota' => '(discount_quota > discount_used_times OR discount_quota = 0)',
				'discount_start' => 'discount_start < '.time(),
				'discount_end' => '(discount_end > '.time().' OR discount_end = 0)',
				'product_match' => '( ('.implode(') OR (' , $product_matches).') )',
				'discount_valid_amount' => '(discount_flat_amount != 0 OR discount_percent_amount != 0)'
			);

			if($config->get('force_discount_currency', 1)){
				$filters['force_currency'] = '(discount_flat_amount = 0 OR discount_currency_id ='.(int)$currency_id.')';
			}
			if(!hikashop_isClient('administrator') || (int)$user_id > 0) {
				hikashop_addACLFilters($filters,'discount_access', '', 2, false, (int)$user_id);
				if(empty($user_id) || (int)$user_id == 0)
					$uid = hikashop_loadUser(false);
				else
					$uid = $user_id;
				$filters['discount_user'] = "(discount_user_id = '' OR discount_user_id LIKE '%,".(int)$uid.",%')";
			}

			$trigger_options = array(
				'ids' => $ids,
				'variant_ids' => $variant_ids,
				'currency_id' => $currency_id,
				'user_id' => $user_id,
				'zone_id' => $zone_id
			);
			JPluginHelper::importPlugin('hikashop');
			$app->triggerEvent('onBeforeLoadProductPriceDiscount', array( &$filters, $rows, $trigger_options ));

			$query = 'SELECT * FROM '.hikashop_table('discount').' WHERE '.implode(' AND ', $filters);
			$this->database->setQuery($query);
			$discounts = $this->database->loadObjectList();

			$app->triggerEvent('onAfterLoadProductPriceDiscount', array( &$discounts, &$rows, $filters, $trigger_options ));
		}

		$now = time();
		$filters = array(
			'a.price_product_id IN ('.implode(',',$ids).')',
			'a.price_currency_id IN ('.implode(',',$this->publishedCurrencies()).')',
			'a.price_start_date < '.$now,
			'(a.price_end_date < 1 OR a.price_end_date > ' . $now . ')'
		);
		if(!hikashop_isClient('administrator') || (int)$user_id > 0) {
			hikashop_addACLFilters($filters,'price_access','a', '', 2, false, (int)$user_id);
			if(empty($user_id) || (int)$user_id == 0)
				$uid = hikashop_loadUser(false);
			else
				$uid = $user_id;
			$filters[] = "(a.price_users = '' OR a.price_users LIKE '%,".(int)$uid.",%')";
		}
		$dir = 'DESC';
		if($price_display_type == 'expensive'){
			$dir = 'ASC';
		}


		JPluginHelper::importPlugin('hikashop');
		$app->triggerEvent('onBeforeLoadProductPrice', array( &$filters, $rows, $options ));

		$query = 'SELECT a.*, CASE WHEN price_site_id = 0 OR price_site_id = \'[unselected]\' THEN \'\' ELSE price_site_id END AS price_site_id FROM '.hikashop_table('price').' AS a WHERE '.implode(' AND ',$filters). ' ORDER BY a.price_site_id ASC, a.price_value '.$dir;
		$this->database->setQuery($query);
		$prices = $this->database->loadObjectList();


		$app->triggerEvent('onAfterLoadProductPrice', array( &$prices, &$rows, $filters, $options ));

		$variantSearch = array();
		$main_currency = (int)$config->get('main_currency', 1);
		$discount_before_tax = (int)$config->get('discount_before_tax', 0);
		foreach($rows as $k => $element) {
			$pricefound = false;
			if(!empty($prices)) {
				$defaultCurrentRowPrices = array();
				$currentRowPrices = array();
				$matches = array();
				foreach($prices as $price) {
					if($price->price_product_id != $element->product_id) {
						continue;
					}
					$defaultCurrentRowPrices[] = $price;

					if($price->price_currency_id != $currency_id) {
						continue;
					}

					if( !empty($price->price_site_id) && $price->price_site_id == '[unselected]') {
						$price->price_site_id = '';
					}

					if($price->price_min_quantity==1)
						$price->price_min_quantity = 0;
					if( !isset($matches[$price->price_min_quantity])) {
						$matches[$price->price_min_quantity] = $price;
					} elseif( empty($matches[$price->price_min_quantity]->price_site_id)) {
						$matches[$price->price_min_quantity] = $price;
					} elseif( $matches[$price->price_min_quantity]->price_site_id == $price->price_site_id) {
						$matches[$price->price_min_quantity] = $price;
					}
					$currentRowPrices[]=$price;
				}
				if( empty($currentRowPrices) && !empty($defaultCurrentRowPrices)) {
					$currentRowPrices = $defaultCurrentRowPrices;
				}
				if(empty($matches)&&!empty($currentRowPrices)) {
					foreach($currentRowPrices as $price) {
						if($price->price_currency_id == $main_currency) {
							$matches[$price->price_min_quantity] = $price;
						}
					}
					if(empty($matches)) {
						$match = array_pop($currentRowPrices);
						if(!empty($currentRowPrices)) {
							foreach($currentRowPrices as $price) {
								if($price->price_currency_id == $match->price_currency_id) {
									$matches[$price->price_min_quantity] = $price;
								}
							}
						}
						$matches[] = $match;
					}
				}

				if(!empty($matches)) {
					$all_loaded = false;
					switch($price_display_type) {
						case 'cheapest':
							$min = 0;
							$minVal = 0;
							foreach($matches as $match) {
								if($match->price_value < $minVal || $minVal == 0) {
									$min = $match;
									$minVal = $match->price_value;
								}
							}

							$pricefound = true;
							$round = $this->getRounding($min->price_currency_id,true);
							$min->price_value_with_tax = $this->getTaxedPrice($min->price_value, $zone_id, $element->product_tax_id, $round);
							$min->taxes = $this->taxRates;
							$rows[$k]->prices = array($min);
							break;
						case 'expensive':
							$max=0;
							$maxVal=0;

							foreach($matches as $match){
								if($match->price_value>$maxVal || $maxVal==0){
									$max = $match;
									$maxVal = $match->price_value;
								}
							}

							$pricefound=true;
							$round = $this->getRounding($max->price_currency_id,true);
							$max->price_value_with_tax = $this->getTaxedPrice($max->price_value,$zone_id,$element->product_tax_id, $round);
							$max->taxes = $this->taxRates;
							$rows[$k]->prices = array($max);
							break;
						case 'unit':
							if(isset($matches[0])) {
								$pricefound = true;
								$round = $this->getRounding($matches[0]->price_currency_id,true);
								$matches[0]->price_value_with_tax = $this->getTaxedPrice($matches[0]->price_value, $zone_id, $element->product_tax_id, $round);
								$matches[0]->taxes = $this->taxRates;
								$rows[$k]->prices = array($matches[0]);
							} else {
								$rows[$k]->prices = array(reset($matches));
							}
							break;
						case 'range':
							$min = 0;
							$minVal = 0;
							$max = 0;
							$maxVal = 0;
							foreach($matches as $match) {
								if($match->price_value > $maxVal || $maxVal == 0) {
									$max = $match;
									$maxVal = $match->price_value;
								}
								if($match->price_value < $minVal || $minVal == 0) {
									$min = $match;
									$minVal = $match->price_value;
								}
							}

							if(empty($min->taxes_added)) {
								$round = $this->getRounding($min->price_currency_id,true);
								$min->price_value_with_tax = $this->getTaxedPrice($min->price_value, $zone_id, $element->product_tax_id, $round);
								$min->taxes = $this->taxRates;
								$min->taxes_added = true;
							}
							if(empty($max->taxes_added)) {
								$round = $this->getRounding($max->price_currency_id,true);
								$max->price_value_with_tax = $this->getTaxedPrice($max->price_value, $zone_id, $element->product_tax_id, $round);
								$max->taxes = $this->taxRates;
								$max->taxes_added = true;
							}

							$pricefound = true;
							if($min->price_value_with_tax == $max->price_value_with_tax) {
								$rows[$k]->prices = array($min);
							} else {
								$rows[$k]->prices = array($min,$max);
							}
							break;
						default:
						case 'all':
							$all_loaded = true;
							foreach($matches as $j => $match) {
								$round = $this->getRounding($match->price_currency_id,true);
								$matches[$j]->price_value_with_tax = $this->getTaxedPrice($match->price_value, $zone_id, $element->product_tax_id, $round);
								$matches[$j]->taxes = $this->taxRates;
							}
							$rows[$k]->prices = $matches;
							break;
					}

					if(!$all_loaded) {
						$rows[$k]->all_prices = $matches;
					} else {
						$rows[$k]->all_prices =& $rows[$k]->prices;
					}
				}
			}
			if(!$pricefound && !empty($element->variant_ids)) {
				$variantSearch = array_merge($variantSearch, $element->variant_ids);
			}
		}

		if(!empty($variantSearch)) {
			$filters = array(
				'price_product_id IN ('.implode(',',$variantSearch).')',
				'price_currency_id IN ('.implode(',',$this->publishedCurrencies()).')',
				'price_start_date < '.$now,
				'(price_end_date < 1 OR price_end_date > ' . $now . ')'
			);
			if(!hikashop_isClient('administrator') || (int)$user_id > 0) {
				hikashop_addACLFilters($filters,'price_access','', '', 2, false, (int)$user_id);
				$filters[] = "(price_users = '' OR price_users LIKE '%,".(int)$user_id.",%')";
			}
			$query = 'SELECT * FROM '.hikashop_table('price').' WHERE '.implode(' AND ',$filters);
			$this->database->setQuery($query);
			$prices = $this->database->loadObjectList();

			if(!empty($prices)) {
				$unset = array();
				foreach($prices as $k => $price) {
					if(empty($price->price_id))
						$unset[] = $k;
					elseif($price->price_min_quantity==1)
						$prices[$k]->price_min_quantity = 0;
				}
				if(!empty($unset)) {
					foreach($unset as $u) {
						unset($prices[$u]);
					}
				}
			}
			if(!empty($prices)) {
				foreach($rows as $k => $element) {
					if(!empty($element->prices))
						continue;
					$currentRowPrices = array();
					$matches = array();
					foreach($prices as $price) {
						if(!empty($element->variant_ids) && in_array($price->price_product_id, $element->variant_ids)) {
							if($price->price_currency_id == $currency_id) {
								$matches[] = $price;
							}
							$currentRowPrices[] = $price;
						}
					}

					if(empty($matches)&&!empty($currentRowPrices)){
						foreach($currentRowPrices as $price){
							if($price->price_currency_id==$main_currency){
								$matches[]=$price;
							}
						}
						if(empty($matches)){
							$match = array_pop($currentRowPrices);
							if(!empty($currentRowPrices)){
								foreach($currentRowPrices as $price){
									if($price->price_currency_id==$match->price_currency_id){
										$matches[]=$price;
									}
								}
							}
							$matches[]=$match;
						}
					}
					$all_loaded = false;
					if(!empty($matches)){
						switch($price_display_type){
							default:
							case 'all':
								$all_loaded = true;
								$found = array();
								foreach($matches as $j => $match){
									if(isset($found[$match->price_value])) continue;
									$found[]=$match->price_value;
									$round = $this->getRounding($match->price_currency_id,true);
									if(empty($this->_loadedVariants[$match->price_product_id]->product_tax_id))
										$this->_loadedVariants[$match->price_product_id]->product_tax_id = $element->product_tax_id;
									$match->price_value_with_tax = $this->getTaxedPrice($match->price_value, $zone_id, $this->_loadedVariants[$match->price_product_id]->product_tax_id, $round);
									$match->taxes = $this->taxRates;
									$found[$match->price_value]=$match;
								}
								$rows[$k]->prices = array_values($found);
								break;
							case 'cheapest':
								$min=0;
								$minVal=0;
								foreach($matches as $match){
									if($match->price_value<$minVal || $minVal==0){
										$min = $match;
										$minVal = $match->price_value;
									}
								}
								$round = $this->getRounding($min->price_currency_id,true);
								if(empty($this->_loadedVariants[$min->price_product_id]->product_tax_id))
									$this->_loadedVariants[$min->price_product_id]->product_tax_id = $element->product_tax_id;
								$min->price_value_with_tax = $this->getTaxedPrice($min->price_value, $zone_id, $this->_loadedVariants[$min->price_product_id]->product_tax_id, $round);
								$min->taxes = $this->taxRates;
								$rows[$k]->prices = array($min);
								break;
							case 'expensive':
								$max=0;
								$maxVal=0;
								foreach($matches as $match){
									if($match->price_value>$maxVal || $maxVal==0){
										$max = $match;
										$maxVal = $match->price_value;
									}
								}
								$round = $this->getRounding($max->price_currency_id,true);
								if(empty($this->_loadedVariants[$max->price_product_id]->product_tax_id))
									$this->_loadedVariants[$max->price_product_id]->product_tax_id = $element->product_tax_id;
								$max->price_value_with_tax = $this->getTaxedPrice($max->price_value, $zone_id, $this->_loadedVariants[$max->price_product_id]->product_tax_id, $round);
								$max->taxes = $this->taxRates;
								$rows[$k]->prices = array($max);
								break;
							case 'unit':
								$found = false;
								foreach($matches as $j => $match){
									if(empty($match->price_min_quantity)){
										$round = $this->getRounding($matches[0]->price_currency_id,true);
										if(empty($this->_loadedVariants[$matches[0]->price_product_id]->product_tax_id))
											$this->_loadedVariants[$matches[0]->price_product_id]->product_tax_id = $element->product_tax_id;
										$matches[0]->price_value_with_tax = $this->getTaxedPrice($matches[0]->price_value, $zone_id, $this->_loadedVariants[$matches[0]->price_product_id]->product_tax_id, $round);
										$matches[0]->taxes = $this->taxRates;
										$rows[$k]->prices = array($matches[0]);
										$found = true;
										break;
									}
								}
								if(!$found){
									$rows[$k]->prices = array(reset($matches));
								}
								break;
							case 'range':
								$min=0;
								$minVal=0;
								$max=0;
								$maxVal=0;
								foreach($matches as $match){
									if($match->price_value>$maxVal || $maxVal==0){
										$max = $match;
										$maxVal = $match->price_value;
									}
									if($match->price_value<$minVal || $minVal==0){
										$min = $match;
										$minVal = $match->price_value;
									}
								}
								$round = $this->getRounding($min->price_currency_id,true);
								if(empty($this->_loadedVariants[$min->price_product_id]->product_tax_id))
									$this->_loadedVariants[$min->price_product_id]->product_tax_id = $element->product_tax_id;
								$min->price_value_with_tax = $this->getTaxedPrice($min->price_value, $zone_id, $this->_loadedVariants[$min->price_product_id]->product_tax_id, $round);
								$min->taxes = $this->taxRates;
								$round = $this->getRounding($max->price_currency_id,true);
								if(empty($this->_loadedVariants[$max->price_product_id]->product_tax_id))
									$this->_loadedVariants[$max->price_product_id]->product_tax_id = $element->product_tax_id;
								$max->price_value_with_tax = $this->getTaxedPrice($max->price_value, $zone_id, $this->_loadedVariants[$max->price_product_id]->product_tax_id, $round);
								$max->taxes = $this->taxRates;
								if($min->price_value_with_tax==$max->price_value_with_tax){
									$rows[$k]->prices = array($min);
								}else{
									$rows[$k]->prices = array($min,$max);
								}
								break;
						}

					}
					if(!$all_loaded) {
						$rows[$k]->all_prices = $matches;
					} else {
						$rows[$k]->all_prices =& $rows[$k]->prices;
					}
				}
			}
		}


		$cids = array();
		if(!empty($rows)) {
			foreach($rows as $k => $row) {
				if(empty($row->prices))
					continue;
				foreach($row->prices as $k2 => $price) {
					if(!empty($price->price_currency_id) && $price->price_currency_id != $currency_id) {
						$cids[$price->price_currency_id] = $price->price_currency_id;
					}
				}
			}
		}

		if(!empty($discounts)) {
			foreach($discounts as $discount) {
				$cids[$discount->discount_currency_id] = $discount->discount_currency_id;
			}
		}

		if(!empty($cids)) {
			if(empty($cids[$currency_id]))
				$cids[$currency_id] = $currency_id;
			if(empty($cids[$main_currency]))
				$cids[$main_currency] = $main_currency;
			hikashop_toInteger($cids);
			$query = 'SELECT * FROM '.hikashop_table('currency').' WHERE currency_id IN ('.implode(',',$cids).')';
			$this->database->setQuery($query);
			$currencies = $this->database->loadObjectList('currency_id');

			foreach($rows as $k => $row) {
				if(!empty($row->prices)) {
					$this->convertPrices($row->prices, $currencies, $currency_id, $main_currency);
				}
			}
			if(!empty($discounts)) {
				$this->convertDiscounts($discounts, $currencies, $currency_id, $main_currency);
				$this->cartDiscountsLeft = array();
				$this->addDiscountToPrices($rows, $discounts, $discount_before_tax, $zone_id);
			}
		}
	}

	function _loadProductVariants(&$products, $ids) {
		if(!empty($products) && empty($ids)){
			$ids = array();
			foreach($products as $product){
				$ids[] = $product->product_id;
			}
		}
		$filters = array(
			'product_parent_id IN (\''.implode('\',\'', $ids).'\')',
			'product_published = 1',
			'product_type = \'variant\'',
			'product_sale_start < '.time(),
			'(product_sale_end > '.time().' OR product_sale_end = 0)',
		);

		$query = 'SELECT product_parent_id, product_id, product_tax_id FROM '.hikashop_table('product').' WHERE '.implode(' AND ', $filters);
		$this->database->setQuery($query);
		$this->_loadedVariants = $this->database->loadObjectList('product_id');

		if(!empty($products)){
			foreach($this->_loadedVariants as $variant){
				foreach($products as $k => $product){
					if($product->product_id == $variant->product_parent_id){
						if(empty($products[$k]->variant_ids))
							$products[$k]->variant_ids = array();
						$products[$k]->variant_ids[] = $variant->product_id;
					}
				}
			}
		}
		return array_keys($this->_loadedVariants);
	}

	function convertUniquePrice($price, $srcCurrency_id, $dstCurrency_id) {
		$config =& hikashop_config();
		$main_currency_id = $config->get('main_currency', 1);

		$currencies = array();
		$ids = array(
			$main_currency_id => $main_currency_id,
			$srcCurrency_id => $srcCurrency_id,
			$dstCurrency_id => $dstCurrency_id
		);

		$currencies = $this->getCurrencies($ids, $currencies);
		$srcCurrency = $currencies[$srcCurrency_id];
		$dstCurrency = $currencies[$dstCurrency_id];
		$mainCurrency = $currencies[$main_currency_id];

		if($srcCurrency_id != $main_currency_id) {
			if(bccomp(sprintf('%F',$srcCurrency->currency_percent_fee), 0, 2)) {
				$price += $price * floatval($srcCurrency->currency_percent_fee) / 100.0;
			}
			if(!empty($srcCurrency->currency_rate) && $srcCurrency->currency_rate > 0.0) {
				$price = floatval($price) / floatval($srcCurrency->currency_rate);
			}
		}
		if($dstCurrency_id != $main_currency_id) {
			$price = floatval($price) * floatval($dstCurrency->currency_rate);
			if(bccomp(sprintf('%F',$dstCurrency->currency_percent_fee), 0, 2)) {
				$price += $price * floatval($dstCurrency->currency_percent_fee) / 100.0;
			}
		}
		$round = $this->getRounding($dstCurrency_id, true);
		return $this->round($price, $round);
	}

	function convertPrices(&$prices,$currencies,$currency_id,$main_currency){
		$unset = array();
		foreach($prices as $k2 => $price){
			if(!empty($price->price_currency_id) && $price->price_currency_id!=$currency_id) {
				if(isset($currencies[$price->price_currency_id]) && isset($currencies[$currency_id]) && isset($currencies[$main_currency])){
					$prices[$k2]->price_orig_value = $price->price_value;
					$prices[$k2]->price_orig_value_with_tax = @$price->price_value_with_tax;
					$prices[$k2]->price_orig_currency_id = $price->price_currency_id;
					$prices[$k2]->price_currency_id = $currency_id;
					if(isset($price->taxes)){
						$prices[$k2]->taxes_orig = $price->taxes;
					}
					$srcCurrency = $currencies[$prices[$k2]->price_orig_currency_id];
					$dstCurrency = $currencies[$currency_id];
					$mainCurrency =  $currencies[$main_currency];
					$prices[$k2]->price_currency_id = $currency_id;
					$prices[$k2]->price_value=floatval($prices[$k2]->price_value);
					$prices[$k2]->price_value_with_tax=floatval(@$prices[$k2]->price_value_with_tax);
					if($srcCurrency->currency_id!=$mainCurrency->currency_id){
						if(bccomp(sprintf('%F',$srcCurrency->currency_percent_fee),0,2)){
							$prices[$k2]->price_value+=$prices[$k2]->price_value*floatval($srcCurrency->currency_percent_fee)/100.0;
							$prices[$k2]->price_value_with_tax+=$prices[$k2]->price_value_with_tax*floatval($srcCurrency->currency_percent_fee)/100.0;
							if(isset($prices[$k2]->taxes)){
								foreach($prices[$k2]->taxes as $k => $tax){
									$prices[$k2]->taxes[$k]->tax_amount+= @$prices[$k2]->taxes[$k]->tax_amount*floatval($srcCurrency->currency_percent_fee)/100.0;
									$prices[$k2]->taxes[$k]->amount+= @$prices[$k2]->taxes[$k]->amount*floatval($srcCurrency->currency_percent_fee)/100.0;
								}
							}
						}
						$prices[$k2]->price_value=$prices[$k2]->price_value/floatval($srcCurrency->currency_rate);
						$prices[$k2]->price_value_with_tax=$prices[$k2]->price_value_with_tax/floatval($srcCurrency->currency_rate);
						if(isset($prices[$k2]->taxes)){
							foreach($prices[$k2]->taxes as $k => $tax){
								$prices[$k2]->taxes[$k]->tax_amount= @$prices[$k2]->taxes[$k]->tax_amount/floatval($srcCurrency->currency_rate);
								$prices[$k2]->taxes[$k]->amount= @$prices[$k2]->taxes[$k]->amount/floatval($srcCurrency->currency_rate);
							}
						}

					}
					if($dstCurrency->currency_id!=$mainCurrency->currency_id){
						$prices[$k2]->price_value=floatval($prices[$k2]->price_value)*floatval($dstCurrency->currency_rate);
						$prices[$k2]->price_value_with_tax=floatval($prices[$k2]->price_value_with_tax)*floatval($dstCurrency->currency_rate);
						if(isset($prices[$k2]->taxes)){
							foreach($prices[$k2]->taxes as $k => $tax){
								$prices[$k2]->taxes[$k]->tax_amount= @$prices[$k2]->taxes[$k]->tax_amount*floatval($dstCurrency->currency_rate);
								$prices[$k2]->taxes[$k]->amount= @$prices[$k2]->taxes[$k]->amount*floatval($dstCurrency->currency_rate);
							}
						}
						if(bccomp(sprintf('%F',$dstCurrency->currency_percent_fee),0,2)){
							$prices[$k2]->price_value+=$prices[$k2]->price_value*floatval($dstCurrency->currency_percent_fee)/100.0;
							$prices[$k2]->price_value_with_tax+=$prices[$k2]->price_value_with_tax*floatval($dstCurrency->currency_percent_fee)/100.0;
							if(isset($prices[$k2]->taxes)){
								foreach($prices[$k2]->taxes as $k => $tax){
									$prices[$k2]->taxes[$k]->tax_amount+= @$prices[$k2]->taxes[$k]->tax_amount*floatval($dstCurrency->currency_percent_fee)/100.0;
									$prices[$k2]->taxes[$k]->amount+= @$prices[$k2]->taxes[$k]->amount*floatval($dstCurrency->currency_percent_fee)/100.0;
								}
							}
						}
					}

				}else {
					$unset[] = $k2;
				}
			}
		}
		if(!empty($unset)){
			foreach($unset as $u){
				unset($prices[$u]);
			}
		}
	}

	public function selectDiscount(&$product, &$discounts, $zone_id, $parent = null) {
		$discountsSelected = array();
		$discountSkippedBecauseOverQuota = false;
		$id = $product->product_id;
		if(!empty($product->product_type) && $product->product_type != 'main' && !empty($product->product_parent_id)) {
			$id = $product->product_parent_id;
		}
		static $zones = array();
		$zoneClass = hikashop_get('class.zone');
		if(empty($zones[$zone_id])) {
			foreach($discounts as $discount) {
				if($discount->discount_zone_id) {
					$zones[$zone_id] = $zoneClass->getZoneParents($zone_id);
					break;
				}
			}
		}

		foreach($discounts as $discount) {
			$value = sprintf('%09.2f', $discount->discount_flat_amount) . '_' . sprintf('%09.4f', $discount->discount_percent_amount).'_'.$discount->discount_id;

			if($discount->discount_zone_id) {
				if(!is_array($discount->discount_zone_id))
					$discount->discount_zone_id = explode(',',trim($discount->discount_zone_id,','));
				if(empty($discount->discount_zone_loaded)) {
					$discount->discount_zone_id = $zoneClass->getZones($discount->discount_zone_id, 'zone_namekey', 'zone_namekey', true);
					$discount->discount_zone_loaded = true;
				}
				if($discount->discount_zone_id && !count(array_intersect($discount->discount_zone_id, $zones[$zone_id]))) {
					continue;
				}
			}
			if(!empty($product->cart_product_quantity) && empty($product->discount)) {
				if(!isset($this->cartDiscountsLeft[$discount->discount_code])) {
					$this->cartDiscountsLeft[$discount->discount_code] = $discount->discount_quota-$discount->discount_used_times;
				}

				if(!empty($discount->discount_quota) && $this->cartDiscountsLeft[$discount->discount_code]<$product->cart_product_quantity) {
					$discountSkippedBecauseOverQuota = true;
					continue;
				}

				$this->cartDiscountsLeft[$discount->discount_code]-=$product->cart_product_quantity;
			}

			if(!empty($discount->discount_product_id)) {
				if(!is_array($discount->discount_product_id))
					$discount->discount_product_id = explode(',', $discount->discount_product_id);

				foreach($product->prices as $k => $price) {
					if(!is_object($price))
						continue;
					if($product->product_id != $price->price_product_id && in_array($price->price_product_id, $discount->discount_product_id))
						$product->prices[$k]->discount = $discountsSelected[0][$value] = $discount;
				}
				if(in_array($product->product_id, $discount->discount_product_id)) {
					$discountsSelected[0][$value] = $discount;
					continue;
				} elseif(!empty($product->product_parent_id) && in_array($product->product_parent_id, $discount->discount_product_id)) {
					$discountsSelected[5][$value] = $discount;
					continue;
				}
			}

			if(empty($discount->discount_product_id) && !empty($discount->discount_category_id)) {
				if(!is_array($discount->discount_category_id))
					$discount->discount_category_id = explode(',',trim($discount->discount_category_id, ','));
				if($discount->discount_category_childs) {
					static $childs = array();
					$key = implode(',',$discount->discount_category_id);
					if(!isset($childs[$key])) {
						if(empty($categoryClass))
							$categoryClass = hikashop_get('class.category');
						$childs[$key] = $categoryClass->getCategories($discount->discount_category_id, 'category_id, category_left, category_right');
						if(!empty($childs[$key])) {
							$categoriesFilters = array();
							foreach($childs[$key] as $category) {
								$categoriesFilters[] = 'category_left >= ' . $category->category_left . ' AND category_right <= ' . $category->category_right;
							}
							if(count($categoriesFilters)) {
								$filters = array();
								$filters[] = '(('.implode(') OR (', $categoriesFilters).'))';
								hikashop_addACLFilters($filters, 'category_access');
								$select = 'SELECT category_id FROM ' . hikashop_table('category') . ' WHERE ' . implode(' AND ',$filters);
								$this->database->setQuery($select);
								$childrenCats = $this->database->loadObjectList();
								$childs[$key] = array_merge($childs[$key], $childrenCats);
							}
						}
					}

					static $products = array();
					$catIds = array();
					foreach($childs[$key] as $cat) {
						if(!empty($cat) && is_object($cat))
							$catIds[] = (int)$cat->category_id;
					}
					$key = implode(',',$catIds);
					if(count($catIds) && !isset($products[$key])) {
						$products[$key] = array();
						$this->database->setQuery('SELECT product_id FROM #__hikashop_product_category WHERE category_id IN ('.$key.')');
						$productsForCategories = $this->database->loadColumn();
						if(!empty($productsForCategories))
							$products[$key] = array_merge($products[$key], $productsForCategories);
						$this->database->setQuery('SELECT product_id FROM #__hikashop_product WHERE product_manufacturer_id IN ('.$key.')');
						$productsForBrands = $this->database->loadColumn();
						if(!empty($productsForBrands))
							$products[$key] = array_merge($products[$key], $productsForBrands);

					}

					if(
						empty($products[$key]) || 
						(
							!in_array($product->product_id,$products[$key]) && 
							(
								empty($product->product_parent_id) || 
								!in_array($product->product_parent_id, $products[$key])
							)
						)
					) {
						continue;
					}
				}
				$categories = $this->_getCategories($id,$discount->discount_category_childs);
				if(!empty($categories)) {
					foreach($categories as $val) {
						if(in_array($val->category_id,$discount->discount_category_id)) {
							$discountsSelected[10][$val->category_depth][$value] = $discount;
							continue;
						}
					}
				}
			}

			if(empty($discount->discount_product_id) && empty($discount->discount_category_id)) {
				$discountsSelected[20][$value] = $discount;
			}
		}
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onSelectDiscount', array(&$product, &$discountsSelected, &$discounts, $zone_id, &$parent));

		if(!empty($discountsSelected)) {
			ksort($discountsSelected);
			$discount = array_shift($discountsSelected);
			if(is_array($discount)) {
				krsort($discount);
				$discount = array_shift($discount);
				if(is_array($discount)) {
					krsort($discount);
					$discount = array_shift($discount);
				}
			}
			$product->discount = hikashop_copy($discount);
		} elseif($discountSkippedBecauseOverQuota) {
		}
	}

	public function convertDiscounts(&$discounts, &$currencies, $currency_id, $main_currency) {
		$unset = array();
		foreach($discounts as $k => $discount) {
			if($discount->discount_currency_id == $currency_id)
				continue;

			if(bccomp(sprintf('%F',$discounts[$k]->discount_flat_amount), 0, 5) == 0) {
				$discounts[$k]->discount_flat_amount = 0;
				continue;
			}

			if(!isset($currencies[$discount->discount_currency_id]) || !isset($currencies[$currency_id]) || !isset($currencies[$main_currency])) {
				$unset[] = $k;
				continue;
			}

			$discounts[$k]->discount_flat_amount_orig = $discounts[$k]->discount_flat_amount;
			$discounts[$k]->discount_currency_id_orig = $discounts[$k]->discount_currency_id;
			$discounts[$k]->discount_currency_id = $currency_id;

			$srcCurrency = $currencies[$discount->discount_currency_id_orig];
			$dstCurrency = $currencies[$currency_id];
			$mainCurrency =  $currencies[$main_currency];

			if($srcCurrency->currency_id != $mainCurrency->currency_id) {
				if(bccomp(sprintf('%F',$srcCurrency->currency_percent_fee), 0, 2)) {
					$discounts[$k]->discount_flat_amount += floatval($discounts[$k]->discount_flat_amount) * floatval($srcCurrency->currency_percent_fee) / 100.0;
				}
				if(bccomp(sprintf('%F',$srcCurrency->currency_rate), 0, 2)) {
					$discounts[$k]->discount_flat_amount = floatval($discounts[$k]->discount_flat_amount) / floatval($srcCurrency->currency_rate);
				}
			}
			if($dstCurrency->currency_id != $mainCurrency->currency_id) {
				$discounts[$k]->discount_flat_amount = floatval($discounts[$k]->discount_flat_amount) * floatval($dstCurrency->currency_rate);
				if(bccomp(sprintf('%F',$dstCurrency->currency_percent_fee), 0, 2)) {
					$discounts[$k]->discount_flat_amount += $discounts[$k]->discount_flat_amount * floatval($dstCurrency->currency_percent_fee) / 100.0;
				}
			}
		}
		if(empty($unset)) {
			foreach($unset as $u) {
				unset($discounts[$u]);
			}
		}
	}

	function convertStats(&$orders){
		$config =& hikashop_config();
		$main_currency = (int)$config->get('main_currency',1);
		$currency_id = hikashop_getCurrency();
		$currencies = array();
		foreach($orders as $k => $order){
			if($order->currency_id!=$currency_id && bccomp(sprintf('%F',$order->total),0,5)){
				$currencies[$order->currency_id]=$order->currency_id;
			}
		}
		if(!empty($currencies)){
			$currencies[$currency_id]=$currency_id;
			$currencies[$main_currency]=$main_currency;
			$null=null;
			$currencies = $this->getCurrencies($currencies,$null);
			$unset = array();

			foreach($orders as $k => $order){
				if($order->currency_id!=$currency_id){
					if(isset($currencies[$order->currency_id]) && isset($currencies[$currency_id]) && isset($currencies[$main_currency])){
						$orders[$k]->total_orig = ($orders[$k]->total);
						$orders[$k]->currency_id_orig = $orders[$k]->currency_id;
						$orders[$k]->currency_id = $currency_id;
						$srcCurrency = $currencies[$order->currency_id_orig];
						$dstCurrency = $currencies[$currency_id];
						$mainCurrency =  $currencies[$main_currency];
						if($srcCurrency->currency_id!=$mainCurrency->currency_id){
							if(bccomp(sprintf('%F',$srcCurrency->currency_percent_fee),0,2)){
								$orders[$k]->total+=$orders[$k]->total*floatval($srcCurrency->currency_percent_fee)/100.0;
							}
							if(bccomp(sprintf('%F',$srcCurrency->currency_rate),0,2)){
								$orders[$k]->total=(floatval($orders[$k]->total)/floatval($srcCurrency->currency_rate));
							}
						}
						if($dstCurrency->currency_id!=$mainCurrency->currency_id){
							$orders[$k]->total=floatval($orders[$k]->total)*floatval($dstCurrency->currency_rate);
							if(bccomp(sprintf('%F',$dstCurrency->currency_percent_fee),0,2)){
								$orders[$k]->total+=$orders[$k]->total*floatval($dstCurrency->currency_percent_fee)/100.0;
							}
						}
					}else{
						$unset[]=$k;
					}
				}
			}
			if(!empty($unset)){
				foreach($unset as $u){
					unset($orders[$u]);
				}
			}
		}
	}

	function convertShippings(&$shippings) {
		$config =& hikashop_config();
		$main_currency = (int)$config->get('main_currency', 1);
		$currency_id = hikashop_getCurrency();
		if(!in_array($currency_id, $this->publishedCurrencies())) {
			$currency_id = $main_currency;
		}
		$currencies = array();
		foreach($shippings as $k => $shipping) {
			if($shipping->shipping_currency_id != $currency_id && bccomp(sprintf('%F',$shipping->shipping_price), 0, 5)) {
				$currencies[$shipping->shipping_currency_id] = $shipping->shipping_currency_id;
			}
		}

		if(empty($currencies))
			return;

		$currencies[$currency_id] = $currency_id;
		$currencies[$main_currency] = $main_currency;
		$null = null;
		$currencies = $this->getCurrencies($currencies,$null);
		$unset = array();

		foreach($shippings as $k => $shipping) {
			if(empty($shipping->shipping_currency_id))
				continue;

			if($shipping->shipping_currency_id == $currency_id)
				continue;

			if(!isset($currencies[$shipping->shipping_currency_id]) || !isset($currencies[$currency_id]) || !isset($currencies[$main_currency])) {
				$unset[] = $k;
				continue;
			}

			if(!isset($shippings[$k]->shipping_params) || !is_object($shippings[$k]->shipping_params)) {
				$shippings[$k]->shipping_params = new stdClass();
			}
			if(!isset($shippings[$k]->shipping_params->shipping_min_price)) {
				$shippings[$k]->shipping_params->shipping_min_price = 0.0;
			}
			if(!isset($shippings[$k]->shipping_params->shipping_max_price)) {
				$shippings[$k]->shipping_params->shipping_max_price = 0.0;
			}

			$shippings[$k]->shipping_price_orig = ($shippings[$k]->shipping_price);
			$shippings[$k]->shipping_params->shipping_min_price_orig = ($shippings[$k]->shipping_params->shipping_min_price);
			$shippings[$k]->shipping_params->shipping_max_price_orig = ($shippings[$k]->shipping_params->shipping_max_price);
			$shippings[$k]->shipping_currency_id_orig = $shippings[$k]->shipping_currency_id;
			$shippings[$k]->shipping_currency_id = $currency_id;
			$srcCurrency = $currencies[$shipping->shipping_currency_id_orig];
			$dstCurrency = $currencies[$currency_id];
			$mainCurrency = $currencies[$main_currency];

			if($srcCurrency->currency_id != $mainCurrency->currency_id) {
				if(bccomp(sprintf('%F',$srcCurrency->currency_percent_fee), 0, 2)) {
					$shippings[$k]->shipping_price += $shippings[$k]->shipping_price * floatval($srcCurrency->currency_percent_fee) / 100.0;
					$shippings[$k]->shipping_params->shipping_min_price += $shippings[$k]->shipping_params->shipping_min_price * floatval($srcCurrency->currency_percent_fee) / 100.0;
					$shippings[$k]->shipping_params->shipping_max_price += $shippings[$k]->shipping_params->shipping_max_price * floatval($srcCurrency->currency_percent_fee) / 100.0;
				}
				if(bccomp(sprintf('%F',$srcCurrency->currency_rate), 0, 2)) {
					$shippings[$k]->shipping_price = (floatval($shippings[$k]->shipping_price) / floatval($srcCurrency->currency_rate));
					$shippings[$k]->shipping_params->shipping_min_price = (floatval($shippings[$k]->shipping_params->shipping_min_price) / floatval($srcCurrency->currency_rate));
					$shippings[$k]->shipping_params->shipping_max_price = (floatval($shippings[$k]->shipping_params->shipping_max_price) / floatval($srcCurrency->currency_rate));
				}
			}

			if($dstCurrency->currency_id != $mainCurrency->currency_id) {
				$shippings[$k]->shipping_price = floatval($shippings[$k]->shipping_price)*floatval($dstCurrency->currency_rate);
				$shippings[$k]->shipping_params->shipping_min_price = floatval($shippings[$k]->shipping_params->shipping_min_price) * floatval($dstCurrency->currency_rate);
				$shippings[$k]->shipping_params->shipping_max_price = floatval($shippings[$k]->shipping_params->shipping_max_price) * floatval($dstCurrency->currency_rate);
				if(bccomp(sprintf('%F',$dstCurrency->currency_percent_fee), 0, 2)) {
					$shippings[$k]->shipping_price += $shippings[$k]->shipping_price*floatval($dstCurrency->currency_percent_fee) / 100.0;
					$shippings[$k]->shipping_params->shipping_min_price += $shippings[$k]->shipping_params->shipping_min_price * floatval($dstCurrency->currency_percent_fee) / 100.0;
					$shippings[$k]->shipping_params->shipping_max_price += $shippings[$k]->shipping_params->shipping_max_price * floatval($dstCurrency->currency_percent_fee) / 100.0;
				}
			}
		}
		if(!empty($unset)) {
			foreach($unset as $u) {
				unset($shippings[$u]);
			}
		}
	}

	function convertPayments(&$payments, $currency_id = 0) {
		$config =& hikashop_config();
		$main_currency = (int)$config->get('main_currency', 1);
		if(empty($currency_id))
			$currency_id = hikashop_getCurrency();
		if(!in_array($currency_id,$this->publishedCurrencies())) {
			$currency_id = $main_currency;
		}
		$currencies = array();
		foreach($payments as $k => $payment) {
			if(isset($payment->payment_params->payment_currency) && @$payment->payment_params->payment_currency != $currency_id)
				$currencies[$payment->payment_params->payment_currency] = $payment->payment_params->payment_currency;
		}

		if(!empty($currencies)){
			$currencies[$currency_id]=$currency_id;
			$currencies[$main_currency]=$main_currency;
			$null=null;
			$currencies = $this->getCurrencies($currencies,$null);
			$unset = array();

			foreach($payments as $k => $payment){
				if(empty($payment->payment_params->payment_currency)){
					continue;
				}
				if($payment->payment_params->payment_currency!=$currency_id){
					if(isset($currencies[$payment->payment_params->payment_currency]) && isset($currencies[$currency_id]) && isset($currencies[$main_currency])){
						$payments[$k]->payment_price_orig = ($payments[$k]->payment_price);
						$payments[$k]->payment_currency_orig = $payments[$k]->payment_params->payment_currency;
						$payments[$k]->payment_params->payment_currency = $currency_id;
						$srcCurrency = $currencies[$payment->payment_currency_orig];
						$dstCurrency = $currencies[$currency_id];
						$mainCurrency =  $currencies[$main_currency];
						if($srcCurrency->currency_id!=$mainCurrency->currency_id){
							if(bccomp(sprintf('%F',$srcCurrency->currency_percent_fee),0,2)){
								$payments[$k]->payment_price+=$payments[$k]->payment_price*floatval($srcCurrency->currency_percent_fee)/100.0;
							}
							if(bccomp(sprintf('%F',$srcCurrency->currency_rate),0,2)){
								$payments[$k]->payment_price=(floatval($payments[$k]->payment_price)/floatval($srcCurrency->currency_rate));
							}
						}
						if($dstCurrency->currency_id!=$mainCurrency->currency_id){
							$payments[$k]->payment_price=floatval($payments[$k]->payment_price)*floatval($dstCurrency->currency_rate);
							if(bccomp(sprintf('%F',$dstCurrency->currency_percent_fee),0,2)){
								$payments[$k]->payment_price+=$payments[$k]->payment_price*floatval($dstCurrency->currency_percent_fee)/100.0;
							}
						}
					}else{
						$unset[]=$k;
					}
				}
			}
			if(!empty($unset)){
				foreach($unset as $u){
					unset($payments[$u]);
				}
			}
		}
	}

	function _getCategories($id, $farAwayParent = false) {
		static $result=array();
		$key = $id . '_' . (int)$farAwayParent;
		if(isset($result[$key]))
			return $result[$key];

		if(!$farAwayParent) {
			$filters = array('a.product_id = '.(int)$id);
			if(!hikashop_isClient('administrator'))
				hikashop_addACLFilters($filters,'category_access', 'b');
			$query = 'SELECT DISTINCT b.category_id, b.category_depth FROM '.
				hikashop_table('product_category').' AS a LEFT JOIN '.
				hikashop_table('category').' AS b ON a.category_id=b.category_id WHERE ('.implode(') AND (',$filters).')';
		} else {
			$filters = array('b.category_right >= a.category_right','c.product_id = '.(int)$id);
			if(!hikashop_isClient('administrator'))
				hikashop_addACLFilters($filters,'category_access', 'b');
			$query = 'SELECT DISTINCT b.category_id, b.category_depth FROM '.hikashop_table('product_category').' AS c LEFT JOIN '.
				hikashop_table('category').' AS a ON c.category_id=a.category_id LEFT JOIN '.
				hikashop_table('category').' AS b ON a.category_left >= b.category_left WHERE ('.implode(') AND (',$filters).')';
		}
		$this->database->setQuery($query);
		$array = $this->database->loadObjectList();

		if(!$farAwayParent) {
			$filters = array('a.product_id = '.(int)$id);
			if(!hikashop_isClient('administrator'))
				hikashop_addACLFilters($filters,'category_access', 'b');
			$query = 'SELECT DISTINCT b.category_id, b.category_depth FROM '.
				hikashop_table('product').' AS a LEFT JOIN '.
				hikashop_table('category').' AS b ON a.product_manufacturer_id=b.category_id WHERE ('.implode(') AND (',$filters).')';
		} else {
			$filters = array('b.category_right >= a.category_right','c.product_id = '.(int)$id);
			if(!hikashop_isClient('administrator'))
				hikashop_addACLFilters($filters,'category_access', 'b');
			$query = 'SELECT DISTINCT b.category_id, b.category_depth FROM '.hikashop_table('product').' AS c LEFT JOIN '.
				hikashop_table('category').' AS a ON c.product_manufacturer_id=a.category_id LEFT JOIN '.
				hikashop_table('category').' AS b ON a.category_left >= b.category_left WHERE ('.implode(') AND (',$filters).')';
		}
		$this->database->setQuery($query);
		$arrayBrands = $this->database->loadObjectList();
		if(!empty($arrayBrands))
			$array = array_merge($array, $arrayBrands);

		$result[$key] = $array;

		return $result[$key];
	}


	function getPrices(&$element, &$ids, $currency_id, $main_currency, $zone_id, $discount_before_tax, $user_id = 0) {
		return $this->getProductPrices($element, $ids, array('zone_id' => $zone_id, 'currency_id' => $currency_id, 'main_currency' => $main_currency, 'discount_before_tax' => $discount_before_tax, 'user_id' => $user_id));
	}


	function getProductPrices(&$element, &$ids, $options) {
		$config = hikashop_config();
		if(!empty($options['zone_id'])){
			$zone_id = (int)$options['zone_id'];
		}else{
			$zone_id = hikashop_getZone();
		}
		if(!empty($options['currency_id'])){
			$currency_id = (int)$options['currency_id'];
		}else{
			$currency_id = hikashop_getCurrency();
		}

		if(!empty($options['main_currency'])){
			$main_currency = (int)$options['main_currency'];
		}else{
			$main_currency = $config->get('main_currency');
		}
		$discount_before_tax = $config->get('discount_before_tax');
		if(!empty($options['discount_before_tax'])){
			$discount_before_tax = $options['discount_before_tax'];
		}
		$user_id = 0;
		if(!empty($options['user_id'])){
			$user_id = (int)$options['user_id'];
		}
		$loadDiscounts = !isset($options['no_discounts']) || !empty($options['no_discounts']);

		$currency_ids = array(
			$currency_id => $currency_id,
			$main_currency => $main_currency
		);
		$now = time();
		$filters = array(
			'p.price_currency_id IN (' . implode(',', $this->publishedCurrencies()) . ')',
			'p.price_start_date < '.$now,
			'(p.price_end_date < 1 OR p.price_end_date > ' . $now . ')'
		);
		$product_matches = array(
			'discount_product_id IN (\'\',\'0\')'
		);

		if(!empty($ids)) {
			$ids_string = array();
			foreach($ids as $id) {
				if(empty($id))
					continue;

				$ids_string[] = (int)$id;
				$product_matches[] = 'discount_product_id LIKE \'%,'.(int)$id.',%\'';
			}
			if(empty($ids_string)) {
				if(empty($element->product_id))
					return false;

				$ids_string = array((int)$element->product_id);
				$ids = array((int)$element->product_id);
			}
			$product_matches[0] = 'discount_product_id IN (\'\',\'0\',\''.implode('\',\'', $ids_string) . '\')';
			$filters[] = 'p.price_product_id IN ('.implode(',', $ids_string).')';
		} else {
			$ids_string = '0';
		}

		$app = JFactory::getApplication();
		if(!hikashop_isClient('administrator') || (int)$user_id > 0)
			hikashop_addACLFilters($filters, 'price_access', 'p', 2, false, (int)$user_id);
		if(empty($user_id) || (int)$user_id == 0)
			$uid = hikashop_loadUser(false);
		else
			$uid = $user_id;
		$filters[] = "(p.price_users = '' OR p.price_users LIKE '%,".(int)$uid.",%')";

		JPluginHelper::importPlugin('hikashop');
		$app->triggerEvent('onBeforeLoadProductPrice', array( &$filters, $element, $options ));

		$query = 'SELECT p.* FROM '.hikashop_table('price').' as p WHERE ('.implode(') AND (', $filters). ') ORDER BY p.price_site_id ASC, p.price_value DESC';
		$this->database->setQuery($query);
		$prices = $this->database->loadObjectList();

		$app->triggerEvent('onAfterLoadProductPrice', array( &$prices, &$element, $filters, $options ));

		if(!empty($prices)) {
			if(is_array($element)) {
				foreach($element as $k => $el) {
					$this->removeAndAddPrices($element[$k], $prices, $currency_ids, $currency_id, $main_currency, $zone_id);
				}
			} else {
				$this->removeAndAddPrices($element, $prices, $currency_ids, $currency_id, $main_currency, $zone_id);
			}
			$uneeded = array();
			foreach($prices as $k => $price) {
				if(empty($price->needed))
					$uneeded[]=$k;
			}
			if(!empty($uneeded)) {
				foreach($uneeded as $k) {
					unset($prices[$k]);
				}
			}
		}

		if($loadDiscounts) {

			$filters = array(
				'discount_type' => 'discount_type = \'discount\'',
				'discount_published' => 'discount_published = 1',
				'discount_quota' => 'discount_quota > discount_used_times OR discount_quota = 0',
				'discount_start' => 'discount_start < '.time(),
				'discount_end' => 'discount_end > '.time().' OR discount_end = 0',
				'product_match' => ''.implode(' OR ', $product_matches).'',
				'discount_valid_amount' => 'discount_flat_amount != 0 OR discount_percent_amount != 0'
			);

			$config = hikashop_config();
			if($config->get('force_discount_currency', 1)) {
				$filters['force_currency'] = '(discount_flat_amount = 0 OR discount_currency_id ='.(int)$currency_id.')';
			}
			if(!hikashop_isClient('administrator') || (int)$user_id > 0)
				hikashop_addACLFilters($filters, 'discount_access', '', 2, false, (int)$user_id);
			if(empty($user_id) || (int)$user_id == 0)
				$uid = hikashop_loadUser(false);
			else
				$uid = $user_id;
			$filters['discount_user'] = "(discount_user_id = '' OR discount_user_id LIKE '%,".(int)$uid.",%')";

			$trigger_options = array(
				'ids' => $ids,
				'variant_ids' => null,
				'currency_id' => $currency_id,
				'user_id' => $user_id,
				'zone_id' => $zone_id
			);
			JPluginHelper::importPlugin('hikashop');
			$app->triggerEvent('onBeforeLoadProductPriceDiscount', array( &$filters, $element, $trigger_options ));

			$query = 'SELECT * FROM '.hikashop_table('discount').' WHERE ('.implode(') AND (',$filters) . ')';
			$this->database->setQuery($query);
			$discounts = $this->database->loadObjectList();

			$app->triggerEvent('onAfterLoadProductPriceDiscount', array( &$discounts, &$element, $filters, $trigger_options ));

			if(!empty($discounts)) {
				foreach($discounts as $discount) {
					if(empty($discount->discount_currency_id))
						continue;
					$currency_ids[$discount->discount_currency_id] = $discount->discount_currency_id;
				}
			}
		}

		$null = null;
		$currencies = $this->getCurrencies($currency_ids, $null);

		$this->convertPrice($element, $currencies, $currency_id, $main_currency);

		if($loadDiscounts && !empty($discounts)) {
			$this->cartDiscountsLeft = array();
			$this->productsDone = array();
			$this->convertDiscounts($discounts, $currencies, $currency_id, $main_currency);
			$this->addDiscountToPrices($element, $discounts, $discount_before_tax, $zone_id);

			if(!empty($element->options)) {
				$this->addDiscountToPrices($element->options, $discounts, $discount_before_tax, $zone_id);
			}
		}
	}

	function removeAndAddPrices(&$element,&$prices,&$currency_ids,$currency_id,$main_currency,$zone_id) {
		$this->removeUneededPrices($element,$prices,$currency_id,$main_currency);
		$this->addTax($prices,$element,$currency_ids,$zone_id,$element->product_tax_id);

		if(!empty($element->variants)) {
			foreach($element->variants as $k2 => $variant) {
				$this->removeUneededPrices($element->variants[$k2], $prices, $currency_id, $main_currency);
				if(empty($element->variants[$k2]->product_tax_id))
					$element->variants[$k2]->product_tax_id = $element->product_tax_id;
				$this->addTax($prices, $element->variants[$k2], $currency_ids, $zone_id, $element->variants[$k2]->product_tax_id);
			}
		}

		if(!empty($element->options)) {
			foreach($element->options as $k2 => $optionElement){
				if(is_object($optionElement))
					$this->removeAndAddPrices($element->options[$k2], $prices, $currency_ids, $currency_id, $main_currency, $zone_id);
			}
		}
	}

	function removeUneededPrices(&$element,&$prices,$currency_id,$main_currency){
		$elementPrices = array();
		foreach($prices as $k => $price){
			if($price->price_product_id == $element->product_id) {
				$elementPrices[$price->price_currency_id][$price->price_min_quantity][]=$k;
			}
		}
		if(empty($elementPrices)){
			return true;
		}


		if(empty($elementPrices[$currency_id])){
			if(isset($elementPrices[$main_currency])){
				$this->_removePrices($elementPrices,$prices,$main_currency);
			}else{
				reset($elementPrices);
				$found=key($elementPrices);
				foreach($elementPrices as $currency => $price){
					if(isset($price[0])){
						$found = $currency;
					}
				}
				$this->_removePrices($elementPrices,$prices,$found);
			}

		}else{
			$this->_removePrices($elementPrices,$prices,$currency_id);
		}
		if(!empty($element->cart_product_quantity)){
			if(empty($element->cart_product_total_quantity)){
				$element->cart_product_total_quantity = $element->cart_product_quantity;
			}
			$elementPrices=array();
			foreach($prices as $k => $price){
				if($price->price_product_id==$element->product_id){
					$price->k=$k;
					$elementPrices[$price->price_min_quantity] = $price;
				}
			}
			krsort($elementPrices);

			$element->all_prices = hikashop_copy($elementPrices);
			$found = false;
			foreach($elementPrices as $qty => $price){
				if($qty>$element->cart_product_total_quantity || $found){
				}else{
					$prices[$price->k]->needed = true;
					$found = true;
				}
			}
		}
		return true;
	}

	function _removePrices(&$elementPrices, &$prices, $main_currency) {
		$multisites = file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php');

		foreach($elementPrices as $currency => $currencyPrices) {
			if($currency!=$main_currency){
				foreach($currencyPrices as $quantityPrices){
					foreach($quantityPrices as $k){
						unset($prices[$k]);
					}
				}
				continue;
			}
			foreach($currencyPrices as $quantityPrices) {
				if ( count( $quantityPrices) <= 1) {
					continue;
				}

				foreach($quantityPrices as $k){
					if ( !empty( $prices[$k]->price_site_id) && $prices[$k]->price_site_id == '[unselected]') {
						$prices[$k]->price_site_id = '';
					}

					if ( !isset( $unique_price_k)) {
						$unique_price_k = $k;
					}
					else if ( empty( $prices[$unique_price_k]->price_site_id)) {
						$unique_price_k = $k;
					}
					else if ( $prices[$unique_price_k]->price_site_id == $prices[$k]->price_site_id) {
						$unique_price_k = $k;
					}
				}
				if ( isset( $unique_price_k)) {
					foreach($quantityPrices as $k){
						if ( $k != $unique_price_k) {
							unset($prices[$k]);
						}
					}
				}
			}
		}
	}

	function convertCoupon(&$coupon, $currency_id) {
		if($coupon->discount_currency_id == $currency_id)
			return true;

		$config =& hikashop_config();
		$main_currency = $config->get('main_currency',1);

		$currencies = array($coupon->discount_currency_id, $currency_id);
		if($coupon->discount_currency_id != $main_currency) {
			$currencies[]=$main_currency;
		}

		$null = null;
		$currenciesData = $this->getCurrencies($currencies,$null);

		$coupon->discount_currency_id_orig = $coupon->discount_currency_id;
		$srcCurrency = $currenciesData[$coupon->discount_currency_id];
		$dstCurrency = $currenciesData[$currency_id];
		$mainCurrency =  $currenciesData[$main_currency];
		$coupon->discount_currency_id = $currency_id;

		$keys = array('discount_flat_amount', 'discount_minimum_order');
		foreach($keys as $key) {
			if(!bccomp(sprintf('%F',$coupon->$key), 0, 5)) {
				$coupon->$key = 0;
				continue;
			}
			$coupon->{$key.'_orig'} = $coupon->$key;
			if($srcCurrency->currency_id != $mainCurrency->currency_id) {
				if(bccomp(sprintf('%F',$srcCurrency->currency_percent_fee), 0, 2))
					$coupon->$key += $coupon->$key * floatval($srcCurrency->currency_percent_fee) / 100.0;
				$coupon->$key = (floatval($coupon->$key) / floatval($srcCurrency->currency_rate));
			}
			if($dstCurrency->currency_id != $mainCurrency->currency_id) {
				$coupon->$key = floatval($coupon->$key) * floatval($dstCurrency->currency_rate);
				if(bccomp(sprintf('%F',$dstCurrency->currency_percent_fee), 0, 2))
					$coupon->$key += $coupon->$key * floatval($dstCurrency->currency_percent_fee) / 100.0;
			}
		}

		return true;
	}

	function getCurrencies($ids, &$currencies) {
		static $cachedCurrencies = array();
		if(!empty($currencies)) {
			foreach($currencies as $currency) {
				$this->checkLocale($currency);
				$cachedCurrencies[(int)$currency->currency_id] = $currency;
			}
		}

		if(is_null($ids))
			return true;

		if(!is_array($ids))
			$ids = array($ids);

		$need = array();
		foreach($ids as $id) {
			if(!isset($cachedCurrencies[(int)$id])) {
				$need[] = (int)$id;
			}
		}

		if(!empty($need)) {
			$query = 'SELECT * FROM '.hikashop_table('currency').' WHERE currency_id IN ('.implode(',',$need).')';
			$this->database->setQuery($query);
			$results = $this->database->loadObjectList();
			foreach($results as $k => $v) {
				$this->checkLocale($results[$k]);
			}
			$this->getCurrencies(null, $results);
		}
		$found = array();
		foreach($ids as $id) {
			if(isset($cachedCurrencies[(int)$id]))
				$found[(int)$id]=$cachedCurrencies[(int)$id];
		}
		return $found;
	}

	function calculateTotal(&$rows, &$order, $currency_id) {
		$total = new stdClass();
		$total->price_value = 0.0;
		$total->price_value_with_tax = 0.0;
		$total->price_currency_id = $currency_id;
		$total->taxes = array();
		$rounding = $this->getRounding($currency_id, true);
		$quantity = 0;

		foreach($rows as $k => $row) {
			if(empty($row->prices) || $row->cart_product_quantity <= 0)
				continue;

			$price = reset($row->prices);
			foreach(get_object_vars($total) as $key => $value) {
				if(!in_array($key,array('price_currency_id', 'price_orig_currency_id', 'price_value_without_discount_with_tax', 'price_value_without_discount', 'taxes')) && isset($price->$key)) {
					$total->$key = $total->$key + (float)hikashop_toFloat($price->$key);
				}
			}

			if(!isset($price->taxes))
				continue;
			foreach($price->taxes as $tax) {
				if(isset($total->taxes[$tax->tax_namekey])) {
					$total->taxes[$tax->tax_namekey]->tax_amount += $this->round($tax->tax_amount,$rounding);
					$total->taxes[$tax->tax_namekey]->amount += $this->round($tax->amount,$rounding);
				} else {
					$total->taxes[$tax->tax_namekey] = new stdClass();
					$total->taxes[$tax->tax_namekey]->tax_namekey = $tax->tax_namekey;
					$total->taxes[$tax->tax_namekey]->tax_rate = $tax->tax_rate;
					$total->taxes[$tax->tax_namekey]->tax_amount = $this->round($tax->tax_amount,$rounding);
					$total->taxes[$tax->tax_namekey]->amount = $this->round($tax->amount,$rounding);
				}
			}
		}

		if(!empty($total->taxes)) {
			$total_taxes = 0;
			foreach($total->taxes as $tax) {
				$total_taxes += $tax->tax_amount;
			}
			$total->price_value_with_tax = $total->price_value + $total_taxes;
		}
		if(is_null($order))
			$order = new stdClass();
		$order->prices = array($total);
	}

	function addAdditionals(&$rows, &$additional_total, $total, $currency_id) {
		if(is_array($total->prices)) {
			if(empty($additional_total))
				$additional_total = new stdClass();
			$additional_total->prices = array(clone(reset($total->prices)));
		}

		if(empty($rows))
			return;

		foreach($rows as $k => &$row) {
			if($row->price_currency_id != $currency_id || empty($additional_total->prices))
				continue;

			foreach($additional_total->prices as $k => $price) {

				if(isset($row->price_value) && is_numeric($row->price_value)) {
					$additional_total->prices[$k]->price_value += $row->price_value;
				}
				if(isset($row->price_value_with_tax) && is_numeric($row->price_value)) {
					$additional_total->prices[$k]->price_value_with_tax += $row->price_value_with_tax;
				}

				if(!isset($row->price_value) || !isset($row->price_value_with_tax) || $row->price_value == $row->price_value_with_tax)
					continue;

				if(!isset($row->taxes) && isset($additional_total->prices[$k]->taxes) && is_array($additional_total->prices[$k]->taxes)) {
					$row->taxes = array();
					$tax = reset($additional_total->prices[$k]->taxes);
					if(is_object($tax)){
						$row->taxes[$tax->tax_namekey] = clone($tax);
						$row->taxes[$tax->tax_namekey]->tax_amount = $row->price_value_with_tax - $row->price_value;
						$row->taxes[$tax->tax_namekey]->amount = $row->price_value;
					}
				}
				if(empty($row->taxes))
					continue;

				foreach($row->taxes as $tax) {
					if(isset($additional_total->prices[$k]->taxes[$tax->tax_namekey])) {
						$additional_total->prices[$k]->taxes[$tax->tax_namekey]->tax_amount += $tax->tax_amount;
						$additional_total->prices[$k]->taxes[$tax->tax_namekey]->amount += $tax->amount;
					} else {
						$additional_total->prices[$k]->taxes[$tax->tax_namekey] = clone($tax);
					}
				}
			}
		}
		unset($row);
	}

	function pricesSelection(&$prices, $quantity) {
		$matches = array();
		$otherCurrencies = array();
		if(!empty($prices)) {
			foreach($prices as $k2 => $price) {
				if($price->price_min_quantity>$quantity)
					continue;

				if(empty( $price->price_orig_currency_id)) {
					$matches[] = $price;
				} else {
					$otherCurrencies[] = $price;
				}
			}
		}

		if(empty($matches) && !empty($otherCurrencies)) {
			$config =& hikashop_config();
			$main_currency = (int)$config->get('main_currency', 1);
			foreach($otherCurrencies as $price){
				if($price->price_orig_currency_id == $main_currency) {
					$matches[] = $price;
				}
			}
			if(empty($matches)) {
				$matches = $otherCurrencies;
			}
		}

		if(!empty($matches)) {
			$tempMatches = array();
			$matchcount = 0;
			foreach($matches as $price) {
				if($price->price_min_quantity == $quantity) {
					$tempMatches[] = $price;
				}
			}
			if(count($tempMatches)) {
				$matches = $tempMatches;
			}
		}
		$prices = hikashop_copy($matches);
	}

	function calculateProductPriceForQuantity(&$product) {
		if(isset($product->prices) && count($product->prices)) {
			foreach($product->prices as $k => $price){
				if(isset($price->unit_price))
					$product->prices[$k] = $price->unit_price;
			}
		}

		JPluginHelper::importPlugin( 'hikashop' );
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeCalculateProductPriceForQuantity', array( &$product) );

		if(function_exists('hikashop_product_price_for_quantity_in_cart')) {
			hikashop_product_price_for_quantity_in_cart($product);
		} elseif(isset($product->prices)) {
			$this->quantityPrices($product->prices, @$product->cart_product_quantity, $product->cart_product_total_quantity);
		}

		$app->triggerEvent('onAfterCalculateProductPriceForQuantity', array( &$product) );
	}

	function quantityPrices(&$prices, $quantity, $total_quantity) {
		$this->pricesSelection($prices,$total_quantity);
		$unitPrice = null;
		if(empty($prices))
			return;

		$unitPrice = reset($prices);
		if(count($prices) > 1) {
			$cheapest_value = $unitPrice->price_value;
			foreach($prices as $price){
				if($cheapest_value > $price->price_value) {
					$unitPrice = $price;
					$cheapest_value = $price->price_value;
				}
			}
		}

		$this->quantityPrice($unitPrice, $quantity);
		$prices = array($unitPrice);
	}

	function quantityPrice(&$price, $quantity) {
		if($quantity <= 0)
			return;

		if($price === null || empty($price))
			$price = new stdClass();
		if(!isset($price->unit_price))
			$price->unit_price = new stdClass();

		$price->unit_price->price_currency_id = $price->price_currency_id;
		$price->unit_price->price_min_quantity = $price->price_min_quantity;
		$rounding = $this->getRounding($price->price_currency_id, true);

		if(isset($price->price_orig_currency_id)) {
			$price->unit_price->price_orig_currency_id = $price->price_orig_currency_id;
		}

		$keys = array(
			'price_value_without_discount',
			'price_value',
			'price_value_with_tax',
			'price_orig_value',
			'price_orig_value_with_tax',
			'price_orig_value_without_discount',
			'price_value_without_discount_with_tax'
		);
		foreach($keys as $key) {
			if(!isset($price->$key))
				continue;
			$price->unit_price->$key = $this->round($price->$key, $rounding);
			$price->$key = $this->round($price->unit_price->$key * $quantity, $rounding);
		}

		if(isset($price->taxes)) {
			$price->unit_price->taxes = array();
			foreach($price->taxes as $k => $tax) {
				$price->unit_price->taxes[$k] = clone($tax);
				$price->taxes[$k]->tax_amount = $this->round(@$tax->tax_amount * $quantity,$rounding);
				$price->taxes[$k]->amount = $this->round(@$tax->amount * $quantity,$rounding);
			}
		}

		if(isset($price->taxes_without_discount)) {
			$price->unit_price->taxes_without_discount = array();
			foreach($price->taxes_without_discount as $k => $tax) {
				$price->unit_price->taxes_without_discount[$k] = clone($tax);
				$price->taxes_without_discount[$k]->tax_amount = $this->round(@$tax->tax_amount * $quantity,$rounding);
				$price->taxes_without_discount[$k]->amount = $this->round(@$tax->amount * $quantity,$rounding);

			}
		}
	}

	function addDiscountToPrices(&$element, &$discounts, $discount_before_tax, $zone_id) {
		if(is_array($element)) {
			foreach($element as $k => $el) {
				$this->addDiscountToPrices($element[$k], $discounts, $discount_before_tax, $zone_id);
			}
			return;
		}

		if(empty($element->discount) && !empty($element->prices)) {
			$this->selectDiscount($element, $discounts, $zone_id);
		}

		if(!empty($element->variants)) {
			foreach($element->variants as $k => $row) {
				if(!empty($row->discount))
					continue;

				$this->selectDiscount($element->variants[$k], $discounts, $zone_id, $element);

				if(empty($element->variants[$k]->discount)) {
					if(!empty($element->discount))
						$element->variants[$k]->discount = $element->discount;
					else
						continue;
				}

				if(empty($row->prices)) {
					if(empty($element->prices))
						continue;
					$element->variants[$k]->prices = hikashop_copy($element->prices);

					if(!empty($element->variants[$k]->cart_product_total_variants_quantity)) {
						$element->variants[$k]->cart_product_total_quantity = $element->variants[$k]->cart_product_total_variants_quantity;
					}

					if((float) $element->variants[$k]->product_price_percentage > 0) {
						foreach($element->variants[$k]->prices as $k2 => $row2) {
							foreach(get_object_vars($row2) as $key => $value) {
								if(in_array($key, array('taxes_without_discount', 'taxes', 'taxes_orig'))) {
									foreach($value as $taxKey => $tax) {
										$element->variants[$k]->prices[$k2]->taxes[$taxKey]->tax_amount = @$tax->tax_amount * (float)$element->variants[$k]->product_price_percentage / 100;
										$element->variants[$k]->prices[$k2]->taxes[$taxKey]->amount = @$tax->amount * (float)$element->variants[$k]->product_price_percentage / 100;
									}
								} elseif(is_numeric($value) && !in_array($key,array('price_currency_id','price_orig_currency_id','price_min_quantity','price_access', 'price_users'))) {
									$element->variants[$k]->prices[$k2]->$key = $value * (float)$element->variants[$k]->product_price_percentage / 100;
								}
							}
						}
					}
				}

				if(empty($element->variants[$k]->product_tax_id))
					$element->variants[$k]->product_tax_id = $element->product_tax_id;

				foreach($row->prices as $k2 => $price) {
					$this->addDiscount($element->variants[$k]->prices[$k2], $element->variants[$k]->discount, $discount_before_tax, $zone_id, $element->variants[$k]->product_tax_id);
				}
			}
		}

		if(!empty($element->discount)) {
			foreach($element->prices as $k => $price) {
				$this->addDiscount($element->prices[$k], $element->discount, $discount_before_tax, $zone_id, $element->product_tax_id);
			}
		}
	}

	function updateRatesWithNewMainCurrency($old_currency, $new_currency) {
		if($old_currency == $new_currency)
			return true;

		$ids = array($old_currency,$new_currency);
		$null = null;
		$currencies = $this->getCurrencies($ids, $null);
		if(empty($currencies[$old_currency]) || empty($currencies[$new_currency]) || empty($currencies[$new_currency]->currency_rate))
			return true;
		$main_currency = 1 / $currencies[$new_currency]->currency_rate;
		$query = 'UPDATE '.hikashop_table('currency').' SET currency_rate = currency_rate * '.$main_currency;
		$this->database->setQuery($query);
		return $this->database->execute();
	}

	public function delete(&$elements) {
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeCurrencyDelete', array(&$elements, &$do));

		if(!$do)
			return false;

		$status = parent::delete($elements);
		if($status) {
			$app->triggerEvent('onAfterCurrencyDelete', array(&$elements));
		}
		return $status;
	}

	public function save(&$element) {
		if(!empty($element->currency_id)) {
			$element->old = $this->get($element->currency_id);
			if(isset($element->currency_code) && @$element->old->currency_code != $element->currency_code) {
				$app = JFactory::getApplication();
				$app->enqueueMessage('You changed the currency code from '.@$element->old->currency_code.' to '.$element->currency_code.'. Note that payment plugins base themselves on the currency code to see if they support or not the current currency. If you change te currency code, that code won\'t be understood by payment plugins and thus you won\'t see them on your checkout with that currency. If you want to change the display of prices so that it doesn\'t use the code but the symbol of the currency, please change the format option of the currency and leave the default value in the currency code.' );
			}
		}
		$element->currency_modified = time();

		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		if(empty($element->currency_id)) {
			$app->triggerEvent('onBeforeCurrencyCreate', array( &$element, &$do ));
		} else {
			$app->triggerEvent('onBeforeCurrencyUpdate', array( &$element, &$do ));
		}

		if(!$do)
			return false;

		$status = parent::save($element);
		if(!$status)
			return $status;

		if(empty($element->currency_id)) {
			$app->triggerEvent('onAfterCurrencyCreate', array( &$element ));
		} else {
			$app->triggerEvent('onAfterCurrencyUpdate', array( &$element ));
		}
		return $status;
	}

	function addDiscount(&$price, $discount, $discount_before_tax, $zone_id, $product_tax_id) {
		$config = hikashop_config();
		if($config->get('floating_tax_prices', 0)) {
			if(!isset($price->price_value_with_tax))
				$price->price_value_with_tax = $price->price_value;
			else
				$price->price_value = $price->price_value_with_tax;
		}

		if(!is_object($price))
			return;

		$price->price_value_without_discount = $price->price_value;

		$round = $this->getRounding(@$price->price_currency_id,true);

		if(!empty($price->discount))
			$discount = $price->discount;

		if($discount_before_tax) {
			if(bccomp($discount->discount_flat_amount,0,5) !== 0) {
				$price->price_value = $price->price_value - floatval($discount->discount_flat_amount);
			} else {
				$price->price_value = (($price->price_value * (100.0 - floatval($discount->discount_percent_amount))) / 100.0);
				if(isset($price->price_orig_value)) {
					$price->price_orig_value_without_discount = $price->price_orig_value;
					$price->price_orig_value = (($price->price_orig_value * (100.0 - floatval($discount->discount_percent_amount))) / 100.0);
				}
			}
			$price->price_value_without_discount_with_tax = $this->getTaxedPrice($price->price_value_without_discount, $zone_id, $product_tax_id, $round);
			$price->taxes_without_discount = $this->taxRates;
			$price->price_value_with_tax = $this->getTaxedPrice($price->price_value, $zone_id, $product_tax_id, $round);
			$price->price_value = $this->round($price->price_value, $round);
			$price->taxes = $this->taxRates;
			if(isset($price->price_orig_value)) {
				$price->price_orig_value_with_tax = $this->getTaxedPrice($price->price_orig_value, $zone_id, $product_tax_id, $round);
				$price->taxes_orig = $this->taxRates;
			}
		} else {
			$price->price_value_without_discount_with_tax = $price->price_value_with_tax;
			if(bccomp($discount->discount_flat_amount, 0, 5) !== 0) {
				$price->price_value_with_tax = $price->price_value_with_tax - floatval($discount->discount_flat_amount);
			} else {
				$price->price_value_with_tax = (($price->price_value_with_tax * (100.0 - floatval($discount->discount_percent_amount))) / 100.0);
				if(isset($price->price_orig_value_with_tax)) {
					$price->price_orig_value_without_discount_with_tax = $price->price_orig_value_with_tax;
					$price->price_orig_value_with_tax = (($price->price_orig_value_with_tax * (100.0 - floatval($discount->discount_percent_amount))) / 100.0);
				}
			}

			$price->price_value_without_discount = $this->getUntaxedPrice($price->price_value_without_discount_with_tax, $zone_id, $product_tax_id, $round);
			$price->taxes_without_discount = $this->taxRates;
			$price->price_value = $this->getUntaxedPrice($price->price_value_with_tax,$zone_id,$product_tax_id, $round);
			$price->price_value_with_tax = $this->round($price->price_value_with_tax, $round);
			$price->taxes = $this->taxRates;
			if(isset($price->price_orig_value_with_tax)) {
				$price->price_orig_value = $this->getUntaxedPrice($price->price_orig_value_with_tax, $zone_id, $product_tax_id, $round);
				$price->taxes_orig = $this->taxRates;
			}
		}
	}

	function getRounding($currency_id, $allow_increment = false) {
		if(empty($currency_id))
			return 2;

		$array = null;
		$currencies = $this->getCurrencies($currency_id, $array);
		$currency = $currencies[$currency_id];
		$round = (int)$currency->currency_locale['int_frac_digits'];
		if($allow_increment && !empty($currency->currency_locale['rounding_increment']) && $currency->currency_locale['rounding_increment'] > 0.00001) {
			$round = array('rounding' => $round, 'increment' => (float)$currency->currency_locale['rounding_increment']);
		}
		return $round;
	}

	function getProductTaxes(&$products, $prices_with_tax = true, $highest_rate_only = false, $include_virtual_products = true){
		$taxes = array();

		$price = 'price_value';
		if($prices_with_tax)
			$price = 'price_value_with_tax';


		if($highest_rate_only) {
			$max = null;
			foreach($products as $product){
				if(empty($product->cart_product_quantity))
					continue;

				if(empty($product->prices) || empty($product->prices[0]->taxes))
					continue;

				if(!$include_virtual_products && !bccomp(sprintf('%F',$product->product_weight), 0, 5))
					continue;

				foreach($product->prices[0]->taxes as $key => $tax){
					if($max == null) {
						$max = $tax;
					} elseif($highest_rate_only == 3) {
						if( $max->tax_rate > $tax->tax_rate) {
							$max = $tax;
						}
					} else {
						if($max->tax_rate < $tax->tax_rate) {
							$max = $tax;
						}
					}

				}
			}
			if($max != null)
				$taxes[$max->tax_namekey] = $max;
			return $taxes;
		}

		$total = 0;
		foreach($products as $product) {
			if(empty($product->cart_product_quantity))
				continue;
			if(!$include_virtual_products && !bccomp(sprintf('%F',$product->product_weight), 0, 5))
				continue;
			if(!empty($product->prices))
				$total += $product->prices[0]->$price;
		}

		if(empty($total)) {
			return $taxes;
		}

		foreach($products as $product){
			if(empty($product->cart_product_quantity))
				continue;
			if(empty($product->prices) || empty($product->prices[0]->taxes))
				continue;
			if(!$include_virtual_products && !bccomp(sprintf('%F',$product->product_weight), 0, 5))
				continue;
			$ratio = $product->prices[0]->$price / $total;
			foreach($product->prices[0]->taxes as $key => $tax){
				if(isset($taxes[$key])){
					$taxes[$key]->tax_ratio += $ratio;
				}else{
					$taxes[$key] = hikashop_copy($tax);
					unset($taxes[$key]->tax_amount);
					unset($taxes[$key]->amount);
					$taxes[$key]->tax_ratio = $ratio;
				}
			}
		}
		return $taxes;
	}

	function addCoupon(&$prices, &$discount, $products = null, $id = array()) {
		$config =& hikashop_config();
		$discount_before_tax = (int)$config->get('coupon_before_tax', 1);

		$config = hikashop_config();
		$floating_tax = (int)$config->get('floating_tax_prices', 0);
		$zone_id = hikashop_getZone(null);

		$taxes = $discount->discount_tax_id;

		if(!empty($discount->discount_tax) && !empty($discount->products)) {
			$taxes = $this->getProductTaxes($discount->products, !$discount_before_tax);
		}
		foreach($prices->prices as $k => $price) {
			if(isset($prices->prices[$k]->price_value_without_discount_with_tax) && $prices->prices[$k]->price_value_without_discount_with_tax > 0)
				continue;

			if(isset($price->taxes)) {
				$price->taxes_without_discount = array();
				foreach($price->taxes as $namekey => $tax) {
					$price->taxes_without_discount[$namekey] = clone($tax);
				}
			}

			$prices->prices[$k]->price_value_without_discount_with_tax = $price->price_value_with_tax;

				$round = $this->getRounding(@$prices->prices[$k]->price_currency_id,true);
				if(bccomp(sprintf('%F',$discount->discount_flat_amount), 0, 5) !== 0) {
					$discount->discount_value_without_tax = $discount->discount_flat_amount_without_tax = $discount->discount_flat_amount;
					$untaxed = null;
					if($discount_before_tax) {
						$discount->discount_flat_amount = $this->getTaxedPrice($discount->discount_flat_amount_without_tax, $zone_id, $taxes, $round);
						$discount->taxes = $this->taxRates;
						$discount->discount_value_without_tax = $discount->discount_flat_amount_without_tax;
						$untaxed = $discount->discount_flat_amount_without_tax;
					} else if($floating_tax) {
						$untaxed = $discount->discount_flat_amount;
					}
					if($untaxed !== null) {
						if(!isset($discount->taxes)) {
							foreach($price->taxes as $namekey => $tax) {
								$discount->taxes[$namekey] = clone($tax);
								$discount->taxes[$namekey]->tax_amount = $this->round($untaxed * $tax->tax_rate, $round);
								$discount->taxes[$namekey]->amount = $this->round($untaxed, $round);

								$price->taxes[$namekey]->tax_amount = $tax->tax_amount - $discount->taxes[$namekey]->tax_amount;
								$price->taxes[$namekey]->amount = $tax->amount - $discount->taxes[$namekey]->amount;
							}
						}else{
							foreach($discount->taxes as $tax){
								if(empty($price->taxes[$tax->tax_namekey])) {
									$price->taxes[$tax->tax_namekey] = clone($tax);
									$price->taxes[$tax->tax_namekey]->tax_amount = 0;
									$price->taxes[$tax->tax_namekey]->amount = 0;
								}
								$price->taxes[$tax->tax_namekey]->tax_amount -= $tax->tax_amount;
								$price->taxes[$tax->tax_namekey]->amount -= $tax->amount;
							}
						}
					}
					$prices->prices[$k]->price_value_with_tax = $price->price_value_with_tax - floatval($discount->discount_flat_amount);
				} elseif(bccomp(sprintf('%F',$discount->discount_percent_amount), 0, 5) !== 0) {
					if($discount_before_tax) {
						$discount->discount_value_without_tax = $discount->discount_percent_amount_calculated_without_tax = $discount->discount_percent_amount_calculated = ($price->price_value*floatval($discount->discount_percent_amount)/100.0);
						$discount->discount_percent_amount_calculated = 0.0;
						if($price->price_value_with_tax != 0.0)
							$discount->discount_percent_amount_calculated = $price->price_value_with_tax * $discount->discount_percent_amount_calculated_without_tax / $price->price_value_with_tax;
					} else {
						if(!$floating_tax)
							$price_value = $price->price_value_with_tax;
						else
							$price_value = $price->price_value;
						$discount->discount_value_without_tax = $discount->discount_percent_amount_calculated_without_tax = $discount->discount_percent_amount_calculated = ($price_value * floatval($discount->discount_percent_amount) / 100.0);
					}

					$discount->discount_percent_amount_calculated = $this->getTaxedPrice($discount->discount_percent_amount_calculated, $zone_id, $taxes, $round, 0);

					$discount->taxes = array();
					if(!empty($price->taxes)) {
						foreach($price->taxes as $namekey => $tax) {
							$discount->taxes[$namekey] = clone($tax);
							$discount->taxes[$namekey]->tax_amount = $this->round($discount->taxes[$namekey]->tax_amount * floatval($discount->discount_percent_amount) / 100.0, $round);
							$price->taxes[$namekey]->tax_amount = $price->taxes[$namekey]->tax_amount - $discount->taxes[$namekey]->tax_amount;
							$discount->taxes[$namekey]->amount = $this->round($discount->taxes[$namekey]->amount * floatval($discount->discount_percent_amount) / 100.0, $round);
							$price->taxes[$namekey]->amount = $price->taxes[$namekey]->amount - $discount->taxes[$namekey]->amount;
						}
					}
					$prices->prices[$k]->price_value_with_tax = $price->price_value_with_tax - $discount->discount_percent_amount_calculated;
					if(isset($price->price_orig_value_with_tax)) {
						$prices->prices[$k]->price_orig_value_without_discount_with_tax = $price->price_orig_value_with_tax;
						$discount->discount_orig_percent_amount_calculated_without_tax = $discount->discount_orig_percent_amount_calculated = ($price->price_orig_value_with_tax * floatval($discount->discount_percent_amount) / 100.0);
						$discount->discount_orig_percent_amount_calculated = $this->getTaxedPrice($discount->discount_orig_percent_amount_calculated, $zone_id, $taxes, $round);
						$prices->prices[$k]->price_orig_value_with_tax = $price->price_orig_value_with_tax - $discount->discount_orig_percent_amount_calculated;
					}
				} else {
					$discount->discount_value_without_tax = 0;
				}

				$discount->discount_value = $prices->prices[$k]->price_value_without_discount_with_tax - $prices->prices[$k]->price_value_with_tax;

			$prices->prices[$k]->price_value_without_discount = $price->price_value;
			$prices->prices[$k]->price_value = $price->price_value - $discount->discount_value_without_tax;
		}
	}

	function &addShipping(&$shippings, &$ref_total) {
		$total = new stdClass();
		$price = reset($ref_total->prices);
		if(is_null($price))
			$price = new stdClass();
		$total->prices = array(clone($price));

		foreach($total->prices as $k => $price) {
			$total->prices[$k]->price_value_without_shipping_with_tax = $price->price_value_with_tax;
			$total->prices[$k]->price_value_without_shipping = $price->price_value;
		}
		foreach($shippings as &$shipping) {
			if(empty($shipping->shipping_price_with_tax) || bccomp(sprintf('%F',$shipping->shipping_price_with_tax), 0, 5) === 0)
				continue;

			foreach($total->prices as $k => $price) {
				$total->prices[$k]->price_value_with_tax += floatval($shipping->shipping_price_with_tax);
				$total->prices[$k]->price_value += $shipping->shipping_price;

				if(!isset($shipping->taxes) && !empty($total->prices[$k]->taxes) && is_array($total->prices[$k]->taxes)) {
					$shipping->taxes = array();
					$tax = reset($total->prices[$k]->taxes);
					if(is_object($tax))
						$shipping->taxes[$tax->tax_namekey] = clone($tax);
					else
						$shipping->taxes[$tax->tax_namekey] = new stdClass();
					$shipping->taxes[$tax->tax_namekey]->tax_amount = $shipping->shipping_price_with_tax - $shipping->shipping_price;
					$shipping->taxes[$tax->tax_namekey]->amount = $shipping->shipping_price;
				}
				if(empty($shipping->taxes))
					continue;
				foreach($shipping->taxes as $tax) {
					if(!empty($tax->tax_namekey)) {
						if(isset($total->prices[$k]->taxes[$tax->tax_namekey])) {
							$total->prices[$k]->taxes[$tax->tax_namekey]->tax_amount += $tax->tax_amount;
							$total->prices[$k]->taxes[$tax->tax_namekey]->amount += $tax->amount;
						} else
							$total->prices[$k]->taxes[$tax->tax_namekey] = clone($tax);
					}
				}
			}
		}
		unset($shipping);
		return $total;
	}

	function addPayment(&$payment, &$total) {
		$price = reset($total->prices);
		if(is_null($price))
			$price = new stdClass();
		if(!isset($payment->total) || is_null($payment->total))
			$payment->total = new stdClass();
		$payment->total->prices = array(clone($price));

		if(isset($payment->total->prices[0]->price_value_without_payment))
			return true;

		foreach($payment->total->prices as $k => $price) {
			$payment->total->prices[$k]->price_value_without_payment = $price->price_value;
			$payment->total->prices[$k]->price_value_without_payment_with_tax = $price->price_value_with_tax;

			$payment->total->prices[$k]->price_value = $price->price_value + $payment->payment_price;
			if(isset($payment->payment_price_with_tax) && $payment->payment_price_with_tax != $payment->payment_price)
				$payment->total->prices[$k]->price_value_with_tax = $price->price_value_with_tax + $payment->payment_price_with_tax;
			else
				$payment->total->prices[$k]->price_value_with_tax = $price->price_value_with_tax + $payment->payment_price;

			if($payment->payment_price_with_tax != $payment->payment_price) {
				if(!isset($payment->taxes) && isset($total->prices[$k]->taxes) && is_array($total->prices[$k]->taxes)) {
					$payment->taxes = array();
					$tax = reset($total->prices[$k]->taxes);
					if(is_object($tax) && !empty($tax->tax_namekey)) {
						$payment->taxes[$tax->tax_namekey] = clone($tax);
						$payment->taxes[$tax->tax_namekey]->tax_amount = $payment->payment_price_with_tax - $payment->payment_price;
						$payment->taxes[$tax->tax_namekey]->amount = $payment->payment_price;
					}
				}
				if(!empty($payment->taxes)) {
					foreach($payment->taxes as $tax) {
						if(!empty($tax->tax_namekey)) {
							if(isset($payment->total->prices[$k]->taxes[$tax->tax_namekey])) {
								$payment->total->prices[$k]->taxes[$tax->tax_namekey]->tax_amount += $tax->tax_amount;
								$payment->total->prices[$k]->taxes[$tax->tax_namekey]->amount += $tax->amount;
							} else
								$payment->total->prices[$k]->taxes[$tax->tax_namekey] = clone($tax);
						}
					}
				}
			}
		}
	}

	function processShippings(&$usable_rates, &$cart, $zone_id = null) {
		if(empty($usable_rates))
			return;

		$this->convertShippings($usable_rates);
		if($zone_id === null)
			$zone_id = hikashop_getZone(null);

		foreach($usable_rates as &$rate) {
			if((!empty($rate->shipping_tax_id) || !empty($rate->shipping_params->shipping_tax) ) && bccomp(sprintf('%F',$rate->shipping_price), 0, 5)) {
				if(!empty($rate->taxes_added))
					continue;

				$rate->taxes_added = true;

				$taxes = $rate->shipping_tax_id;
				if(!empty($rate->shipping_params->shipping_tax)) {
					$highest_rate_only = false;
					if($rate->shipping_params->shipping_tax > 1)
						$highest_rate_only = $rate->shipping_params->shipping_tax;
					$include_virtual_products = !isset($rate->shipping_params->shipping_virtual_included) || (bool) $rate->shipping_params->shipping_virtual_included;
					$done = false;
					foreach($cart->shipping_groups as $group) {
						if(in_array($rate->shipping_id, $group->shippings)) {
							$taxes = $this->getProductTaxes($group->products, false, $highest_rate_only, $include_virtual_products);
							$done = true;
							break;
						}
					}
					if(!$done){
						$taxes = $this->getProductTaxes($cart->products, false, $highest_rate_only, $include_virtual_products);
					}
				}

				$round = $this->getRounding(@$rate->shipping_currency_id, true);
				$rate->shipping_price_with_tax = $this->getTaxedPrice($rate->shipping_price, $zone_id, $taxes, $round);
				$rate->taxes = hikashop_copy($this->taxRates);

				if(isset($rate->shipping_price_orig) && bccomp(sprintf('%F',$rate->shipping_price_orig), 0, 5)) {
					$rate->shipping_price_orig_with_tax = $this->getTaxedPrice($rate->shipping_price_orig, $zone_id, $taxes, $round);
					$rate->taxes_orig = hikashop_copy($this->taxRates);
				} else {
					$rate->shipping_price_orig = 0.0;
					$rate->shipping_price_orig_with_tax = 0.0;
				}
			} else {
				if(!is_object($rate))
					$rate = new stdClass();
				$rate->shipping_price_with_tax = @$rate->shipping_price;
				$rate->shipping_price_orig_with_tax = @$rate->shipping_price_orig;
			}
		}
		unset($rate);

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onAfterProcessShippings', array(&$usable_rates, &$cart));
	}

	function processPayments(&$usable_rates, &$cart, $zone_id = null, $currency_id = 0) {
		if(empty($usable_rates))
			return;

		$this->convertPayments($usable_rates,  $currency_id);
		if($zone_id === null)
			$zone_id = hikashop_getZone(null);

		foreach($usable_rates as &$rate) {
			if(!empty($rate->payment_params->payment_tax_id) && bccomp(sprintf('%F',$rate->payment_price), 0, 5)) {
				if(!empty($rate->taxes_added))
					continue;

				$rate->taxes_added = true;

				$round = $this->getRounding(@$rate->payment_currency_id, true);
				$rate->payment_price_with_tax = $this->getTaxedPrice($rate->payment_price, $zone_id, $rate->payment_params->payment_tax_id, $round);
				$rate->taxes = hikashop_copy($this->taxRates);

				if(isset($rate->payment_price_orig) && bccomp(sprintf('%F',$rate->payment_price_orig), 0, 5)) {
					$rate->payment_price_orig_with_tax = $this->getTaxedPrice($rate->payment_price_orig, $zone_id, $rate->payment_params->payment_tax_id, $round);
					$rate->taxes_orig = hikashop_copy($this->taxRates);
				} else {
					$rate->payment_price_orig = 0.0;
					$rate->payment_price_orig_with_tax = 0.0;
				}
			} else {
				if(!is_object($rate))
					$rate = new stdClass();
				$rate->payment_price_with_tax = @$rate->payment_price;
				$rate->payment_price_orig_with_tax = @$rate->payment_price_orig;
			}
		}
		unset($rate);

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onAfterProcessPayments', array(&$usable_rates, &$cart));
	}

	function addTax(&$prices, &$element, &$currency_ids, $zone_id, $product_tax_id) {
		$element->prices = array();
		foreach($prices as $price) {
			$currency_ids[(int)$price->price_currency_id] = (int)$price->price_currency_id;

			if((int)$price->price_product_id != (int)$element->product_id)
				continue;

			if(empty($price->taxes_added)) {
				$round = $this->getRounding($price->price_currency_id, true);

				$price->price_value_with_tax = $this->getTaxedPrice($price->price_value, $zone_id, $product_tax_id, $round);
				$price->taxes_added = true;
				$price->taxes = $this->taxRates;
			}

			$element->prices[] = hikashop_copy($price);
		}
	}

	function convertPrice(&$element, &$currencies, $currency_id, $main_currency) {
		if(is_array($element)) {
			foreach($element as $k => $row) {
				$this->convertPrice($element[$k], $currencies, $currency_id, $main_currency);
			}
			return;
		}

		if(!empty($element->prices)){
			$this->convertPrices($element->prices,$currencies,$currency_id,$main_currency);
		}
		if(!empty($element->variants)){
			$this->convertPrice($element->variants,$currencies,$currency_id,$main_currency);
		}
		if(!empty($element->options)){
			$this->convertPrice($element->options,$currencies,$currency_id,$main_currency);
		}
	}


	function format($number, $currency_id = 0, $format_override = '') {
		$currency_id = (int) $currency_id;
		if(!$currency_id) {
			$currency_id = $this->mainCurrency();
		}
		$null = null;
		$currencies = $this->getCurrencies($currency_id,$null);
		$data=$currencies[$currency_id];
		if(empty($format_override)) {
			$format = $data->currency_format;
		} else {
			$format = $format_override;
		}
		$locale = $data->currency_locale;

		$config = hikashop_config();
		if(!$config->get('round_calculations', 0) && !empty($locale['rounding_increment']) && $locale['rounding_increment'] > 0.00001){
			$number = $this->roundByIncrement($number, (float)$locale['rounding_increment']);
		}

		preg_match_all('/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?(?:#([0-9]+))?(?:\.([0-9]+))?([in%][in]?)/', $format, $matches, PREG_SET_ORDER);
		foreach ($matches as $fmatch) {
			$value = (float)$number;
			$flags = array(
				'fillchar'  => preg_match('/\=(.)/', $fmatch[1], $match) ? $match[1] : ' ',
				'nogroup'   => preg_match('/\^/', $fmatch[1]) > 0,
				'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ? $match[0] : '+',
				'nosimbol'  => preg_match('/\!/', $fmatch[1]) > 0,
				'isleft'	=> preg_match('/\-/', $fmatch[1]) > 0
			);
			$width	    = trim($fmatch[2]) ? (int)$fmatch[2] : 0;
			$left	    = trim($fmatch[3]) ? (int)$fmatch[3] : 0;
			$conversion = $fmatch[5];
			$right	    = trim($fmatch[4]) ? (int)$fmatch[4] : $locale[($conversion[0] == 'i' ? 'int_' : '').'frac_digits'];

			$positive = true;
			if ($value < 0) {
				$positive = false;
				$value  *= -1;
			}
			$letter = $positive ? 'p' : 'n';

			$prefix = $suffix = $cprefix = $csuffix = $signal = '';

			$signal = $positive ? $locale['positive_sign'] : $locale['negative_sign'];
			switch (true) {
				case $locale[$letter.'_sign_posn'] == 1 && $flags['usesignal'] == '+':
					$prefix = $signal;
					break;
				case $locale[$letter.'_sign_posn'] == 2 && $flags['usesignal'] == '+':
					$suffix = $signal;
					break;
				case $locale[$letter.'_sign_posn'] == 3 && $flags['usesignal'] == '+':
					$cprefix = $signal;
					break;
				case $locale[$letter.'_sign_posn'] == 4 && $flags['usesignal'] == '+':
					$csuffix = $signal;
					break;
				case $flags['usesignal'] == '(':
				case $locale[$letter.'_sign_posn'] == 0:
					$prefix = '(';
					$suffix = ')';
					break;
			}
			if (!$flags['nosimbol']) {
				$currency = $cprefix .
							($conversion[0] == 'i' ? $data->currency_code : $data->currency_symbol) .
							( isset($conversion[1]) ? ' '.( $conversion[1] == 'i' ? $data->currency_code : $data->currency_symbol) : '') .
							$csuffix;
			} else {
				$currency = '';
			}
			$space  = $locale[$letter.'_sep_by_space'] ? ' ' : '';

			$value = $this->numberFormat($value, $right, $locale['mon_decimal_point'],
					 $flags['nogroup'] ? '' : $locale['mon_thousands_sep'], $locale['mon_grouping']);
			$value = @explode($locale['mon_decimal_point'], $value);

			$n = strlen($prefix) + strlen($currency) + strlen($value[0]);
			if ($left > 0 && $left > $n) {
				$value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0];
			}
			$value = implode($locale['mon_decimal_point'], $value);
			if ($locale[$letter.'_cs_precedes']) {
				$value = $prefix . $currency . $space . $value . $suffix;
			} else {
				$value = $prefix . $value . $space . $currency . $suffix;
			}
			if ($width > 0) {
				$value = str_pad($value, $width, $flags['fillchar'], $flags['isleft'] ?
						 STR_PAD_RIGHT : STR_PAD_LEFT);
			}

			$format = str_replace($fmatch[0], $value, $format);
		}
		return $format;
	}

	function numberFormat($number, $decimals = 2 , $dec_point = '.' , $sep = ',', $grouping = 3) {
		$round = ($decimals < 0) ? 0 : $decimals;
		$num = number_format(round($number, $decimals), $round, '.', '');
		$num = explode('.', $num);
		if(!is_array($grouping)) {
			$grouping = array($grouping);
		}
		$size = strlen($num[0]);
		$currentGroup = 0;
		$groups = array();
		$loop_override = 0;
		while ($size && $loop_override < 5) {
			$loop_override++;

			if(empty($grouping[$currentGroup]))
				$grouping[$currentGroup] = 3;

			if($size > $grouping[$currentGroup]) {
				$groups[] = substr($num[0], -$grouping[$currentGroup]);
				$num[0] = substr($num[0], 0, $size - $grouping[$currentGroup]);
				$size = strlen($num[0]);
				if(!empty($grouping[$currentGroup + 1]))
					$currentGroup++;
			} else {
				$groups[] = $num[0];
				$size=0;
			}
		}
		if(!isset($sep[0]))
			$sep = ',';
		$num[0] = trim(implode($sep[0], array_reverse($groups)));
		$num = implode($dec_point[0], $num);

		return $num;
	}


	function checkLocale(&$element) {
		if(empty($element->currency_locale)) {
			$element->currency_locale =	array(
					'mon_decimal_point' => ',',
					'mon_thousands_sep' => ' ',
					'positive_sign' => '',
					'negative_sign' => '-',
					'int_frac_digits' => 2,
					'frac_digits' => 2,
					'p_cs_precedes' => 0,
					'p_sep_by_space' => 1,
					'n_cs_precedes' => 0,
					'n_sep_by_space' => 1,
					'p_sign_posn' => 1,
					'n_sign_posn' => 1,
					'mon_grouping' => array('3')
				);
		} elseif(is_string($element->currency_locale)) {
			$element->currency_locale = hikashop_unserialize($element->currency_locale);
			if(!empty($element->currency_locale['mon_grouping'])) {
				$element->currency_locale['mon_grouping'] = explode(',', $element->currency_locale['mon_grouping']);
			}
		}
	}


	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$fullLoad = false;
		$displayFormat = !empty($options['displayFormat']) ? $options['displayFormat'] : @$typeConfig['displayFormat'];

		$start = (int)@$options['start']; // TODO
		$limit = (int)@$options['limit'];
		$page = (int)@$options['page'];
		if($limit <= 0)
			$limit = 50;


		$config = hikashop_config();
		$forced = array(
			(int)$config->get('main_currency')
		);
		if(!empty($value)) {
			$forced = array_merge($forced, $value);
			hikashop_toInteger($forced);
		}

		$select = array('c.*, concat(c.currency_symbol, \' \', c.currency_code) as name');
		$table = array('#__hikashop_currency AS c');
		$where = array('(c.currency_published = 1 OR c.currency_id IN ('.implode(',', $forced).'))');

		if(!empty($search)) {
			$searchMap = array('c.currency_name', 'c.currency_code', 'c.currency_symbol');
			if(!HIKASHOP_J30)
				$searchVal = '\'%' . $this->db->getEscaped(HikaStringHelper::strtolower($search), true) . '%\'';
			else
				$searchVal = '\'%' . $this->db->escape(HikaStringHelper::strtolower($search), true) . '%\'';
			$where['search'] = '('.implode(' LIKE '.$searchVal.' OR ', $searchMap).' LIKE '.$searchVal.')';
		}

		$order = ' ORDER BY c.currency_code ASC';

		if(count($where))
			$where = ' WHERE ' . implode(' AND ', $where);
		else
			$where = '';

		$query = 'SELECT '.implode(', ', $select) . ' FROM ' . implode(' ', $table) . $where . $order;
		$this->db->setQuery($query, $page, $limit);

		$ret[0] = $this->db->loadObjectList('currency_id');

		if(count($ret[0]) < $limit)
			$fullLoad = true;

		if(empty($value))
			return $ret;

		if($mode == hikashopNameboxType::NAMEBOX_SINGLE && isset($ret[0][$value])) {
			$ret[1][$value] = $ret[0][$value];
		} elseif($mode == hikashopNameboxType::NAMEBOX_SINGLE) {
			$query = 'SELECT '.implode(', ', $select) . ' FROM ' . implode(' ', $table) . ' WHERE c.currency_id = '.(int)$value;
			$this->db->setQuery($query);
			$ret[1][$value] = $this->db->loadObject();
		} elseif($mode == hikashopNameboxType::NAMEBOX_MULTIPLE && is_array($value)) {
			foreach($value as $v) {
				if(isset($ret[0][$v])) {
					$ret[1][$v] = $ret[0][$v];
				}
			}
		}
		return $ret;
	}
}
