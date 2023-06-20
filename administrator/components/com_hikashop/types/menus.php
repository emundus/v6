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
class hikashopMenusType {
	public function load($value) {
		if(isset($this->values))
			return;
		if(!HIKASHOP_J30){
			$query = 'SELECT a.alias as name, a.id as itemid, b.title FROM `#__menu` as a LEFT JOIN `#__menu_types` as b on a.menutype = b.menutype WHERE a.client_id=0 AND a.parent_id!=0 ORDER BY b.title ASC,a.ordering ASC';
		} else {
			$query = 'SELECT a.alias as name, a.id as itemid, b.title , a.link FROM `#__menu` as a LEFT JOIN `#__menu_types` as b on a.menutype = b.menutype WHERE a.client_id=0 AND a.parent_id!=0 AND a.published=1 AND a.type=\'component\' ORDER BY b.title ASC';
		}

		$db = JFactory::getDBO();
		$db->setQuery($query);
		$this->menus = $db->loadObjectList('itemid');

		$this->values = array(
			JHTML::_('select.option', '0', JText::_('HIKA_NONE'))
		);

		$lastGroup = '';
		foreach($this->menus as $oneMenu) {
			if($oneMenu->title != $lastGroup) {
				if(!empty($lastGroup))
					$this->values[] = JHTML::_('select.option', '</OPTGROUP>');
				$this->values[] = JHTML::_('select.option', '<OPTGROUP>', $oneMenu->title);
				$lastGroup = $oneMenu->title;
			}
			if(strpos($oneMenu->link, 'index.php?option=com_hikashop')===false && $oneMenu->itemid != $value)
				continue;
			$this->values[] = JHTML::_('select.option', $oneMenu->itemid, $oneMenu->name);
		}

		if(!empty($lastGroup))
			$this->values[] = JHTML::_('select.option', '</OPTGROUP>');
	}

	public function display($map, $value) {
		$this->load($value);
		return JHTML::_('select.genericlist', $this->values, $map , 'class="custom-select" size="1"', 'value', 'text', $value);
	}
}
