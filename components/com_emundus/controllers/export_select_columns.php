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
	/*
	function send_elements() {
		error_reporting(0);

		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		$current_user = JFactory::getUser();
		if(!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id) && !EmundusHelperAccess::isEvaluator($current_user->id) && !EmundusHelperAccess::isPartner($current_user->id))
			die( JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS') );

		$view = JRequest::getVar('v', null, 'GET');
		$session = JFactory::getSession();
		$cid = $session->get( 'uid' );
		$quick_search = $session->get( 'quick_search' );

		//require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'export_xls'.DS.'xls_'.$view.'.php');
		$elements = JRequest::getVar('ud', null, 'POST', 'array', 0);
		//export_xls($cid, $elements);
		$xls = $this->getModel('xls_'.$view);
		$xls->export_xls($cid, $elements);
		exit();
		}
*/
    /**
     * Gets all eMundus Tags from tags_table
     */
    public function getalltags(){
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;

        $model = $this->getModel('export_select_columns');

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id))
        {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        }
        else
        {
            $tags = $model->getAllTags();
        }
        echo json_encode((object) [
            'status' => true,
            'tags' => $tags
        ]);
        exit;
    }


} //END CLASS
?>
