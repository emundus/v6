<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');

/**
 * eMundus Component Controller
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class EmundusControllerExport_select_columns extends JControllerLegacy {

	function display(){
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ){
			$default = 'export_select_columns';
			JRequest::setVar('view', $default );
		}
		parent::display();
    }	
	/**
	 * Get application form elements to display in XLS file
	*/
	function send_elements() {
		error_reporting(0);
		/*error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);*/
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		$current_user = JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id) && !EmundusHelperAccess::isEvaluator($current_user->id) && !EmundusHelperAccess::isPartner($current_user->id)) 
			die( JText::_('RESTRICTED_ACCESS') );

		$view = JRequest::getVar('v', null, 'GET');
		$session = JFactory::getSession();
		$cid = $session->get( 'uid' );
		$quick_search = $session->get( 'quick_search' );

		//require_once(JPATH_BASE.DS.'libraries'.DS.'emundus'.DS.'export_xls'.DS.'xls_'.$view.'.php');
		$elements = JRequest::getVar('ud', null, 'POST', 'array', 0);
		//export_xls($cid, $elements); 
		$xls = $this->getModel('xls_'.$view);
		$xls->export_xls($cid, $elements); 
		exit();
		}
} //END CLASS
?>