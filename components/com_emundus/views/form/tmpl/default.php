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
JText::script('COM_EMUNDUS_ONBOARD_FORMS');
JText::script('COM_EMUNDUS_ONBOARD_FORMS_DESC');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGN_ASSOCIATED');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED_NOT');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED_TITLE');
JText::script('COM_EMUNDUS_ONBOARD_CANT_REVERT');
JText::script('COM_EMUNDUS_ONBOARD_EMPTY_LIST');
## END ##

## ACTIONS ##
JText::script('COM_EMUNDUS_ONBOARD_ACTION');
JText::script('COM_EMUNDUS_ONBOARD_ACTIONS');
JText::script('COM_EMUNDUS_ONBOARD_ACTION_PUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_ACTION_UNPUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_ACTION_DUPLICATE');
JText::script('COM_EMUNDUS_ONBOARD_ACTION_DELETE');
JText::script('COM_EMUNDUS_ONBOARD_ARCHIVE');
JText::script('COM_EMUNDUS_ONBOARD_ARCHIVED');
JText::script('COM_EMUNDUS_ONBOARD_RESTORE');
## END ##

## FILTERS ##
JText::script('COM_EMUNDUS_ONBOARD_FILTER');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_ALL');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_OPEN');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_CLOSE');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_PUBLISH_FORM');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH_FORM');
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

## FORM ##
JText::script('COM_EMUNDUS_ONBOARD_NOFORM');
JText::script('COM_EMUNDUS_ONBOARD_ADD_FORM');
JText::script('COM_EMUNDUS_ONBOARD_FORMDELETE');
JText::script('COM_EMUNDUS_ONBOARD_FORMDELETED');
JText::script('COM_EMUNDUS_ONBOARD_FORMUNPUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_FORMUNPUBLISHED');
JText::script('COM_EMUNDUS_ONBOARD_FORMPUBLISHED');
JText::script('COM_EMUNDUS_ONBOARD_FORMDUPLICATE');
JText::script('COM_EMUNDUS_ONBOARD_FORMDUPLICATED');
JText::script('COM_EMUNDUS_ONBOARD_FORMDUPLICATE_FAILED');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGN');
JText::script('COM_EMUNDUS_ONBOARD_EVALUATION');
JText::script('COM_EMUNDUS_ONBOARD_ADD_EVAL_FORM');
JText::script('COM_EMUNDUS_ONBOARD_LABEL');
## END ##

## TUTORIAL ##
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_CAMPAIGN');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_FORM');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_FORMBUILDER');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_DOCUMENTS');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_PROGRAM');
## END ##

JText::script('COM_EMUNDUS_ONBOARD_NAME');
JText::script('COM_EMUNDUS_ONBOARD_START_DATE');
JText::script('COM_EMUNDUS_ONBOARD_END_DATE');
JText::script('COM_EMUNDUS_ONBOARD_STATE');
JText::script('COM_EMUNDUS_ONBOARD_NB_FILES');
JText::script('COM_EMUNDUS_ONBOARD_SUBJECT');
JText::script('COM_EMUNDUS_ONBOARD_TYPE');
JText::script('COM_EMUNDUS_ONBOARD_STATUS');
JText::script('COM_EMUNDUS_FORM_DELETE_MODEL_SUCCESS');
JText::script('COM_EMUNDUS_FORM_DELETE_MODEL_FAILURE');

JText::script('COM_EMUNDUS_ONBOARD_NO_PAGE_MODELS');

$lang = JFactory::getLanguage();
$short_lang = substr($lang->getTag(), 0 , 2);
$current_lang = $lang->getTag();
$languages = JLanguageHelper::getLanguages();
if (count($languages) > 1) {
    $many_languages = '1';
    require_once JPATH_SITE . '/components/com_emundus/models/translations.php';
    $m_translations = new EmundusModelTranslations();
    $default_lang = $m_translations->getDefaultLanguage()->lang_code;
} else {
    $many_languages = '0';
    $default_lang = $current_lang;
}

$user = JFactory::getUser();
$coordinator_access = EmundusHelperAccess::asCoordinatorAccessLevel($user->id);
$sysadmin_access = EmundusHelperAccess::isAdministrator($user->id);

$xmlDoc = new DOMDocument();
if ($xmlDoc->load(JPATH_SITE.'/administrator/components/com_emundus/emundus.xml')) {
    $release_version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
}
?>

<list id="em-component-vue"
      component="list_v2"
      type="forms"
      coordinatorAccess="<?= $coordinator_access ?>"
      sysadminAccess="<?= $sysadmin_access ?>"
      shortLang="<?= $short_lang ?>" currentLanguage="<?= $current_lang ?>"
      manyLanguages="<?= $many_languages ?>"
      defaultLang="<?= $default_lang ?>"
>
</list>

<script src="media/com_emundus_vue/app_emundus.js?<?php echo $release_version ?>"></script>
