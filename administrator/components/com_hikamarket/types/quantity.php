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
class hikamarketQuantityType {
	public function display($map, $value) {
		$attribs = '';
		$label = '';
		$id = str_replace(array('][','[',']'),array('__','_',''), $map);

		return '<div class="hk-input-group hkm_quantity_input">'.
			'<input type="text" name="'.$map.'" id="'.$id.'" value="'.$value.'" class="hk-form-control" '.$attribs.'/>'.
			'<div class="hk-input-group-append">'.
				'<button class="hikabtn" onclick="document.getElementById(\''.$id.'\').value=\''.JText::_('UNLIMITED', true).'\';return false;"><i class="fas fa-infinity"></i></button>'.
			'</div>'.
		'</div>';
	}
}
