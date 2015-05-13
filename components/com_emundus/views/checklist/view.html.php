<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
*/
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
/**
 * HTML View class for the HelloWorld Component
 *
 * @package    HelloWorld
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
        $document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus.css" );
        $document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus_application.css" );

		$forms = $this->get('FormsList');
		$attachments = $this->get('AttachmentsList');
		$sent = $this->get('sent');
		$confirm_form_url = $this->get('ConfirmUrl');
		$greeting = $this->get('Greeting');
		$need = $this->get('Need');
		$instructions = $this->get('Instructions');
		$is_other_campaign = $this->get('isOtherActiveCampaign');
		
		$this->assignRef('title', $greeting->title);
		$this->assignRef('text', $greeting->text);
		$this->assignRef('need', $need);
		$this->assignRef('sent', $sent);
		$this->assignRef('confirm_form_url', $confirm_form_url);
		$this->assignRef('forms', $forms);
		$this->assignRef('attachments', $attachments);
		$this->assignRef('instructions', $instructions);
		$this->assignRef('is_other_campaign', $is_other_campaign);

		/*$evaluation =  &$this->getModel('evaluation');
		$evaluation->getEvalColumns();
		$this->assignRef('evaluation', $evaluation);*/
		
		$result = $this->get('Result');
		$this->assignRef('result', $result);
	
		parent::display($tpl);
    }
}
?>