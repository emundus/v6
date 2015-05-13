<?php
/**
 * @version		$Id: smoothtop.php 2012-10-10 20:41:25Z eschneider $
 * @package		plg_sys_smoothtop
 * @version		1.2.3
 * @copyright	Copyright (C) 2012 Eric Schneider. All rights reserved.
 * @credits     Based somewhat on code by Michael Richey. Copyright (C) 2005 - 2011 Michael Richey. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');

class plgSystemSmoothTop extends JPlugin {

	function onBeforeRender() {

		// do we run in administrator ?
		$app = JFactory::getApplication();
		if($app->isAdmin() && $this->params->get('runinadmin',0)==0) return true;

		$doc = JFactory::getDocument();
		// we don't run in pages that aren't html
		if($doc->getType() != 'html') return true;

		// we don't run in modal pages or other incomplete pages
		$nogo = array('component','raw');
		if(in_array(JRequest::getString('tmpl'),$nogo)) return true;

		// sweet - it's on!
		if($this->params->get('topalways',0)==1) $script[]="\twindow.scrollTo(0,0);";

		$hz_pos = $this->params->get('hz-pos','right');
		$vt_pos = $this->params->get('vt-pos','bottom');
		if (is_numeric ($hz_offset = $this->params->get('hz-offset','5px'))) {
			$hz_offset .= 'px';
		};
		if (is_numeric ($vt_offset = $this->params->get('vt-offset','5px'))) {
			$vt_offset .= 'px';
		};

		$script=array();
		$script[]="// Fade our <div> tag to 0 or 'num'";
		$script[]="function fade_me(num){";
		$script[]="	var smoothtop=document.id('smoothtop');";
		$script[]="	if(smoothtop){smoothtop.fade(window.getScrollTop()<".$this->params->get('revealposition',250)."?0:num);}";
		$script[]="}";
		
		$script[]="window.addEvent('domready',function(){";
		$script[]="	// Create Fx.Scroll object";
		$script[]="	var scroll=new Fx.Scroll(window,{";
		$script[]="		'duration':	".$this->params->get('scrollduration',500).",";
		$script[]="		'transition':	Fx.Transitions.".$this->params->get('trans-effect','Expo.easeInOut').",";
		$script[]="		'wait':		false";
		$script[]="	});";

		$script[]="	// Create an <div> tag for SmoothTop";
		$script[]="	var smoothtop=new Element('div',{";
		$script[]="		'id':		'smoothtop',";
		$script[]="		'class':	'smoothtop',";
		$script[]="		'style':	'position:fixed; display:block; visibility:visible; zoom:1; opacity:0; cursor:pointer; ".$hz_pos.":".$hz_offset."; ".$vt_pos.":".$vt_offset.";',";
		$script[]="		'title':	'".$this->params->get('linktitle','')."',";
		$script[]="		'html':		'".$this->params->get('linktext','')."',";
		$script[]="		'events':{";
		$script[]="			// No transparent when hover";
		$script[]="			mouseover: function(){fade_me(1);},";
		$script[]="			// Transparent when no hover";
		$script[]="			mouseout: function(){fade_me(".$this->params->get('non-hover-trans','0.7').");},";
		$script[]="			// Scroll Up on click";
		$script[]="			click: function(){scroll.toTop();}";
		$script[]="		}";
		$script[]="	// Inject our <div> tag into the document body";
		$script[]="	}).inject(document.body);";
		$script[]="	// Gottta do this for stupid IE";
		$script[]="	document.id('smoothtop').setStyle('opacity','0');";
		if ($hz_pos == 'center') {
			$script[]="	document.id('smoothtop').setStyles({'left':(window.getScrollSize().x/2)-(document.id('smoothtop').getSize().x/2)});";
		};
		$script[]="});";

		$script[]="// Show/Hide our <div> tag";
		$script[]="window.addEvent('scroll',function(){fade_me(".$this->params->get('non-hover-trans','0.7').");});";
		
		// check Joomla! version
		$db = JFactory::getDBO();
		$db->setQuery("SELECT version_id FROM #__schemas WHERE extension_id = 700;");
		$version = explode(".", $db->loadResult());
		// use version appropriate means to load mootools
		$version[0] > 2 ? JHtml::_('behavior.framework') : JHtml::_('behavior.mootools');

		$doc->addScriptDeclaration(implode("\n",$script), 'text/javascript');
		if($this->params->get('usestyle',1)==1) $doc->addStyleDeclaration($this->params->get('linkstyle'));
		return true;
	}
}