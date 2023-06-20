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
class hikashopContentparserType {
	protected $values = null;

	public function load() {
		if($this->values !== null)
			return $this->values;

		$values = array(
			'html' => array(
				'plugin' => null,
				'editor' => null,
				'name' => 'HTML',
			),
		);

		$plugin_values = array();

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onHkContentParserLoad', array(&$plugin_values));

		if(!empty($plugin_values))
			$this->values = array_merge($values, $plugin_values, $values);
		else
			$this->values = $values;

		return $this->values;
	}

	public function display($map, $value, $options = '') {
		$parsers = $this->load();

		$values = array();
		foreach($parsers as $k => $parser) {
			$n = isset($parser['name']) ? JText::_($parser['name']) : JText::_(strtoupper($k));
			$values[$k] = JHTML::_('select.option', $k, $n);
		}

		return JHTML::_('select.genericlist', $values, $map, 'class="custom-select" size="1" '.$options, 'value', 'text', $value);
	}
}
