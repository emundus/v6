<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// no direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * HTML View class for the eMundus Component
 *
 * @package    eMundus
 */
class EmundusViewChecklist extends JViewLegacy
{
	private $app;

	protected $_user;
	protected $sent;
	protected $confirm_form_url;
	protected $attachments_prog;
	protected $forms_prog;
	protected $current_phase;
	protected $is_campaign_started;
	protected $is_dead_line_passed;
	protected $isLimitObtained;
	protected $title;
	protected $text;
	protected $need;
	protected $custom_title;
	protected $forms;
	protected $attachments;
	protected $instructions;
	protected $is_other_campaign;
	protected $show_browse_button;
	protected $show_shortdesc_input;
	protected $show_info_panel;
	protected $show_info_legend;
	protected $show_nb_column;
	protected $is_admission;
	protected $required_desc;
	protected $notify_complete_file;
	protected $attachments_to_upload;
	protected $profile_attachments_not_uploaded_ids;
	protected $result;

	function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'checklist.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'campaign.php');

		$this->app   = Factory::getApplication();
		$session     = $this->app->getSession();
		$this->_user = $session->get('emundusUser');

		if (!EmundusHelperAccess::isApplicant($this->_user->id)) {
			die(JText::_('ACCESS_DENIED'));
		}

