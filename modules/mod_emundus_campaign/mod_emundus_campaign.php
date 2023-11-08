<?php
defined('_JEXEC') or die('Access Deny');

// INCLUDES
require_once(dirname(__FILE__) . DS . 'helper.php');
include_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'campaign.php');
include_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'settings.php');
include_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
$m_campaign = new EmundusModelCampaign();
$m_settings = new EmundusModelsettings();
$helper     = new modEmundusCampaignHelper();
$m_profiles = new EmundusModelProfile();
// END INCLUDES

$now    = $helper->now;
$offset = JFactory::getConfig()->get('offset');
$sef    = JFactory::getConfig()->get('sef');

$app     = JFactory::getApplication();
$session = JFactory::getSession();
$db      = JFactory::getDbo();

$user     = JFactory::getUser();
$e_user   = JFactory::getSession()->get('emundusUser');
$app_prof = $m_profiles->getApplicantsProfilesArray();

if ($user->guest || in_array($e_user->profile, $app_prof)) {
	$document = JFactory::getDocument();
	JHtml::script('media/com_emundus/js/jquery.cookie.js');
	JHtml::script('media/jui/js/bootstrap.min.js');
	if (!in_array($params->get('mod_em_campaign_layout'), ['default_tchooz', 'tchooz_single_campaign'])) {
		JHtml::stylesheet('media/com_emundus/css/mod_emundus_campaign.css');
		$document->addStyleSheet("modules/mod_emundus_campaign/css/mod_emundus_campaign.css");
	}
	else {
		$document->addStyleSheet("modules/mod_emundus_campaign/css/mod_emundus_campaign_tchooz.css");
	}

	// PARAMS
	// TCHOOZ PARAMS
	$mod_em_campaign_get_link             = $params->get('mod_em_campaign_get_link', 0);
	$mod_em_campaign_date_format          = $params->get('mod_em_campaign_date_format', 'd/m/Y H:i');
	$mod_em_campaign_show_camp_start_date = $params->get('mod_em_campaign_show_camp_start_date', 1);
	$mod_em_campaign_show_camp_end_date   = $params->get('mod_em_campaign_show_camp_end_date', 1);
	$mod_em_campaign_display_svg          = $params->get('mod_em_campaign_display_svg', 1);
	$mod_em_campaign_display_hover_offset = $params->get('mod_em_campaign_display_hover_offset', 1);
	$mod_em_campaign_show_timezone        = $params->get('mod_em_campaign_show_timezone', 1);
	$mod_em_campaign_list_sections        = $params->get('mod_em_campaign_list_sections', []);
	$mod_em_campaign_intro                = $params->get('mod_em_campaign_intro', null);
	if (empty($mod_em_campaign_intro) && $params->get('mod_em_campaign_layout') == 'default_tchooz') {
		$mod_em_campaign_intro = $m_settings->getArticle(JFactory::getLanguage()->getTag(), 52)->introtext;
	}
	$mod_em_campaign_show_search             = $params->get('mod_em_campaign_show_search', 1);
	$mod_em_campaign_show_results            = $params->get('mod_em_campaign_show_results', 1);
	$mod_em_campaign_list_tab                = $params->get('mod_em_campaign_list_tab');
	$mod_em_campaign_list_show_programme     = $params->get('mod_em_campaign_show_programme', 1);
	$mod_em_campaign_show_programme_logo     = $params->get('mod_em_campaign_show_programme_logo', 0);
	$mod_em_campaign_show_apply_button       = $params->get('mod_em_campaign_show_apply_button', 0);
	$mod_em_campaign_show_pinned_campaign    = $params->get('mod_em_campaign_show_pinned_campaign', 1);
	$mod_em_campaign_order                   = $params->get('mod_em_campaign_orderby');
	$mod_em_campaign_order_type              = $params->get('mod_em_campaign_order_type');
	$ignored_program_code                    = $params->get('mod_em_ignored_program_code');
	$showprogramme                           = $params->get('mod_em_campaign_param_showprogramme');
	$showcampaign                            = $params->get('mod_em_campaign_param_showcampaign');
	$mod_em_campaign_show_documents          = $params->get('mod_em_campaign_show_documents', 1);
	$mod_em_campaign_show_contact            = $params->get('mod_em_campaign_show_contact', 0);
	$mod_em_campaign_show_registration       = $params->get('mod_em_campaign_show_registration', 1);
	$mod_em_campaign_show_registration_steps = $params->get('mod_em_campaign_show_registration_steps');
	$mod_em_campaign_allow_alerting          = $params->get('mod_em_campaign_allow_alerting', 0);
	$mod_em_campaign_google_schema           = $params->get('mod_em_campaign_google_schema', 0);
	$mod_em_campaign_show_faq                = $params->get('mod_em_campaign_show_faq', 0);
	$mod_em_campaign_details_show_programme  = $params->get('mod_em_campaign_details_show_programme', 1);
	$mod_em_campaign_show_filters            = $params->get('mod_em_campaign_show_filters', 0);
	$mod_em_campaign_show_sort               = $params->get('mod_em_campaign_show_sort', 1);
	$mod_em_campaign_show_filters_list       = $params->get('mod_em_campaign_show_filters_list', []);
	$mod_em_campaign_sort_list               = $params->get('mod_em_campaign_sort_list');
	$mod_em_campaign_groupby                 = $params->get('mod_em_campaign_groupby');

	// OLD PARAMS
	$mod_em_campaign_url                       = $params->get('mod_em_campaign_url');
	$mod_em_campaign_class                     = $params->get('mod_em_campaign_class');
	$mod_em_campaign_start_date                = $params->get('mod_em_campaign_start_date');
	$mod_em_campaign_end_date                  = $params->get('mod_em_campaign_end_date');
	$mod_em_campaign_modules_tab               = $params->get('mod_em_campaign_modules_tab', 0);
	$mod_em_campaign_param_tab                 = $params->get('mod_em_campaign_param_tab');
	$mod_em_campaign_display_groupby           = $params->get('mod_em_campaign_display_groupby');
	$mod_em_campaign_itemid                    = $params->get('mod_em_campaign_itemid');
	$mod_em_campaign_itemid2                   = $params->get('mod_em_campaign_itemid2');
	$mod_em_campaign_get_teaching_unity        = $params->get('mod_em_campaign_get_teaching_unity', 0);
	$mod_em_campaign_show_formation_start_date = $params->get('mod_em_campaign_show_formation_start_date', 0);
	$mod_em_campaign_show_formation_end_date   = $params->get('mod_em_campaign_show_formation_end_date', 0);
	$mod_em_campaign_show_admission_start_date = $params->get('mod_em_campaign_show_admission_start_date', 0);
	$mod_em_campaign_show_admission_end_date   = $params->get('mod_em_campaign_show_admission_end_date', 0);
	$mod_em_campaign_show_nav_order            = $params->get('mod_em_campaign_show_nav_order', 1);
	$mod_em_campaign_show_localedate           = $params->get('mod_em_campaign_show_localedate', 0);
	$redirect_url                              = $params->get('mod_em_campaign_link', 'registration');
	$program_code                              = $params->get('mod_em_program_code');
	$modules_tabs                              = $params->get('mod_em_campaign_modules_tab');
	// END PARAMS

	$links = $helper->getLinks();

	$condition = '';

	$order_date      = $app->input->getString('order_date', null);
	$order_time      = $app->input->getString('order_time', null);
	$group_by        = $app->input->getString('group_by', null);
	$searchword      = $app->input->getString('searchword', null);
	$codes           = $app->input->getString('code', null);
	$categories_filt = $app->input->getString('category', null);

	// this verification is used to prevent SQL injection
	if (!empty($order_date) && in_array($order_date, ['start_date', 'end_date', 'formation_start', 'formation_end'])) {
		$session->set('order_date', $order_date);
	}
	elseif (empty($order)) {
		$session->set('order_date', $mod_em_campaign_order);
	}

	if (!empty($order_time) && in_array($order_time, ['asc', 'desc'])) {
		$session->set('order_time', $order_time);
	}
	elseif (empty($order)) {
		$session->set('order_time', $mod_em_campaign_order_type);
	}
	if (isset($group_by) && !empty($group_by)) {
		$session->set('group_by', $group_by);
	}
	elseif (empty($group_by)) {
		$session->set('group_by', $mod_em_campaign_groupby);
	}
	if (isset($codes) && !empty($codes)) {
		$session->set('code', $codes);
	}
	elseif (empty($codes)) {
		$session->clear('code');
	}
	if (isset($categories_filt) && !empty($categories_filt)) {
		$session->set('category', $categories_filt);
	}
	elseif (empty($categories_filt)) {
		$session->clear('category');
	}

	$order           = $session->get('order_date');
	$ordertime       = $session->get('order_time');
	$group_by        = $session->get('group_by');
	$codes           = $session->get('code');
	$categories_filt = $session->get('category');

	$program_array = [];
	if ($params->get('mod_em_campaign_layout') == 'institut_fr') {
		if (!empty($program_code)) {
			$program_array['IN'] = array_map('trim', explode(',', $program_code));
		}
		if (!empty($ignored_program_code)) {
			$program_array['NOT_IN'] = array_map('trim', explode(',', $ignored_program_code));
		}
	}

	include_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'programme.php');
	$m_progs  = new EmundusModelProgramme();
	$programs = $m_progs->getProgrammes(1, $program_array);

	if (in_array('category', $mod_em_campaign_show_filters_list)) {
		$categories = [];
		foreach ($programs as $program) {
			if (!in_array($program['programmes'], $categories) && !empty($program['programmes'])) {
				$categories[] = $program['programmes'];
			}
		}
	}

	$condition = '';
	if (!empty($searchword)) {
		$condition .= ' AND (ca.label LIKE "%"' . $db->quote($searchword) . '"%" OR ca.short_description LIKE "%"' . $db->quote($searchword) . '"%"';
		if ($mod_em_campaign_list_show_programme == 1) {
			$condition .= ' OR pr.code LIKE "%"' . $db->quote($searchword) . '"%"';
		}
		$condition .= ') ';

	}

	if (!empty($program_code)) {
		$condition .= ' AND pr.code IN (' . implode(',', array_map('trim', explode(',', $db->quote($program_code)))) . ')';
	}

	if (!empty($codes)) {
		$condition .= ' AND pr.code IN (' . implode(',', $db->quote(explode(',', $codes))) . ')';
	}
	if (!empty($categories_filt)) {
		$condition .= ' AND pr.programmes IN (' . implode(',', $db->quote(explode(',', $categories_filt))) . ')';
	}


	if (!empty($ignored_program_code)) {
		$condition .= ' AND pr.code NOT IN (' . implode(',', $db->quote(array_map('trim', explode(',', $ignored_program_code)))) . ')';
	}

