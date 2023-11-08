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

require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'access.php');

## GLOBAL ##
JText::script('COM_EMUNDUS_ONBOARD_MODIFY');
JText::script('COM_EMUNDUS_ONBOARD_VISUALIZE');
JText::script('COM_EMUNDUS_ONBOARD_OK');
JText::script('COM_EMUNDUS_ONBOARD_CANCEL');
JText::script('COM_EMUNDUS_ONBOARD_ALL');
JText::script('COM_EMUNDUS_ONBOARD_SYSTEM');
JText::script('COM_EMUNDUS_ONBOARD_EMAILS');
JText::script('COM_EMUNDUS_ONBOARD_EMAILS_DESC');
JText::script('COM_EMUNDUS_ONBOARD_EMAIL_PREVIEWMODEL');
JText::script('COM_EMUNDUS_ONBOARD_CATEGORIES');
JText::script('COM_EMUNDUS_ONBOARD_CANT_REVERT');
JText::script('COM_EMUNDUS_ONBOARD_EMPTY_LIST');
JText::script('COM_EMUNDUS_ONBOARD_LABEL');
## END ##

## ACTIONS ##
JText::script('COM_EMUNDUS_ONBOARD_ACTION');
JText::script('COM_EMUNDUS_ONBOARD_ACTIONS');
JText::script('COM_EMUNDUS_ONBOARD_ACTION_PUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_ACTION_UNPUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_ACTION_DUPLICATE');
JText::script('COM_EMUNDUS_ONBOARD_ACTION_DELETE');
## END ##

## FILTERS ##
JText::script('COM_EMUNDUS_ONBOARD_FILTER');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_ALL');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_OPEN');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_CLOSE');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_PUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_SELECT');
JText::script('COM_EMUNDUS_ONBOARD_DESELECT');
JText::script('COM_EMUNDUS_ONBOARD_TOTAL');
JText::script('COM_EMUNDUS_ONBOARD_SORT');
JText::script('COM_EMUNDUS_ONBOARD_SORT_CREASING');
JText::script('COM_EMUNDUS_ONBOARD_SORT_DECREASING');
JText::script('COM_EMUNDUS_ONBOARD_RESULTS');
JText::script('COM_EMUNDUS_ONBOARD_ALL_RESULTS');
JText::script('COM_EMUNDUS_ONBOARD_SEARCH');
## END ##

## EMAIL ##
JText::script('COM_EMUNDUS_ONBOARD_ADD_EMAIL');
JText::script('COM_EMUNDUS_ONBOARD_NOEMAIL');
JText::script('COM_EMUNDUS_ONBOARD_EMAILDELETE');
JText::script('COM_EMUNDUS_ONBOARD_EMAILDELETED');
JText::script('COM_EMUNDUS_ONBOARD_EMAILUNPUBLISHED');
JText::script('COM_EMUNDUS_ONBOARD_EMAILPUBLISHED');
## END ##

## TUTORIAL ##
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_CAMPAIGN');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_FORM');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_FORMBUILDER');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_DOCUMENTS');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_PROGRAM');
## END ##

JText::script('COM_EMUNDUS_ONBOARD_EMAIL_TAGS');
JText::script('COM_EMUNDUS_ONBOARD_EMAIL_DOCUMENT');

JText::script('COM_EMUNDUS_ONBOARD_ADD_EMAIL');
JText::script('COM_EMUNDUS_ONBOARD_ADDEMAIL_CHOOSETYPE');
JText::script('COM_EMUNDUS_ONBOARD_ADDEMAIL_NAME');
JText::script('COM_EMUNDUS_ONBOARD_ADDEMAIL_RECEIVER');
JText::script('COM_EMUNDUS_ONBOARD_ADDEMAIL_ADDRESS');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_PARAMETER');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_INFORMATION');
JText::script('COM_EMUNDUS_ONBOARD_CHOOSECATEGORY');
JText::script('COM_EMUNDUS_ONBOARD_ADD_RETOUR');
JText::script('COM_EMUNDUS_ONBOARD_ADD_CONTINUER');
JText::script('COM_EMUNDUS_ONBOARD_ADDEMAIL_RESUME');
JText::script('COM_EMUNDUS_ONBOARD_ADDEMAIL_CATEGORY');
JText::script('COM_EMUNDUS_ONBOARD_REQUIRED_FIELDS_INDICATE');
JText::script('COM_EMUNDUS_ONBOARD_EMAILTYPE');
JText::script('COM_EMUNDUS_ONBOARD_ADVANCED_CUSTOMING');
JText::script('COM_EMUNDUS_ONBOARD_SUBJECT_REQUIRED');
JText::script('COM_EMUNDUS_ONBOARD_BODY_REQUIRED');
JText::script('COM_EMUNDUS_ONBOARD_ADDEMAIL_BODY');
JText::script('COM_EMUNDUS_ONBOARD_VARIABLESTIP');
JText::script('COM_EMUNDUS_ONBOARD_TIP');
JText::script('COM_EMUNDUS_ONBOARD_EMAIL_TRIGGER');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM');
JText::script('COM_EMUNDUS_ONBOARD_TRIGGERMODEL_REQUIRED');
JText::script('COM_EMUNDUS_ONBOARD_TRIGGERSTATUS');
JText::script('COM_EMUNDUS_ONBOARD_TRIGGERSTATUS_REQUIRED');
JText::script('COM_EMUNDUS_ONBOARD_TRIGGERTARGET');
JText::script('COM_EMUNDUS_ONBOARD_TRIGGERTARGET_REQUIRED');
JText::script('COM_EMUNDUS_ONBOARD_PROGRAM_ADMINISTRATORS');
JText::script('COM_EMUNDUS_ONBOARD_PROGRAM_EVALUATORS');
JText::script('COM_EMUNDUS_ONBOARD_PROGRAM_CANDIDATES');
JText::script('COM_EMUNDUS_ONBOARD_PROGRAM_DEFINED_USERS');
JText::script('COM_EMUNDUS_ONBOARD_TRIGGER_CHOOSE_USERS');
JText::script('COM_EMUNDUS_ONBOARD_TRIGGER_USERS_REQUIRED');
JText::script('COM_EMUNDUS_ONBOARD_SEARCH_USERS');
JText::script('COM_EMUNDUS_ONBOARD_TRIGGERMODEL');
JText::script('COM_EMUNDUS_ONBOARD_THE_CANDIDATE');
JText::script('COM_EMUNDUS_ONBOARD_MANUAL');
JText::script('COM_EMUNDUS_ONBOARD_TRIGGER_ACTIONS');

