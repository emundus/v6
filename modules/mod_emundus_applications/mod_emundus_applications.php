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
include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'admission.php');
include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'checklist.php');
include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');

$document = JFactory::getDocument();
$document->addStyleSheet("media/com_emundus/lib/bootstrap-336/css/bootstrap.min.css" );
$document->addStyleSheet("media/com_emundus/lib/jquery-plugin-circliful-master/css/material-design-iconic-font.min.css" );
//$document->addStyleSheet( 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.css' );

$document->addCustomTag('<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script><![endif]-->');
$document->addCustomTag('<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->');
$document->addScript("media/com_emundus/lib/jquery-1.12.4.min.js" );
$document->addScript("media/com_emundus/lib/bootstrap-336/js/bootstrap.min.js");
$document->addScript("media/com_emundus/lib/jquery-plugin-circliful-master/js/jquery.circliful.js" );

$app 						= JFactory::getApplication();
$Itemid 					= $app->input->getInt('Itemid', null, 'int');

$eMConfig 					= JComponentHelper::getParams('com_emundus');
$applicant_can_renew 		= $eMConfig->get('applicant_can_renew', '0');
$display_poll 				= $eMConfig->get('display_poll', 0);
$display_poll_id 			= $eMConfig->get('display_poll_id', null);

$description		 		= JText::_($params->get('description', ''));
$show_add_application 		= $params->get('show_add_application', 1);
$position_add_application 	= (int)$params->get('position_add_application', 0);
$show_progress 				= $params->get('show_progress', 1);
$show_progress_forms 		= $params->get('show_progress_forms', 0);
$show_progress_documents 	= $params->get('show_progress_documents', 0);
$show_progress_color 		= $params->get('show_progress_color', '#EA5012');
$show_progress_color_forms 	= $params->get('show_progress_color_forms', '#EA5012');
$show_progress_documents 	= $params->get('show_progress_documents', '#EA5012');

$applications		= modemundusApplicationsHelper::getApplications($params);
$linknames 			= $params->get('linknames', 0);
$moduleclass_sfx 	= htmlspecialchars($params->get('moduleclass_sfx'));
$user 				= JFactory::getSession()->get('emundusUser');
if (empty($user)) {
	$user = new stdClass();
	$user->id = JFactory::getUser()->id;
}
$user->fnums 		= $applications;

$m_application 		= new EmundusModelApplication;
$m_profile			= new EmundusModelProfile;
$checklist 			= new EmundusModelChecklist;

if (isset($user->fnum) && !empty($user->fnum)) {
	$attachments 		= $m_application->getAttachmentsProgress($user->id, $user->profile, array_keys($applications));
	$forms 				= $m_application->getFormsProgress($user->id, $user->profile, array_keys($applications));

	$confirm_form_url 	= $checklist->getConfirmUrl().'&usekey=fnum&rowid='.$user->fnum;

	// If the user can
	$profile = $m_profile->getCurrentProfile($user->id);
	if ($profile['profile'] == 8) {
		$admissionInfo = @EmundusModelAdmission::getAdmissionInfo($user->id);
		$admission_fnum = $admissionInfo->fnum;
	}

	// Check to see if the applicant meets the criteria to renew a file.
	switch ($applicant_can_renew) {

		// If the applicant can only have one file per campaign.
		case 2:
			// True if does not have a file open in one or more of the available campaigns.
			$applicant_can_renew = modemundusApplicationsHelper::getOtherCampaigns($user->id);
			break;

		// If the applicant can only have one file per year.
		case 3:
			// True if periods are found for next year.
			$applicant_can_renew = modemundusApplicationsHelper::getFutureYearCampaigns($user->id);
			break;

	}

	if ($display_poll == 1 && $display_poll_id > 0) {
		$filled_poll_id = modemundusApplicationsHelper::getPoll();
		$poll_url = 'index.php?option=com_fabrik&view=form&formid='.$display_poll_id.'&usekey=fnum&rowid='.$user->fnum.'&tmpl=component';
	} else {
		$poll_url = '';
		$filled_poll_id = 0;
	}
}

require JModuleHelper::getLayoutPath('mod_emundus_applications', $params->get('layout', 'default'));