// Get single campaign
	$cid = $app->input->getInt('cid', 0);
	if (!empty($cid)) {
		$condition = ' AND ca.id = ' . $cid;
	}

	switch ($group_by) {
		case 'month':
			$condition .= ' ORDER BY ' . $order;
			break;
		case 'program':
			$condition .= ' ORDER BY training, ' . $order;
			break;
		case 'ordering':
			$condition .= ' ORDER BY ordering, ' . $order;
			break;
		default:
			$condition .= ' ORDER BY ' . $order;
	}

	switch ($ordertime) {
		case 'asc':
			$condition .= ' ASC';
			break;
		case 'desc':
			$condition .= ' DESC';
			break;
		default:
			$condition .= ' ASC';
	}

	$mod_em_campaign_get_admission_date = ($mod_em_campaign_show_admission_start_date || $mod_em_campaign_show_admission_end_date);
	$currentCampaign                    = $helper->getCurrent($condition, $mod_em_campaign_get_teaching_unity, $order);
	$pastCampaign                       = $helper->getPast($condition, $mod_em_campaign_get_teaching_unity, $order);
	$futurCampaign                      = $helper->getFutur($condition, $mod_em_campaign_get_teaching_unity, $order);
	$allCampaign                        = $helper->getProgram($condition, $mod_em_campaign_get_teaching_unity, $order);

	if ($params->get('mod_em_campaign_layout') == "single_campaign" || $params->get('mod_em_campaign_layout') == "tchooz_single_campaign" || $params->get('mod_em_campaign_layout') == "institut_fr_single_campaign") {
// FAQ
		$faq_articles = $helper->getFaq();

		include_once(JPATH_BASE . DS . 'modules' . DS . 'mod_emundus_campaign_dropfiles' . DS . 'helper.php');
		$dropfiles_helper = new modEmundusCampaignDropfilesHelper();
		$files            = $dropfiles_helper->getFiles();
	}

	if ($params->get('mod_em_campaign_layout') == 'celsa') {
		$formations      = $helper->getFormationsWithType();
		$formationTypes  = $helper->getFormationTypes();
		$formationLevels = $helper->getFormationLevels();
		$voiesDAcces     = $helper->getVoiesDAcces();

		$currentCampaign = $helper->addClassToData($currentCampaign, $formations);
		$pastCampaign    = $helper->addClassToData($pastCampaign, $formations);
		$futurCampaign   = $helper->addClassToData($futurCampaign, $formations);
		$allCampaign     = $helper->addClassToData($allCampaign, $formations);
	}


	$show_registration = 0;
	$modules           = JModuleHelper::getModules('header-c');
	foreach ($modules as $module) {
		if ($module->module == 'mod_emundus_user_dropdown') {
			$show_registration = json_decode($module->params)->show_registration;
		}
	}
	if ($show_registration == 0 || $show_registration == 1 && $user === null && !empty($currentCampaign)) {
		$show_registration = true;
	}
	else {
		$show_registration = false;
	}

	jimport('joomla.html.pagination');
	$session = JFactory::getSession();

	$paginationCurrent = new JPagination($helper->getTotalCurrent(), $session->get('limitstartCurrent'), $session->get('limit'));
	$paginationPast    = new JPagination($helper->getTotalPast(), $session->get('limitstartPast'), $session->get('limit'));
	$paginationFutur   = new JPagination($helper->getTotalFutur(), $session->get('limitstartFutur'), $session->get('limit'));
	$paginationTotal   = new JPagination($helper->getTotal(), $session->get('limitstart'), $session->get('limit'));

	require(JModuleHelper::getLayoutPath('mod_emundus_campaign', $params->get('mod_em_campaign_layout')));
}
?>
