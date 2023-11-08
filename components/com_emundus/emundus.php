<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('ACCESS_DENIED');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$app = Factory::getApplication();

// Require the base controller
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'controller.php');

// Helpers
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'javascript.php');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'files.php');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'filters.php');

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

// Translations for Javascript
Text::script('PLEASE_SELECT');
Text::script('IN');
Text::script('ALL');
Text::script('USERNAME');
Text::script('EMAIL');
Text::script('APPLICATION_CREATION_DATE');
Text::script('CAMPAIGN_ID');
Text::script('SEND_ON');
Text::script('COM_EMUNDUS_ONBOARD_ERROR_MESSAGE');
Text::script('COM_EMUNDUS_ONBOARD_OK');
Text::script('COM_EMUNDUS_ONBOARD_CANCEL');

Text::script('COM_EMUNDUS_EX');
Text::script('COM_EMUNDUS_ADD');
Text::script('COM_EMUNDUS_THESIS_DELETE');
Text::script('COM_EMUNDUS_APPLICATION_TAG');
Text::script('COM_EMUNDUS_ACCESS_FILE');
Text::script('COM_EMUNDUS_ACCESS_ATTACHMENT');
Text::script('COM_EMUNDUS_ACCESS_TAGS');
Text::script('COM_EMUNDUS_ACCESS_STATUS');
Text::script('COM_EMUNDUS_ACCESS_USER');
Text::script('COM_EMUNDUS_ACCESS_EVALUATION');
Text::script('COM_EMUNDUS_ACCESS_EXPORT_EXCEL');
Text::script('COM_EMUNDUS_ACCESS_EXPORT_ZIP');
Text::script('COM_EMUNDUS_ACCESS_EXPORT_PDF');
Text::script('COM_EMUNDUS_EXPORTS_EXPORT_AS_CSV_TEMPLATE');
Text::script('COM_EMUNDUS_ACCESS_MAIL_APPLICANT');
Text::script('COM_EMUNDUS_ACCESS_MAIL_EVALUATOR');
Text::script('COM_EMUNDUS_ACCESS_MAIL_GROUP');
Text::script('COM_EMUNDUS_ACCESS_MAIL_EXPERTS');
Text::script('COM_EMUNDUS_ACCESS_MAIL_ADDRESS');
Text::script('COM_EMUNDUS_ACCESS_COMMENT_FILE');
Text::script('COM_EMUNDUS_ACCESS_ACCESS_FILE');
Text::script('COM_EMUNDUS_CONFIRM_DELETE_FILE');
Text::script('COM_EMUNDUS_SHOW_ELEMENTS');
Text::script('COM_EMUNDUS_CHOOSE_PRG');
Text::script('COM_EMUNDUS_CHOOSE_CAMP');
Text::script('COM_EMUNDUS_CHOOSE_PRG_DEFAULT');
Text::script('COM_EMUNDUS_CHOOSE_FORM_ELEM');
Text::script('COM_EMUNDUS_CHOOSE_EVAL_FORM_ELEM');
Text::script('COM_EMUNDUS_CHOOSEN_FORM_ELEM');
Text::script('COM_EMUNDUS_CHOOSE_ADMISSION_FORM_ELEM');
Text::script('COM_EMUNDUS_CHOOSEN_ADMISSION_FORM_ELEM');
Text::script('COM_EMUNDUS_CHOOSE_OTHER_ADMISSION_ELTS');
Text::script('COM_EMUNDUS_CHOOSE_DECISION_FORM_ELEM');
Text::script('COM_EMUNDUS_CHOOSEN_DECISION_FORM_ELEM');
Text::script('COM_EMUNDUS_CHOOSE_OTHER_COL');
Text::script('COM_EMUNDUS_PHOTO');
Text::script('COM_EMUNDUS_FORMS');
Text::script('COM_EMUNDUS_ATTACHMENT');
Text::script('COM_EMUNDUS_ASSESSMENT');
Text::script('COM_EMUNDUS_COMMENT');
Text::script('COM_EMUNDUS_COMMENTS');
Text::script('COM_EMUNDUS_ACCESS_COMMENT_FILE_CREATE');
Text::script('COM_EMUNDUS_EXCEL_GENERATION');
Text::script('COM_EMUNDUS_CHOOSE_EXTRACTION_METHODE');
Text::script('COM_EMUNDUS_CHOOSE_EXTRACTION_METHODE_AGGREGATE_DISTINCT');
Text::script('COM_EMUNDUS_CHOOSE_EXTRACTION_METHODE_AGGREGATE');
Text::script('COM_EMUNDUS_CHOOSE_EXTRACTION_METHODE_LEFTJOIN');
Text::script('COM_EMUNDUS_DOWNLOAD_EXTRACTION');
Text::script('COM_EMUNDUS_EXPORTS_ZIP_GENERATION');
Text::script('COM_EMUNDUS_DOWNLOAD_ZIP');
Text::script('COM_EMUNDUS_PUBLISH');
Text::script('COM_EMUNDUS_COPY_FILE');
Text::script('COM_EMUNDUS_SHARE_FILE');
Text::script('COM_EMUNDUS_FILTERS_PLEASE_SELECT_FILTER');
Text::script('DELETE');
Text::script('COM_EMUNDUS_ACTIONS_DELETE');
Text::script('COM_EMUNDUS_FILTERS_FILTER_SAVED');
Text::script('COM_EMUNDUS_FILTERS_FILTER_DELETED');
Text::script('COM_EMUNDUS_ERROR_SQL_ERROR');
Text::script('COM_EMUNDUS_FORM_TITLE');
Text::script('COM_EMUNDUS_FORM_GROUP');
Text::script('COM_EMUNDUS_TO_UPPER_CASE');
Text::script('COM_EMUNDUS_ASSOCIATED_GROUPS');
Text::script('COM_EMUNDUS_ASSOCIATED_USERS');
Text::script('COM_EMUNDUS_EVALUATIONS_OVERALL');
Text::script('COM_EMUNDUS_CHOOSE_EXTRACTION_OPTION');
Text::script('COM_EMUNDUS_CHOOSE_OTHER_OPTION');
Text::script('COM_EMUNDUS_EXPORTS_GENERATE_ZIP');
Text::script('COM_EMUNDUS_ACTIONS_CANCEL');
Text::script('COM_EMUNDUS_OK');
Text::script('COM_EMUNDUS_ACTIONS_BACK');
Text::script('COM_EMUNDUS_USERNAME');
Text::script('ID');
Text::script('COM_EMUNDUS_ACTIONS_ALL');
Text::script('COM_EMUNDUS_IN');
Text::script('COM_EMUNDUS_SELECT_HERE');
Text::script('SELECT_HERE');
Text::script('COM_EMUNDUS_FILTERS_CHECK_ALL_ALL');
Text::script('COM_EMUNDUS_FILES_SAVE_FILTER');
Text::script('COM_EMUNDUS_FILES_ENTER_HERE');
Text::script('COM_EMUNDUS_ONBOARD_BUILDER_CURRENCY_OPTIONS');
Text::script('COM_EMUNDUS_ONBOARD_TYPE_CURRENCY');
Text::script('COM_EMUNDUS_ONBOARD_BUILDER_CURRENCY_ALL_OPTIONS');
Text::script('COM_EMUNDUS_ONBOARD_BUILDER_CURRENCY_CURRENCY');
Text::script('COM_EMUNDUS_ONBOARD_BUILDER_CURRENCY_THOUSAND_SEPARATOR');
Text::script('COM_EMUNDUS_ONBOARD_BUILDER_CURRENCY_DECIMAL_SEPARATOR');
Text::script('COM_EMUNDUS_ONBOARD_BUILDER_CURRENCY_DECIMAL_NUMBERS');
Text::script('COM_EMUNDUS_ONBOARD_BUILDER_CURRENCY_REGEX');

