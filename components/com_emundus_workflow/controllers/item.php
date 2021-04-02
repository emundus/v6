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

            $_cit = $this->model;
            $data['last_created'] = date('Y-m-d H:i:s');
            $data['saved_by'] = $user->id;

            $_items = $_cit->createItem($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("ITEM_CREATED"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_CREATE_ITEM"), 'data' => $_items);
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
            $data = $jinput->getRaw('id');
            $_cit = $this->model;

            $_items = $_cit->deleteItem($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("ITEM_DELETED"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_DELETE_ITEM"), 'data' => $_items);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updateItemOrder() {
    }

    public function getcounditembyid() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');
            $_cit = $this->model;

            $_items = $_cit->getCountItemByID($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("ITEM_FOUND"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("ITEM_NOT_FOUND"), 'data' => $_items);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getallitems() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $_wit = $this->model;

            //do stuff
            $items = $_wit->getAllItems();
            if (count($items) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_("ITEMS_RETRIEVED"), 'data' => $items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("NO_ITEMS_FOUND"), 'data' => $items);
            }

        }
        echo json_encode((object)$tab);
        exit;
    }

    // get all items by workflow
    public function getallitemsbyworkflow() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');
            $_cit = $this->model;
            $_items = $_cit->getAllItemsByWorkflowId($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_ITEM_FROM_WORKFLOW"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_GET_ITEM_FROM_WORKFLOW"), 'data' => $_items);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    // get item by id
    public function getitem() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('id');
            $_cit = $this->model;


            $_items = $_cit->getItemByID($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("ITEM_GET"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("ITEM_NOT_GET"), 'data' => $_items);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //save all items
    public function saveworkflow() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('data');
            $_cit = $this->model;
            $data['saved_by'] = $user->id;
            $_items = $_cit->saveWorkflow($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("WORKFLOW_SAVED"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_SAVE_WORKFLOW"), 'data' => $_items);
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

            $_cit = $this->model;
            $_items = $_cit->getInitIDByWorkflow($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("INIT_GET"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("INIT_CANNOT_GET"), 'data' => $_items);
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

            $_cit = $this->model;
            $_items = $_cit->createLink($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("LINK_CREATED"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("LINK_CANNOT_CREATED"), 'data' => $_items);
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

            $_cit = $this->model;
            $_items = $_cit->deleteLink($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("LINK_DELETED"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("LINK_CANNOT_DELETED"), 'data' => $_items);
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

            $_cit = $this->model;
            $_items = $_cit->getAllLinksByWorkflowID($data);

            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_LINK"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_GET_LINK"), 'data' => $_items);
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
            $data = $jinput->getRaw('params');

            $_cit = $this->model;

            $_items = $_cit->updateParamsByItemID($data);


            if ($_items) {
                $tab = array('status' => 1, 'msg' => JText::_("UPDATE_PARAMS"), 'data' => $_items);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_UPDATE_PARAMS"), 'data' => $_items);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

//    public function getcurrentinputstatusbyitem() {
//        $user = JFactory::getUser();
//
//        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
//            $result = 0;
//            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
//        } else {
//            $jinput = JFactory::getApplication()->input;
//            $data = $jinput->getRaw('id');
//
//            $_cit = $this->model;
//
//            $_status = $_cit->getStatusByCurrentItem($data, 'in');
//
//            if ($_status) {
//                $tab = array('status' => 1, 'msg' => JText::_("INPUT_STATUS_BY_ITEM_ID"), 'data' => $_status);
//            } else {
//                $tab = array('status' => 0, 'msg' => JText::_("FAILED_INPUT_STATUS_BY_ITEM_ID"), 'data' => $_status);
//            }
//        }
//        echo json_encode((object)$tab);
//        exit;
//    }
//
//    public function getcurrentoutputstatusbyitem() {
//        $user = JFactory::getUser();
//
//        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
//            $result = 0;
//            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
//        } else {
//            $jinput = JFactory::getApplication()->input;
//            $data = $jinput->getRaw('id');
//
//            $_cit = $this->model;
//
//            $_status = $_cit->getStatusByCurrentItem($data, 'out');
//
//            if ($_status) {
//                $tab = array('status' => 1, 'msg' => JText::_("OUTPUT_STATUS_BY_ITEM_ID"), 'data' => $_status);
//            } else {
//                $tab = array('status' => 0, 'msg' => JText::_("FAILED_OUTPUT_STATUS_BY_ITEM_ID"), 'data' => $_status);
//            }
//        }
//        echo json_encode((object)$tab);
//        exit;
//    }

    //// GOAL --> Get all current status (input and output) by item
    public function getcurrentstatusbyitem() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('id');

            $_cit = $this->model;

            $_statusIn = $_cit->getStatusByCurrentItem($data, 'in');
            $_statusOut = $_cit->getStatusByCurrentItem($data, 'out');

            if (!empty($_statusIn) and !empty($_statusOut)) {
                $tab = array('status' => 1, 'msg' => JText::_("CURRENT_STATUS_BY_ITEM_ID"), 'dataIn' => $_statusIn, 'dataOut' => $_statusOut);
            }

            // no output status
            else if($_statusIn and empty($_statusOut)) {
                $tab = array('status' => 1, 'msg' => JText::_("CURRENT_STATUS_BY_ITEM_ID"), 'dataIn' => $_statusIn, 'dataOut' => null);
            }

            // no input status
            else if(empty($_statusIn) and $_statusOut) {
                $tab = array('status' => 1, 'msg' => JText::_("CURRENT_STATUS_BY_ITEM_ID"), 'dataIn' => null, 'dataOut' => $_statusOut);
            }

            // no input and output status
            else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_GET_CURRENT_STATUS_BY_ITEM_ID"), 'dataIn' => null, 'dataOut' => null);
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

            $_cit = $this->model;

            $_status = $_cit->getAvailableStatusByItem($data, 'in');

            if ($_status) {
                $tab = array('status' => 1, 'msg' => JText::_("AVAILABLE_INPUT_STATUS"), 'data' => $_status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("NO_AVAILABLE_INPUT_STATUS"), 'data' => $_status);
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
                $tab = array('status' => 1, 'msg' => JText::_("AVAILABLE_OUTPUT_STATUS"), 'data' => $_status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("NO_AVAILABLE_OUTPUT_STATUS"), 'data' => $_status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getnonstatusparamsbyitem() {
        $user = JFactory::getUser();

        if (!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('id');

            $_cit = $this->model;

            $_status = $_cit->getNonStatusParams($data);

            if ($_status) {
                $tab = array('status' => 1, 'msg' => JText::_("GET_NON_STATUS_PARAMS"), 'data' => $_status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_GET_NON_STATUS_PARAMS"), 'data' => $_status);
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

            $_cit = $this->model;

            $_status = $_cit->matchAllLinksByWorkflow($data);

            if ($_status) {
                $tab = array('status' => 1, 'msg' => JText::_("LINK_MATCH"), 'data' => $_status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("LINK_NO_MATCH"), 'data' => $_status);
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

            $_cit = $this->model;

            $_status = $_cit->matchLinkByItem($data);

            if ($_status) {
                $tab = array('status' => 1, 'msg' => JText::_("SPECIFIC_LINK_MATCH"), 'data' => $_status, 'permission' => 'available');
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("CANNOT_GET_SPECIFIC_LINK"), 'data' => $_status, 'permission' => 'unavailable');
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

            $_cit = $this->model;

            $_status = $_cit->checkMatchingStatus($data);

            if ($_status) {
                $tab = array('status' => 1, 'msg' => JText::_("MATCHING_ITEM"), 'data' => $_status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("UNMATCHING_ITEM"), 'data' => $_status);
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

            $_cit = $this->model;

            $_status = $_cit->checkExistLink($data);

            if ($_status) {
                $tab = array('status' => 1, 'msg' => JText::_("LINK_EXISTS"), 'data' => $_status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("LINK_DOES_NOT_EXIST"), 'data' => $_status);
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

//                var_dump($data);die;

            $_cit = $this->model;

            $_status = $_cit->getLinkByItem('to',$data);

            if ($_status) {
                $tab = array('status' => 1, 'msg' => JText::_("LINK_EXISTS_YUPP"), 'data' => $_status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_("LINK_DOES_NOT_EXIST_OOPS"), 'data' => $_status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }
}
