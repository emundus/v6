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
$decimal_separator = $config->get('csv_decimal_separator','.');

$export = hikashop_get('helper.spreadsheet');
$export->init($format, 'hikashop_export', $separator, $force_quote);

if(!empty($this->rows)){

	$first = array_keys(get_object_vars(reset($this->rows)));
	$export->writeLine($first);

	foreach($this->rows as $row){
		if(!empty($row->discount_start)) $row->discount_start = hikashop_getDate($row->discount_start,'%Y-%m-%d %H:%M:%S');
		if(!empty($row->discount_end)) $row->discount_end = hikashop_getDate($row->discount_end,'%Y-%m-%d %H:%M:%S');
		$export->writeLine($row);
	}
}

$export->send();
exit;
