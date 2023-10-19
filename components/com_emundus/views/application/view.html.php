<?php
/**
 * @package    Joomla
 * @subpackage emundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */

class EmundusViewApplication extends JViewLegacy{
	var $_user = null;
	var $_db = null;

	function __construct($config = array()){
		// require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');

		$this->_user = JFactory::getSession()->get('emundusUser');
		$this->_db = JFactory::getDBO();

		parent::__construct($config);
	}
    function display($tpl = null){

    	if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id))
			die( JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS') );

        $document = JFactory::getDocument();
        $document->addStyleSheet("media/com_emundus/css/emundus.css" );
        $document->addStyleSheet("media/com_emundus/css/emundus_application.css" );
        $document->addScript("media/jui/js/jquery.min.js" );


		// $menu=JFactory::getApplication()->getMenu()->getActive();
        $menu = JFactory::getApplication()->getMenu();
		$current_menu  = $menu->getActive();
		$access=!empty($current_menu)?$current_menu->access : 0;

		if (!EmundusHelperAccess::asEvaluatorAccessLevel($this->_user->id))
			die("ACCESS_DENIED");

		$menu_params = $menu->getParams($current_menu->id);

		$campaign_id 	= JRequest::getVar('campaign_id', null, 'GET', 'none', 0);
		$rowid 			= JRequest::getVar('rowid', null, 'GET', 'none', 0);
		$aid 			= JRequest::getVar('sid', null, 'GET', 'none', 0);
		$student 		= JFactory::getUser($aid);

		$this->assignRef('student', $student);
		$this->assignRef('current_user', $this->_user);

		$profile = JUserHelper::getProfile($aid);
		$this->assignRef('profile', $profile->emundus_profile);

		$application = $this->getModel('application');
		$details_id = "82, 87, 89"; // list of Fabrik elements ID
		$userDetails = $application->getApplicantDetails($aid, $details_id);
		$this->assignRef('userDetails', $userDetails);

		$infos = array('#__emundus_uploads.filename', '#__users.email', '#__emundus_setup_profiles.label as profile', '#__emundus_personal_detail.gender', '#__emundus_personal_detail.birth_date as birthdate', '#__emundus_users.profile as pid');
		$userInformations = $application->getApplicantInfos($aid, $infos);

		$this->assignRef('userInformations', $userInformations);

		$userCampaigns = $application->getUserCampaigns($aid);
		$this->assignRef('userCampaigns', $userCampaigns);

		$userAttachments = $application->getUserAttachments($aid);
		$this->assignRef('userAttachments', $userAttachments);

		$userComments = $application->getUsersComments($aid);
		$this->assignRef('userComments', $userComments);

		$formsProgress = $application->getFormsProgress();
		$this->assignRef('formsProgress', $formsProgress);

		$attachmentsProgress = $application->getAttachmentsProgress();
		$this->assignRef('attachmentsProgress', $attachmentsProgress);

		$logged = $application->getlogged($aid);
		$this->assignRef('logged', $logged);

		$forms = $application->getForms($aid);
		$this->assignRef('forms', $forms);

		$email = $application->getEmail($aid);
		$this->assignRef('email', $email);

		//Evaluation
		if ($this->_user->profile==16) {
		    $options = array('view');
		}
		else {
		    $options = array('add', 'edit', 'delete');
		}

		$user[0] = array (
	      'user_id' => $student->id,
	      'name' => $student->name,
	      'email_applicant' => $student->email,
	      'campaign' => "",
	      'campaign_id' => $campaign_id,
	      'evaluation_id' => $rowid,
	      'final_grade' => "",
	      'date_result_sent' => "",
	      'result' => "",
	      'comment' => "",
	      'user' => $this->_user->id,
	      'user_name' => "",
	      'ranking' => ""
	      );

		$this->assignRef('campaign_id', $campaign_id);

		$evaluation = EmundusHelperList::createEvaluationBlock($user, $options);
		$this->assignRef('evaluation', $evaluation);
		unset($options);

		$options = array('evaluation');
		$actions = EmundusHelperList::createActionsBlock($user, $options);
		$this->assignRef('actions', $actions);
		unset($options);

        parent::display($tpl);
    }
}
