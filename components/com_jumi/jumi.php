<?php
/**
* @version   $Id$
* @package   Jumi
* @copyright (C) 2008 - 2013 Simon Poghosyan
* @license   GNU/GPL v3 http://www.gnu.org/licenses/gpl.html
*/
defined('_JEXEC') or die('Restricted access');
require_once JPATH_COMPONENT . '/router.php';

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}
define('JV', (version_compare(JVERSION, '3', 'l')) ? 'j2' : 'j3');

jimport('joomla.application.component.controller');

if(JV == 'j2') {
	//j2 stuff here///////////////////////////////////////////////////////////////////////////////////////////////////////
	$controller = JController::getInstance('Jumi');
	$controller->execute(JRequest::getCmd('task'));
	$controller->redirect();
}
else {
	//j3 stuff here///////////////////////////////////////////////////////////////////////////////////////////////////////
	$controller = JControllerLegacy::getInstance('Jumi');
	$controller->execute(JFactory::getApplication()->input->get('task'));
	$controller->redirect();
}
