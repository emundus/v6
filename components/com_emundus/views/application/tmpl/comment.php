<?php
/**
 * Created by PhpStorm.
 * User: brivalland
 * Date: 13/11/14
 * Time: 11:24
 */

defined('_JEXEC') or die('Restricted access');

JFactory::getSession()->set('application_layout', 'comment');
$current_lang = JFactory::getLanguage();
$short_lang = substr($current_lang->getTag(), 0 , 2);
$coordinator_access = EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id);
$sysadmin_access = EmundusHelperAccess::isAdministrator($this->_user->id);
$xmlDoc = new DOMDocument();
if ($xmlDoc->load(JPATH_SITE.'/administrator/components/com_emundus/emundus.xml')) {
    $release_version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
}

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

require_once(JPATH_ROOT . '/components/com_emundus/models/users.php');
$m_users = new EmundusModelUsers();
$emundus_user = JFactory::getSession()->get('emundusUser');
$applicant_profiles = $m_users->getApplicantProfiles();
$applicant_profile_ids = array_map(function($profile) {
    return $profile->id;
}, $applicant_profiles);

$is_applicant = in_array($emundus_user->profile, $applicant_profile_ids);

$user_comment_access = [
    'c' => EmundusHelperAccess::asAccessAction(10, 'c', $this->_user->id, $fnum),
    'r' => EmundusHelperAccess::asAccessAction(10, 'r', $this->_user->id, $fnum),
    'u' => EmundusHelperAccess::asAccessAction(10, 'u', $this->_user->id, $fnum),
    'd' => EmundusHelperAccess::asAccessAction(10, 'd', $this->_user->id, $fnum),
];
?>

<div class="row">
    <div class="panel panel-default widget em-container-comments em-container-form">
        <div class="panel-heading em-container-form-heading">
            <h3 class="panel-title">
                <span class="material-icons-outlined">comment</span>
                <?= JText::_('COM_EMUNDUS_COMMENTS') ?>
            </h3>
            <div class="btn-group pull-right">
                <button id="em-prev-file" class="btn btn-info btn-xxl"><span class="material-icons">arrow_back</span></button>
                <button id="em-next-file" class="btn btn-info btn-xxl"><span class="material-icons">arrow_forward</span></button>
            </div>
        </div>
    </div>
</div>

<div id="em-component-vue"
     component="comments"
     user="<?= $this->_user->id ?>"
     ccid="<?= $this->ccid ?>"
     access='<?= json_encode($user_comment_access); ?>'
     is_applicant="<?= $is_applicant; ?>"
     current_form="<?= 0 ?>"
     currentLanguage="<?= $current_lang->getTag() ?>"
     shortLang="<?= $short_lang ?>"
     coordinatorAccess="<?= $coordinator_access ?>"
     sysadminAccess="<?= $sysadmin_access ?>"
     manyLanguages="<?= $many_languages ?>"
>
</div>

<script src="media/com_emundus_vue/app_emundus.js?<?php echo $release_version ?>"></script>