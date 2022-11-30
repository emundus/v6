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
JText::script('COM_EMUNDUS_USERS_MY_DOCUMENTS_EXPIRES_DATE');
JText::script('COM_EMUNDUS_USERS_MY_DOCUMENTS_STATE_WAITING');
JText::script('COM_EMUNDUS_USERS_MY_DOCUMENTS_STATE_OK');
JText::script('COM_EMUNDUS_USERS_MY_DOCUMENTS_STATE_INVALID');
JText::script('COM_EMUNDUS_USERS_BACK_TO_FILES');

$lang = JFactory::getLanguage();
$short_lang = substr($lang->getTag(), 0 , 2);
$current_lang = $lang->getTag();
$languages = JLanguageHelper::getLanguages();
if (count($languages) > 1) {
    $many_languages = '1';
} else {
    $many_languages = '0';
}

$user = JFactory::getUser();
$coordinator_access = EmundusHelperAccess::asCoordinatorAccessLevel($user->id);
$sysadmin_access = EmundusHelperAccess::isAdministrator($user->id);
$is_applicant = EmundusHelperAccess::isApplicant($user->id);

$xmlDoc = new DOMDocument();
if ($xmlDoc->load(JPATH_SITE.'/administrator/components/com_emundus/emundus.xml')) {
    $release_version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
}

$app = JFactory::getApplication();
$menu = @JFactory::getApplication()->getMenu();
$current_menu = $menu->getActive();
$Itemid = $app->input->getInt('Itemid', $current_menu->id);
$params = $menu->getParams($Itemid);
$attachment_intro = $params->get('em_users_attachments_intro','');
$display_validation_state = $params->get('em_users_attachments_display_validation_state','');
?>

<div id="em-component-vue"
     component="editprofile"
     coordinatorAccess="<?= $coordinator_access ?>"
     sysadminAccess="<?= $sysadmin_access ?>"
     shortLang="<?= $short_lang ?>" currentLanguage="<?= $current_lang ?>"
     manyLanguages="<?= $many_languages ?>"
     isApplicant="<?= $is_applicant ?>"
     attachmentIntro="<?= $attachment_intro ?>"
     displayValidationState="<?= $display_validation_state ?>"
></div>

<script src="media/com_emundus_vue/app_emundus.js?<?php echo $release_version ?>"></script>
