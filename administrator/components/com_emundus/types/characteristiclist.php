<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
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
			$this->values[] = JHTML::_('select.option', $val->characteristic_id, $val->characteristic_value);
		}
	}

	function display($map,$value,$options='class="inputbox" size="1"'){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, $options, 'value', 'text', $value );
	}
}
