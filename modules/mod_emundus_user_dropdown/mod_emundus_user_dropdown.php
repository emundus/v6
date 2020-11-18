<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_emundususerdropdown
 * @copyright	Copyright (C) 2018 emundus.fr, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$layout = $params->get('layout', 'default');
// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';
include_once(JPATH_BASE.'/components/com_emundus/models/profile.php');

$user = JFactory::getSession()->get('emundusUser');

// Here we get the menu which is defined in the params
$jooomla_menu_name = $params->get('menu_name', 0);

$primary_color = $params->get('primary_color', 'ECF0F1');
$secondary_color = $params->get('secondary_color', 'F89406');
$icon = $params->get('icon', 'big circular user outline icon');
$show_logout = $params->get('show_logout', '1');
$url_logout = $params->get('url_logout', 'index.php');
$intro = $params->get('intro', '');

$link_login = $params->get('link_login', 'index.php?option=com_users&view=login&Itemid=1135');
$link_register = $params->get('link_register', 'index.php?option=com_fabrik&view=form&formid=307&Itemid=1136');
$link_forgotten_password = $params->get('link_forgotten_password', 'index.php?option=com_users&view=reset&Itemid=2833');
$show_registration = !$params->get('show_registration', '0');

$document = JFactory::getDocument();
$document->addStyleSheet('media/com_emundus/lib/Semantic-UI-CSS-master/semantic.min.css');

if ($jooomla_menu_name !== 0 || $jooomla_menu_name !== '0') {
	$list = modEmundusUserDropdownHelper::getList($jooomla_menu_name);
}

if (!$show_registration && $user === null && modEmundusUserDropdownHelper::isCampaignActive()) {
	$show_registration = true;
}

//
$m_profiles = new EmundusModelProfile;
$app_prof = $m_profiles->getApplicantsProfilesArray();

$user_prof = [];
foreach ($user->emProfiles as $prof) {
    $user_prof[] = $prof->id;
}

// If all of the user's profiles are found in the list of applicant profiles, then the user is only an applicant.
$only_applicant = !array_diff($user_prof, $app_prof);


// used for getting the page we are currently on.
$app = JFactory::getApplication();
$menu = $app->getMenu();
$active	= $menu->getActive();
$active_id = isset($active) ? $active->id : $menu->getDefault()->id;

require JModuleHelper::getLayoutPath('mod_emundus_user_dropdown', $layout);
