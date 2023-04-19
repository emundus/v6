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

$layout = $params->get('layout', 'default');

$user = JFactory::getUser();

$show_profile_picture = $params->get('show_profile_picture', 1);
$update_profile_picture = $params->get('update_profile_picture', 1);
$show_name = $params->get('show_name', 1);
$show_account_edit_button = $params->get('show_account_edit_button', 1);
$intro = $params->get('intro', '');

$user_fullname = $user->name;

$profile_picture = '/media/com_emundus/images/profile/default-profile.jpg';
if($show_profile_picture == 1) {
    $profile_picture = modEmundusProfileHelper::getProfilePicture();
}

require JModuleHelper::getLayoutPath('mod_emundus_profile', $layout);
