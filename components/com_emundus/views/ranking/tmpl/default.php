<?php

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

require_once (JPATH_ROOT . '/components/com_emundus/helpers/cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();

$lang = Factory::getLanguage();
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

$coordinator_access = EmundusHelperAccess::asCoordinatorAccessLevel($this->user->id);
$sysadmin_access = EmundusHelperAccess::isAdministrator($this->user->id);

Text::script('COM_EMUNDUS_CLASSEMENT_ASK_LOCK_RANKING');
Text::script('COM_EMUNDUS_CLASSEMENT_LOCK_RANKING');
Text::script('COM_EMUNDUS_NB_FILES');
Text::script('COM_EMUNDUS_CLASSEMENT_YOUR_RANKING');
Text::script('COM_EMUNDUS_CLASSEMENT_FILE');
Text::script('COM_EMUNDUS_CLASSEMENT_NOT_RANKED');

?>

<div class="em-p-0-12">
    <h2><?= Text::_('COM_EMUNDUS_CLASSEMENT_TITLE') ?></h2>
    <p class="em-neutral-600-color em-mt-8 em-mb-8"><?= Text::_('COM_EMUNDUS_CLASSEMENT_HIERARCHY_LEVEL') . $this->hierarchy_id . ' - ' . Text::_('COM_EMUNDUS_CLASSEMENT_RANKER') . ' ' . $this->user->name ?></p>
    <div id="em-component-vue"
         component="classement"
         hash="<?= $hash ?>"
         user="<?= $this->user->id ?>"
         hierarchy_id="<?= $this->hierarchy_id ?>"
         shortLang="<?= $short_lang ?>"
         currentLanguage="<?= $current_lang ?>"
         defaultLang="<?= $default_lang ?>"
         manyLanguages="<?= $many_languages ?>"
         coordinatorAccess="<?= $coordinator_access ?>"
         sysadminAccess="<?= $sysadmin_access ?>"
    ></div>
</div>

<script src="media/com_emundus_vue/app_emundus.js?<?php echo $hash ?>"></script>