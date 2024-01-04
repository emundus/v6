<?php

defined('_JEXEC') or die('Access Deny');

$user = JFactory::getUser();
$emundusUser = JFactory::getSession()->get('emundusUser');

$profiles = $params->get('profile');

if(in_array(JFactory::getSession()->get('emundusUser')->profile, $profiles)) {
    JHtml::script('media/com_emundus/js/jquery.cookie.js');
    JHtml::script('media/jui/js/bootstrap.min.js');

    $document = JFactory::getDocument();
    $document->addStyleSheet("modules/mod_emundus_dashboard_vue/src/assets/mod_emundus_dashbord_vue.css" );
    $document->addScript('media/mod_emundus_dashboard_vue/chunk-vendors.js');
    $document->addStyleSheet('media/mod_emundus_dashboard_vue/app.css');

    $programme_filter = $params->get('filter_programmes', 0);

	$display_description = $params->get('display_description', 0);
	$display_shapes = $params->get('display_shapes', 1);
	$display_tchoozy = $params->get('display_dashboard_tchoozy', 1);
	$display_name = $params->get('display_name', 0);
	$name = JFactory::getSession()->get('emundusUser')->name;

	$current_lang = JFactory::getLanguage()->getTag();
    $language = $current_lang == 'fr-FR' ? 1 : 0;

    require_once(JPATH_SITE . '/components/com_emundus/models/dashboard.php');
    require_once(JPATH_SITE . '/components/com_emundus/models/users.php');
    $m_dashboard = new EmundusModelDashboard;
	$m_users = new EmundusModelUsers;
    $dashboard = $m_dashboard->getDashboard($emundusUser->id);
    if (empty($dashboard)) {
        $m_dashboard->createDashboard($emundusUser->id);
    }

	$profile_details = new stdClass();
	if (!$user->guest) {
		if (!empty($emundusUser->profile)) {
			$profile_details = $m_users->getProfileDetails($emundusUser->profile);
		} else {
			$profile_details->label = '';
			$profile_details->description = '';
		}
	}

    require(JModuleHelper::getLayoutPath('mod_emundus_dashboard_vue'));
}
