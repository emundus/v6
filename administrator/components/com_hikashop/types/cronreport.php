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
class hikashopCronreportType {
	public function load() {
		$this->values = array(
			JHTML::_('select.option', '0', JText::_('HIKA_NONE')),
			JHTML::_('select.option', '1', JText::_('EACH_TIME')),
			JHTML::_('select.option', '2', JText::_('ONLY_ACTION'))
		);

		static $initJs = false;
		if($initJs)
			return;
		$initJs = true;

		$js = '
function updateCronReport() {
	var el = document.getElementById("cronsendreport");
	if(!el) return;
	var cronsendreport = el.value;
	el = document.getElementById("cronreportdetail");
	if(!el)
		return;
	el.style.display = (cronsendreport != 0) ? "" : "none";
}
window.hikashop.ready(function(){ updateCronReport(); });
';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
	}

	public function display($map, $value) {
		$this->load();
		return JHTML::_('select.genericlist', $this->values, $map, 'class="custom-select" size="1" onchange="updateCronReport();"', 'value', 'text', (int)$value, 'cronsendreport');
	}
}
