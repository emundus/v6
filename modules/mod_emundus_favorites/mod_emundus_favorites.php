<?php
/**
 * @package        Joomla
 * @subpackage     eMundus
 * @copyright      Copyright (C) 2015 emundus.fr. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addStyleSheet("media/com_emundus/lib/bootstrap-336/css/bootstrap.min.css");
//$document->addStyleSheet( 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.css' );

$document->addCustomTag('<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script><![endif]-->');
$document->addCustomTag('<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->');
$document->addScript("media/jui/js/jquery.min.js");
$document->addScript("media/com_emundus/lib/bootstrap-336/js/bootstrap.min.js");

$app         = JFactory::getApplication();
$Itemid      = $app->input->getInt('Itemid', null, 'int');
$layout      = $params->get('layout', 'default');
$description = JText::_($params->get('description', ''));
$outro       = JText::_($params->get('outro', ''));

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
$user            = JFactory::getSession()->get('emundusUser');
if (empty($user)) {
	$user     = new stdClass();
	$user->id = JFactory::getUser()->id;
}

require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'programme.php');
$m_programme = new EmundusModelProgramme();
$favorites   = $m_programme->getFavorites($user->id);

require JModuleHelper::getLayoutPath('mod_emundus_favorites', $layout);
