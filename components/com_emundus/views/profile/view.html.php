<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     eMundus SAS - Jonas Lerebours
*/
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 

class EmundusViewProfile extends JViewLegacy
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
		if ( !EmundusHelperAccess::asAdministratorAccessLevel($this->_user->id) && !EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id) ){
			die(JText::_('ACCESS_DENIED'));
		}
		$app    = JFactory::getApplication();
		$p 		= JFactory::getApplication()->input->get('rowid', $default=null, $hash= 'GET', $type= 'none', $mask=0);
		$model 	= $this->getModel();
		$profile = $model->getProfile($p);

		if($profile->published !=1) {
            $app->enqueueMessage(JText::_('CANNOT_SETUP_ATTACHMENTS_TO_NON_APPLICANT_USERS'));
			$app->redirect('index.php?option=com_fabrik&view=list&listid=67');
		}
		$attachments = $model->getAttachments($p);
		$forms = $model->getForms($p);
		$this->assignRef('profile', $profile);
		$this->assignRef('forms', $forms);
		$this->assignRef('attachments', $attachments);
		parent::display($tpl);
    }
}
?>