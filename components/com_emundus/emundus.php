<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
*/

// No direct access
defined('_JEXEC') or die('ACCESS_DENIED');

// Require the base controller
require_once( JPATH_COMPONENT.DS.'controller.php' );

// emundus helpers
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
// LOGGER
jimport('joomla.log.log');
JLog::addLogger(
    array(
        'text_file' => 'com_emundus.error.php'
    ),
    JLog::ALL,
    array('com_emundus')
);
JLog::addLogger(
    array(
        'text_file' => 'com_emundus.email.php'
    ),
    JLog::ALL,
    array('com_emundus.email')
);
JLog::addLogger(
    array(
        'text_file' => 'com_emundus.webhook.php'
    ),
    JLog::ALL,
    array('com_emundus.webhook')
);
// translation for javacript
JText::script('PLEASE_SELECT');
JText::script('IN');
JText::script('ALL');
JText::script('USERNAME');
JText::script('EMAIL');
JText::script('APPLICATION_CREATION_DATE');
JText::script('CAMPAIGN_ID');
JText::script('SEND_ON');
JText::script('COM_EMUNDUS_ONBOARD_ERROR_MESSAGE');
JText::script('COM_EMUNDUS_ONBOARD_OK');
JText::script('COM_EMUNDUS_ONBOARD_CANCEL');

JText::script('COM_EMUNDUS_EX');
JText::script('COM_EMUNDUS_APPLICATION_TAG');
JText::script('COM_EMUNDUS_ACCESS_FILE');
JText::script('COM_EMUNDUS_ACCESS_ATTACHMENT');
JText::script('COM_EMUNDUS_ACCESS_TAGS');
JText::script('COM_EMUNDUS_ACCESS_STATUS');
JText::script('COM_EMUNDUS_ACCESS_USER');
JText::script('COM_EMUNDUS_ACCESS_EVALUATION');
JText::script('COM_EMUNDUS_ACCESS_EXPORT_EXCEL');
JText::script('COM_EMUNDUS_ACCESS_EXPORT_ZIP');
JText::script('COM_EMUNDUS_ACCESS_EXPORT_PDF');
JText::script('COM_EMUNDUS_EXPORTS_EXPORT_AS_CSV_TEMPLATE');
JText::script('COM_EMUNDUS_ACCESS_MAIL_APPLICANT');
JText::script('COM_EMUNDUS_ACCESS_MAIL_EVALUATOR');
JText::script('COM_EMUNDUS_ACCESS_MAIL_GROUP');
JText::script('COM_EMUNDUS_ACCESS_MAIL_EXPERTS');
JText::script('COM_EMUNDUS_ACCESS_MAIL_ADDRESS');
JText::script('COM_EMUNDUS_ACCESS_COMMENT_FILE');
JText::script('COM_EMUNDUS_ACCESS_ACCESS_FILE');
JText::script('COM_EMUNDUS_CONFIRM_DELETE_FILE');
JText::script('COM_EMUNDUS_SHOW_ELEMENTS');
JText::script('COM_EMUNDUS_CHOOSE_PRG');
JText::script('COM_EMUNDUS_CHOOSE_CAMP');
JText::script('COM_EMUNDUS_CHOOSE_PRG_DEFAULT');
JText::script('COM_EMUNDUS_CHOOSE_FORM_ELEM');
JText::script('COM_EMUNDUS_CHOOSE_EVAL_FORM_ELEM');
JText::script('COM_EMUNDUS_CHOOSEN_FORM_ELEM');
Jtext::script('COM_EMUNDUS_CHOOSE_ADMISSION_FORM_ELEM');
Jtext::script('COM_EMUNDUS_CHOOSEN_ADMISSION_FORM_ELEM');
JText::script('COM_EMUNDUS_CHOOSE_OTHER_ADMISSION_ELTS');
Jtext::script('COM_EMUNDUS_CHOOSE_DECISION_FORM_ELEM');
Jtext::script('COM_EMUNDUS_CHOOSEN_DECISION_FORM_ELEM');
JText::script('COM_EMUNDUS_CHOOSE_OTHER_COL');
JText::script('COM_EMUNDUS_PHOTO');
JText::script('COM_EMUNDUS_FORMS');
JText::script('COM_EMUNDUS_ATTACHMENT');
JText::script('COM_EMUNDUS_ASSESSMENT');
JText::script('COM_EMUNDUS_COMMENT');
JText::script('COM_EMUNDUS_COMMENTS');
JText::script('COM_EMUNDUS_ACCESS_COMMENT_FILE_CREATE');
JText::script('COM_EMUNDUS_EXCEL_GENERATION');
JText::script('COM_EMUNDUS_CHOOSE_EXTRACTION_METHODE');
JText::script('COM_EMUNDUS_CHOOSE_EXTRACTION_METHODE_AGGREGATE_DISTINCT');
JText::script('COM_EMUNDUS_CHOOSE_EXTRACTION_METHODE_AGGREGATE');
JText::script('COM_EMUNDUS_CHOOSE_EXTRACTION_METHODE_LEFTJOIN');
JText::script('COM_EMUNDUS_DOWNLOAD_EXTRACTION');
JText::script('COM_EMUNDUS_EXPORTS_ZIP_GENERATION');
JText::script('COM_EMUNDUS_DOWNLOAD_ZIP');
JText::script('COM_EMUNDUS_PUBLISH');
JText::script('COM_EMUNDUS_COPY_FILE');
JText::script('COM_EMUNDUS_SHARE_FILE');
JText::script('COM_EMUNDUS_FILTERS_PLEASE_SELECT_FILTER');
JText::script('DELETE');
JText::script('COM_EMUNDUS_ACTIONS_DELETE');
JText::script('COM_EMUNDUS_FILTERS_FILTER_SAVED');
JText::script('COM_EMUNDUS_FILTERS_FILTER_DELETED');
JText::script('COM_EMUNDUS_ERROR_SQL_ERROR');
JText::script('COM_EMUNDUS_FORM_TITLE');
JText::script('COM_EMUNDUS_FORM_GROUP');
JText::script('COM_EMUNDUS_TO_UPPER_CASE');
JText::script('COM_EMUNDUS_ASSOCIATED_GROUPS');
JText::script('COM_EMUNDUS_ASSOCIATED_USERS');
JText::script('COM_EMUNDUS_EVALUATIONS_OVERALL');
JText::script('COM_EMUNDUS_CHOOSE_EXTRACTION_OPTION');
JText::script('COM_EMUNDUS_CHOOSE_OTHER_OPTION');
JText::script('COM_EMUNDUS_EXPORTS_GENERATE_ZIP');
JText::script('COM_EMUNDUS_ACTIONS_CANCEL');
JText::script('COM_EMUNDUS_OK');
JText::script('COM_EMUNDUS_ACTIONS_BACK');
JText::script('COM_EMUNDUS_USERNAME');
JText::script('ID');
JText::script('COM_EMUNDUS_ACTIONS_ALL');
JText::script('COM_EMUNDUS_IN');
JText::script('COM_EMUNDUS_SELECT_HERE');
JText::script('SELECT_HERE');
JText::script('COM_EMUNDUS_FILTERS_CHECK_ALL_ALL');
JText::script('COM_EMUNDUS_FILES_SAVE_FILTER');
JText::script('COM_EMUNDUS_FILES_ENTER_HERE');

