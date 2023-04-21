<?php
/**
 * @package		Joomla
 * @subpackage	eMundus
 * @copyright	Copyright (C) 2019 emundus.fr. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$document = JFactory::getDocument();
$document->addStyleSheet('https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined' );

$user = JFactory::getSession()->get('emundusUser');
if (!$user->just_logged) {
    return;
}

$m_profiles = new EmundusModelProfile;
$app_prof = $m_profiles->getApplicantsProfilesArray();

$user_prof = array_map(function ($profile) {
    return $profile->id;
}, $user->emProfiles);

// If all of the user's profiles are found in the list of applicant profiles, then the user is only an applicant.
$only_applicant = !array_diff($user_prof, $app_prof);
$applicant_option = false;

$ids_array = array();
if (!empty($user->fnums)) {
    foreach ($user->fnums as $fnum) {
        $ids_array[$fnum->profile_id] = $fnum->fnum;
    }
}

$just_logged = $user->just_logged;

if (!empty($user->emProfiles) && sizeof($user->emProfiles > 1) && (!$only_applicant)) {
    $text = JText::_('MOD_EMUNDUS_SWITCH_PROFILE_INFO');
    $text .= '<div class=\"em-flex-row em-flex-center\" style=\"flex-wrap: wrap;\">';

    foreach ($user->emProfiles as $profile) {
        if ($profile->published && !$applicant_option) {
            $text .= '<div class=\"em-switch-profile-card\" onclick=\"postCProfileAtLogin(\''.$profile->id.'.'.$ids_array[$profile->id].'\')\">'.JText::_('APPLICANT').'</div>';
            $applicant_option = true;
        } elseif (!$profile->published) {
            $text .= '<div class=\"em-switch-profile-card\" onclick=\"postCProfileAtLogin(\''.$profile->id.'.\')\">'.trim($profile->label).'</div>';
        }
    }

    $text .= '</div>';
} else {
    $just_logged = false;
}

$user->just_logged = false;
JFactory::getSession()->set('emundusUser',$user);


require JModuleHelper::getLayoutPath('mod_emundus_switch_profile', 'default');
