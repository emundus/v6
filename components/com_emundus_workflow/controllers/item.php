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

class EmundusworkflowControlleritem extends JControllerLegacy {
    var $model = null;

    public function __construct($config = array()) {
        require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'access.php');
        parent::__construct($config);
        $this->model = $this->getModel('item'); //get item model
    }

    public function createitem() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_item = $this->model->createItem($data);

            if ($_item) {
                $tab = array('status' => 1, 'msg' => JText::_("CREATE_ITEM_SUCCESSFULLY"), 'data' => $_item);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CREATE_ITEM_FAILED"), 'data' => $_item);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function deleteitem() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_result = $this->model->deleteItem($data);

            if ($_result) {
                $tab = array('status' => 1, 'msg' => JText::_("DELETE_ITEM_SUCCESSFULLY"), 'data' => $_result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("DELETE_ITEM_FAILED"), 'data' => $_result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getcounditembyid() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_count = $this->model->getCountItemByID($data);

            if ($_count) {
                $tab = array('status' => 1, 'msg' => JText::_("ITEM_FOUND"), 'data' => $_count);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("ITEM_NOT_FOUND"), 'data' => $_count);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getitemmenu() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $_menu = $this->model->getItemMenu();
            if (count($_menu) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_MENU_SUCCESSFULLY"), 'data' => $_menu);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_MENU_FAILED"), 'data' => $_menu);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    // get all items by workflow
    public function getallitemsbystep() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_items = $this->model->getAllItemsByStep($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ITEM_FROM_STEP_SUCCESSFULLY"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_ITEM_FROM_STEP_FAILED"), 'data' => $_items);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    // get item by id --> used to config modal
    public function getitem() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('id');

            $_item = $this->model->getItemByID($data);

            if ($_item) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ITEM_SUCCESSFULLY"), 'data' => $_item);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_ITEM_FAILED"), 'data' => $_item);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //save all items
    public function saveitem() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_result = $this->model->saveItemById($data);

            if ($_result) {
                $tab = array('status' => 1, 'msg' => JText::_("SAVE_WORKFLOW_SUCCESSFULLY"), 'data' => $_result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("SAVE_WORKFLOW_FAILED"), 'data' => $_result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //get init id by workflow
    public function getinitid() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_inititem = $this->model->getInitIDByWorkflow($data);

            if ($_inititem) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_INIT_BLOC_SUCCESSFULLY"), 'data' => $_inititem);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_INIT_BLOC_FAILED"), 'data' => $_inititem);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //create new link
    public function createlink() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_link = $this->model->createLink($data);

            if ($_link) {
                $tab = array('status' => 1, 'msg' => JText::_("CREATE_LINK_SUCCESSFULLY"), 'data' => $_link);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CREATE_LINK_FAILED"), 'data' => $_link);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //delete link
    public function deletelink() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('id');

            $_result = $this->model->deleteLink($data);

            if ($_result) {
                $tab = array('status' => 1, 'msg' => JText::_("DELETE_LINK_SUCCESSFULLY"), 'data' => $_result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("DELETE_LINK_FAILED"), 'data' => $_result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //get all link
    public function getalllinks() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_links = $this->model->getAllLinksByStep($data);

            if ($_links) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_LINK_SUCCESSFULLY"), 'data' => $_links);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_LINK_FAILED"), 'data' => $_links);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //update params
    public function updateparams() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_result = $this->model->updateParamsByItemID($data);

            if ($_result) {
                $tab = array('status' => 1, 'msg' => JText::_("UPDATE_BLOC_PARAMS_SUCCESSFULLY"), 'data' => $_result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("UPDATE_BLOC_PARAMS_FAILED"), 'data' => $_result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //// GOAL --> Get all current status (input and output) by item
    public function getcurrentstatusbyitem() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('id');

            $_statusIn = $this->model->getStatusByCurrentItem($data, 'in');
            $_statusOut = $this->model->getStatusByCurrentItem($data, 'out');

            if (!empty($_statusIn) and !empty($_statusOut)) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ALL_CURRENT_STATUS_SUCCESSFULLY"), 'dataIn' => $_statusIn, 'dataOut' => $_statusOut);
            }

            // no output status
            else if($_statusIn and empty($_statusOut)) {
                $tab = array('status' => 1, 'msg' => JText::_("JUST_GET_CURRENT_INPUT_STATUS"), 'dataIn' => $_statusIn, 'dataOut' => null);
            }

            // no input status
            else if(empty($_statusIn) and $_statusOut) {
                $tab = array('status' => 1, 'msg' => JText::_("JUST_GET_CURRENT_OUTPUT_STATUS"), 'dataIn' => null, 'dataOut' => $_statusOut);
            }

            // no input and output status
            else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_ALL_CURRENT_STATUS_FAILED"), 'dataIn' => null, 'dataOut' => null);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getavailableinputstatus() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_status = $this->model->getAvailableStatusByItem($data, 'in');

            if ($_status) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_AVAILABLE_INPUT_STATUS_SUCCESSFULLY"), 'data' => $_status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_AVAILABLE_INPUT_STATUS_FAILED"), 'data' => $_status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getavailableoutputstatus() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_cit = $this->model;

            $_status = $_cit->getAvailableStatusByItem($data, 'out');

            if ($_status) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_AVAILABLE_OUTPUT_STATUS_SUCCESSFULLY"), 'data' => $_status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_AVAILABLE_OUTPUT_STATUS_FAILED"), 'data' => $_status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    // Next step: Combine getnonstatusparamsbyitem() with getcurrentstatusbyitem() --> reduce 1 SQL query
    public function getnonstatusparamsbyitem() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('id');

            $_params = $this->model->getNonStatusParams($data);

            if ($_params) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_NON_STATUS_PARAMS_SUCCESSFULLY"), 'data' => $_params);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("GET_NON_STATUS_PARAMS_FAILED"), 'data' => $_params);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function matchalllinksbyworkflow() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_links = $this->model->matchAllLinksByWorkflow($data);

            if ($_links) {
                $tab = array('status' => 1, 'msg' => JText::_("MATCH_WORKFLOW_LINKS_SUCCESSFULLY"), 'data' => $_links);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("MATCH_WORKFLOW_LINKS_FAILED"), 'data' => $_links);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function matchalllinksbyitem() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_links = $this->model->matchLinkByItem($data);

            if ($_links) {
                $tab = array('status' => 1, 'msg' => JText::_("MATCH_BLOC_LINK_SUCCESSFULLY"), 'data' => $_links, 'permission' => 'available');
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("MATCH_BLOC_LINK_FAILED"), 'data' => $_links, 'permission' => 'unavailable');
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function checkmatchingitems() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_result = $this->model->checkMatchingStatus($data);

            if ($_result) {
                $tab = array('status' => 1, 'msg' => JText::_("MATCH_ITEM_SUCCESSFULLY"), 'data' => $_result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("MATCH_ITEM_FAILED"), 'data' => $_result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function checkexistlink() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_result = $this->model->checkExistLink($data);

            if ($_result) {
                $tab = array('status' => 1, 'msg' => JText::_("LINK_EXISTS"), 'data' => $_result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("LINK_DOES_NOT_EXIST"), 'data' => $_result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getlinkbytoitem() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');

            $_result = $this->model->getLinkByItem('to',$data);

            if ($_result) {
                $tab = array('status' => 1, 'msg' => JText::_("TO_LINK_EXISTS"), 'data' => $_result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("TO_LINK_DOES_NOT_EXIST"), 'data' => $_result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }
}
