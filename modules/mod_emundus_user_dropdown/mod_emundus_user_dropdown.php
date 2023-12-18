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
$switch_profile_redirect = $params->get('switch_profile_redirect', 'index.php');

$primary_color = $params->get('primary_color', 'ECF0F1');
$secondary_color = $params->get('secondary_color', 'F89406');
$display_svg  = $params->get('display_svg', 1);
$icon = $params->get('icon', 'big circular user outline icon');
$show_logout = $params->get('show_logout', '1');
$show_update = $params->get('show_update', '1');
$url_logout = $params->get('url_logout', 'index.php');
$intro = $params->get('intro', '');
$show_profile_picture = $params->get('show_profile_picture', '1');

$link_login = $params->get('link_login', 'index.php?option=com_users&view=login&Itemid=1135');
$link_register = $params->get('link_register', 'index.php?option=com_fabrik&view=form&formid=307&Itemid=1136');
$link_forgotten_password = $params->get('link_forgotten_password', 'index.php?option=com_users&view=reset&Itemid=2833');
$show_registration = $params->get('show_registration', '0');
$link_edit_profile = JRoute::_('index.php?Itemid=' . $params->get('link_edit_profile', 2805));
$custom_actions = $params->get('custom_actions', []);

if (!empty($custom_actions) && !empty($user->id)) {
	foreach ($custom_actions as $key => $action) {
        $pass = true;

        if (!empty($action->condition)) {
            try {
                $pass = eval($action->condition);
            } catch (Exception $e) {
                $pass = false;
            }
        }

        if (!$pass) {
	        unset($custom_actions->$key);
            continue;
        }

        if (!empty($action->link) && strpos($action->link, '{fnum}') !== false) {
            $action->link = str_replace('{fnum}', $user->fnum, $action->link);
        }
    }
}

$document = JFactory::getDocument();

if ($jooomla_menu_name !== 0 || $jooomla_menu_name !== '0') {
    $list = modEmundusUserDropdownHelper::getList($jooomla_menu_name);
}

if ($show_registration == 0 || ($show_registration == 1 && $user === null && modEmundusUserDropdownHelper::isCampaignActive())) {
    $show_registration = true;
} elseif ($show_registration == 2){
    $show_registration = false;
} else {
	$show_registration = false;
}

//
$m_profiles = new EmundusModelProfile;
$app_prof = $m_profiles->getApplicantsProfilesArray();

if(!empty($user->profile)) {
	$user_profile = $m_profiles->getProfileById($user->profile);
	$profile_label = in_array($user->profile, $app_prof) ?  JText::_('APPLICANT') : $user_profile['label'];
}

$user_prof = [];
foreach ($user->emProfiles as $prof) {
    $user_prof[] = $prof->id;
}

// If all of the user's profiles are found in the list of applicant profiles, then the user is only an applicant.
$only_applicant = !array_diff($user_prof, $app_prof);
$applicant_option = false;


// used for getting the page we are currently on.
$app = JFactory::getApplication();
$menu = $app->getMenu();
$active	= $menu->getActive();
$active_id = isset($active) ? $active->id : $menu->getDefault()->id;

if(!$only_applicant) {
    $first_logged = $user->first_logged;
}

$profile_picture = '';
if($show_profile_picture){
    $profile_picture = modEmundusUserDropdownHelper::getProfilePicture();
}

// Vérifier si il s'agit d'une session  anonyme et ci celles ci sont autorisés
$eMConfig = JComponentHelper::getParams('com_emundus');
$is_anonym_user = $user->anonym;
$allow_anonym_files = $eMConfig->get('allow_anonym_files', false);
if ($is_anonym_user && !$allow_anonym_files) {
    return;
}

require JModuleHelper::getLayoutPath('mod_emundus_user_dropdown', $layout);
