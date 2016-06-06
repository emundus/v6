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
		require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');
		//require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
		
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
		
		parent::__construct($config);
	}
	
    function display($tpl = null)
    {	

		$document = JFactory::getDocument();
        $document->addScript( JURI::base()."media/com_emundus/lib/jquery-1.10.2.min.js" );
        $document->addScript( JURI::base()."media/com_emundus/lib/dropzone/js/dropzone.min.js" );
        $document->addStyleSheet( JURI::base()."media/com_emundus/lib/dropzone/css/dropzone.min.css" );
        $document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus.css" );
        $document->addStyleSheet( JURI::base()."media/com_emundus/css/emundus_application.css" );

		//$greeting = $this->get('Greeting');
        $menu = @JSite::getMenu();
        $current_menu   = $menu->getActive();
        $menu_params    = $menu->getParams(@$current_menu->id);

		$show_browse_button = $menu_params->get('show_browse_button', 1);
		$show_shortdesc_input = $menu_params->get('show_shortdesc_input', 1);
		$show_info_panel = $menu_params->get('show_info_panel', 1);
		$show_info_legend = $menu_params->get('show_info_legend', 1);
		$show_nb_column = $menu_params->get('show_nb_column', 1);
		
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
		$this->assignRef('show_browse_button', $show_browse_button);
		$this->assignRef('show_shortdesc_input', $show_shortdesc_input);
		$this->assignRef('show_info_panel', $show_info_panel);
		$this->assignRef('show_info_legend', $show_info_legend);
		$this->assignRef('show_nb_column', $show_nb_column);
	
		$result = $this->get('Result');
		$this->assignRef('result', $result);
	
		parent::display($tpl);
    }
}
?>