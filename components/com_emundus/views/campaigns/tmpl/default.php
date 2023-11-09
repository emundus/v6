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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\LanguageHelper;

require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');

## GLOBAL ##
Text::script('COM_EMUNDUS_ONBOARD_MODIFY');
Text::script('COM_EMUNDUS_ONBOARD_VISUALIZE');
Text::script('COM_EMUNDUS_ONBOARD_OK');
Text::script('COM_EMUNDUS_ONBOARD_CANCEL');
Text::script('COM_EMUNDUS_ONBOARD_ALL');
Text::script('COM_EMUNDUS_ONBOARD_SYSTEM');
Text::script('COM_EMUNDUS_ONBOARD_CAMPAIGNS');
Text::script('COM_EMUNDUS_ONBOARD_CAMPAIGNS_DESC');
Text::script('COM_EMUNDUS_ONBOARD_FILES');
Text::script('COM_EMUNDUS_ONBOARD_FILE');
Text::script('COM_EMUNDUS_ONBOARD_CANT_REVERT');
Text::script('COM_EMUNDUS_ONBOARD_EMPTY_LIST');
Text::script('COM_EMUNDUS_ONBOARD_LABEL');
## END ##

## ACTIONS ##
Text::script('COM_EMUNDUS_ONBOARD_ACTION');
Text::script('COM_EMUNDUS_ONBOARD_ACTIONS');
Text::script('COM_EMUNDUS_ONBOARD_ACTION_PUBLISH');
Text::script('COM_EMUNDUS_ONBOARD_ACTION_UNPUBLISH');
Text::script('COM_EMUNDUS_ONBOARD_ACTION_DUPLICATE');
Text::script('COM_EMUNDUS_ONBOARD_ACTION_DELETE');
## END ##

## FILTERS ##
Text::script('COM_EMUNDUS_ONBOARD_FILTER');
Text::script('COM_EMUNDUS_ONBOARD_FILTER_ALL');
Text::script('COM_EMUNDUS_ONBOARD_FILTER_OPEN');
Text::script('COM_EMUNDUS_ONBOARD_FILTER_CLOSE');
Text::script('COM_EMUNDUS_ONBOARD_FILTER_PUBLISH');
Text::script('COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH');
Text::script('COM_EMUNDUS_ONBOARD_SELECT');
Text::script('COM_EMUNDUS_ONBOARD_DESELECT');
Text::script('COM_EMUNDUS_ONBOARD_TOTAL');
Text::script('COM_EMUNDUS_ONBOARD_SORT');
Text::script('COM_EMUNDUS_ONBOARD_SORT_CREASING');
Text::script('COM_EMUNDUS_ONBOARD_SORT_DECREASING');
Text::script('COM_EMUNDUS_ONBOARD_RESULTS');
Text::script('COM_EMUNDUS_ONBOARD_ALL_RESULTS');
Text::script('COM_EMUNDUS_ONBOARD_SEARCH');
## END ##

## CAMPAIGN ##
Text::script('COM_EMUNDUS_ONBOARD_ADD_CAMPAIGN');
Text::script('COM_EMUNDUS_ONBOARD_NOCAMPAIGN');
Text::script('COM_EMUNDUS_ONBOARD_NOPROGRAM');
Text::script('COM_EMUNDUS_ONBOARD_FROM');
Text::script('COM_EMUNDUS_ONBOARD_TO');
Text::script('COM_EMUNDUS_ONBOARD_SINCE');
Text::script('COM_EMUNDUS_ONBOARD_CAMPDELETE');
Text::script('COM_EMUNDUS_ONBOARD_CAMPDELETED');
Text::script('COM_EMUNDUS_ONBOARD_CAMPAIGNUNPUBLISH');
Text::script('COM_EMUNDUS_ONBOARD_CAMPAIGNUNPUBLISHED');
Text::script('COM_EMUNDUS_ONBOARD_CAMPAIGNPUBLISHED');
Text::script('COM_EMUNDUS_ONBOARD_CAMPAIGNDUPLICATE');
Text::script('COM_EMUNDUS_ONBOARD_CAMPAIGNDUPLICATED');
Text::script('COM_EMUNDUS_ONBOARD_PROGRAM_ADVANCED_SETTINGS');
Text::script('COM_EMUNDUS_ONBOARD_DOSSIERS_PROGRAM');
Text::script('COM_EMUNDUS_ONBOARD_DOSSIERS_COUNT');
Text::script('COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM');
Text::script('COM_EMUNDUS_ONBOARD_OTHERCAMP_PROGRAM');
Text::script('COM_EMUNDUS_ONBOARD_ALL_PROGRAMS');
Text::script('COM_EMUNDUS_ONBOARD_ALL_SESSIONS');
Text::script('COM_EMUNDUS_ONBOARD_PROGRAMS');
Text::script('COM_EMUNDUS_ONBOARD_PROGRAM');
Text::script('COM_EMUNDUS_ONBOARD_ALL_PROGRAM_CATEGORIES');
## END #

## TUTORIAL ##
Text::script('COM_EMUNDUS_ONBOARD_TUTORIAL_CAMPAIGN');
Text::script('COM_EMUNDUS_ONBOARD_TUTORIAL_FORM');
Text::script('COM_EMUNDUS_ONBOARD_TUTORIAL_FORMBUILDER');
Text::script('COM_EMUNDUS_ONBOARD_TUTORIAL_DOCUMENTS');
Text::script('COM_EMUNDUS_ONBOARD_TUTORIAL_PROGRAM');
## END ##

