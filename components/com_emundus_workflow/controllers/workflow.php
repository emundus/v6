<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      James Dean
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusworkflowControllerworkflow extends JControllerLegacy {

    var $model= null;
    public function __construct($config=array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        parent::__construct($config);
        $this->model = $this->getModel("workflow");
    }

    public function createworkflow() {
        $user = JFactory::getUser();


        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status'=> $result, 'msg' => JText::_('ACCESS_DENIED'));
        }
        else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_wid = $this->model;

            $_workflow = $_wid->createWorkflow($data);

            if($_workflow) {
                $tab = array('status' => 1, 'msg' => JText::_('WORKFLOW_CREATED'), 'data' => $_workflow);
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_('CANNOT_CREATE_WORKFLOW'), 'data' => $_workflow);
            }
            echo json_encode((object)$tab);
            exit;
        }
    }
}
