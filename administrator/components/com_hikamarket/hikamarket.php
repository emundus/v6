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
if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);
include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikamarket'.DS.'helpers'.DS.'helper.php');

$taskGroup = hikaInput::get()->getCmd('ctrl','dashboard');
$hikaMarketConfig = hikamarket::config();
if(HIKASHOP_J40)
	JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'left'));
else
	JHTML::_('behavior.tooltip');
$bar = JToolBar::getInstance('toolbar');
$bar->addButtonPath(HIKAMARKET_BUTTON);

if($taskGroup != 'update' && !$hikaMarketConfig->get('installcomplete')) {
	$url = hikamarket::completeLink('update&task=install', false, true);
	echo '<script>document.location.href="'.$url.'";</script>'."\r\n".
		'Install not finished... You will be redirected to the second part of the install screen<br/>'.
		'<a href="'.$url.'">Please click here if you are not automatically redirected within 3 seconds</a>';
	return;
}

$currentuser = JFactory::getUser();
if($taskGroup != 'update' && !$currentuser->authorise('core.manage', 'com_hikamarket')) {
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
	return;
}
if($taskGroup == 'config' && !$currentuser->authorise('core.admin', 'com_hikamarket')) {
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);;
	return;
}

$className = ucfirst($taskGroup).'MarketController';
$overrideClassName = ucfirst($taskGroup).'MarketControllerOverride';
if(class_exists($overrideClassName)) {
	$className = $overrideClassName;
} elseif(file_exists(HIKAMARKET_CONTROLLER.$taskGroup.'.override.php')) {
	include_once(HIKAMARKET_CONTROLLER.$taskGroup.'.override.php');
}

if(!class_exists($className) && (!file_exists(HIKAMARKET_CONTROLLER.$taskGroup.'.php') || !include_once(HIKAMARKET_CONTROLLER.$taskGroup.'.php'))) {
	if(!hikamarket::getPluginController($taskGroup)) {
		throw new Exception('Controller not found : '.$taskGroup, 404);
		return;
	}
}
ob_start();
if(!class_exists($className)) {
	throw new RuntimeException(JText::sprintf('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER_CLASS', $className), 500);
	return;
}

$classGroup = new $className();
hikaInput::get()->set('view', $classGroup->getName());
$classGroup->execute( hikaInput::get()->getCmd('task', 'listing'));
$classGroup->redirect();
if(hikaInput::get()->getString('tmpl') !== 'component') {
	echo hikamarket::footer();
}
echo '<div id="hikamarket_main_content" class="hikamarket_main_content hika_j'.(int)HIKASHOP_JVERSION.'">'.ob_get_clean().'</div>';
