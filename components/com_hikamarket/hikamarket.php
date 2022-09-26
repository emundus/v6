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
jimport('joomla.application.component.controller');
jimport('joomla.application.component.view');

if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);
include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikamarket'.DS.'helpers'.DS.'helper.php');

hikaInput::get()->set('hikamarket_front_end_main', 1);

global $Itemid;
if(empty($Itemid)) {
	$urlItemid = hikaInput::get()->getInt('Itemid');
	if($urlItemid)
		$Itemid = $urlItemid;
}

$view = hikaInput::get()->getCmd('view');
if(!empty($view) && strlen($view) > 6 && substr($view, -6) == 'market')
	$view = substr($view, 0, -6);
if(!empty($view) && !hikaInput::get()->getCmd('ctrl')) {
	hikaInput::get()->set('ctrl', $view);
	$layout = hikaInput::get()->getCmd('layout');
	if(!empty($layout)) {
		hikaInput::get()->set('task', $layout);
	}
} else {
	$ctrl = hikaInput::get()->getCmd('ctrl');
	if(!empty($ctrl) && substr($ctrl, -6) == 'market')
		hikaInput::get()->set('ctrl', substr($ctrl, 0, -6));
}

if(HIKASHOP_J30) {
	$token = hikamarket::getFormToken();
	$isToken = hikaInput::get()->getString($token, '');
	if(!empty($isToken) && !JSession::checkToken('request')) {
		$app = JFactory::getApplication();
		$app->input->request->set($token, 1);
	}
}

$session = JFactory::getSession();
if(is_null($session->get('registry'))) {
	jimport('joomla.registry.registry');
	$session->set('registry', new JRegistry('session'));
}
$taskGroup = hikaInput::get()->getCmd('ctrl', 'vendor');
$className = ucfirst($taskGroup).'MarketController';
$overrideClassName = ucfirst($taskGroup).'MarketControllerOverride';
if(class_exists($overrideClassName)) {
	$className = $overrideClassName;
} elseif(file_exists(HIKAMARKET_CONTROLLER.$taskGroup.'.override.php')) {
	include_once(HIKAMARKET_CONTROLLER.$taskGroup.'.override.php');
}

if(!class_exists($className) && (!file_exists(HIKAMARKET_CONTROLLER.$taskGroup.'.php') || !include_once(HIKAMARKET_CONTROLLER.$taskGroup.'.php'))) {
	if(!hikamarket::getPluginController($taskGroup)) {
		throw new Exception('Page not found : '.$taskGroup, 404);
		return;
	}
}

$classGroup = new $className();

hikaInput::get()->set('view', $classGroup->getName());

$classGroup->execute(hikaInput::get()->getCmd('task'));
$classGroup->redirect();
if(hikaInput::get()->getString('tmpl') !== 'component') {
	echo hikamarket::footer();
}

hikaInput::get()->set('hikamarket_front_end_main',0);
