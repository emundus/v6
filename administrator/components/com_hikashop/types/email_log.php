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
class HikashopEmail_logType{
	function load($form){
		$this->values = array();
		if(!$form){
			$this->values[] = JHTML::_('select.option', 'all',JText::_('HIKA_ALL') );
		}
		$db = JFactory::getDBO();
		$db->setQuery('SELECT distinct(email_log_name) as type from #__hikashop_email_log;');
		$email_types = $db->loadObjectList();

		foreach($email_types as $type){
			$this->values[] = JHTML::_('select.option', $type->type, JText::_($type->type));
		}
	}

	function display($map,$value,$form=false){
		$this->load($form);
		if(!$form){
			$options =' onchange="document.adminForm.submit();"';
		}
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"'.$options, 'value', 'text', $value );
	}
}
