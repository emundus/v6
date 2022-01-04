<?php

defined('_JEXEC') or die('Access Deny');

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$document 	= JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_dashboard_vue/src/assets/mod_emundus_dashbord_vue.css" );
$document->addScript('media/mod_emundus_dashboard_vue/chunk-vendors.js');
$document->addStyleSheet('media/mod_emundus_dashboard_vue/app.css');


$profiles = $params->get('profile');
$programme_filter = $params->get('filter_programmes');

if(in_array(JFactory::getSession()->get('emundusUser')->profile,$profiles)) {

    $widgets[] = $params->get('widget1');
    $widgets[] = $params->get('widget2');
    $widgets[] = $params->get('widget3');
    $widgets[] = $params->get('widget4');
    $widgets[] = $params->get('widget5');
    $widgets[] = $params->get('widget6');
    $widgets[] = $params->get('widget7');
    $widgets[] = $params->get('widget8');

    require(JModuleHelper::getLayoutPath('mod_emundus_dashboard_vue'));
}
