<?php

defined('_JEXEC') or die('Access Deny');

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'messenger.php');

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$user      = JFactory::getUser();
$applicant = !EmundusHelperAccess::asPartnerAccessLevel($user->id);

if (!$applicant) {
	$emundusUser     = JFactory::getSession()->get('emundusUser');
	$current_profile = $emundusUser->profile;

	require_once(JPATH_SITE . '/components/com_emundus/models/profile.php');
	if (class_exists('EmundusModelProfile')) {
		$m_profile          = new EmundusModelProfile();
		$applicant_profiles = $m_profile->getApplicantsProfilesArray();

		if (in_array($current_profile, $applicant_profiles)) {
			$applicant = true;
		}
	}
}

if ($applicant) {
	$m_messenger = new EmundusModelMessenger();
	$files_count = $m_messenger->getFilesByUser();

	if (count($files_count) > 0) {
		$document = JFactory::getDocument();
		$document->addStyleSheet("modules/mod_emundus_messenger_notifications/src/assets/mod_emundus_messenger_notifications.css");
		$document->addScript('media/mod_emundus_messenger_notifications/chunk-vendors.js');
		$document->addStyleSheet('media/mod_emundus_messenger_notifications/app.css');

		require(JModuleHelper::getLayoutPath('mod_emundus_messenger_notifications'));
	}
}
