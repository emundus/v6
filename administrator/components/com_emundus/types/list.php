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
class hikashopListType {
	protected $values = array();

	protected function load() {
		$this->values = array(
			'nochild' => JHTML::_('select.option', 'nochild', JText::_('NO_CHILD') ),
			'allchildren' => JHTML::_('select.option', 'allchilds', JText::_('ALL_CHILDS')),
			'allchildrenexpand' => JHTML::_('select.option', 'allchildsexpand', JText::_('ALL_CHILDS_EXPANDED'))
		);

		if(JRequest::getCmd('from_display', false) != false)
			return;

		$config = hikashop_config();
		$defaultParams = $config->get('default_params');
		$default = '';
		if(isset($defaultParams['child_display_type'])) {
			if($defaultParams['child_display_type'] == 'allchilds')
				$defaultParams['child_display_type'] = 'allchildren';
			if($defaultParams['child_display_type'] == 'allchildsexpand')
				$defaultParams['child_display_type'] = 'allchildrenexpand';

			$default = ' ('.$this->values[$defaultParams['child_display_type']]->text.')';
		}

		$this->values['inherit'] = JHTML::_('select.option', 'inherit', JText::_('HIKA_INHERIT').$default);
	}

	public function display($map, $value) {
		$this->load();
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value);
	}
}
