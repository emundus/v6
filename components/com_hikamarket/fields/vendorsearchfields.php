<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class JFormFieldVendorsearchfields extends JFormField
{
	public $type = 'help';

	public function getInput() {
		JHTML::_('behavior.modal','a.modal');
		$link = 'index.php?option=com_hikamarket&amp;tmpl=component&amp;ctrl=vendor&amp;task=searchfields&amp;values='.$this->value.'&amp;control=';
		$text = '<input class="inputbox" id="fields" name="'.$this->name.'" type="text" size="20" value="'.$this->value.'">';
		$text .= '<a class="modal" id="linkfields" title="Fields"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}"><button class="btn" onclick="return false">Select</button></a>';
		return $text;
	}
}
