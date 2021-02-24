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

        public function createItem() {}

        public function deleteItem($id) {}

        public function updateItemOrder($id,$old_order,$new_order) {}

        public function getallitems() {
            $user = JFactory::getUser();

            if(!EmundusworkflowHelperAccess::asCoordinatorAccessLevel($user->id)) {
                $result = 0;
                $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
            }
            else {
                $_wit = $this->model;
                //$jinput = JFactory::getApplication()->input;

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
            //return json_encode((object)$tab);
        }

        public function getOrder($id) {}

        public function getParent($id) {}

        public function getChild($id) {}
    }
