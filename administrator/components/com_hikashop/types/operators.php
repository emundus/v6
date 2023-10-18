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
class hikashopOperatorsType {
	public $extra = '';
	protected $values = array();

	public function __construct() {
		$this->values = array(
			JHTML::_('select.option', '<OPTGROUP>', JText::_('HIKA_NUMERIC')),
			JHTML::_('select.option', '=','='),
			JHTML::_('select.option', '!=','!='),
			JHTML::_('select.option', '>','>'),
			JHTML::_('select.option', '<','<'),
			JHTML::_('select.option', '>=','>='),
			JHTML::_('select.option', '<=','<='),
			JHTML::_('select.option', '</OPTGROUP>'),
			JHTML::_('select.option', '<OPTGROUP>',JText::_('HIKA_STRING')),
			JHTML::_('select.option', 'BEGINS',JText::_('HIKA_BEGINS_WITH')),
			JHTML::_('select.option', 'END',JText::_('HIKA_ENDS_WITH')),
			JHTML::_('select.option', 'CONTAINS',JText::_('HIKA_CONTAINS')),
			JHTML::_('select.option', 'NOTCONTAINS',JText::_('HIKA_NOT_CONTAINS')),
			JHTML::_('select.option', 'LIKE','LIKE'),
			JHTML::_('select.option', 'NOT LIKE','NOT LIKE'),
			JHTML::_('select.option', 'REGEXP','REGEXP'),
			JHTML::_('select.option', 'NOT REGEXP','NOT REGEXP'),
			JHTML::_('select.option', '</OPTGROUP>'),
			JHTML::_('select.option', '<OPTGROUP>',JText::_('OTHER')),
			JHTML::_('select.option', 'IS NULL','IS NULL'),
			JHTML::_('select.option', 'IS NOT NULL','IS NOT NULL'),
			JHTML::_('select.option', '</OPTGROUP>'),
		);
	}

	public function display($map, $default = '', $additionalClass = '') {
		return JHTML::_('select.genericlist', $this->values, $map, 'class="custom-select '.$additionalClass.'" size="1" style="width:120px;" '.$this->extra, 'value', 'text', $default);
	}

	public function displayFilter($map, $default = '', $additionalClass = '') {
		return $this->display($map, $default, $additionalClass);
	}
}
