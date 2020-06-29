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
$js = "window.hikashop.ready( function() {window.focus();if(document.all){document.execCommand('print', false, null);}else{window.print();}setTimeout(function(){window.top.hikashop.closeBox();}, 2000);});";
$doc = JFactory::getDocument();
$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");

hikaInput::get()->set('cart_id',hikaInput::get()->getInt('cart_id',0));
hikaInput::get()->set('cart_type',hikaInput::get()->getString('cart_type','cart'));
hikaInput::get()->set('tmpl','component');

$this->setLayout('showcart');
echo $this->loadTemplate();

