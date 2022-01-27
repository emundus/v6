<?php

defined('_JEXEC') or die('Access Deny');

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$document 	= JFactory::getDocument();
$document->addStyleSheet("components/com_emundus_onboard/src/assets/css/bootstrap.css" );
$document->addStyleSheet("modules/mod_emundus_dashboard_vue/src/assets/mod_emundus_dashbord_vue.css" );
$document->addStyleSheet("modules/mod_emundus_dashboard_vue/src/assets/vue-multiselect.min.css" );
$document->addScript('media/mod_emundus_dashboard_vue/chunk-vendors.js');
$document->addStyleSheet('media/mod_emundus_dashboard_vue/app.css');

$profiles = $params->get('profile');
$programme_filter = $params->get('filter_programmes');

$user = JFactory::getUser();

if(in_array(JFactory::getSession()->get('emundusUser')->profile,$profiles)) {
    require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus_onboard' . DS . 'models' . DS . 'dashboard.php');

    $m_dashboard = new EmundusonboardModeldashboard;
    $dashboard = $m_dashboard->getDashboard($user->id);
    if (empty($dashboard)) {
        $m_dashboard->createDashboard($user->id);
    }

    require(JModuleHelper::getLayoutPath('mod_emundus_dashboard_vue'));
}
