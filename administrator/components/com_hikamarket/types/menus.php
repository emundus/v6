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
class hikamarketMenusType {
	private $values = null;
	private function load($value) {
		if(isset($this->values))
			return;
		if(!HIKASHOP_J30){
			$query = 'SELECT m.alias as name, m.id as itemid, mt.title ' .
				' FROM `#__menu` as m LEFT JOIN `#__menu_types` as mt on m.menutype = mt.menutype '.
				' WHERE m.client_id = 0 AND m.parent_id != 0 ORDER BY mt.title ASC, m.ordering ASC';
		} else {
			$query = 'SELECT m.alias as name, m.id as itemid, mt.title , m.link '.
				' FROM `#__menu` as m LEFT JOIN `#__menu_types` as mt on m.menutype = mt.menutype '.
				' WHERE m.client_id = 0 AND m.parent_id != 0 AND m.published = 1 AND m.type=\'component\' '.
				' ORDER BY mt.title ASC';
		}

		$db = JFactory::getDBO();
		$db->setQuery($query);
		$menus = $db->loadObjectList('itemid');

		$this->values = array(
			JHTML::_('select.option', '0', JText::_('HIKA_NONE'))
		);

		$lastGroup = '';
		foreach($menus as $menu) {
			if($menu->title != $lastGroup) {
				if(!empty($lastGroup))
					$this->values[] = JHTML::_('select.option', '</OPTGROUP>');
				$this->values[] = JHTML::_('select.option', '<OPTGROUP>', $menu->title);
				$lastGroup = $menu->title;
			}
			if(strpos($menu->link, 'index.php?option='.HIKAMARKET_COMPONENT)===false && $menu->itemid != $value)
				continue;
			$this->values[] = JHTML::_('select.option', $menu->itemid, $menu->name);
		}

		if(!empty($lastGroup))
			$this->values[] = JHTML::_('select.option', '</OPTGROUP>');
	}

	public function display($map, $value) {
		$this->load($value);
		return JHTML::_('select.genericlist', $this->values, $map, 'class="custom-select" size="1"', 'value', 'text', $value);
	}
}
