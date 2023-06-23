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
class hikashopShowpopupoptionType {
	function load($show_inherit = true, $groupby = false) {
		$this->values = array(
			0 => JHTML::_('select.option', 0, JTEXT::_('NO_POPUP')),
			1 => JHTML::_('select.option', 1, JText::_('ALL_POPUP')),
			2 => JHTML::_('select.option', 2, JText::_('CHOOSE_POPUP'))
		);

		if($show_inherit && hikaInput::get()->getCmd('from_display', false) == false) {
			$config = hikashop_config();
			$defaultParams = $config->get('default_params');
			$default = '';
			if(isset($defaultParams['product_popup_mode']))
				$default = ' ('.$this->values[$defaultParams['product_popup_mode']]->text.')';
			$this->values['inherit'] = JHTML::_('select.option', 'inherit', JText::_('HIKA_INHERIT').$default);
		}
	}

	function display($map, $value, $form = true, $show_inherit = true, $groupby = false) {
		$this->load($show_inherit, $groupby);
		$options = 'class="custom-select" size="1" ';
		if(!$form) {
			$options .= 'onchange="this.form.submit();"';
		}
		return JHTML::_('select.genericlist', $this->values, $map, $options, 'value', 'text', $value);
	}
}
