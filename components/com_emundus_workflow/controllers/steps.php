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

class EmundusworkflowControllersteps extends JControllerLegacy {
    var $model = null;

    public function __construct($config=array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        parent::__construct($config);
        $this->model = $this->getModel("steps");

        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_onboard/models');
    }

    //get all workflows
    public function getallsteps() {
        $user = JFactory::getUser();

        if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $tab = array('status' => 0, 'msg' => JText::_("ACCESS_DENIED"));
        }
        else {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getRaw('id');

            $steps = $this->model->getStepsByWorkflow($id);

            if ($steps) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_STEPS_SUCCESSFULLY"), 'data' => $steps);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_ALL_STEPS_FAILED_OR_NOTHING_STEP"), 'data' => $steps);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

}