<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikamarketNamebox_rawlistType {
	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$fullLoad = true;
		if(empty($options['rawdata']))
			return $ret;

		$ret[0] = $options['rawdata'];

		if(!empty($value) && !is_array($value)) {
			if(is_numeric($value))
				$value = (int)$value;
			$ret[1] = $ret[0][$value];
		} else if(!empty($value) && is_array($value)) {
			foreach($value as $v) {
				if(is_numeric($v))
					$v = (int)$v;
				$ret[1][$v] = $ret[0][$v];
			}
		}
		return $ret;
	}
}
