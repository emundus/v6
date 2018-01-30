<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
*/
 
// No direct access
defined( '_JEXEC' ) or die( 'ACCESS_DENIED' );

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
// translation for javacript
JText::script('COM_EMUNDUS_ACCESS_FILE');
JText::script('COM_EMUNDUS_ACCESS_ATTACHMENT');
JText::script('COM_EMUNDUS_ACCESS_TAGS');
JText::script('COM_EMUNDUS_ACCESS_STATUS');
JText::script('COM_EMUNDUS_ACCESS_USER');
JText::script('COM_EMUNDUS_ACCESS_EVALUATION');
JText::script('COM_EMUNDUS_ACCESS_EXPORT_EXCEL');
JText::script('COM_EMUNDUS_ACCESS_EXPORT_ZIP');
JText::script('COM_EMUNDUS_ACCESS_EXPORT_PDF');
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
JText::script('COM_EMUNDUS_CHOOSE_PRG_DEFAULT');
JText::script('COM_EMUNDUS_CHOOSE_FORM_ELEM');
JText::script('COM_EMUNDUS_CHOOSEN_FORM_ELEM');
Jtext::script('COM_EMUNDUS_CHOOSE_ADMISSION_FORM_ELEM');
Jtext::script('COM_EMUNDUS_CHOOSEN_ADMISSION_FORM_ELEM');
Jtext::script('COM_EMUNDUS_CHOOSE_DECISION_FORM_ELEM');
Jtext::script('COM_EMUNDUS_CHOOSEN_DECISION_FORM_ELEM');
JText::script('COM_EMUNDUS_PHOTO');
JText::script('COM_EMUNDUS_FORMS');
JText::script('COM_EMUNDUS_ATTACHMENT');
JText::script('COM_EMUNDUS_ASSESSMENT');
JText::script('COM_EMUNDUS_COMMENT');
JText::script('COM_EMUNDUS_COMMENTS');
JText::script('COM_EMUNDUS_EXCEL_GENERATION');
JText::script('COM_EMUNDUS_CHOOSE_EXTRACTION_METHODE');
JText::script('COM_EMUNDUS_CHOOSE_EXTRACTION_METHODE_AGGREGATE');
JText::script('COM_EMUNDUS_CHOOSE_EXTRACTION_METHODE_LEFTJOIN');
JText::script('COM_EMUNDUS_DOWNLOAD_EXTRACTION');
JText::script('COM_EMUNDUS_ZIP_GENERATION');
JText::script('COM_EMUNDUS_DOWNLOAD_ZIP');
JText::script('COM_EMUNDUS_PUBLISH');
JText::script('COM_EMUNDUS_COPY_FILE');
JText::script('COM_EMUNDUS_SHARE_FILE');

JText::script('LOADING');
JText::script('TITLE');
JText::script('ENTER_COMMENT');
JText::script('COMMENT_SENT');
JText::script('SHARE_PROGRESS');
JText::script('SHARE_SUCCESS');
JText::script('ERROR_REQUIRED');
JText::script('ERROR');
JText::script('DOWNLOAD_PDF');
JText::script('FORMS_PDF');
JText::script('ATTACHMENT_PDF');
JText::script('ASSESSMENT_PDF');
JText::script('JYES');
JText::script('JNO');
JText::script('YOU_MUST_SELECT_ATTACHMENT');
JText::script('ATTACHEMENTS_AGGREGATIONS');
JText::script('FILES_GENERATED');
JText::script('FILE_NAME');
JText::script('LINK_TO_DOWNLOAD');
JText::script('ALL_IN_ONE_DOC');

// view user
JText::script('NOT_A_VALID_EMAIL');
JText::script('NOT_A_VALID_LOGIN_MUST_NOT_CONTAIN_SPECIAL_CHARACTER');
JText::script('REQUIRED');
JText::script('SELECT_A_VALUE');
JText::script('GROUP_CREATED');
JText::script('USER_CREATED');
JText::script('LOGIN_NOT_GOOD');
JText::script('MAIL_NOT_GOOD');
JText::script('ARE_YOU_SURE_TO_DELETE_USERS');
JText::script('COM_EMUNDUS_USERS_DELETED');
JText::script('SHARE_PROGRESS');
JText::script('SENT');
JText::script('FILES_GENERATED');
JText::script('STATE');
JText::script('SWITCH_PROFILE');
JText::script('PROFILE_CHOSEN');

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
JText::script('STATE');
JText::script('ZIPCODE');
JText::script('CITY');
JText::script('STREET');
JText::script('PERSONAL_DETAILS');
JText::script('NUMBER_OF_CHILDREN');
JText::script('ACCOMPANIED');
JText::script('DISABLED');
JText::script('NATIONALITY');
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
JText::script('EMAIL');
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



//Export PDF
JText::script('COM_EMUNDUS_PDF_GENERATION');
JText::script('COM_EMUNDUS_CREATE_PDF');
JText::script('COM_EMUNDUS_ADD_FILES_TO_PDF');
JText::script('COM_EMUNDUS_EXPORT_FINISHED');
JText::script('COM_EMUNDUS_ERROR_NBFILES_CAPACITY');
JText::script('COM_EMUNDUS_ERROR_CAPACITY_PDF');
JText::script('DECISION_PDF');
JText::script('ADMISSION_PDF');
JText::script('GENERATE_PDF');

//view application layout share
JText::script('COM_EMUNDUS_ARE_YOU_SURE_YOU_WANT_TO_REMOVE_THIS_ACCESS');


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

JHtml::script('media/com_emundus/lib/jquery-1.10.2.min.js');
JHtml::script('media/com_emundus/lib/jquery-ui-1.8.18.min.js');
JHtml::script('media/com_emundus/lib/jquery.doubleScroll.js' );
JHtml::script('media/com_emundus/lib/bootstrap-emundus/js/bootstrap.min.js');
JHtml::script('media/com_emundus/lib/chosen/chosen.jquery.min.js' );
JHTML::script('media/com_emundus/js/em_files.js');
JHTML::script('media/com_emundus/js/em_calendar.js');

JHtml::styleSheet( 'media/com_emundus/lib/chosen/chosen.min.css');
JHtml::styleSheet( 'media/com_emundus/lib/bootstrap-emundus/css/bootstrap.min.css');
JHtml::styleSheet( 'media/com_emundus/css/emundus_files.css');

$app = JFactory::getApplication();

// Require specific controller if requested
if($controller = $app->input->get('controller', '', 'WORD')) {
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
$name = $app->input->get('view', '', 'WORD');

if ($user->authorise('core.viewjob', 'com_emundus') && ($name == 'jobs' || $name == 'job' || $name == 'thesiss' || $name == 'thesis')) {
    $controller->execute($app->input->get('task', '', 'WORD'));
} elseif ($user->guest && $name != 'emailalert' && $name !='programme') {
    $controller->setRedirect('index.php', JText::_("ACCESS_DENIED"), 'error');
} else { 
    // Perform the Request task
    $controller->execute( $app->input->get('task', '', 'WORD') );
}
// Redirect if set by the controller
$controller->redirect();
?>