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
class hikashopRegionType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'world',JText::_('HIKA_ALL') );
		$this->values[] = JHTML::_('select.option', '005',JText::_('SOUTH_AMERICA'));
		$this->values[] = JHTML::_('select.option', '013',JText::_('CENTRAL_AMERICA'));
		$this->values[] = JHTML::_('select.option', '021',JText::_('NORTH_AMERICA'));
		$this->values[] = JHTML::_('select.option', '002',JText::_('AFRICA'));
		$this->values[] = JHTML::_('select.option', '017',JText::_('CENTRAL_AFRICA'));
		$this->values[] = JHTML::_('select.option', '015',JText::_('NORTHERN_AFRICA'));
		$this->values[] = JHTML::_('select.option', '018',JText::_('SOUTHERN_AFRICA'));
		$this->values[] = JHTML::_('select.option', '030',JText::_('EASTERN_ASIA'));
		$this->values[] = JHTML::_('select.option', '034',JText::_('SOUTHERN_ASIA'));
		$this->values[] = JHTML::_('select.option', '035',JText::_('ASIA_AND_PACIFIC'));
		$this->values[] = JHTML::_('select.option', '143',JText::_('CENTRAL_ASIA'));
		$this->values[] = JHTML::_('select.option', '145',JText::_('MIDDLE_EAST'));
		$this->values[] = JHTML::_('select.option', '151',JText::_('NORTHERN_ASIA'));
		$this->values[] = JHTML::_('select.option', '154',JText::_('NORTHERN_EUROPE'));
		$this->values[] = JHTML::_('select.option', '155',JText::_('WESTERN_EUROPE'));
		$this->values[] = JHTML::_('select.option', '039',JText::_('SOUTHERN_EUROPE'));
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="custom-select" size="1"', 'value', 'text', $value );
	}
}
