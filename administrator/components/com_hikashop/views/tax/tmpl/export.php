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
hikashop_cleanBuffers();

$config =& hikashop_config();
$format = $config->get('export_format','csv');
$separator = $config->get('csv_separator',';');
$force_quote = $config->get('csv_force_quote',1);
$force_text = $config->get('csv_force_text', false);
$decimal_separator = $config->get('csv_decimal_separator','.');

$export = hikashop_get('helper.spreadsheet');
$export->init($format, 'hikashopexport', $separator, $force_quote, $decimal_separator, $force_text);

$classTax = hikashop_get('class.tax');
$columns = array(
	'1' => 'tax_namekey',
	'2' => 'tax_rate'
);
$main_currency = (int)$config->get('main_currency', 1);
$count_curr_column = 0;
$main_curr_code = '';
$i = 3;

if(count($this->currencies)>1){
	foreach($this->currencies as $id => $currency){
		$columns[$i] = JText::sprintf('AMOUNT_X',$currency->currency_code);
		$i++;
		$columns[$i] = JText::sprintf('TAXCLOUD_TAX',$currency->currency_code);
		$i++;
		if ($currency->currency_id == $main_currency)
			$main_curr_code = $currency->currency_code;
	}
}
if ($main_curr_code == '')
	$main_curr_code = $this->currencies['1']->currency_code;

$columns[$i] = JText::_('TOTAL_AMOUNT').'('.$main_curr_code.')';
$i++;
$columns[$i] = 'tax_amount';
if(count($this->currencies)>1)
	$count_curr_column = $i - 4;

$export->writeline($columns);

if(!empty($this->rows)) {
	foreach($this->rows as $k => $tax) {
		$data = array();
		$data[] = $tax->tax_namekey;
		$data[] = $tax->tax_rate;

		if($count_curr_column>0) {
			if (is_array($tax->tax_amounts)) {
				foreach($tax->tax_amounts as $id => $value){
					$data[] = $tax->amounts[$id];
					$data[] = $tax->tax_amounts[$id];
				}
			}
		}
		$data[] = round($tax->amount,2);
		$data[] = round($tax->tax_amount,2);

		$export->writeline($data);
	}
}
$export->send();
exit;
