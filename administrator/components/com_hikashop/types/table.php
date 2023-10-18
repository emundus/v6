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
class hikashopTableType {
	protected $externalValues = null;
	public $value = null;

	public function load($form = false) {
		$this->values = array();
		if(!$form) {
			$this->values[''] = JHTML::_('select.option', '', JText::_('HIKA_ALL') );
		}

		$this->values['address'] = JHTML::_('select.option', 'address', JText::_('ADDRESS'));
		if(hikashop_level(1)) {
			$this->values['user'] = JHTML::_('select.option', 'user', JText::_('HIKA_USER') );
			$this->values['product'] = JHTML::_('select.option', 'product', JText::_('PRODUCT'));
			$this->values['category'] = JHTML::_('select.option', 'category', JText::_('CATEGORY'));
			$this->values['contact'] = JHTML::_('select.option', 'contact', JText::_('HIKA_CONTACT'));

			if(hikashop_level(2)) {
				$this->values['order'] = JHTML::_('select.option', 'order', JText::_('HIKASHOP_ORDER'));
				$this->values['item'] = JHTML::_('select.option', 'item', JText::_('HIKASHOP_ITEM'));
				$this->values['entry'] = JHTML::_('select.option', 'entry', JText::_('HIKASHOP_ENTRY'));
			}
		}

		if($this->externalValues == null) {
			$this->externalValues = array();
			JPluginHelper::importPlugin('hikashop');
			$app = JFactory::getApplication();
			$app->triggerEvent('onTableFieldsLoad', array( &$this->externalValues ) );
		}
		if(!empty($this->externalValues)) {
			foreach($this->externalValues as $externalValue) {
				if(!empty($externalValue->table) && substr($externalValue->value, 0, 4) != 'plg.')
					$externalValue->value = 'plg.' . $externalValue->value;
				$this->values[$externalValue->value] = JHTML::_('select.option', $externalValue->value, $externalValue->text);
			}
		}
	}

	public function display($map, $value, $form = false, $optionsArg = '') {
		$this->load($form);
		$options = 'class="custom-select" size="1" ';
		if(!$form) {
			$options .= 'onchange="document.adminForm.submit();" ';
		}
		return JHTML::_('select.genericlist', $this->values, $map, $options . $optionsArg, 'value', 'text', $value);
	}
}