JText::script('USERNAME_Q');
JText::script('ID_Q');
JText::script('ALL_Q');
JText::script('LAST_NAME_Q');
JText::script('FIRST_NAME_Q');
JText::script('FNUM_Q');
JText::script('EMAIL_Q');

JText::script('BACK');

JText::script('COM_EMUNDUS_LOADING');
JText::script('TITLE');
JText::script('COM_EMUNDUS_COMMENTS_ADD_COMMENT');
JText::script('COM_EMUNDUS_COMMENTS_ERROR_PLEASE_COMPLETE');
JText::script('COM_EMUNDUS_COMMENTS_ENTER_COMMENT');
JText::script('COM_EMUNDUS_COMMENTS_SENT');
JText::script('COM_EMUNDUS_ACCESS_SHARE_PROGRESS');
JText::script('COM_EMUNDUS_ACCESS_SHARE_SUCCESS');
JText::script('COM_EMUNDUS_ACCESS_ERROR_REQUIRED');
JText::script('ERROR');
JText::script('COM_EMUNDUS_EXPORTS_DOWNLOAD_PDF');
JText::script('COM_EMUNDUS_EXPORTS_FORMS_PDF');
JText::script('COM_EMUNDUS_EXPORTS_ATTACHMENT_PDF');
JText::script('COM_EMUNDUS_EXPORTS_ASSESSMENT_PDF');
JText::script('JYES');
JText::script('JNO');
JText::script('COM_EMUNDUS_PLEASE_SELECT');
JText::script('COM_EMUNDUS_EXPORTS_CHANGE_STATUS');
JText::script('COM_EMUNDUS_EXPORTS_EXPORT_SET_TAG');
JText::script('COM_EMUNDUS_ATTACHMENTS_YOU_MUST_SELECT_ATTACHMENT');
JText::script('COM_EMUNDUS_ATTACHMENTS_AGGREGATIONS');
JText::script('COM_EMUNDUS_LETTERS_FILES_GENERATED');
JText::script('FILE_NAME');
JText::script('COM_EMUNDUS_ATTACHMENTS_LINK_TO_DOWNLOAD');
JText::script('LINK_TO_DOWNLOAD');
JText::script('COM_EMUNDUS_ATTACHMENTS_ALL_IN_ONE_DOC');
JText::script('COM_EMUNDUS_EXPORTS_PDF_TAGS');
JText::script('COM_EMUNDUS_EXPORTS_PDF_STATUS');
JText::script('COM_EMUNDUS_EXPORTS_ADD_HEADER');
JText::script('COM_EMUNDUS_TAGS_DELETE_TAGS');
JText::script('COM_EMUNDUS_TAGS_CATEGORIES');
JText::script('COM_EMUNDUS_TAGS_DELETE_TAGS_CONFIRM');
JText::script('COM_EMUNDUS_TAGS_DELETE_SUCCESS');
JText::script('COM_EMUNDUS_FILTERS_CONFIRM_DELETE_FILTER');
JText::script('COM_EMUNDUS_APPLICATION_ADD_TAGS');
JText::script('COM_EMUNDUS_FILES_PLEASE_SELECT_TAG');
JText::script('COM_EMUNDUS_SELECT_SOME_OPTIONS');
JText::script('COM_EMUNDUS_SELECT_AN_OPTION');
JText::script('COM_EMUNDUS_SELECT_NO_RESULT');
JText::script('VALID');
JText::script('INVALID');
JText::script('COM_EMUNDUS_ATTACHMENTS_UNCHECKED');
JText::script('COM_EMUNDUS_EXPORTS_SELECT_AT_LEAST_ONE_FILE');
JText::script('COM_EMUNDUS_EXPORTS_INFORMATION');
JText::script('COM_EMUNDUS_FILTERS_YOU_HAVE_SELECT');
JText::script('COM_EMUNDUS_FILTERS_SELECT_ALL');
JText::script('COM_EMUNDUS_FILES_FILE');
JText::script('COM_EMUNDUS_FILES_FILES');
JText::script('COM_EMUNDUS_FILES_SELECT_ALL_FILES');
JText::script('COM_EMUNDUS_USERS_SELECT_USER');
JText::script('COM_EMUNDUS_USERS_SELECT_USERS');
JText::script('COM_EMUNDUS_APPLICATION_WARNING_CHANGE_STATUS');
JText::script('COM_EMUNDUS_APPLICATION_MAIL_CHANGE_STATUT_INFO');
JText::script('COM_EMUNDUS_APPLICATION_VALIDATE_CHANGE_STATUT');
JText::script('COM_EMUNDUS_APPLICATION_CANCEL_CHANGE_STATUT');
JText::script('COM_EMUNDUS_APPLICATION_DOCUMENT_PRINTED_ON');
JText::script('COM_EMUNDUS_APPLICATION_APPLICANT');
JText::script('COM_EMUNDUS_CHECKLIST_PROFILE_FILES_FOUND');
JText::script('COM_EMUNDUS_CHECKLIST_PROFILE_FILES_FOUND_TEXT');
JText::script('COM_EMUNDUS_CHECKLIST_PROFILE_FILES_FOUND_TEXT_2');
JText::script('COM_EMUNDUS_CHECKLIST_PROFILE_FILES_UPLOAD');
JText::script('COM_EMUNDUS_CHECKLIST_PROFILE_ATTACHMENT_FOUND');
JText::script('COM_EMUNDUS_CHECKLIST_PROFILE_ATTACHMENT_FOUND_TEXT');
JText::script('COM_EMUNDUS_CHECKLIST_PROFILE_ATTACHMENT_FOUND_UPDATE');
JText::script('COM_EMUNDUS_CHECKLIST_PROFILE_ATTACHMENT_FOUND_CONTINUE_WITHOUT_UPDATE');
JText::script('COM_EMUNDUS_USERS_MY_DOCUMENTS_LOAD');
JText::script('COM_EMUNDUS_ACCOUNT_INFORMATIONS');
JText::script('COM_EMUNDUS_ACCOUNT_PERSONAL_DETAILS');
JText::script('COM_EMUNDUS_USERS_DEFAULT_LANGAGE');
JText::script('COM_EMUNDUS_USERS_NATIONALITY');
JText::script('COM_EMUNDUS_USERS_EDIT_PROFILE_PASSWORD');
JText::script('COM_EMUNDUS_PUBLISH_UPDATE');


