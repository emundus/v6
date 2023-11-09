<?php
/**
 * @package    Joomla
 * @subpackage emundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// no direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

use Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
class EmundusViewApplication extends JViewLegacy
{

	private $app;
	private $user;
	private $jdocument;

	protected $student;
	protected $current_user;
	protected $profile;
	protected $userDetails;
	protected $userInformations;
	protected $userCampaigns;
	protected $userAttachments;
	protected $userComments;
	protected $formsProgress;
	protected $attachmentsProgress;
	protected $logged;
	protected $forms;
	protected $email;
	protected $campaign_id;
	protected $evaluation;
	protected $actions;

	function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'filters.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'list.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'emails.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'export.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'menu.php');

		$this->app = Factory::getApplication();
		if (version_compare(JVERSION, '4.0', '>')) {
			$session         = $this->app->getSession();
			$this->user      = $this->app->getIdentity();
			$this->jdocument = $this->app->getDocument();
		}
		else {
			$session         = Factory::getSession();
			$this->user      = Factory::getUser();
			$this->jdocument = Factory::getDocument();
		}

		$this->current_user = $session->get('emundusUser');

		parent::__construct($config);
	}

	function display($tpl = null)
	{

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->user->id)) {
			die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
		}

		if (version_compare(JVERSION, '4.0', '>')) {
			$wa = $this->jdocument->getWebAssetManager();
			$wa->registerAndUseStyle('media/com_emundus/css/emundus.css');
			$wa->registerAndUseStyle('media/com_emundus/css/emundus_application.css');
			$wa->registerAndUseScript('media/jui/js/jquery.min.js');
		}
		else {
			$this->jdocument->addStyleSheet("media/com_emundus/css/emundus.css");
			$this->jdocument->addStyleSheet("media/com_emundus/css/emundus_application.css");
			$this->jdocument->addScript("media/jui/js/jquery.min.js");
		}

		$this->campaign_id = $this->app->input->get('campaign_id', null, 'GET', 'none', 0);
		$rowid             = $this->app->input->get('rowid', null, 'GET', 'none', 0);
		$aid               = $this->app->input->get('sid', null, 'GET', 'none', 0);

		$this->student = Factory::getUser($aid);

		$profile       = UserHelper::getProfile($aid);
		$this->profile = $profile->emundus_profile;

		$application       = $this->getModel('application');
		$details_id        = "82, 87, 89";
		$this->userDetails = $application->getApplicantDetails($aid, $details_id);

		$infos                  = array('#__emundus_uploads.filename', '#__users.email', '#__emundus_setup_profiles.label as profile', '#__emundus_personal_detail.gender', '#__emundus_personal_detail.birth_date as birthdate', '#__emundus_users.profile as pid');
		$this->userInformations = $application->getApplicantInfos($aid, $infos);

		$this->userCampaigns = $application->getUserCampaigns($aid);

		$this->userAttachments = $application->getUserAttachments($aid);

		$this->userComments = $application->getUsersComments($aid);

		$this->formsProgress = $application->getFormsProgress();

		$this->attachmentsProgress = $application->getAttachmentsProgress();

		$this->logged = $application->getlogged($aid);

		$this->forms = $application->getForms($aid);

		$this->email = $application->getEmail($aid);

		//Evaluation
		if ($this->current_user->profile == 16) {
			$options = array('view');
		}
		else {
			$options = array('add', 'edit', 'delete');
		}

		$user[0] = array(
			'user_id'          => $this->student->id,
			'name'             => $this->student->name,
			'email_applicant'  => $this->student->email,
			'campaign'         => "",
			'campaign_id'      => $this->campaign_id,
			'evaluation_id'    => $rowid,
			'final_grade'      => "",
			'date_result_sent' => "",
			'result'           => "",
			'comment'          => "",
			'user'             => $this->user->id,
			'user_name'        => "",
			'ranking'          => ""
		);

		$this->evaluation = EmundusHelperList::createEvaluationBlock($user, $options);
		unset($options);

		$options       = array('evaluation');
		$this->actions = EmundusHelperList::createActionsBlock($user, $options);
		unset($options);

		parent::display($tpl);
	}
}
