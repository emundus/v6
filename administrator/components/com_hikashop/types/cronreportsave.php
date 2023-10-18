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
class hikashopCronreportsaveType {
	public function load() {
		$this->values = array(
			JHTML::_('select.option', '0',JText::_('HIKASHOP_NO')),
			JHTML::_('select.option', '1',JText::_('SIMPLIFIED_REPORT')),
			JHTML::_('select.option', '2',JText::_('DETAILED_REPORT'))
		);

		static $initJs = false;
		if($initJs)
			return;
		$initJs = true;

		$js = '
function updateCronReportSave() {
	var el = document.getElementById("cronsavereport");
	if(!el) return;
	var cronsavereport = el.value;
	el = document.getElementById("cronreportsave");
	if(!el)
		return;
	el.style.display = (cronsavereport != 0) ? "" : "none";
}
window.hikashop.ready(function(){ updateCronReport(); });
';

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
	}

	public function display($map, $value) {
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="custom-select" size="1" onchange="updateCronReportSave();"', 'value', 'text', (int) $value ,'cronsavereport');
	}
}
