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
class hikashopPartnersType{
	function load($value){
		$this->values = array();
		$db = JFactory::getDBO();
		$query = 'SELECT * FROM '.hikashop_table('user').' WHERE user_partner_activated = 1 OR user_id='.$db->Quote($value);
		$db->setQuery($query);
		$partners = $db->loadObjectList();
		if(!empty($partners)){
			foreach($partners as $partner){
				$this->values[] = JHTML::_('select.option', $partner->user_id, $partner->user_id.' '.$partner->user_partner_email );
			}
		}
	}
	function display($map,$value){
		$this->load($value);
		return JHTML::_('select.genericlist', $this->values, $map, 'class="custom-select" size="1"', 'value', 'text', $value );
	}
}
