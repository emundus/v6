<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the eMundus Component
 *
 * @package    eMundus
 */

class EmundusViewChecklist extends JViewLegacy
{
	var $_user = null;
	var $_db = null;

	function __construct($config = array()) {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');

		$this->_user = JFactory::getSession()->get('emundusUser');
		$this->_db = JFactory::getDBO();

		if (!EmundusHelperAccess::isApplicant($this->_user->id))
			die(JText::_('ACCESS_DENIED'));

		parent::__construct($config);
	}

    function display($tpl = null) {
		$app 	= JFactory::getApplication();
		$db 	= JFactory::getDbo();
    	$layout = $app->input->getString('layout', null);

    	$sent 				= $this->get('sent');
		$confirm_form_url 	= $this->get('ConfirmUrl');

		$this->assignRef('sent', $sent);
		$this->assignRef('confirm_form_url', $confirm_form_url);

		$end_date = new JDate($this->_user->fnums[$this->_user->fnum]->end_date);

		$offset 	= $app->get('offset', 'UTC');
		$dateTime 	= new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
		$now 		= $dateTime->setTimezone(new DateTimeZone($offset));

		if ($end_date < $now) {
			include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'checklist.php');
			$m_checklist = new EmundusModelChecklist;
			$m_checklist->setDelete(0, $this->_user);
		}

    	switch  ($layout) {

			// layout displayed when paid
			case 'paid':
			include_once(JPATH_BASE.'/components/com_emundus/models/application.php');

			// 1. if application form not sent yet, send it // 2. trigger emails // 3. display reminder list
			$m_application 		= new EmundusModelApplication;
			$m_files            = new EmundusModelFiles;
			$applications 		= $m_application->getApplications($this->_user->id);
			$attachments 		= $m_application->getAttachmentsProgress($this->_user->id, $this->_user->profile, array_keys($applications));
			$forms 				= $m_application->getFormsProgress($this->_user->id, $this->_user->profile, array_keys($applications));

			if ((int)($attachments[$this->_user->fnum])>=100 && (int)($forms[$this->_user->fnum])>=100) {
				$eMConfig = JComponentHelper::getParams('com_emundus');
				$accept_created_payments = $eMConfig->get('accept_created_payments', 0);
				$fnumInfos = $m_files->getFnumInfos($this->_user->fnum);

				$paid = count($m_application->getHikashopOrder($fnumInfos))>0?1:0;

				// If created payments aren't accepted then we don't need to check.
				if ($accept_created_payments) {
					$payment_created_offline = count($m_application->getHikashopOrder($fnumInfos, true)) > 0 ? 1 : 0;
				} else {
					$payment_created_offline = false;
				}

				if ($accept_created_payments == 2 || $paid || $payment_created_offline) {

					if ($eMConfig->get('redirect_after_payment')) {

						require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'checklist.php');
						$m_checklist = new EmundusModelChecklist;

						// If redirect after payment is active then the file is not sent and instead we redirect to the submitting form.
						$app->redirect($m_checklist->getConfirmUrl().'&usekey=fnum&rowid='.$this->_user->fnum);

					} else {
						// Don't send the application if the payment has not been fully sent.
						$m_application->sendApplication($this->_user->fnum, $this->_user, [], $eMConfig->get('status_after_payment', 1));
					}
				}

				$applications = $m_application->getApplications($this->_user->id);
			}

			$this->assignRef('applications', $applications);
			$this->assignRef('attachments', $attachments);
			$this->assignRef('forms', $forms);
			break;

			default :
			$document = JFactory::getDocument();
	        $document->addScript("media/com_emundus/lib/jquery-1.10.2.min.js" );
	        $document->addScript("media/com_emundus/lib/dropzone/js/dropzone.min.js" );
	        $document->addStyleSheet("media/com_emundus/lib/dropzone/css/dropzone.min.css" );
	        $document->addStyleSheet("media/com_emundus/css/emundus.css" );
	        $document->addStyleSheet("media/com_emundus/css/emundus_application.css" );

			//$greeting = $this->get('Greeting');
	        $menu 			= @JFactory::getApplication()->getMenu();
	        $current_menu   = $menu->getActive();
	        $menu_params    = $menu->getParams(@$current_menu->id);

			$show_browse_button   = $menu_params->get('show_browse_button', 1);
			$show_shortdesc_input = $menu_params->get('show_shortdesc_input', 1);
			$show_info_panel 	  = $menu_params->get('show_info_panel', 1);
			$show_info_legend 	  = $menu_params->get('show_info_legend', 1);
			$show_nb_column 	  = $menu_params->get('show_nb_column', 1);
			$custom_title         = $menu_params->get('custom_title', null);


			$forms 				= $this->get('FormsList');
			$attachments 		= $this->get('AttachmentsList');
			$need 				= $this->get('Need');
			$instructions 		= $this->get('Instructions');
			$is_other_campaign 	= $this->get('isOtherActiveCampaign');

			if ($need == 0) {
				$title = JText::_('APPLICATION_COMPLETED_TITLE');
				$text = JText::_('APPLICATION_COMPLETED_TEXT');
			} else {
				$title = JText::_('APPLICATION_INCOMPLETED_TITLE');
				$text = JText::_('APPLICATION_INCOMPLETED_TEXT');
			}

			$this->assignRef('title', $title);
			$this->assignRef('text', $text);
			$this->assignRef('need', $need);
			$this->assignRef('custom_title', $custom_title);
			$this->assignRef('confirm_form_url', $confirm_form_url);
			$this->assignRef('forms', $forms);
			$this->assignRef('attachments', $attachments);
			$this->assignRef('instructions', $instructions);
			$this->assignRef('is_other_campaign', $is_other_campaign);
			$this->assignRef('show_browse_button', $show_browse_button);
			$this->assignRef('show_shortdesc_input', $show_shortdesc_input);
			$this->assignRef('show_info_panel', $show_info_panel);
			$this->assignRef('show_info_legend', $show_info_legend);
			$this->assignRef('show_nb_column', $show_nb_column);

			$result = $this->get('Result');
			$this->assignRef('result', $result);

		}

		parent::display($tpl);
    }
}
?>