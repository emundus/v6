<?php
/**
 * @package		Joomla
 * @subpackage	eMundus
 * @copyright	Copyright (C) 2019 emundus.fr. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
$m_profile = new EmundusModelProfile();

$user = JFactory::getSession()->get('emundusUser');
$applicant_profiles = $m_profile->getApplicantsProfilesArray();

$specific_profiles = $params->get('for_specific_profiles', '');
if (!empty($specific_profiles)) {
    $specific_profiles = explode(',',$specific_profiles);
} else {
	$specific_profiles = [];
}

if (empty($user->profile) || in_array($user->profile, $applicant_profiles) || (!empty($specific_profiles) && in_array($user->profile, $specific_profiles))) {
    require_once dirname(__FILE__).'/helper.php';
    include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
    include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'list.php');
    require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
    include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
    include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
    include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
    include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
	$m_application = new EmundusModelApplication();

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
    $status_for_delete = $eMConfig->get('status_for_delete', 0);
    if (!empty($status_for_delete) || $status_for_delete == '0') {
        $status_for_delete = explode(',', $status_for_delete);
    }

    $applicant_can_renew = $eMConfig->get('applicant_can_renew', '0');
    $display_poll = $eMConfig->get('display_poll', 0);
    $display_poll_id = $eMConfig->get('display_poll_id', null);
    $id_applicants = $eMConfig->get('id_applicants', null);
    $id_profiles = $eMConfig->get('id_profiles', '');
    $applicants = !empty($id_applicants) ? explode(',', $id_applicants) : [];
	if(!empty($id_profiles)) {
		$id_profiles = explode(',', $id_profiles);
	} else {
		$id_profiles = [];
	}

    $description = JText::_($params->get('description', ''));
    $show_fnum = $params->get('show_fnum', 0);
    $mod_emundus_applications_show_programme = $params->get('mod_emundus_applications_show_programme',1);
    $mod_emundus_applications_show_end_date = $params->get('mod_emundus_applications_show_end_date',1);
    $show_add_application = $params->get('show_add_application', 1);
    $show_show_campaigns = $params->get('show_show_campaigns', 0);
    $campaigns_list_url = $params->get('show_campaigns_url', 'liste-des-campagnes');
    $position_add_application = (int)$params->get('position_add_application', 0);
    $show_progress = $params->get('show_progress', 1);
    $show_progress_forms = $params->get('show_progress_forms', 0);
    $show_progress_documents = $params->get('show_progress_documents', 0);
    $show_progress_color = $params->get('show_progress_color', '#EA5012');
    $show_progress_color_forms = $params->get('show_progress_color_forms', '#EA5012');
    $show_progress_documents = $params->get('show_progress_documents', '#EA5012');
    $admission_status = $params->get('admission_status') ? explode(',', $params->get('admission_status')) : [];
    $add_admission_prefix = $params->get('add_admission_prefix', 1);
    $absolute_urls = $params->get('absolute_urls', 1);

    $show_status = $params->get('show_status', '') !== '' ? explode(',', $params->get('show_status', '')) : null;

    $show_remove_files = $params->get('show_remove_files', 1);
    $show_archive_files = $params->get('show_archived_files', 1);
    $show_state_files = $params->get('show_state_files', 0);
    $show_payment_status = $params->get('show_payment_status', 0);
    $visible_status = $params->get('visible_status', '');
    if ($visible_status != "") {
      $visible_status = explode(',', $params->get('visible_status', ''));
    } else {
		$visible_status = [];
    }
    $mod_em_applications_show_search = $params->get('mod_em_applications_show_search', 1);
    $mod_em_applications_show_sort = $params->get('mod_em_applications_show_sort', 0);
    $mod_em_applications_show_filters = $params->get('mod_em_applications_show_filters', 0);

    $order_applications = $params->get('order_applications', 'esc.end_date');
    $applications_as_desc = $params->get('order_applications_asc_des', 'DESC');
    $query_order_by = $order_applications . ' ' . $applications_as_desc;

    $file_status = $params->get('file_status', 1);
    $file_tags = JText::_($params->get('tags', ''));
    $cc_list_url = $params->get('cc_list_url', 'index.php?option=com_fabrik&view=form&formid=102');

    $groups = $params->get('mod_em_application_group', null);
    $title_other_section = $params->get('mod_em_application_group_title_other', 'MOD_EMUNDUS_APPLICATIONS_OTHER_FILES');
    $date_format = $params->get('mod_em_application_date_format', 'd/m/Y H:i');
    $mod_em_applications_show_hello_text = $params->get('mod_em_applications_show_hello_text',1);
    $custom_actions  = $params->get('mod_em_application_custom_actions');
	$show_tabs = $params->get('mod_em_applications_show_tabs',1);
	$actions = $params->get('mod_emundus_applications_actions',[]);

    // Due to the face that ccirs-drh is totally different, we use a different method all together to avoid further complicating the existing one.
    if ($layout == '_:ccirs-drh') {
        $cc_list_url = $params->get('cc_list_url', 'index.php');
        $applications = modemundusApplicationsHelper::getDrhApplications();
    } elseif ($layout == '_:ccirs') {
        $cc_list_url = $params->get('cc_list_url', 'index.php');
        $applications = modemundusApplicationsHelper::getApplications($layout, $query_order_by);
    } else {
        // We send the layout as a param because Hesam needs different information.
        $applications = modemundusApplicationsHelper::getApplications($layout, $query_order_by);
		$tabs = $m_application->getTabs(JFactory::getUser()->id);
    }

    $linknames = $params->get('linknames', 0);
    $moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

    if (empty($user)) {
        $user = new stdClass();
        $user->id = JFactory::getUser()->id;
    }

    $user->fnums = $applications;

    if (empty($user->profile)) {
        $h_list = new EmundusHelperList();
        $user->profile = $h_list->getProfile($user->id);
    }

    $m_application = new EmundusModelApplication();
    $m_email = new EmundusModelEmails();
    $m_files = new EmundusModelFiles();
    $m_campaign = new EmundusModelCampaign();


	$fnums = array_keys($applications);

    $progress = $m_application->getFilesProgress($fnums);
    $attachments = $progress['attachments'];
    $forms = $progress['forms'];

    if ($show_add_application) {
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

		$available_campaigns = [];
        // Check to see if the applicant meets the criteria to renew a file.
        switch ($applicant_can_renew) {
            // Applicants can apply as many times as they like
            case 1:
                // We need to check if there are any available campaigns.
	            $available_campaigns = modemundusApplicationsHelper::getAvailableCampaigns();
                break;

            // If the applicant can only have one file per campaign.
            case 2:
                // True if does not have a file open in one or more of the available campaigns.
	            $available_campaigns = modemundusApplicationsHelper::getOtherCampaigns($user->id);
                break;

            // If the applicant can only have one file per year.
            case 3:
                // True if periods are found for next year.
	            $available_campaigns = modemundusApplicationsHelper::getFutureYearCampaigns($user->id);
                break;
        }

	    $applicant_can_renew = !empty($available_campaigns);
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

    if (!empty($show_payment_status)) {

        foreach ($applications as $application => $val) {
            $order_status = modemundusApplicationsHelper::getHikashopOrder($applications[$application]);
            $applications[$application]->order_status = $order_status->orderstatus_namekey;
            $applications[$application]->order_color = $order_status->orderstatus_color;
        }

    }

    $status = $m_files->getStatus();

    JPluginHelper::importPlugin('emundus','custom_event_handler');
    \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onBeforeRenderApplications', ['applications' => $applications, 'layout' => $layout, 'params' => $params, 'user' => $user]]);

    require JModuleHelper::getLayoutPath('mod_emundus_applications', $layout);
}


