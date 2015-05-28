<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
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
JText::script('COM_EMUNDUS_CHOOSE_FORM_ELEM');
JText::script('COM_EMUNDUS_CHOOSEN_FORM_ELEM');
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

JText::script('TITLE');
JText::script('ENTER_COMMENT');
JText::script('COMMENT_SENT');
JText::script('SHARE_PROGRESS');
JText::script('SHARE_SUCCESS');
JText::script('ERROR_REQUIRED');
JText::script('ERROR');
JText::script('DOWNLOAD_PDF');
JText::script('JYES');
JText::script('JNO');
JText::script('YOU_MUST_SELECT_ATTACHMENT');
JText::script('GENERATE_PDF');
JText::script('ATTACHEMENTS_AGGREGATIONS');
JText::script('FILES_GENERATED');
JText::script('FILE_NAME');
JText::script('LINK_TO_DOWNLOAD');
JText::script('ALL_IN_ONE_DOC');

// view user
JText::script('NOT_A_VALID_EMAIL');
JText::script('REQUIRED');
JText::script('SELECT_A_VALUE');
JText::script('GROUP_CREATED');
JText::script('USER_CREATED');
JText::script('LOGIN_NOT_GOOD');
JText::script('MAIL_NOT_GOOD');
JText::script('ARE_YOU_SURE_TO_DELETE_USERS');
JText::script('NOT_A_VALID_LOGIN_MUST_NOT_CONTAIN_SPECIAL_CHARACTER');
JText::script('COM_EMUNDUS_USERS_DELETED');
JText::script('SHARE_PROGRESS');
JText::script('SENT');
JText::script('FILES_GENERATED');
JText::script('STATE');

//view application layout share
JText::script('COM_EMUNDUS_ARE_YOU_SURE_YOU_WANT_TO_REMOVE_THIS_ACCESS');

JHtml::script(JURI::base() . 'media/com_emundus/lib/jquery-1.10.2.min.js');
JHtml::script(JURI::base() . 'media/com_emundus/lib/jquery-ui-1.8.18.min.js');
JHtml::script(JURI::base() . 'media/com_emundus/lib/jquery.doubleScroll.js' );
JHtml::script(JURI::base() . 'media/com_emundus/lib/bootstrap-emundus/js/bootstrap.min.js');
JHtml::script(JURI::base() . 'media/com_emundus/lib/chosen/chosen.jquery.min.js' );
JHTML::script(JURI::Base() . 'media/com_emundus/js/em_files.js');

JHtml::styleSheet(JURI::base() . 'media/com_emundus/lib/chosen/chosen.min.css');
JHtml::stylesheet(JURI::base() . 'media/com_emundus/lib/bootstrap-emundus/css/bootstrap.min.css');
JHtml::stylesheet(JURI::base() . 'media/com_emundus/css/emundus_files.css');

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

if ($user->authorise('core.viewjob', 'com_emundus') && ($name == 'jobs' || $name == 'job')) {
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