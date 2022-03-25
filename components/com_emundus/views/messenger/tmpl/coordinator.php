<?php
/**
 * @package     Joomla
 * @subpackage  com_emundus_onboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

JText::script('COM_EMUNDUS_MESSENGER_TITLE');
JText::script('COM_EMUNDUS_MESSENGER_SEND_DOCUMENT');
JText::script('COM_EMUNDUS_MESSENGER_ASK_DOCUMENT');
JText::script('COM_EMUNDUS_MESSENGER_DROP_HERE');
JText::script('COM_EMUNDUS_MESSENGER_SEND');
JText::script('COM_EMUNDUS_MESSENGER_WRITE_MESSAGE');

$jinput = JFactory::getApplication()->input;
$fnum 	= $jinput->getString('fnum', null);
$user = JFactory::getUser();

$lang = JFactory::getLanguage();
$actualLanguage = substr($lang->getTag(), 0, 2);
$languages = JLanguageHelper::getLanguages();
if (count($languages) > 1) {
    $many_languages = '1';
} else {
    $many_languages = '0';
}

$coordinator_access = EmundusHelperAccess::isCoordinator($user->id);

?>
<div id="em-component-vue" component="messagescoordinator" coordinatorAccess="<?= $coordinator_access ?>" actualLanguage="<?= $actualLanguage ?>" manyLanguages="<?= $many_languages ?>" fnum="<?= $fnum ?>" user="<?= $user->id ?>"></div>

<script src="media/com_emundus_vue/app_emundus.js"></script>