// view user
JText::script('COM_EMUNDUS_USERS_ERROR_NOT_A_VALID_EMAIL');
JText::script('COM_EMUNDUS_USERS_ERROR_NOT_A_VALID_LOGIN_MUST_NOT_CONTAIN_SPECIAL_CHARACTER');
JText::script('REQUIRED');
JText::script('COM_EMUNDUS_SELECT_A_VALUE');
JText::script('GROUP_CREATED');
JText::script('COM_EMUNDUS_USERS_USER_CREATED');
JText::script('LOGIN_NOT_GOOD');
JText::script('MAIL_NOT_GOOD');
JText::script('COM_EMUNDUS_USERS_ARE_YOU_SURE_TO_DELETE_USERS');
JText::script('COM_EMUNDUS_USERS_DELETED');
JText::script('COM_EMUNDUS_ACCESS_SHARE_PROGRESS');
JText::script('COM_EMUNDUS_APPLICATION_SENT');
JText::script('COM_EMUNDUS_LETTERS_FILES_GENERATED');
JText::script('COM_EMUNDUS_STATE');
JText::script('COM_EMUNDUS_PROFILE_SWITCH_PROFILE');
JText::script('COM_EMUNDUS_PROFILE_PROFILE_CHOSEN');
Jtext::script('COM_EMUNDUS_USERS_ARE_YOU_SURE_TO_REGENERATE_PASSWORD');
Jtext::script('COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_ERROR_TITLE');
Jtext::script('COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_ERROR_TEXT');
Jtext::script('COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_ERROR_UPDATE_TEXT');

