<?php
/**
 * @package		Joomla
 * @subpackage	eMundus
 * @copyright	Copyright (C) 2015 emundus.fr. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the latest functions only once
require_once dirname(__FILE__).'/helper.php';
include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'checklist.php');
include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');

$document = JFactory::getDocument();
$document->addStyleSheet( JURI::base()."media/com_emundus/lib/bootstrap-336/css/bootstrap.min.css" );
$document->addStyleSheet( JURI::base()."media/com_emundus/lib/jquery-plugin-circliful-master/css/material-design-iconic-font.min.css" );
$document->addStyleSheet( 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.css' );


$document->addCustomTag('<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script><![endif]-->');
$document->addCustomTag('<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->');
$document->addScript( 'https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js');
$document->addScript( JURI::base()."media/com_emundus/lib/bootstrap-336/js/bootstrap.min.js");
$document->addScript( JURI::base()."media/com_emundus/lib/jquery-plugin-circliful-master/js/jquery.circliful.js" );

$app 						= JFactory::getApplication();
$Itemid 					= $app->input->getInt('Itemid', null, 'int');

$eMConfig 					= JComponentHelper::getParams('com_emundus');
$applicant_can_renew 		= $eMConfig->get('applicant_can_renew', '0');

$description		 		= $params->get('description', '');
$show_add_application 		= $params->get('show_add_application', 1);
$position_add_application 	= (int)$params->get('position_add_application', 0);

$applications		= modemundusApplicationsHelper::getApplications($params);
$linknames 			= $params->get('linknames', 0);
$moduleclass_sfx 	= htmlspecialchars($params->get('moduleclass_sfx'));
$user 				= JFactory::getUser();

$m_application 		= new EmundusModelApplication;
$checklist 			= new EmundusModelChecklist;

if (isset($user->fnum) && !empty($user->fnum)) {
	$attachments 		= $m_application->getAttachmentsProgress($user->id, $user->profile, array_keys($applications));
	$forms 				= $m_application->getFormsProgress($user->id, $user->profile, array_keys($applications));

	$confirm_form_url 	= $checklist->getConfirmUrl(); 
}

require JModuleHelper::getLayoutPath('mod_emundus_applications', $params->get('layout', 'default'));
