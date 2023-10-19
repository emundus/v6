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
$config =& hikashop_config();
$separator = $config->get('csv_separator',";");
ob_start();
$first_row = reset($this->rows);
echo '"'.implode('"'.$separator.'"',array_keys(get_object_vars($first_row))).'"';
foreach($this->rows as $row){
	foreach(get_object_vars($row) as $k => $v){
		$row->$k = '"'.str_replace('"','\"',$v).'"';
	}
	echo "\n".implode($separator,get_object_vars($row));
}
$data = ob_get_clean();
ini_set('output_buffering', 0);
ini_set('zlib.output_compression', 0);
hikashop_cleanBuffers();
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Content-type: application/octet-stream");
header("Content-Transfer-Encoding: binary");
$len = strlen($data);
header("Content-Length: $len");
header("Cache-Control: maxage=1");
header("Pragma: public");
header("Content-Disposition: attachment; filename=\"entries_".date('Y-m-d_H:i:s').".csv\"");
echo $data;
exit;
