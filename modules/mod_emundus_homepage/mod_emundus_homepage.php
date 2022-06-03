<?php
/**
 * @package        Joomla
 * @subpackage    eMundus
 * @copyright    Copyright (C) emundus.fr. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$document 	= JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_homepage/style/mod_emundus_homepage.css" );
$document->addStyleSheet('https://fonts.googleapis.com/icon?family=Material+Icons' );

// INCLUDES
require_once(dirname(__FILE__).DS.'helper.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
$m_campaign     = new EmundusModelCampaign;

$helper = new modEmundusHomepageHelper;

// PARAMS
$mod_em_homepage_sections = $params->get('mod_em_homepage_sections');

$mod_em_homepage_banner_image = $params->get('mod_em_homepage_banner_image');
$mod_em_homepage_banner_text = $params->get('mod_em_homepage_banner_text');
$mod_em_homepage_banner_opacity = $params->get('mod_em_homepage_banner_opacity');

$mod_em_homepage_filters_display = $params->get('mod_em_homepage_filters_display');

$mod_em_homepage_introtext_text = $params->get('mod_em_homepage_introtext_text');

$mod_em_homepage_list_display = $params->get('mod_em_homepage_list_display');
$mod_em_homepage_list_program_category = $params->get('mod_em_homepage_list_program_category');
$mod_em_homepage_list_program = $params->get('mod_em_homepage_list_program');
$mod_em_homepage_list_timezone = $params->get('mod_em_homepage_list_timezone');
$mod_em_homepage_list_start_date_display = $params->get('mod_em_homepage_list_start_date_display');
$mod_em_homepage_list_end_date_display = $params->get('mod_em_homepage_list_end_date_display');
$mod_em_homepage_list_text_display = $params->get('mod_em_homepage_list_text_display');
$mod_em_homepage_list_more_informations_display = $params->get('mod_em_homepage_list_more_informations_display');
$mod_em_homepage_list_alerting_display = $params->get('mod_em_homepage_list_alerting_display');
$mod_em_homepage_list_pinned_campaign_display = $params->get('mod_em_homepage_list_pinned_campaign_display');

$mod_em_homepage_details_tabs = $params->get('mod_em_homepage_details_tabs');
$mod_em_homepage_details_text = $params->get('mod_em_homepage_details_text');
$mod_em_homepage_details_start_date_display = $params->get('mod_em_homepage_details_start_date_display');
$mod_em_homepage_details_end_date_display = $params->get('mod_em_homepage_details_end_date_display');
//

/*
if(in_array('banner', $mod_em_homepage_sections)) {
    if(file_exists(JPATH_BASE.'/'.$mod_em_homepage_banner_image)) {
        $mod_em_homepage_banner_image = JPATH_BASE.'/'.$mod_em_homepage_banner_image;
    } else {
        $mod_em_homepage_banner_image = false;
    }
}*/


if(in_array('banner', $mod_em_homepage_sections)) {
    if(file_exists($mod_em_homepage_banner_image)) {
        $mod_em_homepage_banner_image;
    } else {
        $mod_em_homepage_banner_image = false;
    }
}

if ($params->get('mod_em_campaign_layout') == "details") {
    $faq_articles = $helper->getFaq();

    require_once(JPATH_BASE.DS.'modules'.DS.'mod_emundus_campaign_dropfiles'.DS.'helper.php');
    $dropfiles_helper = new modEmundusCampaignDropfilesHelper;
    $files = $dropfiles_helper->getFiles();
}

require(JModuleHelper::getLayoutPath('mod_emundus_homepage', 'default'));
