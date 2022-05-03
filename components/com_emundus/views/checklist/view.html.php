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

class EmundusViewChecklist extends JViewLegacy {
    var $_user = null;
    var $_db = null;

    function __construct($config = array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'checklist.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'campaign.php');

        $this->_user = JFactory::getSession()->get('emundusUser');
        $this->_db = JFactory::getDBO();

        if (!EmundusHelperAccess::isApplicant($this->_user->id)) {
            die(JText::_('ACCESS_DENIED'));
        }

        parent::__construct($config);
    }

    function display($tpl = null) {
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $m_checklist = new EmundusModelChecklist;

        $app = JFactory::getApplication();
        $layout = $app->input->getString('layout', null);

        $sent = $this->get('sent');
        $confirm_form_url = $this->get('ConfirmUrl');

        $this->assignRef('sent', $sent);
        $this->assignRef('confirm_form_url', $confirm_form_url);

        switch ($layout) {
            // layout displayed when paid
            case 'paid':
                include_once(JPATH_BASE.'/components/com_emundus/models/application.php');

                // 1. if application form not sent yet, send it // 2. trigger emails // 3. display reminder list
                $m_application 	= new EmundusModelApplication;
                $m_files = new EmundusModelFiles;
                
                $accept_other_payments = $eMConfig->get('accept_other_payments', 0);
                $fnumInfos = $m_files->getFnumInfos($this->_user->fnum);

                if ($accept_other_payments == 2 || !empty($m_application->getHikashopOrder($fnumInfos))) {

                    switch ($eMConfig->get('redirect_after_payment')) {

                        // If redirect after payment is active then the file is not sent and instead we redirect to the submitting form.
                        default:
                        case 1:
                            $app->redirect($m_checklist->getConfirmUrl().'&usekey=fnum&rowid='.$this->_user->fnum);
                        break;

                        // Send the user to the homepage
                        case 2:
                            $app->redirect('index.php', JText::_('EM_PAYMENT_CONFIRMATION_MESSAGE'), 'message');
                        break;

                        // Send the user to the profiles first page
                        case 3:
                            $app->redirect('index.php?option=com_emundus&task=openfile&fnum=' . $this->_user->fnum, JText::_('EM_PAYMENT_CONFIRMATION_MESSAGE_CONTINUE_CANDIDATURE'), 'message');
                        break;
                    }
                }

                $app->redirect($m_checklist->getConfirmUrl($this->_user->profile).'&usekey=fnum&rowid='.$this->_user->fnum);
                break;

            default :
                $document = JFactory::getDocument();
                $document->addScript("media/jui/js/jquery.min.js" );
                $document->addScript("media/com_emundus/lib/dropzone/js/dropzone.min.js" );
                $document->addStyleSheet("media/com_emundus/lib/dropzone/css/dropzone.min.css" );
                $document->addStyleSheet("media/com_emundus/css/emundus.css" );
                $document->addStyleSheet("media/com_emundus/css/emundus_application.css" );

                $menu = @JFactory::getApplication()->getMenu();
                $current_menu = $menu->getActive();
                $menu_params = $menu->getParams(@$current_menu->id);

                $show_browse_button = $menu_params->get('show_browse_button', 0);
                $show_shortdesc_input = $menu_params->get('show_shortdesc_input', 0);
                $show_info_panel = $menu_params->get('show_info_panel', 0);
                $show_info_legend = $menu_params->get('show_info_legend', 0);
                $show_nb_column = $menu_params->get('show_nb_column', 0);
                $custom_title = $menu_params->get('custom_title', null);
                $is_admission = $menu_params->get('is_admission', 0);
                $required_desc = $menu_params->get('required_desc', 0);
                $notify_complete_file = $menu_params->get('notify_complete_file', 0);

                $forms 				= $this->get('FormsList');
                $attachments 		= $this->get('AttachmentsList');
                $need 				= $this->get('Need');
                $instructions 		= $this->get('Instructions');
                $is_other_campaign 	= $this->get('isOtherActiveCampaign');

                if ($need == 0) {
                    $title = JText::_('COM_EMUNDUS_ATTACHMENTS_APPLICATION_COMPLETED_TITLE');
                    $text = JText::_('COM_EMUNDUS_ATTACHMENTS_APPLICATION_COMPLETED_TEXT');
                } else {
                    $title = JText::_('COM_EMUNDUS_ATTACHMENTS_APPLICATION_INCOMPLETED_TITLE');
                    $text = JText::_('COM_EMUNDUS_ATTACHMENTS_APPLICATION_INCOMPLETED_TEXT');
                }

                if ($notify_complete_file) {
	                require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
	                $m_application = new EmundusModelApplication;
	                $attachments_prog = $m_application->getAttachmentsProgress($this->_user->fnum);
	                $forms_prog = $m_application->getFormsProgress($this->_user->fnum);
	                $this->assignRef('attachments_prog', $attachments_prog);
	                $this->assignRef('forms_prog', $forms_prog);
                }


                $end_date = !empty($is_admission) ? $this->_user->fnums[$this->_user->fnum]->admission_end_date : $this->_user->fnums[$this->_user->fnum]->end_date;

                $offset = $app->get('offset', 'UTC');
                $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
                $now = $dateTime->setTimezone(new DateTimeZone($offset))->format("Y-m-d");

                $is_dead_line_passed = $end_date < $now;

                // Check campaign limit, if the limit is obtained, then we set the deadline to true
                $m_campaign = new EmundusModelCampaign;

                $isLimitObtained = $m_campaign->isLimitObtained($this->_user->fnums[$this->_user->fnum]->campaign_id);

                if (($is_dead_line_passed || $isLimitObtained === true) && $eMConfig->get('can_edit_after_deadline', 0) == 0) {
                    $m_checklist->setDelete(0, $this->_user);
                } elseif (!empty($eMConfig->get('can_edit_until_deadline', 0)) || $eMConfig->get('can_edit_after_deadline', 0) == 1) {
                    $m_checklist->setDelete(1, $this->_user);
                }

                $this->assignRef('is_dead_line_passed', $is_dead_line_passed);
                $this->assignRef('isLimitObtained', $isLimitObtained);
                $this->assignRef('user', $this->_user);
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
                $this->assignRef('is_admission', $is_admission);
                $this->assignRef('required_desc', $required_desc);
                $this->assignRef('notify_complete_file', $notify_complete_file);

                $result = $this->get('Result');
                $this->assignRef('result', $result);
        }

        parent::display($tpl);
    }
}
