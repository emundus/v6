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
class hikashopMasstableType {
	var $externalValues = null;

	public function load($form = false) {
		$this->values = array();
		if(!$form) {
			$this->values[] = JHTML::_('select.option', '', JText::_('HIKA_ALL'));
		}

		if($this->externalValues == null) {
			$this->externalValues = array();
			JPluginHelper::importPlugin('hikashop');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onMassactionTableLoad', array( &$this->externalValues ) );
		}
		if(!empty($this->externalValues)) {
			foreach($this->externalValues as $externalValue) {
				$this->values[] = JHTML::_('select.option', $externalValue->value, $externalValue->text);
			}
		}
	}

	public function display($map, $value, $form = false, $optionsArg = '') {
		$this->load($form);

		$options = 'class="inputbox" size="1"';
		if(!$form) {
			$options .= ' onchange="document.adminForm.submit();"';
		}

		return JHTML::_('select.genericlist', $this->values, $map, $options . $optionsArg, 'value', 'text', $value);
	}
}
