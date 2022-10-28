<?php
defined('_JEXEC') or die('Access Deny');

// INCLUDES
require_once(dirname(__FILE__).DS.'helper.php');
include_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
$m_campaign     = new EmundusModelCampaign;

include_once (JPATH_BASE.DS.'modules'.DS.'mod_emundus_campaign_dropfiles'.DS.'helper.php');
$helper = new modEmundusCampaignHelper;
// END INCLUDES

JHtml::stylesheet('media/com_emundus/css/mod_emundus_campaign.css');
JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$document 	= JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_campaign/css/mod_emundus_campaign.css" );

// PARAMS
$mod_em_campaign_url=$params->get('mod_em_campaign_url');
$mod_em_campaign_class=$params->get('mod_em_campaign_class');
$mod_em_campaign_intro=$params->get('mod_em_campaign_intro', '');
$mod_em_campaign_start_date=$params->get('mod_em_campaign_start_date');
$mod_em_campaign_end_date=$params->get('mod_em_campaign_end_date');
$mod_em_campaign_list_tab=$params->get('mod_em_campaign_list_tab');
$mod_em_campaign_modules_tab =$params->get('mod_em_campaign_modules_tab', 0);
$mod_em_campaign_param_tab=$params->get('mod_em_campaign_param_tab');
$mod_em_campaign_display_groupby=$params->get('mod_em_campaign_display_groupby');
$mod_em_campaign_groupby=$params->get('mod_em_campaign_groupby');
$mod_em_campaign_order=$params->get('mod_em_campaign_orderby');
$mod_em_campaign_order_type=$params->get('mod_em_campaign_order_type');
$mod_em_campaign_itemid=$params->get('mod_em_campaign_itemid');
$mod_em_campaign_itemid2=$params->get('mod_em_campaign_itemid2');
$mod_em_campaign_date_format = $params->get('mod_em_campaign_date_format', 'd/m/Y H:i');
$mod_em_campaign_show_camp_start_date = $params->get('mod_em_campaign_show_camp_start_date', 1);
$mod_em_campaign_show_camp_end_date = $params->get('mod_em_campaign_show_camp_end_date', 1);
$mod_em_campaign_get_teaching_unity =$params->get('mod_em_campaign_get_teaching_unity', 0);
$mod_em_campaign_get_link =$params->get('mod_em_campaign_get_link', 0);
$mod_em_campaign_show_formation_start_date = $params->get('mod_em_campaign_show_formation_start_date', 0);
$mod_em_campaign_show_formation_end_date = $params->get('mod_em_campaign_show_formation_end_date', 0);
$mod_em_campaign_show_admission_start_date = $params->get('mod_em_campaign_show_admission_start_date', 0);
$mod_em_campaign_show_admission_end_date = $params->get('mod_em_campaign_show_admission_end_date', 0);
$mod_em_campaign_show_nav_order = $params->get('mod_em_campaign_show_nav_order', 1);
$mod_em_campaign_show_timezone = $params->get('mod_em_campaign_show_timezone', 1);
$mod_em_campaign_show_localedate = $params->get('mod_em_campaign_show_localedate', 0);
$mod_em_campaign_show_search = $params->get('mod_em_campaign_show_search', 1);
$mod_em_campaign_show_results = $params->get('mod_em_campaign_show_results', 1);
$showcampaign=$params->get('mod_em_campaign_param_showcampaign');
$showprogramme=$params->get('mod_em_campaign_param_showprogramme');
$redirect_url=$params->get('mod_em_campaign_link', 'registration');
$program_code=$params->get('mod_em_program_code');
$ignored_program_code=$params->get('mod_em_ignored_program_code');
$modules_tabs = $params->get('mod_em_campaign_modules_tab');
$offset = JFactory::getConfig()->get('offset');
$sef = JFactory::getConfig()->get('sef');
// END PARAMS

$condition ='';

$session = JFactory::getSession();
$db = JFactory::getDbo();

$app = JFactory::getApplication();
$order_date = $app->input->getString('order_date', null);
$order_time = $app->input->getString('order_time', null);
$searchword = $app->input->getString('searchword', null);

if (isset($order_date) && !empty($order_date)) {
	$session->set('order_date', $order_date);
} elseif (empty($order)) {
	$session->set('order_date', $mod_em_campaign_order);
}
if (isset($order_time) && !empty($order_time)) {
	$session->set('order_time', $order_time);
} elseif (empty($order)) {
	$session->set('order_time', $mod_em_campaign_order_type);
}

