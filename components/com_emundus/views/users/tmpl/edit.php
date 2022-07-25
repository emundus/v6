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

require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');

JText::script('COM_EMUNDUS_ONBOARD_OK');
JText::script('COM_EMUNDUS_ONBOARD_CANCEL');

JText::script('COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE');
JText::script('COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_TIP');
JText::script('COM_EMUNDUS_USERS_MY_DOCUMENTS');
JText::script('COM_EMUNDUS_USERS_MY_DOCUMENTS_ADD');
JText::script('COM_EMUNDUS_USERS_MY_DOCUMENTS_DOCUMENT_TYPE');
JText::script('COM_EMUNDUS_USERS_MY_DOCUMENTS_PLEASE_SELECT');
JText::script('COM_EMUNDUS_USERS_MY_DOCUMENTS_DROP_HERE');
JText::script('COM_EMUNDUS_USERS_MY_DOCUMENTS_DROP_DATE');
JText::script('COM_EMUNDUS_USERS_MY_DOCUMENTS_DELETE');
JText::script('COM_EMUNDUS_USERS_MY_DOCUMENTS_DELETE_CONFIRM');
JText::script('COM_EMUNDUS_USERS_MY_DOCUMENTS_SUCCESS_DELETED');
JText::script('COM_EMUNDUS_USERS_EDIT_PROFILE_SAVE');
JText::script('COM_EMUNDUS_USERS_EDIT_PROFILE_SAVE_SUCCESS');
JText::script('COM_EMUNDUS_USERS_EDIT_PROFILE_SAVE_SUCCESS_TEXT');
JText::script('COM_EMUNDUS_USERS_EDIT_PROFILE_SAVE_FAILED');
JText::script('COM_EMUNDUS_USERS_EDIT_PROFILE_SAVE_FAILED_TEXT');
JText::script('COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_WRONG_TYPE_TEXT');
JText::script('COM_EMUNDUS_USERS_EDIT_PROFILE_PASSWORD_TITLE');

$lang = JFactory::getLanguage();
$actualLanguage = substr($lang->getTag(), 0, 2);
$languages = JLanguageHelper::getLanguages();
if (count($languages) > 1) {
    $many_languages = '1';
} else {
    $many_languages = '0';
}

$user = JFactory::getUser();
$coordinator_access = EmundusHelperAccess::isCoordinator($user->id);
$is_applicant = EmundusHelperAccess::isApplicant($user->id)

?>

<div id="em-component-vue"
     component="editprofile"
     coordinatorAccess="<?= $coordinator_access ?>"
     actualLanguage="<?= $actualLanguage ?>"
     manyLanguages="<?= $many_languages ?>"
     isApplicant="<?= $is_applicant ?>"></div>

<script src="media/com_emundus_vue/app_emundus.js"></script>
