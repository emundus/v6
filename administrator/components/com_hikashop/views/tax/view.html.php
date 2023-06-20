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

class TaxViewTax extends hikashopView{
	var $ctrl= 'tax';
	var $nameListing = 'RATES';
	var $nameForm = 'RATE';
	var $icon = 'university';
	var $triggerView = true;

	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = HikaStringHelper::strtolower(trim($pageInfo->search));
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$pageInfo->filter->filter_status = $app->getUserStateFromRequest($this->paramBase.".filter_status",'filter_status','','array');
		$pageInfo->filter->filter_end = $app->getUserStateFromRequest($this->paramBase.".filter_end",'filter_end','','string');
		$pageInfo->filter->filter_start = $app->getUserStateFromRequest($this->paramBase.".filter_start",'filter_start','','string');
		$database	= JFactory::getDBO();
		$searchMap = array('a.tax_namekey','a.tax_rate');

		$this->searchOptions = array('order'=> '', 'order_Dir'=> '', 'status'=> '', 'end'=> '', 'start'=> '');
		$this->openfeatures_class = "hidden-features";

		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeTaxListing', array($this->paramBase, &$this->extrafilters, &$pageInfo, &$filters));

		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}
		$query = ' FROM '.hikashop_table('tax').' AS a '.$filters.$order;
		$database->setQuery('SELECT a.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList('tax_namekey');
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows);
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		$filters = array('order_type=\'sale\'');
		if(is_array($pageInfo->filter->filter_status) && count($pageInfo->filter->filter_status) == 1) {
			$pageInfo->filter->filter_status = reset($pageInfo->filter->filter_status);
		}
		switch($pageInfo->filter->filter_status){
			case '':
			case 'all':
				break;
			default:
				if(!is_array($pageInfo->filter->filter_status)) {
					$filters[] = 'order_status = '.$database->Quote($pageInfo->filter->filter_status);
					break;
				}
				if(!count($pageInfo->filter->filter_status) || in_array('', $pageInfo->filter->filter_status))
					break;
				$statuses = array();
				foreach($pageInfo->filter->filter_status as $status){
					$statuses[] = $database->Quote($status);
				}
				$filters[]='order_status IN ('.implode(',',$statuses).')';
				break;
		}

		switch($pageInfo->filter->filter_start){
			case '':
			case '0000-00-00 00:00:00':
				$pageInfo->filter->filter_start = '';
				switch($pageInfo->filter->filter_end){
					case '':
					case '0000-00-00 00:00:00':
						$pageInfo->filter->filter_end = '';
						break;
					default:
						$filter_end = hikashop_getTime($pageInfo->filter->filter_end.' 23:59', '%d %B %Y %H:%M');
						$filters[]='order_created <= '.(int)$filter_end;
						$pageInfo->filter->filter_end=(int)$filter_end;
						break;
				}
				break;
			default:
				$filter_start = hikashop_getTime($pageInfo->filter->filter_start.' 00:00', '%d %B %Y %H:%M');
				switch($pageInfo->filter->filter_end){
					case '':
					case '0000-00-00 00:00:00':
						$pageInfo->filter->filter_end = '';
						$filters[]='order_created >= '.(int)$filter_start;
						$pageInfo->filter->filter_start=(int)$filter_start;
						break;
					default:
						$filter_end = hikashop_getTime($pageInfo->filter->filter_end.' 23:59', '%d %B %Y %H:%M');
						$filters[]='order_created >= '.(int)$filter_start. ' AND order_created <= '.(int)$filter_end;
						$pageInfo->filter->filter_start=(int)$filter_start;
						$pageInfo->filter->filter_end=(int)$filter_end;
						break;
				}
				break;
		}
		if(!empty($filters)){
			$filters_txt = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters_txt = '';
		}

		$database->setQuery('SELECT order_tax_info, order_currency_id, order_discount_price, order_discount_tax, order_full_price, order_number FROM '.hikashop_table('order').$filters_txt);
		$orders_taxes = $database->loadObjectList();

		$config = hikashop_config();
		$main_currency = $config->get('main_currency');
		$currencyClass = hikashop_get('class.currency');
		$currency_ids = array($main_currency=>$main_currency);
		$currencies = array();
		if(count($orders_taxes)){
			foreach($orders_taxes as $k => $v){
				$currency_ids[$v->order_currency_id] = $v->order_currency_id;
			}
		}
		$null = null;
		$currencies = $currencyClass->getCurrencies($currency_ids,$null);

		if(count($orders_taxes)){
			foreach($orders_taxes as $k => $v){
				$orders_taxes[$k]->order_tax_info = hikashop_unserialize($v->order_tax_info);
				$info =& $orders_taxes[$k]->order_tax_info;
				if(!$info) continue;

				foreach($info as $k2 => $taxes_info){
					$tax_amount = $taxes_info->tax_amount;
					if(!isset($taxes_info->tax_rate)) {
						if(!isset($rows[$taxes_info->tax_namekey])) {
							if(!empty($taxes_info->tax_namekey))
								$app->enqueueMessage(JText::sprintf('THE_ORDER_X_HAS_A_TAX_RATE_WHICH_COULD_NOT_BE_FOUND', $v->order_number, $taxes_info->tax_namekey));
							continue;
						}
						$taxes_info->tax_rate = $rows[$taxes_info->tax_namekey]->tax_rate;
					}
					if(isset($taxes_info->amount)) {
						$info[$k2]->amount = $taxes_info->amount;
					} else {
						if($taxes_info->tax_rate != 0)
							$info[$k2]->amount = $currencyClass->round($tax_amount/$taxes_info->tax_rate,$currencyClass->getRounding($v->order_currency_id));
						elseif(count($info) == 1)
							$info[$k2]->amount = $v->order_full_price;
						else
							$info[$k2]->amount = 0;
					}
					$info[$k2]->tax_amount = $currencyClass->round($tax_amount,$currencyClass->getRounding($v->order_currency_id));
					if($main_currency!=$v->order_currency_id){
						$info[$k2]->tax_amount_main_currency = $currencyClass->convertUniquePrice($info[$k2]->tax_amount,$v->order_currency_id,$main_currency);
						$info[$k2]->amount_main_currency = $currencyClass->convertUniquePrice($info[$k2]->amount,$v->order_currency_id,$main_currency);
					}else{
						$info[$k2]->tax_amount_main_currency = $info[$k2]->tax_amount;
						$info[$k2]->amount_main_currency = $info[$k2]->amount;
					}
				}
			}
		}

		if($pageInfo->elements->page){
			foreach($rows as $k => $tax){
				$tax_amounts = array();
				$amounts = array();
				foreach($currencies as $currency_id => $currency){
					$tax_amount = 0;
					$amount = 0;
					if(count($orders_taxes)){
						foreach($orders_taxes as $order_taxes){
							if($order_taxes->order_currency_id != $currency_id || !$order_taxes->order_tax_info) continue;
							foreach($order_taxes->order_tax_info as $order_tax){
								if($order_tax->tax_namekey != $tax->tax_namekey) continue;
								$tax_amount += $order_tax->tax_amount;
								$amount += $order_tax->amount;
							}
						}
					}
					$tax_amounts[$currency_id] = $tax_amount;
					$amounts[$currency_id] = $amount;
				}
				$rows[$k]->tax_amounts = $tax_amounts;
				$rows[$k]->amounts = $amounts;

				$tax_amount_main_currency = 0;
				$amount_main_currency = 0;
				if(count($orders_taxes)){
					foreach($orders_taxes as $order_taxes){
						if(!$order_taxes->order_tax_info) continue;
						foreach($order_taxes->order_tax_info as $order_tax){
							if($order_tax->tax_namekey != $tax->tax_namekey) continue;
							$tax_amount_main_currency += $order_tax->tax_amount_main_currency;
							$amount_main_currency += $order_tax->amount_main_currency;
						}
					}
				}
				$rows[$k]->tax_amount = $tax_amount_main_currency;
				$rows[$k]->amount = $amount_main_currency;
			}
		}

		$tax_zone_type = $config->get('tax_zone_type','shipping');
		$database	= JFactory::getDBO();
		$mainCurr = $config->get('main_currency', 1);

		$filters[] = 'o.order_discount_tax  = 0';
		$filters[] = 'o.order_payment_tax = 0';
		$filters[] = 'o.order_shipping_tax = 0';
		$filters[] = 'op.order_product_tax = 0';


		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}
		$target = 'o.order_id, o.order_full_price, a.address_country, o.order_currency_id';
		$left_join1 = ' LEFT JOIN '.hikashop_table('address').' AS a ON a.address_id = o.order_'.$tax_zone_type.'_address_id ';
		$left_join2 = ' LEFT JOIN '.hikashop_table('order_product').' AS op ON op.order_id = o.order_id';

		$address_query = 'SELECT '.$target.' FROM '.hikashop_table('order').' AS o '.$left_join1.$left_join2. $filters;
		$database->setQuery($address_query);
		$tax_null_order = $database->loadObjectList();

		$ue_countries = array(
			'country_Austria_14' => 'AT',
			'country_Belgium_21' => 'BE',
			'country_Bulgaria_33' => 'BG',
			'country_Croatia_53' => 'HR',
			'country_Cyprus_55' => 'CY',
			'country_Czech_Republic_56' => 'CZ',
			'country_Denmark_57' => 'DK',
			'country_Estonia_67' => 'EE',
			'country_Finland_72' => 'FI',
			'country_France_73' => 'FR',
			'country_Monaco_141' => 'FR',
			'country_Germany_81' => 'DE',
			'country_Greece_84' => 'GR',
			'country_Hungary_97' => 'HU',
			'country_Ireland_103' => 'IE',
			'country_Italy_105' => 'IT',
			'country_Latvia_117' => 'LV',
			'country_Lithuania_123' => 'LT',
			'country_Luxembourg_124' => 'LU',
			'country_Malta_132' => 'MT',
			'country_Netherlands_150' => 'NL',
			'country_Poland_170' => 'PL',
			'country_Portugal_171' => 'PT',
			'country_Romania_175' => 'RO',
			'country_Slovakia_189' => 'SK',
			'country_Slovenia_190' => 'SI',
			'country_Spain_195' => 'ES',
			'country_Sweden_203' => 'SE',
		);
		$data_title = new stdClass();
		$data_title->tr_type = 'title';
		$data_title->tax_namekey = JText::_('ADDITIONAL_INFORMATION');

		$object_in_ue = new stdClass();
		$object_in_ue->tr_type = 'ue_tax';
		$object_in_ue->tax_namekey = JText::_('HIKA_ORDER_WITHOUT_TAX_IN_EU');
		$object_in_ue->tax_rate = '0';

		$object_out_ue = new stdClass();
		$object_out_ue->tr_type = 'ue_tax';
		$object_out_ue->tax_namekey = JText::_('HIKA_ORDER_WITHOUT_TAX_OUT_EU');
		$object_out_ue->tax_rate = '0';

		$processed_order = array();
		$curr_data_title = array();
		$curr_in_ue_total = array();
		$curr_out_ue_total = array();
		$total_in_ue = 0;
		$total_out_ue = 0;

		foreach($currencies as $currency_id => $currency) {
			$curr_data_title[$currency_id] = '0';
			$curr_in_ue_total[$currency_id] = '0';
			$curr_out_ue_total[$currency_id] = '0';

			foreach ($tax_null_order as $order) {
				if($order->order_currency_id !=$currency_id)
					continue;
				if (!in_array($order->order_id, $processed_order)) {
					if ($mainCurr != $order->order_currency_id) {
						$order_full_price = $currencyClass->convertUniquePrice($order->order_full_price, $order->order_currency_id, $mainCurr);
					}else
							$order_full_price = $order->order_full_price;
					if (array_key_exists($order->address_country, $ue_countries)) {
						$total_in_ue = $total_in_ue + round((float)$order_full_price,2);
						$curr_in_ue_total[$order->order_currency_id] += round($order->order_full_price,2);
					}
					else {
						$total_out_ue = $total_out_ue + round((float)$order_full_price,2);
						$curr_out_ue_total[$order->order_currency_id] += round($order->order_full_price,2);
					}
					$processed_order[] = $order->order_id;
				}
			}
		}

		$object_in_ue->amounts = array();
		$object_in_ue->tax_amounts = array();
		$object_out_ue->amounts = array();
		$object_out_ue->tax_amounts = array();

		foreach ($curr_in_ue_total as $id => $value) {
			$object_in_ue->amounts[$id] = $value;
			$object_in_ue->tax_amounts[$id] = '0';
		}
		foreach ($curr_out_ue_total as $id => $value) {
			$object_out_ue->amounts[$id] = $value;
			$object_out_ue->tax_amounts[$id] = '0';
		}

		$object_in_ue->tax_amount = '0';
		$object_in_ue->amount = $total_in_ue;
		$object_out_ue->tax_amount = '0';
		$object_out_ue->amount = $total_out_ue;

		$rows['data_title'] = $data_title;
		$rows['in_ue'] = $object_in_ue;
		$rows['out_ue'] = $object_out_ue;

		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('currencies',$currencies);
		$this->assignRef('main_currency',$main_currency);
		$this->assignRef('currencyHelper',$currencyClass);
		$category = hikashop_get('type.categorysub');
		$category->type = 'status';
		$this->assignRef('category',$category);

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$this->getPagination();

		$this->toolbar = array(
			'export',
			'addNew',
			'editList',
			'deleteList'
		);
		$return = hikaInput::get()->getString('return','');
		if(!empty($return)){
			$this->toolbar[]='cancel';
		}
		$this->assignRef('return',$return);
		$this->toolbar[]='|';
		$this->toolbar[]=array('name' => 'pophelp', 'target' => $this->ctrl.'-listing');
		$this->toolbar[]='dashboard';
	}

	public function export(){
		$this->listing();
		unset($this->rows['data_title']);
	}

	function form(){

		$tax_namekey = hikaInput::get()->getString('tax_namekey');
		if(empty($tax_namekey)){
			$id = hikaInput::get()->get('cid', array(), 'array');
			if(is_array($id) && count($id)) $tax_namekey = reset($id);
			else $tax_namekey = $id;
		}

		$class = hikashop_get('class.tax');
		if(!empty($tax_namekey)){
			$element = $class->get($tax_namekey);
			$task='edit';
		}else{
			$element = new stdClass();
			$element->banner_url = HIKASHOP_LIVE;
			$task='add';
			$tax_namekey='';
		}
		$this->assignRef('element',$element);

		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&tax_namekey='.$tax_namekey);

		$this->toolbar = array(
			'save-group',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		$return = hikaInput::get()->getString('return','');
		$this->assignRef('return',$return);
	}
}
