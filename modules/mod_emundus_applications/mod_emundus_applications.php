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
include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'checklist.php');
include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'list.php');

$document = JFactory::getDocument();
$document->addStyleSheet("media/com_emundus/lib/bootstrap-336/css/bootstrap.min.css" );
$document->addStyleSheet("media/com_emundus/lib/jquery-plugin-circliful-master/css/material-design-iconic-font.min.css" );

$document->addCustomTag('<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script><![endif]-->');
$document->addCustomTag('<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->');
$document->addScript("media/com_emundus/lib/jquery-1.12.4.min.js" );
$document->addScript("media/com_emundus/lib/bootstrap-336/js/bootstrap.min.js");
$document->addScript("media/com_emundus/lib/jquery-plugin-circliful-master/js/jquery.circliful.js" );

$app 						= JFactory::getApplication();
$Itemid 					= $app->input->getInt('Itemid', null, 'int');
$layout                     = $params->get('layout', 'default');

$eMConfig 					= JComponentHelper::getParams('com_emundus');
$applicant_can_renew 		= $eMConfig->get('applicant_can_renew', '0');
$display_poll 				= $eMConfig->get('display_poll', 0);
$display_poll_id 			= $eMConfig->get('display_poll_id', null);
$application_fee			= $eMConfig->get('application_fee', 0);
$id_applicants 			 	= $eMConfig->get('id_applicants', '0');
$applicants 			 	= explode(',',$id_applicants);

$description		 		= JText::_($params->get('description', ''));
$show_add_application 		= $params->get('show_add_application', 1);
$position_add_application 	= (int)$params->get('position_add_application', 0);
$show_progress 				= $params->get('show_progress', 1);
$show_progress_forms 		= $params->get('show_progress_forms', 0);
$show_progress_documents 	= $params->get('show_progress_documents', 0);
$show_progress_color 		= $params->get('show_progress_color', '#EA5012');
$show_progress_color_forms 	= $params->get('show_progress_color_forms', '#EA5012');
$show_progress_documents 	= $params->get('show_progress_documents', '#EA5012');
$admission_status          = explode(',', $params->get('admission_status'));

$show_remove_files         = $params->get('show_remove_files', 1);
$show_archive_files        = $params->get('show_archived_files', 1);
$show_state_files          = $params->get('show_state_files', 0);


// Due to the face that ccirs-drh is totally different, we use a different method all together to avoid further complicating the existing one.
if ($layout == '_:ccirs-drh') {
	$applications = modemundusApplicationsHelper::getDrhApplications();
	$cc_list_url = $params->get('cc_list_url', 'index.php');
} elseif ($layout == '_:ccirs') {
    $cc_list_url = $params->get('cc_list_url', 'index.php');
	$applications = modemundusApplicationsHelper::getApplications($layout);
} else {
	// We send the layout as a param because Hesam needs different information.
	$applications = modemundusApplicationsHelper::getApplications($layout);
    $states = modemundusApplicationsHelper::getStatusFiles();

}


$linknames 			= $params->get('linknames', 0);
$moduleclass_sfx 	= htmlspecialchars($params->get('moduleclass_sfx'));
$user 				= JFactory::getSession()->get('emundusUser');
if (empty($user)) {
	$user = new stdClass();
	$user->id = JFactory::getUser()->id;
}

$user->fnums 	= $applications;

if (empty($user->profile)) {
	$h_list = new EmundusHelperList();
	$user->profile = $h_list->getProfile($user->id);
}


$m_application 	= new EmundusModelApplication;
$m_profile		= new EmundusModelProfile;
$m_checklist 	= new EmundusModelChecklist;

// show application files if applicant profile like current profile and nothing if not
$applicant_profiles = $m_profile->getApplicantsProfilesArray();

if (empty($user->profile) || in_array($user->profile, $applicant_profiles)) {
	
	if (isset($user->fnum) && !empty($user->fnum)) {
		$fnums = array_keys($applications);
		$attachments = $m_application->getAttachmentsProgress($user->id, $user->profile, $fnums);
		$forms = $m_application->getFormsProgress($user->id, $user->profile, $fnums);
		$confirm_form_url = $m_application->getConfirmUrl($fnums);
		$first_page = $m_application->getFirstPage('index.php', $fnums);

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

	$offset = $app->get('offset', 'UTC');
	try {
		$dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
		$dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
		$now = $dateTime->format('Y-m-d H:i:s');
	} catch (Exception $e) {
		echo $e->getMessage() . '<br />';
	}

	if (!empty($user->end_date)) {
		$is_dead_line_passed = (strtotime(date($now)) > strtotime($user->end_date));
	}
	if (!empty($user->status)) {
		$is_app_sent = ($user->status != 0);
	}

	require JModuleHelper::getLayoutPath('mod_emundus_applications', $layout);
}


