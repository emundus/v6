<?php

JText::script('COM_EMUNDUS_ONBOARD_ADD_RETOUR');
JText::script('COM_EMUNDUS_ONBOARD_ADD_CONTINUER');
JText::script('COM_EMUNDUS_ONBOARD_OK');
JText::script('COM_EMUNDUS_ONBOARD_CANCEL');
JText::script('COM_EMUNDUS_ONBOARD_NEXT');
JText::script('COM_EMUNDUS_ONBOARD_LOAD_FILE');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM');
JText::script('COM_EMUNDUS_ONBOARD_SELECT_ALL');
JText::script('COM_EMUNDUS_ONBOARD_MODIFY');
JText::script('COM_EMUNDUS_ONBOARD_UPDATE_ICON');
JText::script('COM_EMUNDUS_SWAL_OK_BUTTON');
JText::script('COM_EMUNDUS_ONBOARD_SETTINGS_CONTENT_PUBLISH');

JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_GLOBAL');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_NO_LANGUAGES_AVAILABLE');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_ORPHELINS');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_DEFAULT');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_DEFAULT_DESC');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SECONDARY');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SECONDARY_DESC');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SELECT_LANGUAGE');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_NO_TRANSLATION_TITLE');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_NO_TRANSLATION_TEXT');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SELECT_OBJECT');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SELECT');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_AUTOSAVE');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_AUTOSAVE_PROGRESS');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_AUTOSAVE_LAST');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_NO_ORPHELINS_TITLE');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_NO_ORPHELINS_TEXT');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_ORPHELIN_CONFIRM_TRANSLATION');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_OTHER_LANGUAGE');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SETUP_PROGRESSING');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SETUP_SUCCESS');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SUGGEST_LANGUAGE');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SUGGEST_LANGUAGE_FIELD');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SUGGEST_LANGUAGE_SEND');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SUGGEST_LANGUAGE_SENDED');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SUGGEST_LANGUAGE_SENDED_TEXT');
JText::script('COM_EMUNDUS_ONBOARD_SETTINGS_MENU_CONTENT');
JText::script('COM_EMUNDUS_ONBOARD_SETTINGS_MENU_CONTENT_DESC');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_ORPHANS_CONGRATULATIONS');
JText::script('COM_EMUNDUS_ONBOARD_BANNER');
JText::script('COM_EMUNDUS_FORM_BUILDER_RECOMMENDED_SIZE');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_EXPORT');

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

require_once(JPATH_ROOT . '/components/com_emundus/helpers/cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();

$app = JFactory::getApplication();
$default_menu = $app->input->getInt('default_menu', 1);

$redirect_on_close = '';
$menus = $app->getMenu();
$items = $menus->getItems('component', 'com_emundus');
foreach ($items as $item) {
	if ($item->query['view'] == 'settings') {
		$redirect_on_close = $item->alias;
		break;
	}
}
?>

<div id="em-component-vue"
     component="TranslationTool"
     shortLang="<?= $short_lang ?>"
     currentLanguage="<?= $current_lang ?>"
     defaultLang="<?= $default_lang ?>"
     coordinatorAccess="<?= $coordinator_access ?>"
     sysadminAccess="<?= $sysadmin_access ?>"
     manyLanguages="<?= $many_languages ?>"
     showModalOnLoad="1"
     defaultMenuIndex="<?= $default_menu ?>"
     redirectOnClose="<?= $redirect_on_close ?>"
></div>

<script src="media/com_emundus/js/settings.js?<?php echo $hash ?>"></script>
<script src="media/com_emundus_vue/app_emundus.js?<?php echo $hash ?>"></script>