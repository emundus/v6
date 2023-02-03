<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikamarketMenu_layoutType {
	protected $values = null;

	protected function load() {
		if(!empty($this->values))
			return $this->values;

		$this->values = array(
			'div' => JHTML::_('select.option', 'div', JText::_('DIV')),
			'table' => JHTML::_('select.option', 'table', JText::_('TABLE')),
			'list' => JHTML::_('select.option', 'list', JText::_('LIST')),
			'inherit' => JHTML::_('select.option', 'inherit', JText::_('HIKA_INHERIT')),
		);

		JPluginHelper::importPlugin('hikashop');
		JFactory::getApplication()->triggerEvent('onHkmLayoutTypeLoad', array(&$this->values));

		return $this->values;
	}

	public function display($map, $value, &$js, $id = '') {
		$values = $this->load();
		$options = '';

		if(hikaInput::get()->getCmd('from_display', false) != false)
			unset($values['inherit']);

		if(empty($id))
			return JHTML::_('select.genericlist', $values, $map, 'class="inputbox" size="1" '.$options, 'value', 'text', $value);

		$js .= '
if(!window.localPage) window.localPage = {};
if(window.localPage.switchPanel)
	window.localPage.switchPanel(\''.$id.'\',\''.$value.'\',\'layout\');
';
		$options = 'onchange="if(window.localPage.switchPanel) window.localPage.switchPanel(\''.$id.'\',this.value,\'layout\');"';
		return JHTML::_('select.genericlist', $values, $map, 'class="inputbox" size="1" '.$options, 'value', 'text', $value);
	}
}