Text::script('USERNAME_Q');
Text::script('ID_Q');
Text::script('ALL_Q');
Text::script('LAST_NAME_Q');
Text::script('FIRST_NAME_Q');
Text::script('FNUM_Q');
Text::script('EMAIL_Q');

Text::script('BACK');

Text::script('COM_EMUNDUS_LOADING');
Text::script('TITLE');
Text::script('COM_EMUNDUS_COMMENTS_ADD_COMMENT');
Text::script('COM_EMUNDUS_COMMENTS_ERROR_PLEASE_COMPLETE');
Text::script('COM_EMUNDUS_COMMENTS_ENTER_COMMENT');
Text::script('COM_EMUNDUS_COMMENTS_SENT');
Text::script('COM_EMUNDUS_ACCESS_SHARE_PROGRESS');
Text::script('COM_EMUNDUS_ACCESS_SHARE_SUCCESS');
Text::script('COM_EMUNDUS_ACCESS_ERROR_REQUIRED');
Text::script('ERROR');
Text::script('COM_EMUNDUS_EXPORTS_DOWNLOAD_PDF');
Text::script('COM_EMUNDUS_EXPORTS_FORMS_PDF');
Text::script('COM_EMUNDUS_EXPORTS_ATTACHMENT_PDF');
Text::script('COM_EMUNDUS_EXPORTS_ASSESSMENT_PDF');
Text::script('JYES');
Text::script('JNO');
Text::script('COM_EMUNDUS_PLEASE_SELECT');
Text::script('COM_EMUNDUS_EXPORTS_CHANGE_STATUS');
Text::script('COM_EMUNDUS_EXPORTS_EXPORT_SET_TAG');
Text::script('COM_EMUNDUS_ATTACHMENTS_YOU_MUST_SELECT_ATTACHMENT');
Text::script('COM_EMUNDUS_ATTACHMENTS_AGGREGATIONS');
Text::script('COM_EMUNDUS_LETTERS_FILES_GENERATED');
Text::script('FILE_NAME');
Text::script('COM_EMUNDUS_ATTACHMENTS_LINK_TO_DOWNLOAD');
Text::script('LINK_TO_DOWNLOAD');
Text::script('COM_EMUNDUS_ATTACHMENTS_ALL_IN_ONE_DOC');
Text::script('COM_EMUNDUS_EXPORTS_PDF_TAGS');
Text::script('COM_EMUNDUS_EXPORTS_PDF_STATUS');
Text::script('COM_EMUNDUS_EXPORTS_ADD_HEADER');
Text::script('COM_EMUNDUS_TAGS_DELETE_TAGS');
Text::script('COM_EMUNDUS_TAGS_CATEGORIES');
Text::script('COM_EMUNDUS_TAGS_DELETE_TAGS_CONFIRM');
Text::script('COM_EMUNDUS_TAGS_DELETE_SUCCESS');
Text::script('COM_EMUNDUS_FILTERS_CONFIRM_DELETE_FILTER');
Text::script('COM_EMUNDUS_APPLICATION_ADD_TAGS');
Text::script('COM_EMUNDUS_FILES_PLEASE_SELECT_TAG');
Text::script('COM_EMUNDUS_SELECT_SOME_OPTIONS');
Text::script('COM_EMUNDUS_SELECT_AN_OPTION');
Text::script('COM_EMUNDUS_SELECT_NO_RESULT');
Text::script('VALID');
Text::script('INVALID');
Text::script('COM_EMUNDUS_ATTACHMENTS_UNCHECKED');
Text::script('COM_EMUNDUS_EXPORTS_SELECT_AT_LEAST_ONE_FILE');
Text::script('COM_EMUNDUS_EXPORTS_INFORMATION');
Text::script('COM_EMUNDUS_FILTERS_YOU_HAVE_SELECT');
Text::script('COM_EMUNDUS_FILTERS_SELECT_ALL');
Text::script('COM_EMUNDUS_FILES_FILE');
Text::script('COM_EMUNDUS_FILES_FILES');
Text::script('COM_EMUNDUS_FILES_SELECT_ALL_FILES');
Text::script('COM_EMUNDUS_USERS_SELECT_USER');
Text::script('COM_EMUNDUS_USERS_SELECT_USERS');
Text::script('COM_EMUNDUS_APPLICATION_WARNING_CHANGE_STATUS');
Text::script('COM_EMUNDUS_APPLICATION_MAIL_CHANGE_STATUT_INFO');
Text::script('COM_EMUNDUS_APPLICATION_VALIDATE_CHANGE_STATUT');
Text::script('COM_EMUNDUS_APPLICATION_CANCEL_CHANGE_STATUT');
Text::script('COM_EMUNDUS_APPLICATION_DOCUMENT_PRINTED_ON');
Text::script('COM_EMUNDUS_APPLICATION_APPLICANT');
Text::script('COM_EMUNDUS_CHECKLIST_PROFILE_FILES_FOUND');
Text::script('COM_EMUNDUS_CHECKLIST_PROFILE_FILES_FOUND_TEXT');
Text::script('COM_EMUNDUS_CHECKLIST_PROFILE_FILES_FOUND_TEXT_2');
Text::script('COM_EMUNDUS_CHECKLIST_PROFILE_FILES_UPLOAD');
Text::script('COM_EMUNDUS_CHECKLIST_PROFILE_ATTACHMENT_FOUND');
Text::script('COM_EMUNDUS_CHECKLIST_PROFILE_ATTACHMENT_FOUND_TEXT');
Text::script('COM_EMUNDUS_CHECKLIST_PROFILE_ATTACHMENT_FOUND_UPDATE');
Text::script('COM_EMUNDUS_CHECKLIST_PROFILE_ATTACHMENT_FOUND_CONTINUE_WITHOUT_UPDATE');
Text::script('COM_EMUNDUS_USERS_MY_DOCUMENTS_LOAD');
Text::script('COM_EMUNDUS_ACCOUNT_INFORMATIONS');
Text::script('COM_EMUNDUS_ACCOUNT_PERSONAL_DETAILS');
Text::script('COM_EMUNDUS_USERS_DEFAULT_LANGAGE');
Text::script('COM_EMUNDUS_USERS_NATIONALITY');
Text::script('COM_EMUNDUS_USERS_EDIT_PROFILE_PASSWORD');
Text::script('COM_EMUNDUS_PUBLISH_UPDATE');
Text::script('COM_EMUNDUS_FILES_FILTER');
Text::script('COM_EMUNDUS_FILES_APPLY_FILTER');