//Export Excel
JText::script('COM_EMUNDUS_ADD_DATA_TO_CSV');
JText::script('COM_EMUNDUS_LIMIT_POST_SERVER');
JText::script('COM_EMUNDUS_ERROR_XLS');
JText::script('COM_EMUNDUS_ERROR_CSV_CAPACITY');
JText::script('COM_EMUNDUS_XLS_GENERATION');
JText::script('COM_EMUNDUS_EXPORT_FINISHED');
JText::script('COM_EMUNDUS_ERROR_CAPACITY_XLS');
JText::script('COM_EMUNDUS_CREATE_CSV');
JText::script('EXPECTED_GRADUATION_DATE');
JText::script('GRADE_POINT_AVERAGE');
JText::script('GRADUATION_DATE');
JText::script('TYPE_OF_DEGREE');
JText::script('INSTITUTION');
JText::script('FIELD');
JText::script('OTHER_INFORMATION');
JText::script('ADDRESS_FOR_CORRESPONDANCE');
JText::script('PERMANENT_ADDRESS_FOR_CORRESPONDANCE');
JText::script('CRIMINAL_DETAILS');
JText::script('CRIMINAL_CHARGES');
JText::script('PHYSICAL_DETAILS');
JText::script('PHYSICAL_DISABILITY');
JText::script('MOBILE');
JText::script('TELEPHONE');
JText::script('COUNTRY');
JText::script('COM_EMUNDUS_STATE');
JText::script('ZIPCODE');
JText::script('CITY');
JText::script('STREET');
JText::script('PERSONAL_DETAILS');
JText::script('NUMBER_OF_CHILDREN');
JText::script('ACCOMPANIED');
JText::script('DISABLED');
JText::script('COM_EMUNDUS_FORMS_NATIONALITY');
JText::script('BIRTH_PLACE');
JText::script('DATE_OF_BIRTH');
JText::script('MARITAL_STATUS');
JText::script('GENDER');
JText::script('MAIDEN_NAME');
JText::script('BACHELOR_DEGREE_ENGINEERING_DEGREE');
JText::script('OTHER_EDUCATION_OR_MASTER_DEGREE');
JText::script('LANGUAGE');
JText::script('MOTHER_TONGUE');
JText::script('DEGREE_LANGUAGE');
JText::script('ENGLISH');
JText::script('ENGLISH_READING');
JText::script('ENGLISH_SPEAKING');
JText::script('ENGLISH_WRITING');
JText::script('ENGLISH_TEST_SCORE');
JText::script('ENGLISH_TEST_NAME');
JText::script('OTHER_LANGUAGE');
JText::script('OTHER_LANGUAGE');
JText::script('OTHER_LANGUAGES');
JText::script('LANGUAGE_READING');
JText::script('LANGUAGE_WRITING');
JText::script('LANGUAGE_SPEAKING');
JText::script('OTHER_TEST_NAME');
JText::script('OTHER_TEST_SCORE');
JText::script('OTHER_TEST_DATE');
JText::script('INETRNSHIP');
JText::script('DURATION');
JText::script('COMPANY_OR_ACADEMIC_INSTITUTION');
JText::script('WORK_DESCRIPTION');
JText::script('FULL_TIME_OR_PART_TIME_ACTIVITY');
JText::script('PROFESSIONAL_EXPERIENCE');
JText::script('FIRST_REFEREE');
JText::script('SECOND_REFEREE');
JText::script('FIRST_NAME');
JText::script('LAST_NAME');
JText::script('UNIVERSITY_ORGANISATION');
JText::script('FAX_NUMBER');
JText::script('WEBSITE');
JText::script('COM_EMUNDUS_EMAIL');
JText::script('POSITION');
JText::script('APPLICATION_SCHOLARSHIP');
JText::script('ERASMUS_MUNDUS_SCHOLARSHIP');
JText::script('CATEGORY_B_DETAILS');
JText::script('FINANCIAL_INFORMATION');
JText::script('SOURCE_FUNDING');
JText::script('HOW_DID_YOU_LEARNED_ABOUT_THIS_MASTER');
JText::script('SELECT_ONE');
JText::script('FIRST_PREFERENCE');
JText::script('SECONDE_PREFERENCE');
JText::script('DID_YOU_APPLY_FOR_ANOTHER_PROGRAM');
JText::script('PROGRAM_NAME');
JText::script('CHOOSE_YOUR_OPTION');
JText::script('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS_OTHERS_EVAL');
JText::script('COM_EMUNDUS_EXPORTS_GENERATE_EXCEL');
JText::script('COM_EMUNDUS_USER_REGENERATE_PASSWORD_SUCCESS');

//Export PDF
JText::script('COM_EMUNDUS_EXPORTS_PDF_GENERATION');
JText::script('COM_EMUNDUS_EXPORTS_CREATE_PDF');
JText::script('COM_EMUNDUS_EXPORTS_ADD_FILES_TO_PDF');
JText::script('COM_EMUNDUS_EXPORT_FINISHED');
JText::script('COM_EMUNDUS_ERROR_EXPORTS_NBFILES_CAPACITY');
JText::script('COM_EMUNDUS_ERROR_CAPACITY_PDF');
JText::script('COM_EMUNDUS_EXPORTS_DECISION_PDF');
JText::script('COM_EMUNDUS_EXPORTS_ADMISSION_PDF');
JText::script('COM_EMUNDUS_EXPORTS_GENERATE_PDF');
JText::script('COM_EMUNDUS_EXPORTS_PDF_OPTIONS');
JText::script('FILES_UPLOADED');
JText::script('COM_EMUNDUS_TAGS');
JText::script('COM_EMUNDUS_EXPORTS_FILE_NOT_FOUND');
JText::script('COM_EMUNDUS_EXPORTS_FILE_NOT_DEFINED');
JText::script('ID_CANDIDAT');
JText::script('FNUM');
JText::script('COM_EMUNDUS_APPLICATION_SENT_ON');
JText::script('DOCUMENT_PRINTED_ON');
JText::script('COM_EMUNDUS_USERS_ARE_YOU_SURE_TO_DELETE_USERS');
JText::script('COM_EMUNDUS_USERS_EDIT_PROFILE_NO_FORM_FOUND');