## TUTORIAL ##
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_CAMPAIGN');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_FORM');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_FORMBUILDER');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_DOCUMENTS');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_PROGRAM');
## END ##

JText::script('COM_EMUNDUS_ONBOARD_EMAIL_TAGS');
JText::script('COM_EMUNDUS_ONBOARD_EMAIL_DOCUMENT');

# receiver
JText::script('COM_EMUNDUS_ONBOARD_RECEIVER_CC_TAGS');
JText::script('COM_EMUNDUS_ONBOARD_RECEIVER_BCC_TAGS');

JText::script('COM_EMUNDUS_ONBOARD_RECEIVER_CC_TAGS_PLACEHOLDER');
JText::script('COM_EMUNDUS_ONBOARD_RECEIVER_BCC_TAGS_PLACEHOLDER');

JText::script('COM_EMUNDUS_ONBOARD_PLACEHOLDER_EMAIL_DOCUMENT');

JText::script('COM_EMUNDUS_ONBOARD_EMAIL_DOCUMENT');

JText::script('COM_EMUNDUS_ONBOARD_CC_BCC_TOOLTIPS');

JText::script('COM_EMUNDUS_ONBOARD_EMAIL_TAGS');
JText::script('COM_EMUNDUS_ONBOARD_PLACEHOLDER_EMAIL_TAGS');

JText::script('COM_EMUNDUS_ONBOARD_CANDIDAT_ATTACHMENTS');
JText::script('COM_EMUNDUS_ONBOARD_PLACEHOLDER_CANDIDAT_ATTACHMENTS');

JText::script('COM_EMUNDUS_ONBOARD_NAME');
JText::script('COM_EMUNDUS_ONBOARD_START_DATE');
JText::script('COM_EMUNDUS_ONBOARD_END_DATE');
JText::script('COM_EMUNDUS_ONBOARD_STATE');
JText::script('COM_EMUNDUS_ONBOARD_NB_FILES');
JText::script('COM_EMUNDUS_ONBOARD_SUBJECT');
JText::script('COM_EMUNDUS_ONBOARD_TYPE');
JText::script('COM_EMUNDUS_ONBOARD_CATEGORY');
JText::script('COM_EMUNDUS_ONBOARD_STATUS');
JText::script('COM_EMUNDUS_ONBOARD_EMAIL_TYPE_SYSTEM');
JText::script('COM_EMUNDUS_ONBOARD_EMAIL_TYPE_MODEL');

$lang         = JFactory::getLanguage();
$short_lang   = substr($lang->getTag(), 0, 2);
$current_lang = $lang->getTag();
$languages    = JLanguageHelper::getLanguages();
if (count($languages) > 1) {
	$many_languages = '1';
}
else {
	$many_languages = '0';
}

$user               = JFactory::getUser();
$coordinator_access = EmundusHelperAccess::asCoordinatorAccessLevel($user->id);
$sysadmin_access    = EmundusHelperAccess::isAdministrator($user->id);
$lang               = JFactory::getLanguage();
$short_lang         = substr($lang->getTag(), 0, 2);
$current_lang       = $lang->getTag();

require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();
?>

<list id="em-component-vue"
      component="list_v2"
      type="emails"
      coordinatorAccess="<?= $coordinator_access ?>"
      sysadminAccess="<?= $sysadmin_access ?>"
      shortLang="<?= $short_lang ?>" currentLanguage="<?= $current_lang ?>"
      manyLanguages="<?= $many_languages ?>">
</list>

<script src="media/com_emundus_vue/app_emundus.js?<?php echo $hash ?>"></script>