// view user
Text::script('COM_EMUNDUS_USERS_ERROR_NOT_A_VALID_EMAIL');
Text::script('COM_EMUNDUS_USERS_ERROR_NOT_A_VALID_LOGIN_MUST_NOT_CONTAIN_SPECIAL_CHARACTER');
Text::script('REQUIRED');
Text::script('COM_EMUNDUS_SELECT_A_VALUE');
Text::script('GROUP_CREATED');
Text::script('COM_EMUNDUS_USERS_USER_CREATED');
Text::script('LOGIN_NOT_GOOD');
Text::script('MAIL_NOT_GOOD');
Text::script('COM_EMUNDUS_USERS_ARE_YOU_SURE_TO_DELETE_USERS');
Text::script('COM_EMUNDUS_USERS_DELETED');
Text::script('COM_EMUNDUS_ACCESS_SHARE_PROGRESS');
Text::script('COM_EMUNDUS_APPLICATION_SENT');
Text::script('COM_EMUNDUS_LETTERS_FILES_GENERATED');
Text::script('COM_EMUNDUS_STATE');
Text::script('COM_EMUNDUS_PROFILE_SWITCH_PROFILE');
Text::script('COM_EMUNDUS_PROFILE_PROFILE_CHOSEN');
Text::script('COM_EMUNDUS_USERS_ARE_YOU_SURE_TO_REGENERATE_PASSWORD');