// Submit application
JText::script('COM_EMUNDUS_CONGRATULATIONS');
JText::script('COM_EMUNDUS_YOUR_FILE_HAS_BEEN_SENT');

//Export ZIP
JText::script('COM_EMUNDUS_EXPORTS_ZIP_GENERATION');
JText::script('COM_EMUNDUS_EXPORTS_CREATE_ZIP');

//WHO'S WHO
JText::script('COM_EMUNDUS_TROMBI_GENERATE');
JText::script('COM_EMUNDUS_TROMBI_DOWNLOAD');

// Email to applicant
JText::script('COM_EMUNDUS_EMAILS_SEND_CUSTOM_EMAIL');
JText::script('COM_EMUNDUS_EMAILS_ERROR_GETTING_PREVIEW');
JText::script('COM_EMUNDUS_EMAILS_EMAIL_PREVIEW');
JText::script('COM_EMUNDUS_EMAILS_EMAIL_PREVIEW_BEFORE_SEND');
JText::script('COM_EMUNDUS_EMAILS_NO_EMAILS_SENT');
JText::script('COM_EMUNDUS_EMAILS_EMAILS_SENT');
JText::script('COM_EMUNDUS_EMAILS_FAILED');
JText::script('COM_EMUNDUS_EMAILS_SEND_FAILED');
JText::script('COM_EMUNDUS_MAILS_SEND_TO');
JText::script('COM_EMUNDUS_MAILS_EMAIL_SENDING');
JText::script('COM_EMUNDUS_EMAILS_CANCEL_EMAIL');

//view application layout share
JText::script('COM_EMUNDUS_ACCESS_ARE_YOU_SURE_YOU_WANT_TO_REMOVE_THIS_ACCESS');


//view ametys
JText::script('COM_EMUNDUS_CANNOT_RETRIEVE_EMUNDUS_PROGRAMME_LIST');
JText::script('COM_EMUNDUS_RETRIEVE_AMETYS_STORED_PROGRAMMES');
JText::script('COM_EMUNDUS_RETRIEVE_EMUNDUS_STORED_PROGRAMMES');
JText::script('COM_EMUNDUS_COMPARE_DATA');
JText::script('COM_EMUNDUS_ADD_DATA');
JText::script('COM_EMUNDUS_SYNC_DONE');
JText::script('COM_EMUNDUS_NO_SYNC_NEEDED');
JText::script('COM_EMUNDUS_CANNOT_RETRIEVE_EMUNDUS_PROGRAMME_LIST');
JText::script('COM_EMUNDUS_DATA_TO_ADD');
JText::script('COM_EMUNDUS_ERROR_MISSING_FORM_DATA');

JText::script('CONFIRM_PASSWORD');

JText::script('JGLOBAL_SELECT_AN_OPTION');

//Award list
JText::script('COM_EMUNDUS_VOTE_NON_ACCEPTED');
JText::script('COM_EMUNDUS_VOTE_ACCEPTED');


//Messenger
JText::script('COM_EMUNDUS_MESSENGER_TITLE');
JText::script('COM_EMUNDUS_MESSENGER_SEND_DOCUMENT');
JText::script('COM_EMUNDUS_MESSENGER_ASK_DOCUMENT');
JText::script('COM_EMUNDUS_MESSENGER_DROP_HERE');
JText::script('COM_EMUNDUS_PLEASE_SELECT');
JText::script('COM_EMUNDUS_MESSENGER_SEND');
JText::script('COM_EMUNDUS_MESSENGER_WRITE_MESSAGE');
JText::script('COM_EMUNDUS_MESSENGER_TYPE_ATTACHMENT');

// GENERATE LETTER
JText::script('COM_EMUNDUS_EXPORT_MODE');
JText::script('COM_EMUNDUS_EXPORT_BY_CANDIDAT');
JText::script('COM_EMUNDUS_EXPORT_BY_DOCUMENT');
JText::script('COM_EMUNDUS_EXPORT_BY_FILES');
JText::script('COM_EMUNDUS_PDF_MERGE');
JText::script('COM_EMUNDUS_CANDIDAT_EXPORT_TOOLTIP');
JText::script('COM_EMUNDUS_DOCUMENT_EXPORT_TOOLTIP');
JText::script('COM_EMUNDUS_CANDIDAT_MERGE_TOOLTIP');
JText::script('COM_EMUNDUS_DOCUMENT_MERGE_TOOLTIP');
JText::script('COM_EMUNDUS_SELECT_IMPOSSIBLE');
JText::script('COM_EMUNDUS_MESSENGER_ATTACHMENTS');
JText::script('GENERATE_DOCUMENT');
JText::script('DOWNLOAD_DOCUMENT');
JText::script('NO_LETTER_FOUND');
JText::script('AFFECTED_CANDIDATS');
JText::script('GENERATED_DOCUMENTS_LABEL');
JText::script('GENERATED_DOCUMENTS_COUNT');
JText::script('CANDIDAT_GENERATED');
JText::script('DOCUMENT_GENERATED');
JText::script('CANDIDATE');
JText::script('DOCUMENT_NAME');
JText::script('CANDIDAT_INFORMATION');
JText::script('CANDIDAT_STATUS');
JText::script('EMAIL_SUBJECT');
JText::script('EMAIL_BODY');
JText::script('ATTACHMENT_LETTER');
JText::script('MESSAGE_INFORMATION');
JText::script('EMAIL_FAILED');
JText::script('CAMPAIGN_YEAR');
JText::script('COM_EMUNDUS_CAMPAIGN_UNSAVED_CHANGES');
JText::script('CANDIDATE_EMAIL');
JText::script('EMAIL_TAGS');
JText::script('SEND_EMAIL_TOOLTIPS');
JText::script('COM_EMUNDUS_UNAVAILABLE_FEATURES');
JText::script('COM_EMUNDUS_EMAILS_SENDING_EMAILS');
JText::script('COM_EMUNDUS_AURION_EXPORT');
JText::script('EXPORT_CHANGE_STATUS');
JText::script('EXPORT_SET_TAG');
JText::script('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS');
JText::script('EVALUATION_PERIOD_NOT_STARTED');
JText::script('EVALUATION_PERIOD_PASSED');


