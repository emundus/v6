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
jimport('joomla.application.component.controller');
jimport('joomla.application.component.view');

if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);
include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php');

hikaInput::get()->set('hikashop_front_end_main', 1);

if(defined('JDEBUG') && JDEBUG){
	error_reporting(E_ALL);
 	@ini_set('display_errors', 1);
}

$config =& hikashop_config();
if($config->get('store_offline')) {
	$tmpl = hikaInput::get()->getCmd('tmpl', '');
	if(in_array($tmpl, array('ajax', 'raw', 'component'))) {
		$ret = array(
			'ret' => 0,
			'message' => JText::_('SHOP_IN_MAINTENANCE')
		);
		echo json_encode($ret);
		exit;
	}
	$app = JFactory::getApplication();
	$app->enqueueMessage(JText::_('SHOP_IN_MAINTENANCE'));
	return;
}

global $Itemid;
if(empty($Itemid)) {
	$urlItemid = hikaInput::get()->getInt('Itemid');
	if($urlItemid) {
		$Itemid = $urlItemid;
	}
}

$view = hikaInput::get()->getCmd('view');
if(!empty($view) && !hikaInput::get()->getCmd('ctrl')) {
	hikaInput::get()->set('ctrl', $view);
	$layout = hikaInput::get()->getString('layout');
	if(!empty($layout)){
		hikaInput::get()->set('task', $layout);
	}
}

if(HIKASHOP_J30) {
	$token = hikashop_getFormToken();
	$isToken = hikaInput::get()->getVar($token, '');
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
$taskGroup = hikaInput::get()->getCmd('ctrl','category');

if($taskGroup != 'checkout') {
	$app = JFactory::getApplication();
	$app->setUserState('com_hikashop.ssl_redirect',0);
}

$classGroup = hikashop_get('controller.'.$taskGroup);

if(empty($classGroup)) {
	throw new Exception('Page not found : '.$taskGroup, 404);
	return;
}

hikaInput::get()->set('view', $classGroup->getName() );

$classGroup->execute(hikaInput::get()->getString('task'));

$classGroup->redirect();
if(hikaInput::get()->getString('tmpl') !== 'component'){
	echo hikashop_footer();
}

hikaInput::get()->set('hikashop_front_end_main',0);