//Export Excel
Text::script('COM_EMUNDUS_ADD_DATA_TO_CSV');
Text::script('COM_EMUNDUS_LIMIT_POST_SERVER');
Text::script('COM_EMUNDUS_ERROR_XLS');
Text::script('COM_EMUNDUS_ERROR_CSV_CAPACITY');
Text::script('COM_EMUNDUS_XLS_GENERATION');
Text::script('COM_EMUNDUS_EXPORT_FINISHED');
Text::script('COM_EMUNDUS_ERROR_CAPACITY_XLS');
Text::script('COM_EMUNDUS_CREATE_CSV');
Text::script('EXPECTED_GRADUATION_DATE');
Text::script('GRADE_POINT_AVERAGE');
Text::script('GRADUATION_DATE');
Text::script('TYPE_OF_DEGREE');
Text::script('INSTITUTION');
Text::script('FIELD');
Text::script('OTHER_INFORMATION');
Text::script('ADDRESS_FOR_CORRESPONDANCE');
Text::script('PERMANENT_ADDRESS_FOR_CORRESPONDANCE');
Text::script('CRIMINAL_DETAILS');
Text::script('CRIMINAL_CHARGES');
Text::script('PHYSICAL_DETAILS');
Text::script('PHYSICAL_DISABILITY');
Text::script('MOBILE');
Text::script('TELEPHONE');
Text::script('COUNTRY');
Text::script('COM_EMUNDUS_STATE');
Text::script('ZIPCODE');
Text::script('CITY');
Text::script('STREET');
Text::script('PERSONAL_DETAILS');
Text::script('NUMBER_OF_CHILDREN');
Text::script('ACCOMPANIED');
Text::script('DISABLED');
Text::script('COM_EMUNDUS_FORMS_NATIONALITY');
Text::script('BIRTH_PLACE');
Text::script('DATE_OF_BIRTH');
Text::script('MARITAL_STATUS');
Text::script('GENDER');
Text::script('MAIDEN_NAME');
Text::script('BACHELOR_DEGREE_ENGINEERING_DEGREE');
Text::script('OTHER_EDUCATION_OR_MASTER_DEGREE');
Text::script('LANGUAGE');
Text::script('MOTHER_TONGUE');
Text::script('DEGREE_LANGUAGE');
Text::script('ENGLISH');
Text::script('ENGLISH_READING');
Text::script('ENGLISH_SPEAKING');
Text::script('ENGLISH_WRITING');
Text::script('ENGLISH_TEST_SCORE');
Text::script('ENGLISH_TEST_NAME');
Text::script('OTHER_LANGUAGE');
Text::script('OTHER_LANGUAGE');
Text::script('OTHER_LANGUAGES');
Text::script('LANGUAGE_READING');
Text::script('LANGUAGE_WRITING');
Text::script('LANGUAGE_SPEAKING');
Text::script('OTHER_TEST_NAME');
Text::script('OTHER_TEST_SCORE');
Text::script('OTHER_TEST_DATE');
Text::script('INETRNSHIP');
Text::script('DURATION');
Text::script('COMPANY_OR_ACADEMIC_INSTITUTION');
Text::script('WORK_DESCRIPTION');
Text::script('FULL_TIME_OR_PART_TIME_ACTIVITY');
Text::script('PROFESSIONAL_EXPERIENCE');
Text::script('FIRST_REFEREE');
Text::script('SECOND_REFEREE');
Text::script('FIRST_NAME');
Text::script('LAST_NAME');
Text::script('UNIVERSITY_ORGANISATION');
Text::script('FAX_NUMBER');
Text::script('WEBSITE');
Text::script('COM_EMUNDUS_EMAIL');
Text::script('POSITION');
Text::script('APPLICATION_SCHOLARSHIP');
Text::script('ERASMUS_MUNDUS_SCHOLARSHIP');
Text::script('CATEGORY_B_DETAILS');
Text::script('FINANCIAL_INFORMATION');
Text::script('SOURCE_FUNDING');
Text::script('HOW_DID_YOU_LEARNED_ABOUT_THIS_MASTER');
Text::script('SELECT_ONE');
Text::script('FIRST_PREFERENCE');
Text::script('SECONDE_PREFERENCE');
Text::script('DID_YOU_APPLY_FOR_ANOTHER_PROGRAM');
Text::script('PROGRAM_NAME');
Text::script('CHOOSE_YOUR_OPTION');
Text::script('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS_OTHERS_EVAL');
Text::script('COM_EMUNDUS_EXPORTS_GENERATE_EXCEL');
Text::script('COM_EMUNDUS_USER_REGENERATE_PASSWORD_SUCCESS');

//Export PDF
Text::script('COM_EMUNDUS_EXPORTS_PDF_GENERATION');
Text::script('COM_EMUNDUS_EXPORTS_CREATE_PDF');
Text::script('COM_EMUNDUS_EXPORTS_ADD_FILES_TO_PDF');
Text::script('COM_EMUNDUS_EXPORT_FINISHED');
Text::script('COM_EMUNDUS_ERROR_EXPORTS_NBFILES_CAPACITY');
Text::script('COM_EMUNDUS_ERROR_CAPACITY_PDF');
Text::script('COM_EMUNDUS_EXPORTS_DECISION_PDF');
Text::script('COM_EMUNDUS_EXPORTS_ADMISSION_PDF');
Text::script('COM_EMUNDUS_EXPORTS_GENERATE_PDF');
Text::script('COM_EMUNDUS_EXPORTS_PDF_OPTIONS');
Text::script('FILES_UPLOADED');
Text::script('COM_EMUNDUS_TAGS');
Text::script('COM_EMUNDUS_EXPORTS_FILE_NOT_FOUND');
Text::script('COM_EMUNDUS_EXPORTS_FILE_NOT_DEFINED');
Text::script('ID_CANDIDAT');
Text::script('FNUM');
Text::script('COM_EMUNDUS_APPLICATION_SENT_ON');
Text::script('DOCUMENT_PRINTED_ON');
Text::script('COM_EMUNDUS_USERS_ARE_YOU_SURE_TO_DELETE_USERS');
Text::script('COM_EMUNDUS_USERS_EDIT_PROFILE_NO_FORM_FOUND');
Text::script('JCANCEL');
Text::script('JACTION_DELETE');
Text::script('COM_EMUNDUS_USERS_EDIT_PROFILE_NO_FORM_FOUND');
Text::script('COM_EMUNDUS_WANT_RESET_PASSWORD');

// Submit application
Text::script('COM_EMUNDUS_CONGRATULATIONS');
Text::script('COM_EMUNDUS_YOUR_FILE_HAS_BEEN_SENT');

//Export ZIP
Text::script('COM_EMUNDUS_EXPORTS_ZIP_GENERATION');
Text::script('COM_EMUNDUS_EXPORTS_CREATE_ZIP');

//WHO'S WHO
Text::script('COM_EMUNDUS_TROMBI_GENERATE');
Text::script('COM_EMUNDUS_TROMBI_DOWNLOAD');
Text::script('COM_EMUNDUS_TROMBINOSCOPE_GENERATE_FAILED');

