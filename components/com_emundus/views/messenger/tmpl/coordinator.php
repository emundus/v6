<?php
/**
 * @package     Joomla
 * @subpackage  com_emundus
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
$short_lang = substr($lang->getTag(), 0 , 2);
$current_lang = $lang->getTag();
$languages = JLanguageHelper::getLanguages();
if (count($languages) > 1) {
    $many_languages = '1';
} else {
    $many_languages = '0';
}

$coordinator_access = EmundusHelperAccess::asCoordinatorAccessLevel($user->id);
$sysadmin_access = EmundusHelperAccess::isAdministrator($user->id);

require_once (JPATH_COMPONENT.DS.'helpers'.DS.'cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();

?>
<div id="em-component-vue"
     component="messagescoordinator"
     coordinatorAccess="<?= $coordinator_access ?>"
     shortLang="<?= $short_lang ?>"
     currentLanguage="<?= $current_lang ?>"
     manyLanguages="<?= $many_languages ?>"
     fnum="<?= $fnum ?>"
     user="<?= $user->id ?>"
     sysadminAccess="<?= $sysadmin_access ?>"
>
</div>

<script src="media/com_emundus_vue/app_emundus.js?<?php echo $hash ?>"></script>
