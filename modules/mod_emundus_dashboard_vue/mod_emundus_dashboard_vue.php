<?php

defined('_JEXEC') or die('Access Deny');
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
	$display_name = $params->get('display_name', 0);
	$name = JFactory::getSession()->get('emundusUser')->name;

	$current_lang = JFactory::getLanguage()->getTag();
    $language = $current_lang == 'fr-FR' ? 1 : 0;

    require_once(JPATH_SITE . '/components/com_emundus/models/dashboard.php');
    $m_dashboard = new EmundusModelDashboard;
    $dashboard = $m_dashboard->getDashboard($emundusUser->id);
    if (empty($dashboard)) {
        $m_dashboard->createDashboard($emundusUser->id);
    }

    require(JModuleHelper::getLayoutPath('mod_emundus_dashboard_vue'));
}
