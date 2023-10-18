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
class hikashopLayoutType {
	public function load() {
		$this->values = array(
			'div' => JHTML::_('select.option', 'div', JText::_('DIV')),
			'table' => JHTML::_('select.option', 'table', JText::_('TABLE')),
			'list' => JHTML::_('select.option', 'list', JText::_('LIST')),
		);

		if(hikaInput::get()->getCmd('from_display', false) == false)
			$this->values['inherit'] = JHTML::_('select.option', 'inherit', JText::_('HIKA_INHERIT'));

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onHkLayoutTypeLoad', array(&$this->values));
	}

	public function display($map, $value, &$js, $update = true, $id = '', $control = '', $module = false) {
		$this->load();
		$options = '';

		$optValues = array();
		foreach($this->values as $k => $optValue) {
			$optValues[] = $optValue->value;
		}
		$optValues = '\''.implode('\',\'',$optValues).'\'';

		if($update) {
			$options = 'var options = ['.$optValues.'];';
			if(!$module) {
				$js .= $options.'switchPanel(\''.$value.'\',options,\'layout\');';
				$options = 'onchange="'.$options.'return switchPanel(this.value,options,\'layout\');"';
			} elseif(!HIKASHOP_J30) {
				$js .= $options.'switchPanelMod(\''.$value.'\',options,\'layout\',\''.$control.'\');';
				$options = 'onchange="'.$options.'return switchPanelMod(this.value,options,\'layout\',\''.$control.'\');"';
			}
		}

		if(!empty($id))
			return JHTML::_('select.genericlist', $this->values, $map, 'class="custom-select" size="1" '.$options, 'value', 'text', $value, 'layout_select'.$control, $id);
		return JHTML::_('select.genericlist', $this->values, $map, 'class="custom-select" size="1" '.$options, 'value', 'text', $value, 'layout_select'.$control );
	}
}
