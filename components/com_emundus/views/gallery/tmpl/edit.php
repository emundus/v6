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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

Text::script('COM_EMUNDUS_ONBOARD_OK');
Text::script('COM_EMUNDUS_ONBOARD_CANCEL');

Text::script('COM_EMUNDUS_GALLERY_DISPLAY');
Text::script('COM_EMUNDUS_GALLERY_DETAILS');
Text::script('COM_EMUNDUS_GALLERY_SETTINGS');
Text::script('COM_EMUNDUS_GALLERY_DISPLAY_INTRO');
Text::script('COM_EMUNDUS_ONBOARD_EDIT_GALLERY');
Text::script('COM_EMUNDUS_ONBOARD_EDIT_PREVIEW');
Text::script('COM_EMUNDUS_GALLERY_VIGNETTES');

Text::script('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_TITLE');
Text::script('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_SUBTITLE');
Text::script('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_SUBTITLE_ICON');
Text::script('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_SUBTITLE_ICON_NO_ICON');
Text::script('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_TAG');
Text::script('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_TAGS');
Text::script('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_RESUME');
Text::script('COM_EMUNDUS_GALLERY_DISPLAY_FIELDS_IMAGE');

Text::script('COM_EMUNDUS_GALLERY_DETAILS_TITLE');
Text::script('COM_EMUNDUS_GALLERY_DETAILS_FIELDS_BANNER');
Text::script('COM_EMUNDUS_GALLERY_DETAILS_FIELDS_LOGO');

Text::script('COM_EMUNDUS_GALLERY_SETTINGS_TITLE');
Text::script('COM_EMUNDUS_GALLERY_SETTINGS_INTRO');
Text::script('COM_EMUNDUS_GALLERY_SETTINGS_ENABLE_VOTE');
Text::script('COM_EMUNDUS_GALLERY_SETTINGS_MAX_VOTE');
Text::script('COM_EMUNDUS_GALLERY_SETTINGS_START_DATE');
Text::script('COM_EMUNDUS_GALLERY_SETTINGS_END_DATE');
Text::script('COM_EMUNDUS_GALLERY_SETTINGS_VOTING_ACCESS');
Text::script('COM_EMUNDUS_GALLERY_SETTINGS_VOTING_ACCESS_PUBLIC');
Text::script('COM_EMUNDUS_GALLERY_SETTINGS_VOTING_ACCESS_REGISTERED');
Text::script('COM_EMUNDUS_GALLERY_SETTINGS_STATUS');

require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');

$lang = Factory::getApplication()->getLanguage();
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

$user = Factory::getApplication()->getIdentity();
$coordinator_access = EmundusHelperAccess::asCoordinatorAccessLevel($user->id);
$sysadmin_access = EmundusHelperAccess::isAdministrator($user->id);

$id = Factory::getApplication()->input->getInt('gid', 0);

require_once (JPATH_COMPONENT.DS.'helpers'.DS.'cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();
?>


<div id="em-component-vue"
     component="editgallery"
     gallery="<?= $id ;?>"
     shortLang="<?= $short_lang ?>" currentLanguage="<?= $current_lang ?>"
     defaultLang="<?= $default_lang ?>"
     manyLanguages="<?= $many_languages ?>"
     coordinatorAccess="<?= $coordinator_access ?>"
     sysadminAccess="<?= $sysadmin_access ?>"
></div>

<script src="media/com_emundus_vue/app_emundus.js?<?php echo $hash ?>"></script>