// Email to applicant
Text::script('COM_EMUNDUS_EMAILS_SEND_CUSTOM_EMAIL');
Text::script('COM_EMUNDUS_EMAILS_ERROR_GETTING_PREVIEW');
Text::script('COM_EMUNDUS_EMAILS_EMAIL_PREVIEW');
Text::script('COM_EMUNDUS_EMAILS_EMAIL_PREVIEW_BEFORE_SEND');
Text::script('COM_EMUNDUS_EMAILS_NO_EMAILS_SENT');
Text::script('COM_EMUNDUS_EMAILS_EMAILS_SENT');
Text::script('COM_EMUNDUS_EMAILS_FAILED');
Text::script('COM_EMUNDUS_EMAILS_SEND_FAILED');
Text::script('COM_EMUNDUS_MAILS_SEND_TO');
Text::script('COM_EMUNDUS_MAILS_EMAIL_SENDING');
Text::script('COM_EMUNDUS_EMAILS_CANCEL_EMAIL');

//view application layout share
Text::script('COM_EMUNDUS_ACCESS_ARE_YOU_SURE_YOU_WANT_TO_REMOVE_THIS_ACCESS');

//view ametys
Text::script('COM_EMUNDUS_CANNOT_RETRIEVE_EMUNDUS_PROGRAMME_LIST');
Text::script('COM_EMUNDUS_RETRIEVE_AMETYS_STORED_PROGRAMMES');
Text::script('COM_EMUNDUS_RETRIEVE_EMUNDUS_STORED_PROGRAMMES');
Text::script('COM_EMUNDUS_COMPARE_DATA');
Text::script('COM_EMUNDUS_ADD_DATA');
Text::script('COM_EMUNDUS_SYNC_DONE');
Text::script('COM_EMUNDUS_NO_SYNC_NEEDED');
Text::script('COM_EMUNDUS_CANNOT_RETRIEVE_EMUNDUS_PROGRAMME_LIST');
Text::script('COM_EMUNDUS_DATA_TO_ADD');
Text::script('COM_EMUNDUS_ERROR_MISSING_FORM_DATA');

Text::script('CONFIRM_PASSWORD');

Text::script('JGLOBAL_SELECT_AN_OPTION');

//Award list
Text::script('COM_EMUNDUS_VOTE_NON_ACCEPTED');
Text::script('COM_EMUNDUS_VOTE_ACCEPTED');


//Messenger
Text::script('COM_EMUNDUS_MESSENGER_TITLE');
Text::script('COM_EMUNDUS_MESSENGER_SEND_DOCUMENT');
Text::script('COM_EMUNDUS_MESSENGER_ASK_DOCUMENT');
Text::script('COM_EMUNDUS_MESSENGER_DROP_HERE');
Text::script('COM_EMUNDUS_PLEASE_SELECT');
Text::script('COM_EMUNDUS_MESSENGER_SEND');
Text::script('COM_EMUNDUS_MESSENGER_WRITE_MESSAGE');
Text::script('COM_EMUNDUS_MESSENGER_TYPE_ATTACHMENT');

// GENERATE LETTER
Text::script('COM_EMUNDUS_EXPORT_MODE');
Text::script('COM_EMUNDUS_EXPORT_BY_CANDIDAT');
Text::script('COM_EMUNDUS_EXPORT_BY_DOCUMENT');
Text::script('COM_EMUNDUS_EXPORT_BY_FILES');
Text::script('COM_EMUNDUS_PDF_MERGE');
Text::script('COM_EMUNDUS_CANDIDAT_EXPORT_TOOLTIP');
Text::script('COM_EMUNDUS_DOCUMENT_EXPORT_TOOLTIP');
Text::script('COM_EMUNDUS_CANDIDAT_MERGE_TOOLTIP');
Text::script('COM_EMUNDUS_DOCUMENT_MERGE_TOOLTIP');
Text::script('COM_EMUNDUS_SELECT_IMPOSSIBLE');
Text::script('COM_EMUNDUS_MESSENGER_ATTACHMENTS');
Text::script('GENERATE_DOCUMENT');
Text::script('DOWNLOAD_DOCUMENT');
Text::script('NO_LETTER_FOUND');
Text::script('AFFECTED_CANDIDATS');
Text::script('GENERATED_DOCUMENTS_LABEL');
Text::script('GENERATED_DOCUMENTS_COUNT');
Text::script('CANDIDAT_GENERATED');
Text::script('DOCUMENT_GENERATED');
Text::script('CANDIDATE');
Text::script('DOCUMENT_NAME');
Text::script('CANDIDAT_INFORMATION');
Text::script('CANDIDAT_STATUS');
Text::script('EMAIL_SUBJECT');
Text::script('EMAIL_BODY');
Text::script('ATTACHMENT_LETTER');
Text::script('MESSAGE_INFORMATION');
Text::script('EMAIL_FAILED');
Text::script('CAMPAIGN_YEAR');
Text::script('COM_EMUNDUS_CAMPAIGN_UNSAVED_CHANGES');
Text::script('CANDIDATE_EMAIL');
Text::script('EMAIL_TAGS');
Text::script('SEND_EMAIL_TOOLTIPS');
Text::script('COM_EMUNDUS_UNAVAILABLE_FEATURES');
Text::script('COM_EMUNDUS_EMAILS_SENDING_EMAILS');
Text::script('COM_EMUNDUS_AURION_EXPORT');
Text::script('EXPORT_CHANGE_STATUS');
Text::script('EXPORT_SET_TAG');
Text::script('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS');
Text::script('EVALUATION_PERIOD_NOT_STARTED');
Text::script('EVALUATION_PERIOD_PASSED');


// EXPORT EXCEL MODEL
Text::script('COM_EMUNDUS_CHOOSE_LETTER');
Text::script('COM_EMUNDUS_MODEL_ERR');

// UPLOADED IMAGE IS TOO SMALL
Text::script('COM_EMUNDUS_ERROR_IMAGE_TOO_SMALL');

