<?php

defined('_JEXEC') or die('Restricted Access');
require_once (JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
include_once(JPATH_BASE.'/components/com_emundus/models/campaign.php');
include_once(JPATH_BASE.'/components/com_emundus/models/profile.php');
include_once(JPATH_BASE.'/components/com_emundus/models/users.php');

JText::script('MOD_EMUNDUS_EVALUATIONS_MY_EVALUATIONS');
JText::script('MOD_EMUNDUS_EVALUATIONS_FNUM');
JText::script('MOD_EMUNDUS_EVALUATIONS_APPLICANT_NAME');
JText::script('MOD_EMUNDUS_EVALUATIONS_BACK');
JText::script('MOD_EMUNDUS_EVALUATIONS_ATTACHMENT_NAME');
JText::script('MOD_EMUNDUS_EVALUATIONS_ATTACHMENT_SENT_ON');
JText::script('MOD_EMUNDUS_EVALUATIONS_ATTACHMENT_DESCRIPTION');
JText::script('MOD_EMUNDUS_EVALUATIONS_ATTACHMENT_SEND_BY');
JText::script('MOD_EMUNDUS_EVALUATIONS_FORMS');
JText::script('MOD_EMUNDUS_EVALUATIONS_ATTACHMENT');
JText::script('MOD_EMUNDUS_EVALUATIONS_PLEASE_SELECT');
JText::script('MOD_EMUNDUS_EVALUATIONS_FILES_TO_EVALUATE_INTRO');
JText::script('MOD_EMUNDUS_EVALUATIONS_FILES_TO_EVALUATE');
JText::script('MOD_EMUNDUS_EVALUATIONS_SELECT_A_CAMPAIGN');
JText::script('MOD_EMUNDUS_EVALUATIONS_APPLICATION_FORM');
JText::script('MOD_EMUNDUS_EVALUATIONS_EVALUATION_GRID');
JText::script('MOD_EMUNDUS_EVALUATIONS_ATTACHMENTS');
JText::script('MOD_EMUNDUS_EVALUATIONS_MISSING_EVALUATION_GRID');
JText::script('MOD_EMUNDUS_EVALUATIONS_COMPLETED');
JText::script('MOD_EMUNDUS_EVALUATIONS_APPLICATION_DOWNLOAD');
JText::script('MOD_EMUNDUS_EVALUATIONS_NO_FILE');

JText::script('COM_EMUNDUS_ATTACHMENTS_CHECK');
JText::script('COM_EMUNDUS_ATTACHMENTS_SEND_DATE');
JText::script('COM_EMUNDUS_ATTACHMENTS_MODIFICATION_DATE');
JText::script('COM_EMUNDUS_ATTACHMENTS_MODIFIED_BY');
JText::script('COM_EMUNDUS_ATTACHMENTS_DOCUMENT_PREVIEW_INCOMPLETE_MSG');
JText::script('COM_EMUNDUS_ATTACHMENTS_DOCUMENT_TYPE');
JText::script('COM_EMUNDUS_ATTACHMENTS_MINI_DESCRIPTION');
JText::script('COM_EMUNDUS_ATTACHMENTS_CAMPAIGN_ID');
JText::script('COM_EMUNDUS_ATTACHMENTS_CATEGORY');
JText::script('COM_EMUNDUS_ATTACHMENTS_SAVE');
JText::script('COM_EMUNDUS_ATTACHMENTS_FILTER_ACTION');
JText::script('COM_EMUNDUS_ATTACHMENTS_REPLACE');
JText::script('COM_EMUNDUS_ATTACHMENTS_NO_ATTACHMENTS_FOUND');
JText::script('COM_EMUNDUS_ATTACHMENTS_REFRESH_TITLE');
JText::script('COM_EMUNDUS_ATTACHMENTS_DELETE_TITLE');
JText::script('COM_EMUNDUS_ATTACHMENTS_CLOSE');
JText::script('COM_EMUNDUS_ATTACHMENTS_USER_NOT_FOUND');
JText::script('COM_EMUNDUS_ATTACHMENTS_DOCUMENT_PREVIEW_UNAVAILABLE');
JText::script('COM_EMUNDUS_ATTACHMENTS_UPLOADED_BY');
JText::script('COM_EMUNDUS_ATTACHMENTS_WAITING');
JText::script('COM_EMUNDUS_ATTACHMENTS_WARNING');
JText::script('COM_EMUNDUS_ATTACHMENTS_FILE_TYPE_NOT_SUPPORTED');
JText::script('COM_EMUNDUS_ATTACHMENTS_FILE_NOT_FOUND');
JText::script('COM_EMUNDUS_ATTACHMENTS_DOWNLOAD');
JText::script('UPLOAD_BY_APPLICANT');
JText::script('COM_EMUNDUS_ATTACHMENTS_PERMISSIONS');
JText::script('COM_EMUNDUS_ATTACHMENTS_CAN_BE_VIEWED');
JText::script('COM_EMUNDUS_ATTACHMENTS_CAN_BE_DELETED');
JText::script('COM_EMUNDUS_ATTACHMENTS_UNAUTHORIZED_ACTION');
JText::script('COM_EMUNDUS_VIEW_FORM_OTHER_PROFILES');
JText::script('COM_EMUNDUS_ATTACHMENTS_PERMISSION_VIEW');
JText::script('COM_EMUNDUS_ATTACHMENTS_PERMISSION_DELET');
JText::script('COM_EMUNDUS_ATTACHMENTS_COMPLETED');
JText::script('COM_EMUNDUS_ATTACHMENTS_EXPORT_LINK');
JText::script('COM_EMUNDUS_EXPORTS_EXPORT');
JText::script('COM_EMUNDUS_ACTIONS_SEARCH');
JText::script('COM_EMUNDUS_ATTACHMENTS_SELECT_CATEGORY');
JText::script('COM_EMUNDUS_ATTACHMENTS_LINK_TO_DOWNLOAD');

$user = JFactory::getSession()->get('emundusUser');
$label = $params->get('label', 'Mes dossiers à évaluer');
$intro = $params->get('intro', 'Veuillez sélectionner une campagne afin de commencer l\'évaluation de vos dossiers');
$readonly = $params->get('readonly_eval', '0');

if (!empty($label)) {
    echo '<h3 class="em-h3 em-mb-16">'.$label.'</h3>';
}

if (!empty($intro)) {
    echo '<p class="em-mt-8">'.$intro.'</p>';
}
echo '<div id="em-evaluations-vue" user="'. $user->id . '" module="' . $module->id . '" readonly="' . $readonly . '"></div>';
?>

<script src="media/mod_emundus_evaluations/app.js"></script>
