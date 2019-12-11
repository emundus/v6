<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
hikashop_cleanBuffers();

$config =& hikashop_config();
$format = $config->get('export_format','csv');
$separator = $config->get('csv_separator',';');
$force_quote = $config->get('csv_force_quote',1);
$force_text = $config->get('csv_force_text', false);
$decimal_separator = $config->get('csv_decimal_separator','.');

$export = hikashop_get('helper.spreadsheet');
$export->init($format, 'hikashop_export', $separator, $force_quote, $decimal_separator, $force_text);

if(!empty($this->orders)){
	$maxProd = 0;
	$productFields = null;
	foreach($this->orders as $order){
		if(empty($order->products))
			continue;
		$nbProd = count($order->products);
		if($maxProd < $nbProd){
			$maxProd = $nbProd;
			if(empty($productFields)){
				$productFields = array_keys(get_object_vars(reset($order->products)));
			}
		}
	}

	if($maxProd && !empty($productFields)) {
		$first = array();
		$o = reset($this->orders);
		foreach($o as $key => $val) {
			if(is_array($val))
				continue;
			$first[] = $key;
		}
		$o = null;
		for($i=1;$i<=$maxProd;$i++){
			foreach($productFields as $field){
				$first[] = 'item'.$i.'_'.$field;
			}
		}
	} else {
		$first = array_keys(get_object_vars(reset($this->orders)));
	}
	$export->writeLine($first);

	foreach($this->orders as $row){
		if(!empty($row->user_created)) $row->user_created = hikashop_getDate($row->user_created,'%Y-%m-%d %H:%M:%S');
		if(!empty($row->order_created)) $row->order_created = hikashop_getDate($row->order_created,'%Y-%m-%d %H:%M:%S');
		if(!empty($row->order_modified)) $row->order_modified = hikashop_getDate($row->order_modified,'%Y-%m-%d %H:%M:%S');
		if(!empty($row->order_modified)) $row->order_invoice_created = hikashop_getDate($row->order_invoice_created,'%Y-%m-%d %H:%M:%S');

		if($maxProd && !empty($productFields)){
			for($i=1;$i<=$maxProd;$i++){
				$prod =& $row->products[$i-1];
				foreach($productFields as $field){
					$n = 'item_'.$i.'_'.$field;
					$row->$n = @$prod->$field;
				}
			}
		}

		$export->writeLine($row);
	}
}

$export->send();
exit;
