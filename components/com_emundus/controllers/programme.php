<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      Benjamin Rivalland
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusControllerProgramme extends JControllerLegacy {
    var $_user = null;
    var $_db = null;

    function __construct($config = array()){
        $this->_user = JFactory::getUser();
        $this->_db = JFactory::getDBO();
        parent::__construct($config);
    }

    function display($cachable = false, $urlparams = false) {
        // Set a default view if none exists
        if ( ! JRequest::getCmd( 'view' ) ) {
            $default = 'programme';
            JRequest::setVar('view', $default );
        }
        parent::display();
    }

    public function getprogrammes(){ 
        $user = JFactory::getUser();
        $view = JRequest::getVar('view', null, 'GET', 'none',0);
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);

        $model = $this->getModel('programme');   

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id))
        {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        }
        else
        {
            $programmes = $model->getProgrammes();

            if (count($programmes) > 0)
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAMMES_RETRIEVED'), 'data' => $programmes);
            else
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_PROGRAMMES'), 'data' => $programmes);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function addprogrammes(){ 
        $user = JFactory::getUser();
        $view = JRequest::getVar('view', null, 'GET', 'none',0);
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $data = JRequest::getVar('data', null, 'POST', 'none',0);

        $model = $this->getModel('programme');   

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id))
        {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        }
        else
        {
            $result = $model->addProgrammes($data);

            if ($result === true)
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAMMES_ADDED'), 'data' => $result);
            else
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_PROGRAMMES'), 'data' => $result);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function editprogrammes() {
        $user = JFactory::getUser();
        $view = JRequest::getVar('view', null, 'GET', 'none',0);
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $data = JRequest::getVar('data', null, 'POST', 'none',0);

        $model = $this->getModel('programme');   

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id))
        {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        }
        else
        {
            $result = $model->editProgrammes($data);

            if ($result === true)
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAMMES_EDITED'), 'data' => $result);
            else
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_EDIT_PROGRAMMES'), 'data' => $result);
        }
        echo json_encode((object)$tab);
        exit;
    }


    /**
     * Adds a programme to the user's list of favorites.
     */
    public function favorite() {

    	$jinput = JFactory::getApplication()->input;
    	$pid = $jinput->post->getInt('programme_id');
    	$uid = $jinput->post->getInt('user_id');

    	if (empty($uid))
		    $uid = JFactory::getUser()->id;

    	$result = new stdClass();
	    $result->status = false;

    	if (empty($uid) || empty($pid)) {
    		echo json_encode($result);
    		exit;
	    }

	    require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'programme.php');
	    $m_programme = new EmundusModelProgramme();
	    $result->status = $m_programme->favorite($pid, $uid);

	    echo json_encode($result);
	    exit;

    }


	/**
	 * Removes a programme from the user's list of favorites.
	 */
	public function unfavorite() {

		$jinput = JFactory::getApplication()->input;
		$pid = $jinput->post->getInt('programme_id');
		$uid = $jinput->post->getInt('user_id');

		if (empty($uid))
			$uid = JFactory::getUser()->id;

		$result = new stdClass();
		$result->status = false;

		if (empty($uid) || empty($pid)) {
			echo json_encode($result);
			exit;
		}

		require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'programme.php');
		$m_programme = new EmundusModelProgramme();
		$result->status = $m_programme->unfavorite($pid, $uid);

		echo json_encode($result);
		exit;

	}

}
