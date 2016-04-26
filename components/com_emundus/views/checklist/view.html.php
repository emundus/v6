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
	
	function __construct($config = array()){
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
	
    function display($tpl = null)
    {
		/*
		$menu=JSite::getMenu()->getActive();
		$access=!empty($menu)?$menu->access : 0;
		if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id,$access)) die("You are not allowed to access to this page.");
		*/	

		$document = JFactory::getDocument();
        $document->addScript( JURI::base()."media/com_emundus/lib/jquery-1.10.2.min.js" );
        $document->addScript( JURI::base()."media/com_emundus/lib/dropzone/js/dropzone.min.js" );
        $document->addStyleSheet( JURI::base()."media/com_emundus/lib/dropzone/css/dropzone.min.css" );
        $document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus.css" );
        $document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus_application.css" );

		//$greeting = $this->get('Greeting');
		
		$sent = $this->get('sent');
		$confirm_form_url = $this->get('ConfirmUrl');
		$forms = $this->get('FormsList');
		$attachments = $this->get('AttachmentsList');
		$need = $this->get('Need');
		$instructions = $this->get('Instructions');
		$is_other_campaign = $this->get('isOtherActiveCampaign');
		
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
		$this->assignRef('sent', $sent);
		$this->assignRef('confirm_form_url', $confirm_form_url);
		$this->assignRef('forms', $forms);
		$this->assignRef('attachments', $attachments);
		$this->assignRef('instructions', $instructions);
		$this->assignRef('is_other_campaign', $is_other_campaign);

	
		$result = $this->get('Result');
		$this->assignRef('result', $result);
	
		parent::display($tpl);
    }
}
?>