Text::script('COM_EMUNDUS_EMAILS_CC_PLACEHOLDER');
Text::script('COM_EMUNDUS_EMAILS_BCC_PLACEHOLDER');

// VUE ATTACHMENT
Text::script('SEARCH');
Text::script('COM_EMUNDUS_ATTACHMENTS_FILE_NAME');
Text::script('COM_EMUNDUS_ATTACHMENTS_DESCRIPTION');
Text::script('STATUS');
Text::script('COM_EMUNDUS_ATTACHMENTS_REPLACE');
Text::script('EXPORT');
Text::script('DELETE_SELECTED_ATTACHMENTS');
Text::script('CONFIRM_DELETE_SELETED_ATTACHMENTS');
Text::script('SELECT_CATEGORY');
Text::script('APPLICATION_FORM');
Text::script('UPLOAD_BY_APPLICANT');
Text::script('COM_EMUNDUS_ATTACHMENTS_SEND_DATE');
Text::script('COM_EMUNDUS_ATTACHMENTS_MODIFICATION_DATE');
Text::script('COM_EMUNDUS_ATTACHMENTS_MODIFIED_BY');
Text::script('COM_EMUNDUS_ATTACHMENTS_DOCUMENT_PREVIEW_INCOMPLETE_MSG');
Text::script('COM_EMUNDUS_ATTACHMENTS_DOCUMENT_TYPE');
Text::script('COM_EMUNDUS_ATTACHMENTS_MINI_DESCRIPTION');
Text::script('COM_EMUNDUS_ATTACHMENTS_CAMPAIGN_ID');
Text::script('COM_EMUNDUS_ATTACHMENTS_CATEGORY');
Text::script('COM_EMUNDUS_ATTACHMENTS_SAVE');
Text::script('COM_EMUNDUS_ATTACHMENTS_FILTER_ACTION');
Text::script('COM_EMUNDUS_ATTACHMENTS_REPLACE');
Text::script('COM_EMUNDUS_ATTACHMENTS_NO_ATTACHMENTS_FOUND');
Text::script('COM_EMUNDUS_ATTACHMENTS_WAITING');
Text::script('COM_EMUNDUS_ATTACHMENTS_REFRESH_TITLE');
Text::script('COM_EMUNDUS_ATTACHMENTS_DELETE_TITLE');
Text::script('COM_EMUNDUS_ATTACHMENTS_CLOSE');
Text::script('COM_EMUNDUS_ATTACHMENTS_USER_NOT_FOUND');
Text::script('COM_EMUNDUS_ATTACHMENTS_UPLOADED_BY');
Text::script('COM_EMUNDUS_ATTACHMENTS_CHECK');
Text::script('COM_EMUNDUS_ATTACHMENTS_WARNING');
Text::script('COM_EMUNDUS_ATTACHMENTS_PERMISSIONS');
Text::script('COM_EMUNDUS_ATTACHMENTS_CAN_BE_VIEWED');
Text::script('COM_EMUNDUS_ATTACHMENTS_CAN_BE_DELETED');
Text::script('COM_EMUNDUS_ATTACHMENTS_UNAUTHORIZED_ACTION');
Text::script('COM_EMUNDUS_ATTACHMENTS_PERMISSION_VIEW');
Text::script('COM_EMUNDUS_ATTACHMENTS_PERMISSION_DELETE');
Text::script('COM_EMUNDUS_ATTACHMENTS_COMPLETED');
Text::script('COM_EMUNDUS_ATTACHMENTS_SYNC');
Text::script('COM_EMUNDUS_ATTACHMENTS_SYNC_TITLE');
Text::script('COM_EMUNDUS_ATTACHMENTS_SYNC_WRITE');
Text::script('COM_EMUNDUS_ATTACHMENTS_SYNC_READ');
Text::script('COM_EMUNDUS_ONBOARD_DOCUMENTS');
Text::script('COM_EMUNDUS_ATTACHMENTS_NAME');
Text::script('COM_EMUNDUS_ATTACHMENTS_DESCRIPTION');
Text::script('COM_EMUNDUS_ATTACHMENTS_OPEN_IN_GED');
Text::script('COM_EMUNDUS_ATTACHMENTS_EXPORT_LINK');
Text::script('COM_EMUNDUS_ATTACHMENTS_SELECT_CATEGORY');
Text::script('COM_EMUNDUS_EMAILS_SELECT_CATEGORY');
Text::script('COM_EMUNDUS_EXPORTS_EXPORT');
Text::script('COM_EMUNDUS_EXPORTS_EXPORT_TO_ZIP');
Text::script('COM_EMUNDUS_ACTIONS_SEARCH');
Text::script('COM_EMUNDUS_TROMBINOSCOPE');

Text::script('COM_EMUNDUS_VIEW_FORM_SELECT_PROFILE');
Text::script('COM_EMUNDUS_VIEW_FORM_OTHER_PROFILES');
Text::script('COM_EMUNDUS_FILES_ARE_EDITED_BY_OTHER_USERS');
Text::script('COM_EMUNDUS_FILES_IS_EDITED_BY_OTHER_USER');
Text::script('COM_EMUNDUS_FILE_EDITED_BY_ANOTHER_USER');
Text::script('COM_EMUNDUS_LIST_RETRIEVED');
Text::script('COM_EMUNDUS_ERROR_CANNOT_RETRIEVE_LIST');

// GOTENBERG EXPORT FAILED
Text::script('COM_EMUNDUS_EXPORT_FAILED');

// LOGS
Text::script('COM_EMUNDUS_LOGS_DOWNLOAD');
Text::script('COM_EMUNDUS_LOGS_DOWNLOAD_ERROR');
Text::script('COM_EMUNDUS_LOGS_EXPORT');

