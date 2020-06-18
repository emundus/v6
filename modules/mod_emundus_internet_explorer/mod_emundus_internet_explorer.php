<?php
defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'stats.php');

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$document   = JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_internet_explorer/style/mod_emundus_internet_explorer.css" );

$helper = new modEmundusInternetExplorerHelper;

$message = str_replace("\r\n", "",nl2br(addslashes($params->get('message'))));
$layout  = substr($params->get('layout', 'default'), 2);

if($message === "")
	$message = "TEXT_DEFAULT";

$session = JFactory::getSession();
$agent = $_SERVER['HTTP_USER_AGENT'];
if(($session->get('showInternetExplorer') === null || $session->get('showInternetExplorer') === true) && (preg_match('/MSIE/i',$agent) || preg_match('/Trident/i',$agent))) {
	$show = true;
} else {
	$show = false;
}

require(JModuleHelper::getLayoutPath('mod_emundus_internet_explorer',$layout));