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

class hikashopCharacteristiclistType{

	function load(){
		$query = 'SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_parent_id=0';
		$database = JFactory::getDBO();
		$database->setQuery($query);
		$object = $database->loadObjectList();
		$this->values = array();

		foreach($object as $val){
			$this->values[] = JHTML::_('select.option', $val->characteristic_id, hikashop_translate($val->characteristic_value));
		}
	}

	function display($map,$value,$options='class="custom-select" size="1"'){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, $options, 'value', 'text', $value );
	}
}
