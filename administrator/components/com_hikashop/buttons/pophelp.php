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
class JButtonPophelp extends JButton {
	protected $name = 'Pophelp';

	public function fetchButton($type = 'Pophelp', $namekey = '', $id = 'pophelp') {
		$doc = JFactory::getDocument();
		$config = hikashop_config();
		$level = $config->get('level');
		$url = HIKASHOP_HELPURL . $namekey . '&level=' . $level;
		if(hikashop_isSSL())
			$url = str_replace('http://', 'https://', $url);

		$js = '
function displayDoc(){
	var d = document, init = false, b = d.getElementById("iframedoc");
	if(!b) return true;
	if(typeof(b.openHelp) == "undefined") { b.openHelp = true; init = true; }
	if(b.openHelp) {
		b.innerHTML = \'<iframe src="'.$url.'" width="100%" height="100%" style="border:0px" border="no" scrolling="auto"></iframe>\';
		b.style.display = "block";
		b.style.height = \'300px\';
	} else {
		b.innerHTML = "";
		b.style.display = "none";
	}
	b.openHelp = !b.openHelp;
	return false;
}';

		if(HIKASHOP_J40)
			$btnClass = 'btn btn-info';
		else
			$btnClass = 'btn btn-small';

		$doc->addScriptDeclaration($js);
		if(!HIKASHOP_J30)
			return '<a href="' . $url . '" target="_blank" onclick="return displayDoc();" class="toolbar"><span class="icon-32-help" title="' . JText::_('HIKA_HELP', true) . '"></span>' . JText::_('HIKA_HELP') . '</a>';
		return '<button class="'.$btnClass.'" onclick="return displayDoc();"><i class="icon-help"></i> '.JText::_('HIKA_HELP').'</button>';
	}

	public function fetchId($type = 'Pophelp', $html = '', $id = 'pophelp') {
		return $this->name . '-' . $id;
	}
}

class JToolbarButtonPophelp extends JButtonPophelp {}
