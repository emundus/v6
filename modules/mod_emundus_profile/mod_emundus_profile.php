<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_emundususerdropdown
 * @copyright	Copyright (C) 2018 emundus.fr, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
require_once dirname(__FILE__).'/helper.php';

Jtext::script('COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_ERROR_TITLE');
Jtext::script('COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_ERROR_TEXT');
Jtext::script('COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_ERROR_UPDATE_TEXT');
Jtext::script('COM_EMUNDUS_ONBOARD_OK');

$layout = $params->get('layout', 'default');

$user = JFactory::getUser();
$e_user = JFactory::getSession()->get('emundusUser');

$show_profile_picture = $params->get('show_profile_picture', 1);
$update_profile_picture = $params->get('update_profile_picture', 1);
$show_name = $params->get('show_name', 1);
$show_account_edit_button = $params->get('show_account_edit_button', 1);
$intro = $params->get('intro', '');

$user_fullname = $e_user->firstname.' '.$e_user->lastname;
$u_params = json_decode($user->params);
$external = $user->password === '' || $u_params->OAuth2 === 'openid';

$profile_picture = '';
if($show_profile_picture == 1) {
    $profile_picture = modEmundusProfileHelper::getProfilePicture();
}

require JModuleHelper::getLayoutPath('mod_emundus_profile', $layout);