Text::script('COM_EMUNDUS_CRUD_FILTER_LABEL');
Text::script('COM_EMUNDUS_LOG_READ_TYPE');
Text::script('COM_EMUNDUS_LOG_CREATE_TYPE');
Text::script('COM_EMUNDUS_LOG_UPDATE_TYPE');
Text::script('COM_EMUNDUS_LOG_DELETE_TYPE');
Text::script('COM_EMUNDUS_NO_ACTION_FOUND');
Text::script('COM_EMUNDUS_NO_LOG_USERS_FOUND');
Text::script('COM_EMUNDUS_NO_LOGS_FILTER_FOUND');

Text::script('COM_EMUNDUS_CRUD_FILTER_PLACEHOLDER');
Text::script('COM_EMUNDUS_TYPE_FILTER_PLACEHOLDER');
Text::script('COM_EMUNDUS_ACTOR_FILTER_PLACEHOLDER');
Text::script('COM_EMUNDUS_ACCESS_FORM_READ');
Text::script('COM_EMUNDUS_LOGS_FILTERS_FOUND_RESULTS');

Text::script('COM_EMUNDUS_CRUD_LOG_FILTER_HINT');
Text::script('COM_EMUNDUS_TYPES_LOG_FILTER_HINT');
Text::script('COM_EMUNDUS_ACTOR_LOG_FILTER_HINT');

Text::script('COM_EMUNDUS_NO_LOGS_FILTERS_FOUND_RESULTS');

// ADD LABEL OF LOGS CATEGORY
Text::script('COM_EMUNDUS_ACCESS_FILE');                   # 1
Text::script('COM_EMUNDUS_ACCESS_ATTACHMENT');             # 4
Text::script('COM_EMUNDUS_ACCESS_EVALUATION');             # 5
Text::script('COM_EMUNDUS_ACCESS_EXPORT_EXCEL');           # 6
Text::script('COM_EMUNDUS_ACCESS_EXPORT_ZIP');             # 7
Text::script('COM_EMUNDUS_ACCESS_EXPORT_PDF');             # 8
Text::script('COM_EMUNDUS_ACCESS_MAIL_APPLICANT');         # 9
Text::script('COM_EMUNDUS_ACCESS_COMMENT_FILE');           # 10
Text::script('COM_EMUNDUS_ACCESS_ACCESS_FILE');            # 11
Text::script('COM_EMUNDUS_ACCESS_ACCESS_FILE_CREATE');     # 11
Text::script('COM_EMUNDUS_ACCESS_USER');                   # 12
Text::script('COM_EMUNDUS_ACCESS_STATUS');                 # 13
Text::script('COM_EMUNDUS_ACCESS_TAGS');                   # 14
Text::script('COM_EMUNDUS_ACCESS_MAIL_EVALUATOR');         # 15
Text::script('COM_EMUNDUS_ACCESS_MAIL_GROUP');             # 16
Text::script('COM_EMUNDUS_ACCESS_MAIL_EXPERT');            # 18
Text::script('COM_EMUNDUS_ACCESS_GROUPS');                 # 19
Text::script('COM_EMUNDUS_ADD_USER');                      # 20
Text::script('COM_EMUNDUS_ACTIVATE');                      # 21
Text::script('COM_EMUNDUS_DEACTIVATE');                    # 22
Text::script('COM_EMUNDUS_AFFECT');                        # 23
Text::script('COM_EMUNDUS_EDIT_USER');                     # 24
Text::script('COM_EMUNDUS_SHOW_RIGHT');                    # 25
Text::script('COM_EMUNDUS_DELETE_USER');                   # 26
Text::script('COM_EMUNDUS_ACCESS_LETTERS');                # 27
Text::script('COM_EMUNDUS_PUBLISH');                       # 28
Text::script('COM_EMUNDUS_DECISION');                      # 29
Text::script('COM_EMUNDUS_COPY_FILE');                     # 30
Text::script('COM_EMUNDUS_ACCESS_MULTI_LETTERS');          # 31
Text::script('COM_EMUNDUS_ADMISSION');                     # 32
Text::script('COM_EMUNDUS_EXTENAL_EXPORT');                # 33
Text::script('COM_EMUNDUS_INTERVIEW');                     # 34
Text::script('COM_EMUNDUS_FICHE_DE_SYNTHESE');             # 35
Text::script('COM_EMUNDUS_MESSENGER');                     # 36
Text::script('COM_EMUNDUS_ACCESS_LOGS');                   # 37

Text::script('COM_EMUNDUS_EDIT_COMMENT_BODY');
Text::script('COM_EMUNDUS_EDIT_COMMENT_TITLE');
Text::script('COM_EMUNDUS_FORM_BUILDER_DELETE_MODEL');
Text::script('COM_EMUNDUS_FORM_PAGE_MODELS');
Text::script('COM_EMUNDUS_FORM_MY_FORMS');
Text::script('COM_EMUNDUS_ONBOARD_PROGRAM_ADDUSER');
Text::script('COM_EMUNDUS_ACTIONS_EDIT_USER');
Text::script('COM_EMUNDUS_USERS_ERROR_PLEASE_COMPLETE');
Text::script('COM_EMUNDUS_USERS_SHOW_USER_RIGHTS');
Text::script('COM_EMUNDUS_MAILS_SEND_EMAIL');
Text::script('COM_EMUNDUS_USERS_CREATE_GROUP');
Text::script('COM_EMUNDUS_USERS_AFFECT_USER');
Text::script('COM_EMUNDUS_USERS_AFFECT_GROUP_ERROR');
Text::script('COM_EMUNDUS_ERROR_OCCURED');
Text::script('COM_EMUNDUS_USERS_CREATE_USER_CONFIRM');
Text::script('COM_EMUNDUS_USERS_EDIT_USER_CONFIRM');
Text::script('COM_EMUNDUS_USERS_AFFECT_USER_CONFIRM');
Text::script('COM_EMUNDUS_MAIL_SEND_NEW');