// EXPORT EXCEL MODEL
JText::script('COM_EMUNDUS_CHOOSE_LETTER');
JText::script('COM_EMUNDUS_MODEL_ERR');

// UPLOADED IMAGE IS TOO SMALL
JText::script('COM_EMUNDUS_ERROR_IMAGE_TOO_SMALL');

JText::script('COM_EMUNDUS_EMAILS_CC_PLACEHOLDER');
JText::script('COM_EMUNDUS_EMAILS_BCC_PLACEHOLDER');

// VUE ATTACHMENT
JText::script('SEARCH');
JText::script('COM_EMUNDUS_ATTACHMENTS_FILE_NAME');
JText::script('COM_EMUNDUS_ATTACHMENTS_DESCRIPTION');
JText::script('STATUS');
JText::script('COM_EMUNDUS_ATTACHMENTS_REPLACE');
JText::script('EXPORT');
JText::script('DELETE_SELECTED_ATTACHMENTS');
JText::script('CONFIRM_DELETE_SELETED_ATTACHMENTS');
JText::script('SELECT_CATEGORY');
JText::script('APPLICATION_FORM');
JText::script('UPLOAD_BY_APPLICANT');
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
JText::script('COM_EMUNDUS_ATTACHMENTS_WAITING');
JText::script('COM_EMUNDUS_ATTACHMENTS_REFRESH_TITLE');
JText::script('COM_EMUNDUS_ATTACHMENTS_DELETE_TITLE');
JText::script('COM_EMUNDUS_ATTACHMENTS_CLOSE');
JText::script('COM_EMUNDUS_ATTACHMENTS_USER_NOT_FOUND');
JText::script('COM_EMUNDUS_ATTACHMENTS_UPLOADED_BY');
JText::script('COM_EMUNDUS_ATTACHMENTS_CHECK');
JText::script('COM_EMUNDUS_ATTACHMENTS_WARNING');
JText::script('COM_EMUNDUS_ATTACHMENTS_PERMISSIONS');
JText::script('COM_EMUNDUS_ATTACHMENTS_CAN_BE_VIEWED');
JText::script('COM_EMUNDUS_ATTACHMENTS_CAN_BE_DELETED');
JText::script('COM_EMUNDUS_ATTACHMENTS_UNAUTHORIZED_ACTION');
JText::script('COM_EMUNDUS_ATTACHMENTS_PERMISSION_VIEW');
JText::script('COM_EMUNDUS_ATTACHMENTS_PERMISSION_DELETE');
JText::script('COM_EMUNDUS_ATTACHMENTS_COMPLETED');
JText::script('COM_EMUNDUS_ATTACHMENTS_SYNC');
JText::script('COM_EMUNDUS_ATTACHMENTS_SYNC_TITLE');
JText::script('COM_EMUNDUS_ATTACHMENTS_SYNC_WRITE');
JText::script('COM_EMUNDUS_ATTACHMENTS_SYNC_READ');
JText::script('COM_EMUNDUS_ONBOARD_DOCUMENTS');
JText::script('COM_EMUNDUS_ATTACHMENTS_NAME');
JText::script('COM_EMUNDUS_ATTACHMENTS_DESCRIPTION');
JText::script('COM_EMUNDUS_ATTACHMENTS_OPEN_IN_GED');
JText::script('COM_EMUNDUS_ATTACHMENTS_EXPORT_LINK');
JText::script('COM_EMUNDUS_ATTACHMENTS_SELECT_CATEGORY');
JText::script('COM_EMUNDUS_EMAILS_SELECT_CATEGORY');
JText::script('COM_EMUNDUS_EXPORTS_EXPORT');
JText::script('COM_EMUNDUS_EXPORTS_EXPORT_TO_ZIP');
JText::script('COM_EMUNDUS_ACTIONS_SEARCH');
JText::script('COM_EMUNDUS_TROMBINOSCOPE');

JText::script('COM_EMUNDUS_VIEW_FORM_SELECT_PROFILE');
JText::script('COM_EMUNDUS_VIEW_FORM_OTHER_PROFILES');
JText::script('COM_EMUNDUS_FILES_ARE_EDITED_BY_OTHER_USERS');
JText::script('COM_EMUNDUS_FILES_IS_EDITED_BY_OTHER_USER');
JText::script('COM_EMUNDUS_FILE_EDITED_BY_ANOTHER_USER');
JText::script('COM_EMUNDUS_LIST_RETRIEVED');
JText::script('COM_EMUNDUS_ERROR_CANNOT_RETRIEVE_LIST');

