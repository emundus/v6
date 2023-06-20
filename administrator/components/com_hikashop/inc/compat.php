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

function bccomp($num1, $num2, $scale = 0) {
	if(!preg_match("/^\+?(\d+)(\.\d+)?$/", $num1, $tmp1) || !preg_match("/^\+?(\d+)(\.\d+)?$/", $num2, $tmp2))
		return 0;
	$num1 = ltrim($tmp1[1], '0');
	$num2 = ltrim($tmp2[1], '0');
	if(strlen($num1) > strlen($num2))
		return 1;
	if(strlen($num1) < strlen($num2))
		return -1;
	$dec1 = isset($tmp1[2]) ? rtrim(substr($tmp1[2], 1), '0') : '';
	$dec2 = isset($tmp2[2]) ? rtrim(substr($tmp2[2], 1), '0') : '';
	if($scale != null) {
		$dec1 = substr($dec1, 0, $scale);
		$dec2 = substr($dec2, 0, $scale);
	}
	$DLen = max(strlen($dec1), strlen($dec2));
	$num1 .= str_pad($dec1, $DLen, '0');
	$num2 .= str_pad($dec2, $DLen, '0');
	for($i = 0; $i < strlen($num1); $i++) {
		if((int)$num1[$i] > (int)$num2[$i])
			return 1;
		if((int)$num1[$i] < (int)$num2[$i])
			return -1;
	}
	return 0;
}