$order = $session->get('order_date');
$ordertime = $session->get('order_time');

if ($params->get('mod_em_campaign_layout') == "institut_fr") {
    include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'programme.php');
    $m_progs = new EmundusModelProgramme;
    if(!empty($program_code)) {
        $program_array['IN'] = array_map('trim', explode(',', $program_code));
    }
    if(!empty($ignored_program_code)) {
        $program_array['NOT_IN'] = array_map('trim', explode(',', $ignored_program_code));
    }
    $programs = $m_progs->getProgrammes(1, $program_array);
}

if (isset($searchword) && !empty($searchword)) {
    $condition = ' AND (pr.code LIKE "%"'.$db->Quote($searchword).'"%" OR ca.label LIKE "%"'.$db->Quote($searchword).'"%" OR ca.description LIKE "%"'.$db->Quote($searchword).'"%" OR ca.short_description LIKE "%"'.$db->Quote($searchword).'"%") ';
}

if (!empty($program_code)) {
    $condition .= " AND pr.code IN(" . implode ( "','", array_map('trim', explode(',', $db->Quote($program_code)))) . ") ";
}

if (!empty($ignored_program_code)) {
    $condition .= " AND pr.code NOT IN(" . implode ( "','", array_map('trim', explode(',', $db->Quote($ignored_program_code)))) . ") ";
}

// Get single campaign
$cid = JFactory::getApplication()->input->getInt('cid', 0);
if (!empty($cid)) {
    $condition = ' AND ca.id = ' . $cid;
}

switch ($mod_em_campaign_groupby) {
    case 'month':
        $condition .= ' ORDER BY '.$order;
        break;
    case 'program':
        $condition .= ' ORDER BY training, '.$order;
        break;
    case 'ordering':
        $condition .= ' ORDER BY ordering, '.$order;
        break;
}

switch ($ordertime) {
    case 'asc':
        $condition .=' ASC';
        break;
    case 'desc':
        $condition .=' DESC';
        break;
}

$mod_em_campaign_get_admission_date = ($mod_em_campaign_show_admission_start_date||$mod_em_campaign_show_admission_end_date);
$currentCampaign    = $helper->getCurrent($condition, $mod_em_campaign_get_teaching_unity);
$pastCampaign       = $helper->getPast($condition, $mod_em_campaign_get_teaching_unity);
$futurCampaign      = $helper->getFutur($condition, $mod_em_campaign_get_teaching_unity);
$allCampaign        = $helper->getProgram($condition, $mod_em_campaign_get_teaching_unity);

if ($params->get('mod_em_campaign_layout') == "single_campaign" || $params->get('mod_em_campaign_layout') == "tchooz_single_campaign") {
// FAQ
    $faq_articles = $helper->getFaq();

    $dropfiles_helper = new modEmundusCampaignDropfilesHelper;
    $files = $dropfiles_helper->getFiles();
}

if ($params->get('mod_em_campaign_layout') == "celsa") {
    $formations = $helper->getFormationsWithType();
    $formationTypes = $helper->getFormationTypes();
    $formationLevels = $helper->getFormationLevels();
    $voiesDAcces = $helper->getVoiesDAcces();

    $currentCampaign = $helper->addClassToData($currentCampaign, $formations);
    $pastCampaign = $helper->addClassToData($pastCampaign, $formations);
    $futurCampaign = $helper->addClassToData($futurCampaign, $formations);
    $allCampaign = $helper->addClassToData($allCampaign, $formations);
}

$now = $helper->now;

jimport('joomla.html.pagination');
$session = JFactory::getSession();

$paginationCurrent  = new JPagination($helper->getTotalCurrent(), $session->get('limitstartCurrent'), $session->get('limit'));
$paginationPast     = new JPagination($helper->getTotalPast(), $session->get('limitstartPast'), $session->get('limit'));
$paginationFutur    = new JPagination($helper->getTotalFutur(), $session->get('limitstartFutur'), $session->get('limit'));
$paginationTotal    = new JPagination($helper->getTotal(), $session->get('limitstart'), $session->get('limit'));

require(JModuleHelper::getLayoutPath('mod_emundus_campaign', $params->get('mod_em_campaign_layout')));

?>