// GOTENBERG EXPORT FAILED
JText::script('COM_EMUNDUS_EXPORT_FAILED');

// LOGS
JText::script('COM_EMUNDUS_LOGS_DOWNLOAD');
JText::script('COM_EMUNDUS_LOGS_DOWNLOAD_ERROR');
JText::script('COM_EMUNDUS_LOGS_EXPORT');

JText::script('COM_EMUNDUS_CRUD_FILTER_LABEL');
JText::script('COM_EMUNDUS_LOG_READ_TYPE');
JText::script('COM_EMUNDUS_LOG_CREATE_TYPE');
JText::script('COM_EMUNDUS_LOG_UPDATE_TYPE');
JText::script('COM_EMUNDUS_LOG_DELETE_TYPE');
JText::script('COM_EMUNDUS_NO_ACTION_FOUND');
JText::script('COM_EMUNDUS_NO_LOG_USERS_FOUND');
JText::script('COM_EMUNDUS_NO_LOGS_FILTER_FOUND');

JText::script('COM_EMUNDUS_CRUD_FILTER_PLACEHOLDER');
JText::script('COM_EMUNDUS_TYPE_FILTER_PLACEHOLDER');
JText::script('COM_EMUNDUS_ACTOR_FILTER_PLACEHOLDER');
JText::script('COM_EMUNDUS_ACCESS_FORM_READ');
JText::script('COM_EMUNDUS_LOGS_FILTERS_FOUND_RESULTS');

JText::script('COM_EMUNDUS_CRUD_LOG_FILTER_HINT');
JText::script('COM_EMUNDUS_TYPES_LOG_FILTER_HINT');
JText::script('COM_EMUNDUS_ACTOR_LOG_FILTER_HINT');

JText::script('COM_EMUNDUS_NO_LOGS_FILTERS_FOUND_RESULTS');

// ADD LABEL OF LOGS CATEGORY
JText::script('COM_EMUNDUS_ACCESS_FILE');                   # 1
JText::script('COM_EMUNDUS_ACCESS_ATTACHMENT');             # 4
JText::script('COM_EMUNDUS_ACCESS_EVALUATION');             # 5
JText::script('COM_EMUNDUS_ACCESS_EXPORT_EXCEL');           # 6
JText::script('COM_EMUNDUS_ACCESS_EXPORT_ZIP');             # 7
JText::script('COM_EMUNDUS_ACCESS_EXPORT_PDF');             # 8
JText::script('COM_EMUNDUS_ACCESS_MAIL_APPLICANT');         # 9
JText::script('COM_EMUNDUS_ACCESS_COMMENT_FILE');           # 10
JText::script('COM_EMUNDUS_ACCESS_ACCESS_FILE');            # 11
JText::script('COM_EMUNDUS_ACCESS_ACCESS_FILE_CREATE');     # 11
JText::script('COM_EMUNDUS_ACCESS_USER');                   # 12
JText::script('COM_EMUNDUS_ACCESS_STATUS');                 # 13
JText::script('COM_EMUNDUS_ACCESS_TAGS');                   # 14
JText::script('COM_EMUNDUS_ACCESS_MAIL_EVALUATOR');         # 15
JText::script('COM_EMUNDUS_ACCESS_MAIL_GROUP');             # 16
JText::script('COM_EMUNDUS_ACCESS_MAIL_EXPERT');            # 18
JText::script('COM_EMUNDUS_ACCESS_GROUPS');                 # 19
JText::script('COM_EMUNDUS_ADD_USER');                      # 20
JText::script('COM_EMUNDUS_ACTIVATE');                      # 21
JText::script('COM_EMUNDUS_DEACTIVATE');                    # 22
JText::script('COM_EMUNDUS_AFFECT');                        # 23
JText::script('COM_EMUNDUS_EDIT_USER');                     # 24
JText::script('COM_EMUNDUS_SHOW_RIGHT');                    # 25
JText::script('COM_EMUNDUS_DELETE_USER');                   # 26
JText::script('COM_EMUNDUS_ACCESS_LETTERS');                # 27
JText::script('COM_EMUNDUS_PUBLISH');                       # 28
JText::script('COM_EMUNDUS_DECISION');                      # 29
JText::script('COM_EMUNDUS_COPY_FILE');                     # 30
JText::script('COM_EMUNDUS_ACCESS_MULTI_LETTERS');          # 31
JText::script('COM_EMUNDUS_ADMISSION');                     # 32
JText::script('COM_EMUNDUS_EXTENAL_EXPORT');                # 33
JText::script('COM_EMUNDUS_INTERVIEW');                     # 34
JText::script('COM_EMUNDUS_FICHE_DE_SYNTHESE');             # 35
JText::script('COM_EMUNDUS_MESSENGER');                     # 36
JText::script('COM_EMUNDUS_ACCESS_LOGS');                   # 37

