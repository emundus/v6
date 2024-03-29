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

Text::script('COM_EMUNDUS_RANKING_NO_FILES');
Text::script('COM_EMUNDUS_CLASSEMENT_ASK_LOCK_RANKING');
Text::script('COM_EMUNDUS_CLASSEMENT_LOCK_RANKING');
Text::script('COM_EMUNDUS_NB_FILES');
Text::script('COM_EMUNDUS_CLASSEMENT_YOUR_RANKING');
Text::script('COM_EMUNDUS_CLASSEMENT_FILE');
Text::script('COM_EMUNDUS_CLASSEMENT_NOT_RANKED');
Text::script('COM_EMUNDUS_CLASSEMENT_RANKING_SELECT_LABEL');

Text::script('COM_EMUNDUS_CLASSEMENT_MODAL_COMPARISON_HEADER_TITLE');
Text::script('COM_EMUNDUS_MODAL_COMPARISON_SELECT_A_FILE_TO_COMPARE_TO');
Text::script('COM_EMUNDUS_MODAL_COMPARISON_BACK_BUTTON');

// Translation for the file view
Text::script('COM_EMUNDUS_FILES_EVALUATION');
Text::script('COM_EMUNDUS_FILES_DECISION');
Text::script('COM_EMUNDUS_FILES_TO_EVALUATE');
Text::script('COM_EMUNDUS_FILES_EVALUATED');
Text::script('COM_EMUNDUS_ONBOARD_FILE');
Text::script('COM_EMUNDUS_ONBOARD_STATUS');
Text::script('COM_EMUNDUS_FILES_APPLICANT_FILE');
Text::script('COM_EMUNDUS_FILES_ATTACHMENTS');
Text::script('COM_EMUNDUS_FILES_COMMENTS');
Text::script('COM_EMUNDUS_ONBOARD_NOFILES');
Text::script('COM_EMUNDUS_FILES_ELEMENT_SELECTED');
Text::script('COM_EMUNDUS_FILES_ELEMENTS_SELECTED');
Text::script('COM_EMUNDUS_FILES_UNSELECT');
Text::script('COM_EMUNDUS_FILES_OPEN_IN_NEW_TAB');
Text::script('COM_EMUNDUS_FILES_CANNOT_ACCESS');
Text::script('COM_EMUNDUS_FILES_CANNOT_ACCESS_DESC');
Text::script('COM_EMUNDUS_FILES_DISPLAY_PAGE');
Text::script('COM_EMUNDUS_FILES_NEXT_PAGE');
Text::script('COM_EMUNDUS_FILES_PAGE');
Text::script('COM_EMUNDUS_FILES_TOTAL');
Text::script('COM_EMUNDUS_FILES_ALL');
Text::script('COM_EMUNDUS_FILES_ADD_COMMENT');
Text::script('COM_EMUNDUS_FILES_CANNOT_ACCESS_COMMENTS');
Text::script('COM_EMUNDUS_FILES_CANNOT_ACCESS_COMMENTS_DESC');
Text::script('COM_EMUNDUS_FILES_COMMENT_TITLE');
Text::script('COM_EMUNDUS_FILES_COMMENT_BODY');
Text::script('COM_EMUNDUS_FILES_VALIDATE_COMMENT');
Text::script('COM_EMUNDUS_FILES_COMMENT_DELETE');
Text::script('COM_EMUNDUS_FILES_ASSOCS');
Text::script('COM_EMUNDUS_FILES_TAGS');
Text::script('COM_EMUNDUS_FILES_PAGE_ON');
Text::script('COM_EMUNDUS_ERROR_OCCURED');
Text::script('COM_EMUNDUS_ACTIONS_CANCEL');
Text::script('COM_EMUNDUS_OK');
Text::script('COM_EMUNDUS_FILES_FILTER_NO_ELEMENTS_FOUND');
Text::script('COM_EMUNDUS_RANKING_LOCK_RANKING_CONFIRM_TITLE');
Text::script('COM_EMUNDUS_RANKING_LOCK_RANKING_CONFIRM_TEXT');
Text::script('COM_EMUNDUS_RANKING_LOCK_RANKING_CONFIRM_YES');
Text::script('COM_EMUNDUS_RANKING_LOCK_RANKING_CONFIRM_NO');
Text::script('COM_EMUNDUS_RANKING_RANKER');
Text::script('COM_EMUNDUS_CLASSEMENT_ASK_USERS_LOCK_RANKING');
Text::script('COM_EMUNDUS_CLASSEMENT_ASK_HIERARCHIES_LOCK_RANKING');
Text::script('COM_EMUNDUS_CLASSEMENT_CANCEL_ASK_LOCK_RANKING');
Text::script('COM_EMUNDUS_CLASSEMENT_CONFIRM_ASK_LOCK_RANKING');
Text::script('COM_EMUNDUS_RANKING_LOCK_RANKING_ASK_CONFIRM_SUCCESS_TITLE');
Text::script('COM_EMUNDUS_RANKING_UPDATE_RANKING_ERROR_TITLE');

?>
<div class="em-flex-row em-w-100 em-h-100 em-flex-align-start">

    <?php if ($this->display_filters): ?>
    <aside class="em-left-panel filters em-h-100">
        <div class="content">
            <h3>Filtres</h3>
            <?php echo JHtml::_('content.prepare', '{loadposition emundus_filters}'); ?>
        </div>
        <div class="em-left-panel-opener em-pointer em-flex-row">
            <span>Filtres</span>
            <span class="material-icons-outlined">arrow_drop_down</span>
        </div>
    </aside>
    <?php endif; ?>

    <div class="em-p-0-12 em-w-100 em-h-100">
        <h2><?= Text::_($this->params->get('title', 'COM_EMUNDUS_CLASSEMENT_TITLE')) ?></h2>
        <section id="ranking-introduction">
            <?= Text::_($this->params->get('introduction', '')) ?>
        </section>

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
             fileTabsStr="<?= $this->comparison_modal_tabs ?>"
        ></div>
    </div>
</div>

<script src="media/com_emundus_vue/app_emundus.js?<?php echo $hash ?>"></script>

<script>
    const leftPanelOpener = document.querySelector('.em-left-panel-opener');
    if (leftPanelOpener) {
        const leftPanel = document.querySelector('.em-left-panel.filters');

        leftPanelOpener.addEventListener('click', function () {
            console.log('click');
            leftPanel.classList.toggle('em-left-panel-opened');
        });
    }
</script>

<style>
    .em-left-panel.filters {
        position: relative;
        width: 0;
        height: calc(100vh - 112px);
        transition: 0.3s;
        box-shadow: 10px 0px 10px -9px #aaa;
    }

    .em-left-panel-opened {
        width: 33% !important;
        padding: 16px;
    }

    .em-left-panel .content {
        display: none;
    }

    .em-left-panel-opened .content {
        display: block;
    }

    .em-left-panel-opener {
        position: absolute;
        top: 50%;
        right: -75px;
        transform: rotate(-90deg);
        background-color: #353544;
        color: #fff;
        padding: 4px 34px;
        border-radius:0 0 8px 8px;
    }

    .em-left-panel-opened .em-left-panel-opener {
        right: -80px;
    }
</style>