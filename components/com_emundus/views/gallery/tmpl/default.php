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

JText::script('COM_EMUNDUS_ONBOARD_MODIFY');
JText::script('COM_EMUNDUS_ONBOARD_GALLERY');
JText::script('COM_EMUNDUS_ONBOARD_GALLERY_NAME');
JText::script('COM_EMUNDUS_ONBOARD_ADD_GALLERY');
JText::script('COM_EMUNDUS_ONBOARD_EMPTY_LIST');
JText::script('COM_EMUNDUS_ONBOARD_FILTER');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_ALL');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_PUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_RESULTS');
JText::script('COM_EMUNDUS_ONBOARD_ALL_RESULTS');
JText::script('COM_EMUNDUS_ONBOARD_SEARCH');
JText::script('COM_EMUNDUS_CAMPAIGN');
JText::script('COM_EMUNDUS_ACCESS_CREATE');

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

require_once (JPATH_COMPONENT.DS.'helpers'.DS.'cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();
?>

<list id="em-component-vue"
      component="list_v2"
      type="gallery"
      coordinatorAccess="<?= $coordinator_access ?>"
      sysadminAccess="<?= $sysadmin_access ?>"
      shortLang="<?= $short_lang ?>" currentLanguage="<?= $current_lang ?>"
      manyLanguages="<?= $many_languages ?>"
      defaultLang="<?= $default_lang ?>"
>
</list>

<script src="media/com_emundus_vue/app_emundus.js?<?php echo $hash ?>"></script>
