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
class JButtonPophelp extends JButton {
	private $name = 'Pophelp';

	public function fetchButton($type = 'Pophelp', $namekey = '', $id = 'pophelp') {
		$doc = JFactory::getDocument();
		$config = hikamarket::config();
		$level = $config->get('level');
		$url = HIKAMARKET_HELPURL.$namekey.'&level='.$level;
		$js = '
function displayDoc(){
	var d = document, init = false, b = d.getElementById("iframedoc");
	if(!b) return true;
	if(typeof(b.openHelp) == "undefined") { b.openHelp = true; init = true; }
	if(b.openHelp) { b.innerHTML = \'<iframe src="'.$url.'" width="100%" height="100%" style="border:0px" border="no" scrolling="auto"></iframe>\'; b.setStyle("display","block"); }
	try {
		if(typeof(b.fxEffect) == "undefined") { b.fxEffect = b.effects({duration: 1500, transition: Fx.Transitions.Quart.easeOut}); }
		if(b.openHelp){
			if(init) { b.height = 0; b.style.height = 0; }
			b.fxEffect.stop(); b.fxEffect.start({height: 300});
		}else{
			b.fxEffect.stop(); b.fxEffect.start({height: 0}).chain(function() { b.innerHTML = ""; b.setStyle("display", "none"); });
		}
	} catch(err) {
		if(typeof(b.vslide) == "undefined") { b.vslide = new Fx.Slide("iframedoc"); }
		if(b.openHelp){
			if(init) { b.vslide.hide(); }
			b.vslide.slideIn();
		}else{
			b.vslide.slideOut().chain(function() { b.innerHTML = ""; b.setStyle("display", "none");	});
		}
	}
	b.openHelp = !b.openHelp;
	return false;
}';
		$doc->addScriptDeclaration($js);
		if(!HIKASHOP_BACK_RESPONSIVE)
			return '<a href="' . $url . '" target="_blank" onclick="return displayDoc();" class="toolbar"><span class="icon-32-help" title="' . JText::_('HIKA_HELP', true) . '"></span>' . JText::_('HIKA_HELP') . '</a>';

		$btnClass = (HIKASHOP_J40) ? 'btn btn-info' : 'btn btn-small';
		return '<button class="'.$btnClass.'" onclick="return displayDoc();"><i class="icon-help"></i> ' . JText::_('HIKA_HELP') . '</button>';
	}

	public function fetchId($type = 'Pophelp', $html = '', $id = 'pophelp') {
		return $this->name . '-' . $id;
	}
}

class JToolbarButtonPophelp extends JButtonPophelp {}
