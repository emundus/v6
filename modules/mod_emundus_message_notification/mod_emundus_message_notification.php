<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_emundususerdropdown
 * @copyright	Copyright (C) 2018 emundus.fr, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

$user = JFactory::getSession()->get('emundusUser');

$helper = new modEmundusMessageNotificationHelper;

$primary_color = $params->get('primary_color', 'BB0E29');
$secondary_color = $params->get('secondary_color', 'ECF0F1');


$document = JFactory::getDocument();

$messages = $helper->getMessages($user->id);
$message_contacts = $helper->getContacts($user->id);

$layout = $params->get('layout','default');

if ($layout == 'default') {
    if ($messages != "0") {
        require JModuleHelper::getLayoutPath('mod_emundus_message_notification', $layout);
    }
}
else
    require JModuleHelper::getLayoutPath('mod_emundus_message_notification', $layout);

