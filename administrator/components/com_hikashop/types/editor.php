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
class hikashopEditorType{
	public function __construct() {
		$db = JFactory::getDBO();
		$query = 'SELECT element,name FROM '.hikashop_table('extensions',false).' WHERE folder=\'editors\' AND enabled=1 AND type=\'plugin\' ORDER BY ordering ASC, name ASC';
		$db->setQuery($query);
		$joomEditors = $db->loadObjectList();
		$this->values = array(
			JHTML::_('select.option', '0', JText::_('HIKA_DEFAULT'))
		);
		if(empty($joomEditors))
			return;
		foreach($joomEditors as $myEditor) {
			$this->values[] = JHTML::_('select.option', $myEditor->element,$myEditor->name);
		}
	}
	public function display($map,$value) {
		return JHTML::_('select.genericlist', $this->values, $map , 'class="custom-select" size="1"', 'value', 'text', $value);
	}
}