Text::script('COM_EMUNDUS_ONBOARD_ADD_CAMPAIGN');
Text::script('COM_EMUNDUS_ONBOARD_ADDCAMP_PARAMETER');
Text::script('COM_EMUNDUS_ONBOARD_ADDCAMP_CAMPNAME');
Text::script('COM_EMUNDUS_ONBOARD_ADDCAMP_STARTDATE');
Text::script('COM_EMUNDUS_ONBOARD_ADDCAMP_ENDDATE');
Text::script('COM_EMUNDUS_ONBOARD_ADDCAMP_INFORMATION');
Text::script('COM_EMUNDUS_ONBOARD_ADDCAMP_RESUME');
Text::script('COM_EMUNDUS_ONBOARD_ADDCAMP_DESCRIPTION');
Text::script('COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM');
Text::script('COM_EMUNDUS_ONBOARD_ADDCAMP_CHOOSEPROG');
Text::script('COM_EMUNDUS_ONBOARD_ADDCAMP_PICKYEAR');
Text::script('COM_EMUNDUS_ONBOARD_ADDPROGRAM');
Text::script('COM_EMUNDUS_ONBOARD_ADD_RETOUR');
Text::script('COM_EMUNDUS_ONBOARD_ADD_QUITTER');
Text::script('COM_EMUNDUS_ONBOARD_ADD_CONTINUER');
Text::script('COM_EMUNDUS_ONBOARD_FILTER_PUBLISH');
Text::script('COM_EMUNDUS_ONBOARD_FILTER_CLOSE');
Text::script('COM_EMUNDUS_ONBOARD_DEPOTDEDOSSIER');
Text::script('COM_EMUNDUS_ONBOARD_PROGNAME');
Text::script('COM_EMUNDUS_ONBOARD_PROGCODE');
Text::script('COM_EMUNDUS_ONBOARD_CHOOSECATEGORY');
Text::script('COM_EMUNDUS_ONBOARD_NAMECATEGORY');
Text::script('COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME');
Text::script('COM_EMUNDUS_ONBOARD_REQUIRED_FIELDS_INDICATE');
Text::script('COM_EMUNDUS_ONBOARD_PROGRAM_RESUME');
Text::script('COM_EMUNDUS_ONBOARD_PROG_REQUIRED_LABEL');
Text::script('COM_EMUNDUS_ONBOARD_CAMP_REQUIRED_RESUME');
Text::script('COM_EMUNDUS_ONBOARD_OK');
Text::script('COM_EMUNDUS_ONBOARD_CANCEL');
Text::script('COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH');
Text::script('COM_EMUNDUS_ONBOARD_TRANSLATETIP');
Text::script('COM_EMUNDUS_ONBOARD_TIP');
Text::script('COM_EMUNDUS_ONBOARD_FILES_LIMIT');
Text::script('COM_EMUNDUS_ONBOARD_FILES_LIMIT_NUMBER');
Text::script('COM_EMUNDUS_ONBOARD_FILES_LIMIT_STATUS');
Text::script('COM_EMUNDUS_ONBOARD_FILES_LIMIT_REQUIRED');
Text::script('COM_EMUNDUS_ONBOARD_TRIGGERSTATUS_REQUIRED');
Text::script('COM_EMUNDUS_ONBOARD_TRANSLATE_IN');
Text::script('COM_EMUNDUS_ONBOARD_PROGRAM_INTRO_DESC');
Text::script("COM_EMUNDUS_CAMPAIGN_ONGOING");
Text::script("COM_EMUNDUS_CAMPAIGN_YET_TO_COME");

Text::script('COM_EMUNDUS_ONBOARD_NAME');
Text::script('COM_EMUNDUS_ONBOARD_START_DATE');
Text::script('COM_EMUNDUS_ONBOARD_END_DATE');
Text::script('COM_EMUNDUS_ONBOARD_STATE');
Text::script('COM_EMUNDUS_ONBOARD_NB_FILES');
Text::script('COM_EMUNDUS_ONBOARD_SUBJECT');
Text::script('COM_EMUNDUS_ONBOARD_TYPE');
Text::script('COM_EMUNDUS_ONBOARD_STATUS');

Text::script('COM_EMUNDUS_CAMPAIGNS_PIN');
Text::script('COM_EMUNDUS_ONBOARD_CAMPAIGNS_CAMPAIGN_PINNED');
Text::script('COM_EMUNDUS_ONBOARD_CAMPAIGNS_CAMPAIGN_PINNED_TEXT');

if (version_compare(JVERSION, '4.0', '>')) {
	$lang = Factory::getApplication()->getLanguage();
	$user = Factory::getApplication()->getIdentity();
}
else {
	$lang = Factory::getLanguage();
	$user = Factory::getUser();
}

$short_lang   = substr($lang->getTag(), 0, 2);
$current_lang = $lang->getTag();
$languages    = LanguageHelper::getLanguages();
if (count($languages) > 1) {
	$many_languages = '1';
	require_once JPATH_SITE . '/components/com_emundus/models/translations.php';
	$m_translations = new EmundusModelTranslations();
	$default_lang   = $m_translations->getDefaultLanguage()->lang_code;
}
else {
	$many_languages = '0';
	$default_lang   = $current_lang;
}

$coordinator_access = EmundusHelperAccess::asCoordinatorAccessLevel($user->id);
$sysadmin_access    = EmundusHelperAccess::isAdministrator($user->id);

require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();
?>

<list id="em-component-vue"
      component="list_v2"
      type="campaigns"
      coordinatorAccess="<?= $coordinator_access ?>"
      sysadminAccess="<?= $sysadmin_access ?>"
      shortLang="<?= $short_lang ?>" currentLanguage="<?= $current_lang ?>"
      manyLanguages="<?= $many_languages ?>"
      defaultLang="<?= $default_lang ?>"
>
</list>

<script src="media/com_emundus_vue/app_emundus.js?<?php echo $hash ?>"></script>
