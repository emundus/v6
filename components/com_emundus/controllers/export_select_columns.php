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
		if ( ! JFactory::getApplication()->input->get( 'view' ) ){
			$default = 'export_select_columns';
			JFactory::getApplication()->input->set('view', $default );
		}
		parent::display();
    }

    function __construct($config = array()){
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'programme.php');

        parent::__construct($config);
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

		$view = JFactory::getApplication()->input->get('v', null, 'GET');
		$session = JFactory::getSession();
		$cid = $session->get( 'uid' );
		$quick_search = $session->get( 'quick_search' );

		//require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'export_xls'.DS.'xls_'.$view.'.php');
		$elements = JFactory::getApplication()->input->get('ud', null, 'POST', 'array', 0);
		//export_xls($cid, $elements);
		$xls = $this->getModel('xls_'.$view);
		$xls->export_xls($cid, $elements);
		exit();
		}
*/

    public function getformtags(){
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;

        $model = $this->getModel('export_select_columns');

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $prg = $jinput->getString('code', null);
            $camp = $jinput->getInt('camp', null);
            $profile = $jinput->getInt('profile', null);

            $code = array();
            $camps = array();
            $code[] = $prg;
            $camps[] = $camp;
            $elements = EmundusHelperFiles::getElements($code, $camps, [], $profile);

            $allowed_groups = EmundusHelperAccess::getUserFabrikGroups($user->id);
            if ($allowed_groups !== true) {
                foreach ($elements as $key => $elt) {
                    if (!in_array($elt->group_id, $allowed_groups)) {
                        unset($elements[$key]);
                    }
                }
            }

            $tab = array('status' => true, 'msg' => JText::_("ACCESS_DENIED"), 'tags' => $elements);
        }
        echo json_encode((object) $tab);
        exit;
    }

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
