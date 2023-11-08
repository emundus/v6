<?php
/**
 * @package        Joomla
 * @subpackage     eMundus
 * @copyright      Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the latest functions only once
require_once dirname(__FILE__) . '/helper.php';

$user = JFactory::getSession()->get('emundusUser');
if (!empty($user->fnum)) {
	include_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'application.php');
	require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'checklist.php');
	require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
	require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
	require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'campaign.php');

	$layout          = $params->get('layout', 'default');
	$print           = $params->get('showprint', 1);
	$send            = $params->get('showsend', 1);
	$admission       = $params->get('admission', 0);
	$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

	$eMConfig                = JComponentHelper::getParams('com_emundus');
	$status_for_send         = explode(',', $eMConfig->get('status_for_send', 0));
	$id_applicants           = $eMConfig->get('id_applicants', '0');
	$applicants              = explode(',', $id_applicants);
	$can_edit_after_deadline = $eMConfig->get('can_edit_after_deadline', '0');
	$application_fee         = $eMConfig->get('application_fee', 0);

	$application = modemundusSendApplicationHelper::getApplication($user->fnum);

	$m_application = new EmundusModelApplication;
	$m_checklist   = new EmundusModelChecklist;
	$m_profile     = new EmundusModelProfile;
	$m_files       = new EmundusModelFiles;
	$m_campaign    = new EmundusModelCampaign;

	$current_phase   = $m_campaign->getCurrentCampaignWorkflow($user->fnum);
	$attachments     = $m_application->getAttachmentsProgress($user->fnum);
	$forms           = $m_application->getFormsProgress($user->fnum);
	$application_fee = (!empty($application_fee) && !empty($m_profile->getHikashopMenu($user->profile)));

	if ($application_fee) {
		$fnumInfos = $m_files->getFnumInfos($user->fnum);

		$order = $m_application->getHikashopOrder($fnumInfos);
		$cart  = $m_application->getHikashopCartUrl($user->profile);
		$paid  = !empty($order);
	}

	// We redirect to the "send application" form, this form will redirect to payment if required.
	$confirm_form_url = $m_checklist->getConfirmUrl() . '&usekey=fnum&rowid=' . $user->fnum;
	$uri              = JUri::getInstance();
	$is_confirm_url   = false;

	if (preg_match('/formid=[0-9]+&/', $confirm_form_url, $matches)) {
		if (!empty($matches) && strpos($uri->getQuery(), $matches[0]) !== false) {
			$is_confirm_url = true;
		}
	}

	$app    = JFactory::getApplication();
	$offset = $app->get('offset', 'UTC');
	try {
		$dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
		$dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
		$now      = $dateTime->format('Y-m-d H:i:s');
	}
	catch (Exception $e) {
		echo $e->getMessage() . '<br />';
	}

	if (!empty($user->end_date)) {
		$is_dead_line_passed = strtotime(date($now)) > strtotime($user->end_date);

		if (!empty($current_phase) && !empty($current_phase->end_date)) {
			$is_dead_line_passed = strtotime(date($now)) > strtotime($current_phase->end_date);
		}
		else if ($admission) {
			$is_dead_line_passed = strtotime(date($now)) > strtotime($user->admission_end_date);
		}
	}

	if (!empty($current_phase) && !is_null($current_phase->entry_status)) {
		$is_app_sent = !in_array($user->status, $current_phase->entry_status);

		$status_for_send = array_merge($status_for_send, $current_phase->entry_status);
	}
	else if (!empty($user->status)) {
		$is_app_sent = $user->status != 0;
	}

	require JModuleHelper::getLayoutPath('mod_emundus_send_application', $layout);
}


