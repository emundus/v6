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
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();

		if (!EmundusHelperAccess::isApplicant($this->_user->id)) {
			die(JText::_('ACCESS_DENIED'));
		}
		
		parent::__construct($config);
	}
	
    function display($tpl = null)
    {	
    	$app = JFactory::getApplication();
    	$layout = $app->input->getString('layout', null);

    	$sent 				= $this->get('sent');
		$confirm_form_url 	= $this->get('ConfirmUrl');
		
		$this->assignRef('sent', $sent);
		$this->assignRef('confirm_form_url', $confirm_form_url);

    	switch  ($layout)
		{
			// layout displayed when paid
			case 'paid':
			include_once(JPATH_BASE.'/components/com_emundus/models/application.php');

			// 1. if application form not sent yet, send it // 2. trigger emails // 3. display reminder list
			$application = new EmundusModelApplication;
			$applications 		= $application->getApplications($this->_user->id);
			$attachments 		= $application->getAttachmentsProgress($this->_user->id, $this->_user->profile, array_keys($applications));
			$forms 				= $application->getFormsProgress($this->_user->id, $this->_user->profile, array_keys($applications));

			if((int)($attachments[$this->_user->fnum])>=100 && (int)($forms[$this->_user->fnum])>=100 && $sent != 1) {
				$eMConfig = JComponentHelper::getParams('com_emundus');
				$can_edit_until_deadline = $eMConfig->get('can_edit_until_deadline', 0);
				$application_fee = $eMConfig->get('application_fee', 0);

				$params = array(
					'type_mail' => 'paid_validation',
					'can_edit_until_deadline' => $can_edit_until_deadline,
					'application_fee' => $application_fee
				);

				$application->sendApplication($this->_user->fnum, $this->_user, $params);
				$applications 		= $application->getApplications($this->_user->id);
			}

			$this->assignRef('applications', $applications);
			$this->assignRef('attachments', $attachments);
			$this->assignRef('forms', $forms);

			break;

			default :
			$document = JFactory::getDocument();
	        $document->addScript( JURI::base()."media/com_emundus/lib/jquery-1.10.2.min.js" );
	        $document->addScript( JURI::base()."media/com_emundus/lib/dropzone/js/dropzone.min.js" );
	        $document->addStyleSheet( JURI::base()."media/com_emundus/lib/dropzone/css/dropzone.min.css" );
	        $document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus.css" );
	        $document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus_application.css" );

			//$greeting = $this->get('Greeting');
	        $menu 			= @JSite::getMenu();
	        $current_menu   = $menu->getActive();
	        $menu_params    = $menu->getParams(@$current_menu->id);

			$show_browse_button   = $menu_params->get('show_browse_button', 1);
			$show_shortdesc_input = $menu_params->get('show_shortdesc_input', 1);
			$show_info_panel 	  = $menu_params->get('show_info_panel', 1);
			$show_info_legend 	  = $menu_params->get('show_info_legend', 1);
			$show_nb_column 	  = $menu_params->get('show_nb_column', 1);
			
			
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