		parent::__construct($config);
	}

	function display($tpl = null)
	{
		$eMConfig    = ComponentHelper::getParams('com_emundus');
		$m_checklist = new EmundusModelChecklist;

		$layout = $this->app->input->getString('layout', null);

		$this->sent             = $this->get('sent');
		$this->confirm_form_url = $this->get('ConfirmUrl');

		switch ($layout) {
			case 'paid':
				include_once(JPATH_BASE . '/components/com_emundus/models/application.php');

				// 1. if application form not sent yet, send it // 2. trigger emails // 3. display reminder list
				$m_application = new EmundusModelApplication;
				$m_files       = new EmundusModelFiles;

				$accept_other_payments = $eMConfig->get('accept_other_payments', 0);
				$fnumInfos             = $m_files->getFnumInfos($this->_user->fnum);
				$order                 = $m_application->getHikashopOrder($fnumInfos);

				if ($accept_other_payments == 2 || !empty($order)) {

					switch ($eMConfig->get('redirect_after_payment')) {

						// If redirect after payment is active then the file is not sent and instead we redirect to the submitting form.
						default:
						case 1:
							$this->app->redirect($m_checklist->getConfirmUrl() . '&usekey=fnum&rowid=' . $this->_user->fnum);
							break;

						// Send the user to the homepage
						case 2:
							$this->app->redirect('index.php', JText::_('EM_PAYMENT_CONFIRMATION_MESSAGE'), 'message');
							break;

						// Send the user to the profiles first page
						case 3:
							$this->app->redirect('index.php?option=com_emundus&task=openfile&fnum=' . $this->_user->fnum, JText::_('EM_PAYMENT_CONFIRMATION_MESSAGE_CONTINUE_CANDIDATURE'), 'message');
							break;
					}
				}
				elseif (empty($order)) {
					$this->app->redirect('index.php', JText::_('EM_PAYMENT_CANCEL_MESSAGE'), 'error');
				}

				$this->app->redirect($m_checklist->getConfirmUrl($this->_user->profile) . '&usekey=fnum&rowid=' . $this->_user->fnum);
				break;

			default :
				require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'users.php');
				$m_users = new EmundusModelUsers;

				if (version_compare(JVERSION, '4.0', '>')) {
					$document = $this->app->getDocument();
					$wa       = $document->getWebAssetManager();
					$wa->useScript('jquery');
					$wa->registerAndUseStyle('com_emundus.dropzone', 'media/com_emundus/lib/dropzone/css/dropzone.min.css');
					$wa->registerAndUseScript('com_emundus.dropzone', 'media/com_emundus/lib/dropzone/js/dropzone.min.js', ['defer' => true]);
					$wa->registerAndUseStyle('com_emundus', 'media/com_emundus/css/emundus.css');
					$wa->registerAndUseStyle('com_emundus.application', 'media/com_emundus/css/emundus_application.css');
				}
				else {
					$document = JFactory::getDocument();
					$document->addScript("media/jui/js/jquery.min.js");
					$document->addScript("media/com_emundus/lib/dropzone/js/dropzone.min.js");
					$document->addStyleSheet("media/com_emundus/lib/dropzone/css/dropzone.min.css");
					$document->addStyleSheet("media/com_emundus/css/emundus.css");
					$document->addStyleSheet("media/com_emundus/css/emundus_application.css");
				}

				$menu         = $this->app->getMenu();
				$current_menu = $menu->getActive();
				$menu_params  = $menu->getParams($current_menu->id);

				$this->show_browse_button   = $menu_params->get('show_browse_button', 0);
				$this->show_shortdesc_input = $menu_params->get('show_shortdesc_input', 0);
				$this->show_info_panel      = $menu_params->get('show_info_panel', 0);
				$this->show_info_legend     = $menu_params->get('show_info_legend', 0);
				$this->show_nb_column       = $menu_params->get('show_nb_column', 0);
				$this->custom_title         = $menu_params->get('custom_title', null);
				$this->is_admission         = $menu_params->get('is_admission', 0);
				$this->required_desc        = $menu_params->get('required_desc', 0);
				$this->notify_complete_file = $menu_params->get('notify_complete_file', 0);

				$this->forms             = $this->get('FormsList');
				$this->attachments       = $this->get('AttachmentsList');
				$this->need              = $this->get('Need');
				$this->instructions      = $this->get('Instructions');
				$this->is_other_campaign = $this->get('isOtherActiveCampaign');

				if ($this->need == 0) {
					$this->title = JText::_('COM_EMUNDUS_ATTACHMENTS_APPLICATION_COMPLETED_TITLE');
					$this->text  = JText::_('COM_EMUNDUS_ATTACHMENTS_APPLICATION_COMPLETED_TEXT');
				}
				else {
					$this->title = JText::_('COM_EMUNDUS_ATTACHMENTS_APPLICATION_INCOMPLETED_TITLE');
					$this->text  = JText::_('COM_EMUNDUS_ATTACHMENTS_APPLICATION_INCOMPLETED_TEXT');
				}

				require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'application.php');
				$m_application = new EmundusModelApplication;

				$this->attachments_prog = $m_application->getAttachmentsProgress($this->_user->fnum);
				$this->forms_prog       = $m_application->getFormsProgress($this->_user->fnum);

				$profile_attachments_ids                    = array();
				$all_profile_attachments_ids                = array();
				$this->attachments_to_upload                = array();
				$this->profile_attachments_not_uploaded_ids = array();

				$profile_attachments              = $m_users->getProfileAttachments($this->_user->id, $this->_user->fnum);
				$all_profile_attachments          = $m_users->getProfileAttachments($this->_user->id);
				$profile_attachments_not_uploaded = $m_users->getProfileAttachmentsAllowed();

				foreach ($profile_attachments_not_uploaded as $profile_attachment_not_uploaded) {
					$this->profile_attachments_not_uploaded_ids[] = $profile_attachment_not_uploaded->id;
				}

				if (!empty($all_profile_attachments)) {
					foreach ($all_profile_attachments as $all_profile_attachment) {
						$all_profile_attachments_ids[] = $all_profile_attachment->id;
					}

					foreach ($this->profile_attachments_not_uploaded_ids as $key => $profile_attachment_not_uploaded_id) {
						$neededObject = array_filter(
							$this->attachments,
							function ($e) use ($profile_attachment_not_uploaded_id) {
								return $e->id == $profile_attachment_not_uploaded_id;
							}
						);

						if (in_array($profile_attachment_not_uploaded_id, $all_profile_attachments_ids) || (int) $neededObject[0]->nb > 0) {
							unset($this->profile_attachments_not_uploaded_ids[$key]);
						}
					}
				}

				if (!empty($profile_attachments)) {
					foreach ($profile_attachments as $profile_attachment) {
						$profile_attachments_ids[] = $profile_attachment->id;
					}

					foreach ($this->attachments as $attachment) {
						if (in_array($attachment->id, $profile_attachments_ids)) {
							$this->attachments_to_upload[] = $attachment->id;
						}
					}
				}

				$this->profile_attachments_not_uploaded_ids = implode(',', $this->profile_attachments_not_uploaded_ids);

				$offset   = $this->app->get('offset', 'UTC');
				$dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
				$now      = $dateTime->setTimezone(new DateTimeZone($offset))->format("Y-m-d H:i:s");

				// Check campaign limit, if the limit is obtained, then we set the deadline to true
				$m_campaign          = new EmundusModelCampaign;
				$this->current_phase = $m_campaign->getCurrentCampaignWorkflow($this->_user->fnum);

				if (!empty($this->current_phase) && !empty($this->current_phase->end_date)) {
					$current_end_date   = $this->current_phase->end_date;
					$current_start_date = $this->current_phase->start_date;
				}
				else if (!empty($this->is_admission)) {
					$current_end_date   = $this->_user->fnums[$this->_user->fnum]->admission_end_date;
					$current_start_date = $this->_user->fnums[$this->_user->fnum]->admission_start_date;
				}
				else {
					$current_end_date   = !empty($this->_user->fnums[$this->_user->fnum]->end_date) ? $this->_user->fnums[$this->_user->fnum]->end_date : $this->_user->end_date;
					$current_start_date = $this->_user->fnums[$this->_user->fnum]->start_date;
				}

				$this->isLimitObtained     = $m_campaign->isLimitObtained($this->_user->fnums[$this->_user->fnum]->campaign_id);
				$this->is_campaign_started = $now > $current_start_date;
				$this->is_dead_line_passed = $current_end_date < $now;

				if (($this->is_dead_line_passed || $this->isLimitObtained === true) && $eMConfig->get('can_edit_after_deadline', 0) == 0) {
					$m_checklist->setDelete(0, $this->_user);
				}
				elseif (!empty($eMConfig->get('can_edit_until_deadline', 0)) || $eMConfig->get('can_edit_after_deadline', 0) == 1) {
					$m_checklist->setDelete(1, $this->_user);
				}

				$this->result = $this->get('Result');
		}

		parent::display($tpl);
	}
}