JText::script('COM_EMUNDUS_EDIT_COMMENT_BODY');
JText::script('COM_EMUNDUS_EDIT_COMMENT_TITLE');
JText::script('COM_EMUNDUS_FORM_BUILDER_DELETE_MODEL');
JText::script('COM_EMUNDUS_FORM_PAGE_MODELS');
JText::script('COM_EMUNDUS_FORM_MY_FORMS');

// PASSWORD CHARACTER VALIDATION
JText::script('COM_EMUNDUS_PASSWORD_WRONG_FORMAT_TITLE');
JText::script('COM_EMUNDUS_PASSWORD_WRONG_FORMAT_DESCRIPTION');

// DELETE ADVANCED FILTERS
JText::script('COM_EMUNDUS_DELETE_ADVANCED_FILTERS');

JText::script('COM_EMUNDUS_MAIL_GB_BUTTON');


// ONBOARD

$app = JFactory::getApplication();

// Require specific controller if requested
if ($controller = $app->input->get('controller', '', 'WORD')) {
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) {
        require_once $path;
    } else {
        $controller = '';
    }
}

// Create the controller
$classname    = 'EmundusController'.$controller;
$controller   = new $classname();

$user = JFactory::getUser();
$secret = JFactory::getConfig()->get('secret');
$webhook_token = JFactory::getConfig()->get('webhook_token') ?: '';

$name = $app->input->get('view', '', 'CMD');
$task = $app->input->get('task', '', 'CMD');
$format = $app->input->get('format', '', 'CMD');
$token = $app->input->get('token', '', 'ALNUM');

$xmlDoc = new DOMDocument();
$release_version = '1.0.0';
if ($xmlDoc->load(JPATH_SITE.'/administrator/components/com_emundus/emundus.xml')) {
    $release_version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
}

if(!in_array($name,['settings','campaigns','emails','form'])) {
    JHTML::script("//cdnjs.cloudflare.com/ajax/libs/tinymce/4.4.1/tinymce.min.js");
    JHtml::script('media/com_emundus/lib/jquery-1.12.4.min.js');
    JHtml::script('media/com_emundus/lib/jquery-ui-1.12.1.min.js');
    JHtml::script('media/com_emundus/lib/bootstrap-emundus/js/bootstrap.min.js');
    //TODO : Stop use chosen replace by an other js native library
    //JHtml::script('media/com_emundus/lib/chosen/chosen.jquery.min.js' );
    JHtml::script('media/jui/js/chosen.jquery.min.js');
    JFactory::getDocument()->addScript('media/com_emundus/js/em_files.js?' . $release_version);
    JFactory::getDocument()->addScript('media/com_emundus/js/mixins/exports.js?' . $release_version);
    JFactory::getDocument()->addScript('media/com_emundus/js/mixins/utilities.js?' . $release_version);
    JHTML::script('libraries/emundus/selectize/dist/js/standalone/selectize.js' );
    JHTML::script('libraries/emundus/sumoselect/jquery.sumoselect.min.js');

    JHtml::styleSheet('media/com_emundus/css/reset.css');
    JHtml::styleSheet('media/com_emundus/lib/Semantic-UI-CSS-master/semantic.min.css' );
    //JHtml::styleSheet('media/com_emundus/lib/chosen/chosen.min.css');
    JHtml::styleSheet('media/jui/css/chosen.css');
    JHtml::styleSheet('media/com_emundus/lib/bootstrap-emundus/css/bootstrap.min.css');
    JHtml::styleSheet('media/com_emundus/css/emundus_files.css');
    JHTML::stylesheet('libraries/emundus/selectize/dist/css/normalize.css' );
    JHTML::stylesheet('libraries/emundus/selectize/dist/css/selectize.default.css' );
    JHTML::stylesheet('libraries/emundus/sumoselect/sumoselect.css');
}
JHTML::script('media/com_emundus_vue/chunk-vendors_emundus.js');

JHtml::styleSheet('media/com_emundus_vue/app_emundus.css');
JHTML::styleSheet('https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined');

/** QUILL */
JHTML::script('https://cdn.quilljs.com/1.3.6/quill.min.js');
JHtml::script('components/com_emundus/src/assets/js/quill/image-resize.min.js');
JHtml::styleSheet('components/com_emundus/src/assets/js/quill/quill-mention/quill.mention.min.css');
JHtml::script('components/com_emundus/src/assets/js/quill/quill-mention/quill.mention.min.js');

// The task 'getproductpdf' can be executed as public (when not signed in and form any view).
if ($task == 'getproductpdf') {
    $controller->execute($task);
}

if ($user->authorise('core.viewjob', 'com_emundus') && ($name == 'jobs' || $name == 'job' || $name == 'thesiss' || $name == 'thesis'))
{
    $controller->execute($task);
}
elseif ($user->guest && (($name === 'webhook' || $app->input->get('controller', '', 'WORD') === 'webhook') && $format === 'raw') && ($secret === $token || $webhook_token == JApplicationHelper::getHash($token)))
{
    $controller->execute($task);
}
elseif ($user->guest && $name != 'emailalert' && $name !='programme' && $name != 'search_engine' && $name != 'ccirs' && ($name != 'campaign' && $json != 'json') && $task != 'passrequest' && $task != 'getusername')
{
    $controller->setRedirect('index.php', JText::_("ACCESS_DENIED"), 'error');
}
else
{
    if ($name != 'search_engine') {
       // Perform the Request task
       $controller->execute($task);
    }
}
// Redirect if set by the controller
$controller->redirect();
?>
