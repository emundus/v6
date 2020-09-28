<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class JFormFieldFilters extends JFormField
{
	var $type = 'help';
	function getInput() {
		JHTML::_('behavior.modal','a.modal');
		$link = 'index.php?option=com_hikashop&amp;tmpl=component&amp;ctrl=choose&amp;task=filters&amp;values='.$this->value.'&amp;control=';
		$text = '<input class="inputbox" id="filters" name="'.$this->name.'" type="text" size="20" value="'.$this->value.'">';
		$text .= '<a class="modal" id="linkfilters" title="Filters"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}"><button class="btn" onclick="return false">Select</button></a>';
		return $text;
	}
}
