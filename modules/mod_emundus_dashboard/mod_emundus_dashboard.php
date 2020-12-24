<?php

defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$document 	= JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_dashboard/css/mod_emundus_dashboard.css" );
$document->addStyleSheet("modules/mod_emundus_dashboard/css/bootstrap.css" );

$widgets[] = $params->get('widget1');
$widgets[] = $params->get('widget2');
$widgets[] = $params->get('widget3');
$widgets[] = $params->get('widget4');
$widgets[] = $params->get('widget5');
$widgets[] = $params->get('widget6');
$widgets[] = $params->get('widget7');
$widgets[] = $params->get('widget8');

$helper = new modEmundusDashboardHelper();

$campaigns = [];
$cpt_last_campaigns = 0;

foreach ($widgets as $widget) {
    switch ($widget) {
        case 'last_campaign_active':
            $document->addStyleSheet("modules/mod_emundus_dashboard/css/last_campaign_active.css" );
            if(empty($campaigns)) {
                $campaigns = $helper->getLastCampaignActive();
            }
            break;
        case 'faq':
            $document->addStyleSheet("modules/mod_emundus_dashboard/css/faq.css" );
            break;
        case 'files_number_by_status':
            $document->addStyleSheet("modules/mod_emundus_dashboard/css/files_number_by_status.css" );
            $total_files = $helper->getFilesByStatus();
            break;
        case 'tips':
            $document->addStyleSheet("modules/mod_emundus_dashboard/css/tips.css" );
            break;
    }
}

require(JModuleHelper::getLayoutPath('mod_emundus_dashboard'));
