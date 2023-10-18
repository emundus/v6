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
class hikashopWeightType {

	function display($map, $weight_unit, $id = '', $extra = '', $check = true) {
		$config =& hikashop_config();
		$symbols = explode(',',$config->get('weight_symbols', 'kg,g'));

		if(empty($weight_unit))
			$weight_unit = $symbols[0];

		$this->values = array();
		if(!in_array($weight_unit, $symbols)) {
			$this->values[] = JHTML::_('select.option', $weight_unit, JText::_($weight_unit) );

			$app = JFactory::getApplication();
			if($check && hikashop_isClient('administrator'))
				$app->enqueueMessage(JText::sprintf('HIKASHOP_PRODUCT_INVALID_WEIGHT_UNIT',$weight_unit));
		}
		foreach($symbols as $symbol) {
			$this->values[] = JHTML::_('select.option', $symbol, JText::_($symbol) );
		}

		if(!empty($extra))
			$extra = ' '.trim($extra);

		if(!empty($id))
			return JHTML::_('select.genericlist', $this->values, $map, 'class="custom-select weightselect" size="1"'.$extra, 'value', 'text', $weight_unit, $id);
		return JHTML::_('select.genericlist', $this->values, $map, 'class="custom-select weightselect" size="1"'.$extra, 'value', 'text', $weight_unit);
	}
}
