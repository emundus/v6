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
class hikashopEncodingType{
	function __construct(){
		$this->values = array(
			JHTML::_('select.option', 'binary', 'Binary' ),
			JHTML::_('select.option', 'quoted-printable', 'Quoted-printable' ),
			JHTML::_('select.option', '7bit', '7 Bit'),
			JHTML::_('select.option', '8bit', '8 Bit'),
			JHTML::_('select.option', 'base64', 'Base 64'),
		);
	}
	function display($map,$value){
		return JHTML::_('select.genericlist', $this->values, $map , 'class="custom-select" size="1"', 'value', 'text', $value);
	}
}
