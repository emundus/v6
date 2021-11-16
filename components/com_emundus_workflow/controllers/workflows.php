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
jimport('joomla.application.component.model');

class EmundusworkflowControllerworkflows extends JControllerLegacy {
    var $model = null;

    public function __construct($config=array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        parent::__construct($config);
        $this->model = $this->getModel("workflows");

        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_onboard/models');
    }

    //get all workflows
    public function getallworkflows() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status' => 0, 'msg' => JText::_("ACCESS_DENIED"));
        }
        else {
            header('Content-Type: application/json');
            $_workflows = $this->model->getAllWorkflows();

            if (count($_workflows) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_WORKFLOWS_SUCCESSFULLY"), 'data' => $_workflows, 'count' => count($_workflows));
            }
            else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_ALL_WORKFLOWS_FAILED"), 'data' => $_workflows, 'count' => count($_workflows));
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

}