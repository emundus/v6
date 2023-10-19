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

Text::script('COM_EMUNDUS_GALLERY_DISPLAY');
Text::script('COM_EMUNDUS_GALLERY_DETAILS');
Text::script('COM_EMUNDUS_GALLERY_SETTINGS');
Text::script('COM_EMUNDUS_GALLERY_DISPLAY_INTRO');

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

require_once (JPATH_COMPONENT.DS.'helpers'.DS.'cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();
?>


<div id="em-component-vue"
     component="editgallery"
     gallery="<?= $this->id ;?>"
     shortLang="<?= $short_lang ?>" currentLanguage="<?= $current_lang ?>"
     defaultLang="<?= $default_lang ?>"
     manyLanguages="<?= $many_languages ?>"
     coordinatorAccess="<?= $coordinator_access ?>"
     sysadminAccess="<?= $sysadmin_access ?>"
></div>

<script src="media/com_emundus_vue/app_emundus.js?<?php echo $hash ?>"></script>
