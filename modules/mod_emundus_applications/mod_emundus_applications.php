<?php
/**
 * @package		Joomla
 * @subpackage	eMundus
 * @copyright	Copyright (C) 2019 emundus.fr. All rights reserved.
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
require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');

$document = JFactory::getDocument();
$document->addStyleSheet("media/com_emundus/lib/bootstrap-336/css/bootstrap.min.css" );
$document->addStyleSheet("media/com_emundus/lib/jquery-plugin-circliful-master/css/material-design-iconic-font.min.css" );
$document->addStyleSheet("modules/mod_emundus_applications/style/mod_emundus_applications.css" );

$document->addCustomTag('<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script><![endif]-->');
$document->addCustomTag('<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->');

$document->addScript("media/jui/js/jquery.min.js" );
$document->addScript("media/com_emundus/lib/bootstrap-336/js/bootstrap.min.js");
$document->addScript("media/com_emundus/lib/jquery-plugin-circliful-master/js/jquery.circliful.js" );

$app = JFactory::getApplication();
$Itemid = $app->input->getInt('Itemid', null, 'int');
$layout = $params->get('layout', 'default');

$eMConfig = JComponentHelper::getParams('com_emundus');
$status_for_send = explode(',', $eMConfig->get('status_for_send', 0));
$applicant_can_renew = $eMConfig->get('applicant_can_renew', '0');
$display_poll = $eMConfig->get('display_poll', 0);
$display_poll_id = $eMConfig->get('display_poll_id', null);
$id_applicants = $eMConfig->get('id_applicants', '0');
$id_profiles = $eMConfig->get('id_profiles', '0');
$applicants = explode(',', $id_applicants);
$id_profiles = explode(',', $id_profiles);

$description = JText::_($params->get('description', ''));
$show_add_application = $params->get('show_add_application', 1);
$position_add_application = (int)$params->get('position_add_application', 0);
$show_progress = $params->get('show_progress', 1);
$show_progress_forms = $params->get('show_progress_forms', 0);
$show_progress_documents = $params->get('show_progress_documents', 0);
$show_progress_color = $params->get('show_progress_color', '#EA5012');
$show_progress_color_forms = $params->get('show_progress_color_forms', '#EA5012');
$show_progress_documents = $params->get('show_progress_documents', '#EA5012');
$admission_status = explode(',', $params->get('admission_status'));
$add_admission_prefix = $params->get('add_admission_prefix', 1);

$show_remove_files = $params->get('show_remove_files', 1);
$show_archive_files = $params->get('show_archived_files', 1);
$show_state_files = $params->get('show_state_files', 0);

$file_status = $params->get('file_status', 1);

$file_tags = JText::_($params->get('tags', ''));

$cc_list_url = $params->get('cc_list_url', 'index.php?option=com_fabrik&view=form&formid=102');

// Due to the face that ccirs-drh is totally different, we use a different method all together to avoid further complicating the existing one.
if ($layout == '_:ccirs-drh') {
	$cc_list_url = $params->get('cc_list_url', 'index.php');
	$applications = modemundusApplicationsHelper::getDrhApplications();
} elseif ($layout == '_:ccirs') {
	$cc_list_url = $params->get('cc_list_url', 'index.php');
	$applications = modemundusApplicationsHelper::getApplications($layout);
} else {
	// We send the layout as a param because Hesam needs different information.
	$applications = modemundusApplicationsHelper::getApplications($layout);
    $states = modemundusApplicationsHelper::getStatusFiles();
}

$linknames = $params->get('linknames', 0);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
$user = JFactory::getSession()->get('emundusUser');
if (empty($user)) {
	$user = new stdClass();
	$user->id = JFactory::getUser()->id;
}

$user->fnums = $applications;

if (empty($user->profile)) {
	$h_list = new EmundusHelperList();
	$user->profile = $h_list->getProfile($user->id);
}

$m_application = new EmundusModelApplication;
$m_profile = new EmundusModelProfile;
$m_checklist = new EmundusModelChecklist;
$m_email = new EmundusModelEmails;

// show application files if applicant profile like current profile and nothing if not
$applicant_profiles = $m_profile->getApplicantsProfilesArray();

if (empty($user->profile) || in_array($user->profile, $applicant_profiles)) {

	$fnums = array_keys($applications);
	$attachments = $m_application->getAttachmentsProgress($fnums);
	$forms = $m_application->getFormsProgress($fnums);

	if (EmundusHelperAccess::asAccessAction(1, 'c')) {
		$applicant_can_renew = 1;
	} else {
        foreach ($user->emProfiles as $profile) {
            if (in_array($profile->id, $id_profiles)) {
                $applicant_can_renew = 1;
                break;
            }
        }
    }


	// Check to see if the applicant meets the criteria to renew a file.
	switch ($applicant_can_renew) {

		// Applicants can apply as many times as they like
		case 1:
			// We need to check if there are any available campaigns.
			$applicant_can_renew = modemundusApplicationsHelper::getAvailableCampaigns();
			break;

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

	if ($display_poll == 1 && $display_poll_id > 0 && isset($user->fnum) && !empty($user->fnum)) {
		$filled_poll_id = modemundusApplicationsHelper::getPoll();
		$poll_url = 'index.php?option=com_fabrik&view=form&formid='.$display_poll_id.'&usekey=fnum&rowid='.$user->fnum.'&tmpl=component';
	} else {
		$poll_url = '';
		$filled_poll_id = 0;
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


