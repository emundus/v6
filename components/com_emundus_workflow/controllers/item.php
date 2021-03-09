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

        public function __construct($config=array()) {
            require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
            parent::__construct($config);
            $this->model = $this->getModel('item'); //get item model
        }

        public function createitem() {
            $user = JFactory::getUser();

            if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
                $result = 0;
                $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
            }
            else {
                $jinput = JFactory::getApplication()->input;
                $data = $jinput->getRaw('data');
                $_cit = $this->model;
                $data['last_created'] = date('Y-m-d H:i:s');

                $_items = $_cit->createItem($data);

                if($_items) {
                    $tab = array('status' => 1, 'msg' => JText::_("ITEM_CREATED"), 'data' => $_items);
                }
                else {
                    $tab = array('status' => 0, 'msg' => JText::_("CANNOT_CREATE_ITEM"), 'data' => $_items);
                }
            }
            echo json_encode((object)$tab);
            exit;
        }

        public function deleteitem() {
            $user = JFactory::getUser();

            if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
                $result = 0;
                $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
            }
            else {
                $jinput = JFactory::getApplication()->input;
                $data = $jinput->getRaw('id');
                $_cit = $this->model;

                $_items = $_cit->deleteItem($data);

                if($_items) {
                    $tab = array('status' => 1, 'msg' => JText::_("ITEM_DELETED"), 'data' => $_items);
                }
                else {
                    $tab = array('status' => 0, 'msg' => JText::_("CANNOT_DELETE_ITEM"), 'data' => $_items);
                }
            }
            echo json_encode((object)$tab);
            exit;
        }

        public function updateItemOrder() {}

        public function getcounditembyid() {
            $user = JFactory::getUser();

            if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
                $result = 0;
                $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
            }
            else {
                $jinput = JFactory::getApplication()->input;
                $data = $jinput->getRaw('data');
                $_cit = $this->model;

                $_items = $_cit->getCountItemByID($data);

                if($_items) {
                    $tab = array('status' => 1, 'msg' => JText::_("ITEM_FOUND"), 'data' => $_items);
                }
                else {
                    $tab = array('status' => 0, 'msg' => JText::_("ITEM_NOT_FOUND"), 'data' => $_items);
                }
            }
            echo json_encode((object)$tab);
            exit;
        }

        public function getallitems() {
            $user = JFactory::getUser();

            if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
                $result = 0;
                $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
            }
            else {
                $_wit = $this->model;

                //do stuff
                $items= $_wit->getAllItems();
                if (count($items) > 0) {
                    $tab = array('status' => 1, 'msg' => JText::_("ITEMS_RETRIEVED"), 'data' => $items);
                }
                else {
                    $tab = array('status' => 0, 'msg' => JText::_("NO_ITEMS"), 'data' => $items);
                }

            }
            echo json_encode((object)$tab);
            exit;
        }

        // get all items by workflow
        public function getallitemsbyworkflow() {
            $user = JFactory::getUser();

            if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
                $result = 0;
                $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
            }
            else {
                $jinput = JFactory::getApplication()->input;
                $data = $jinput->getRaw('data');
                $_cit = $this->model;
                $_items = $_cit->getAllItemsByWorkflowId($data);

                if($_items) {
                    $tab = array('status' => 1, 'msg' => JText::_("GET_ITEM_FROM_WORKFLOW"), 'data' => $_items);
                }
                else {
                    $tab = array('status' => 0, 'msg' => JText::_("CANNOT_GET_ITEM_FROM_WORKFLOW"), 'data' => $_items);
                }
            }
            echo json_encode((object)$tab);
            exit;
        }

        // get item by id
        public function getitem() {
            $user = JFactory::getUser();

            if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
                $result = 0;
                $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
            }
            else {
                $jinput = JFactory::getApplication()->input;
                $data = $jinput->getRaw('id');
                $_cit = $this->model;

                $_items = $_cit->getItemByID($data);

                if($_items) {
                    $tab = array('status' => 1, 'msg' => JText::_("ITEM_GET"), 'data' => $_items);
                }
                else {
                    $tab = array('status' => 0, 'msg' => JText::_("ITEM_NOT_GET"), 'data' => $_items);
                }
            }
            echo json_encode((object)$tab);
            exit;
        }

        //save all items
        public function saveitems() {
            $user = JFactory::getUser();

            if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
                $result = 0;
                $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
            }
            else {
                $jinput = JFactory::getApplication()->input;
                $data = $jinput->getRaw('data');
                $_cit = $this->model;
                $_items = $_cit->saveItems($data);

                if($_items) {
                    $tab = array('status' => 1, 'msg' => JText::_("ITEM_SAVED"), 'data' => $_items);
                }
                else {
                    $tab = array('status' => 0, 'msg' => JText::_("ITEM_CANNOT_SAVED"), 'data' => $_items);
                }
            }
            echo json_encode((object)$tab);
            exit;
        }

        //get init id by workflow
        public function getinitid() {
            $user = JFactory::getUser();

            if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
                $result = 0;
                $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
            }
            else {
                $jinput = JFactory::getApplication()->input;
                $data = $jinput->getRaw('data');

                $_cit = $this->model;
                $_items = $_cit->getInitIDByWorkflow($data);

                if($_items) {
                    $tab = array('status' => 1, 'msg' => JText::_("INIT_GET"), 'data' => $_items);
                }
                else {
                    $tab = array('status' => 0, 'msg' => JText::_("INIT_CANNOT_GET"), 'data' => $_items);
                }
            }
            echo json_encode((object)$tab);
            exit;
        }
    }