// PASSWORD CHARACTER VALIDATION
Text::script('COM_EMUNDUS_PASSWORD_WRONG_FORMAT_TITLE');
Text::script('COM_EMUNDUS_PASSWORD_WRONG_FORMAT_DESCRIPTION');

// DELETE ADVANCED FILTERS
Text::script('COM_EMUNDUS_DELETE_ADVANCED_FILTERS');

Text::script('COM_EMUNDUS_MAIL_GB_BUTTON');

// Require specific controller if requested
if ($controller = $app->input->get('controller', '', 'WORD')) {
	$path = JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'controllers' . DS . $controller . '.php';
	if (file_exists($path)) {
		require_once $path;
	}
	else {
		$controller = '';
	}
}

// Create the controller
$classname  = 'EmundusController' . $controller;
$controller = new $classname();

$user          = JFactory::getUser();
$secret        = JFactory::getConfig()->get('secret');
$webhook_token = JFactory::getConfig()->get('webhook_token') ?: '';

$eMConfig = JComponentHelper::getParams('com_emundus');
$cdn      = $eMConfig->get('use_cdn', 1);

$name   = $app->input->get('view', '', 'CMD');
$task   = $app->input->get('task', '', 'CMD');
$format = $app->input->get('format', '', 'CMD');
$token  = $app->input->get('token', '', 'ALNUM');

$xmlDoc          = new DOMDocument();
$release_version = '1.0.0';
if ($xmlDoc->load(JPATH_SITE . '/administrator/components/com_emundus/emundus.xml')) {
	$release_version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
}

if (!in_array($name, ['settings', 'campaigns', 'emails', 'form'])) {
	if ($cdn == 1) {
		JHTML::script("//cdnjs.cloudflare.com/ajax/libs/tinymce/4.4.1/tinymce.min.js");
	}
	else {
		JHTML::script('media/com_emundus/js/lib/tinymce.min.js');
	}
	JHtml::script('media/com_emundus/lib/jquery-1.12.4.min.js');
	JHtml::script('media/com_emundus/lib/jquery-ui-1.12.1.min.js');
	JHtml::script('media/com_emundus/lib/bootstrap-emundus/js/bootstrap.min.js');
	//TODO : Stop use chosen replace by an other js native library
	//JHtml::script('media/com_emundus/lib/chosen/chosen.jquery.min.js' );
	JHtml::script('media/jui/js/chosen.jquery.min.js');
	JFactory::getDocument()->addScript('media/com_emundus/js/em_files.js?' . $release_version);
	JFactory::getDocument()->addScript('media/com_emundus/js/mixins/exports.js?' . $release_version);
	JFactory::getDocument()->addScript('media/com_emundus/js/mixins/utilities.js?' . $release_version);
	JHTML::script('libraries/emundus/selectize/dist/js/standalone/selectize.js');
	JHTML::script('libraries/emundus/sumoselect/jquery.sumoselect.min.js');

	//JHtml::styleSheet('media/com_emundus/css/reset.css');
	JHtml::styleSheet('media/jui/css/chosen.css');
	JHtml::styleSheet('media/com_emundus/lib/bootstrap-emundus/css/bootstrap.min.css');
	JHtml::styleSheet('media/com_emundus/css/emundus_files.css');
	JHTML::stylesheet('libraries/emundus/selectize/dist/css/normalize.css');
	JHTML::stylesheet('libraries/emundus/selectize/dist/css/selectize.default.css');
	JHTML::stylesheet('libraries/emundus/sumoselect/sumoselect.css');
}

// VUE
require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();
JFactory::getDocument()->addScript('media/com_emundus_vue/chunk-vendors_emundus.js?' . $hash);
JHtml::styleSheet('media/com_emundus_vue/app_emundus.css');

// QUILL
if ($cdn == 1) {
	JHTML::script('https://cdn.quilljs.com/1.3.6/quill.min.js');
}
else {
	JHTML::script('media/com_emundus/js/lib/quill.min.js');
}
JHtml::script('components/com_emundus/src/assets/js/quill/image-resize.min.js');
JHtml::styleSheet('components/com_emundus/src/assets/js/quill/quill-mention/quill.mention.min.css');
JHtml::script('components/com_emundus/src/assets/js/quill/quill-mention/quill.mention.min.js');

// The task 'getproductpdf' can be executed as public (when not signed in and form any view).
if ($task == 'getproductpdf') {
	$controller->execute($task);
}

if ($user->authorise('core.viewjob', 'com_emundus') && ($name == 'jobs' || $name == 'job' || $name == 'thesiss' || $name == 'thesis')) {
	$controller->execute($task);
}
elseif ($user->guest && ((($name === 'webhook' || $app->input->get('controller', '', 'WORD') === 'webhook') && $format === 'raw') && ($secret === $token || $webhook_token == JApplicationHelper::getHash($token)) || $task == 'getfilereferent')) {
	$controller->execute($task);
}
elseif ($user->guest && $name != 'emailalert' && $name != 'programme' && $name != 'search_engine' && $name != 'ccirs' && ($name != 'campaign') && $task != 'passrequest' && $task != 'getusername' && $task != 'getpasswordsecurity') {
	JPluginHelper::importPlugin('emundus', 'custom_event_handler');
	$app->triggerEvent('onCallEventHandler', ['onAccessDenied', []]);

	$controller->setRedirect('index.php', Text::_("ACCESS_DENIED"), 'error');
}
else {
	if ($name != 'search_engine') {
		// Perform the Request task
		$controller->execute($task);
	}
}
// Redirect if set by the controller
$controller->redirect();
?>
