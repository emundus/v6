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
$document = JFactory::getDocument();
$document->addScript('media/com_emundus_onboard/chunk-vendors_onboard.js');
$document->addStyleSheet('media/com_emundus_onboard/app_onboard.css');

require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'access.php');

## GLOBAL ##
JText::script('COM_EMUNDUS_ONBOARD_MODIFY');
JText::script('COM_EMUNDUS_ONBOARD_VISUALIZE');
JText::script('COM_EMUNDUS_ONBOARD_OK');
JText::script('COM_EMUNDUS_ONBOARD_CANCEL');
JText::script('COM_EMUNDUS_ONBOARD_ALL');
JText::script('COM_EMUNDUS_ONBOARD_SYSTEM');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNS');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNS_DESC');
JText::script('COM_EMUNDUS_ONBOARD_FILES');
JText::script('COM_EMUNDUS_ONBOARD_FILE');
## END ##

## ACTIONS ##
JText::script('COM_EMUNDUS_ONBOARD_ACTION');
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

## CAMPAIGN ##
JText::script('COM_EMUNDUS_ONBOARD_ADD_CAMPAIGN');
JText::script('COM_EMUNDUS_ONBOARD_NOCAMPAIGN');
JText::script('COM_EMUNDUS_ONBOARD_FROM');
JText::script('COM_EMUNDUS_ONBOARD_TO');
JText::script('COM_EMUNDUS_ONBOARD_SINCE');
JText::script('COM_EMUNDUS_ONBOARD_CAMPDELETE');
JText::script('COM_EMUNDUS_ONBOARD_CAMPDELETED');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNUNPUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNUNPUBLISHED');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNPUBLISHED');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNDUPLICATE');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNDUPLICATED');
JText::script('COM_EMUNDUS_ONBOARD_PROGRAM_ADVANCED_SETTINGS');
JText::script('COM_EMUNDUS_ONBOARD_DOSSIERS_PROGRAM');
JText::script('COM_EMUNDUS_ONBOARD_DOSSIERS_COUNT');
JTEXT::script('COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM');
JTEXT::script('COM_EMUNDUS_ONBOARD_OTHERCAMP_PROGRAM');
JTEXT::script('COM_EMUNDUS_ONBOARD_ALL_PROGRAMS');
## END #

## TUTORIAL ##
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_CAMPAIGN');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_FORM');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_FORMBUILDER');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_DOCUMENTS');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_PROGRAM');
## END ##

JText::script('COM_EMUNDUS_ONBOARD_ADD_CAMPAIGN');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_PARAMETER');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_CAMPNAME');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_STARTDATE');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_ENDDATE');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_INFORMATION');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_RESUME');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_DESCRIPTION');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_CHOOSEPROG');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_PICKYEAR');
JText::script('COM_EMUNDUS_ONBOARD_ADDPROGRAM');
JText::script('COM_EMUNDUS_ONBOARD_ADD_RETOUR');
JText::script('COM_EMUNDUS_ONBOARD_ADD_QUITTER');
JText::script('COM_EMUNDUS_ONBOARD_ADD_CONTINUER');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_PUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_CLOSE');
JText::script('COM_EMUNDUS_ONBOARD_DEPOTDEDOSSIER');
JText::script('COM_EMUNDUS_ONBOARD_PROGNAME');
JText::script('COM_EMUNDUS_ONBOARD_PROGCODE');
JText::script('COM_EMUNDUS_ONBOARD_CHOOSECATEGORY');
JText::script('COM_EMUNDUS_ONBOARD_NAMECATEGORY');
JText::script('COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME');
JText::script('COM_EMUNDUS_ONBOARD_REQUIRED_FIELDS_INDICATE');
JText::script('COM_EMUNDUS_ONBOARD_PROGRAM_RESUME');
JText::script('COM_EMUNDUS_ONBOARD_PROG_REQUIRED_LABEL');
JText::script('COM_EMUNDUS_ONBOARD_CAMP_REQUIRED_RESUME');
JText::script('COM_EMUNDUS_ONBOARD_OK');
JText::script('COM_EMUNDUS_ONBOARD_CANCEL');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATETIP');
JText::script('COM_EMUNDUS_ONBOARD_TIP');
JText::script('COM_EMUNDUS_ONBOARD_FILES_LIMIT');
JText::script('COM_EMUNDUS_ONBOARD_FILES_LIMIT_NUMBER');
JText::script('COM_EMUNDUS_ONBOARD_FILES_LIMIT_STATUS');
JText::script('COM_EMUNDUS_ONBOARD_FILES_LIMIT_REQUIRED');
JText::script('COM_EMUNDUS_ONBOARD_TRIGGERSTATUS_REQUIRED');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATE_IN');
JText::script('COM_EMUNDUS_ONBOARD_PROGRAM_INTRO_DESC');

JText::script('COM_EMUNDUS_ONBOARD_NAME');
JText::script('COM_EMUNDUS_ONBOARD_START_DATE');
JText::script('COM_EMUNDUS_ONBOARD_END_DATE');
JText::script('COM_EMUNDUS_ONBOARD_STATE');
JText::script('COM_EMUNDUS_ONBOARD_NB_FILES');
JText::script('COM_EMUNDUS_ONBOARD_SUBJECT');
JText::script('COM_EMUNDUS_ONBOARD_TYPE');
JText::script('COM_EMUNDUS_ONBOARD_STATUS');

$lang = JFactory::getLanguage();
$actualLanguage = substr($lang->getTag(), 0, 2);
$languages = JLanguageHelper::getLanguages();
if (count($languages) > 1) {
    $many_languages = '1';
} else {
    $many_languages = '0';
}

$user = JFactory::getUser();
$coordinator_access = EmundusonboardHelperAccess::isCoordinator($user->id);
?>

<list id="em-component-vue" component="list" type="campaign" coordinatorAccess="<?= $coordinator_access ?>" actualLanguage="<?= $actualLanguage ?>" manyLanguages="<?= $many_languages ?>">
</list>

<script src="media/com_emundus_onboard/app_onboard.js"></script>