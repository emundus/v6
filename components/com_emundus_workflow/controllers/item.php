<?php
    defined('_JEXEC') or die ('Restricted access');
    jimport('joomla.application.component.controller');

    class EmundusWorkflowController extends JControllerLegacy {
        var $model = null;

        public function __construct($config=array()) {
            parent::__construct($config);
            $this->model = $this->getModel('item'); //get item model
        }

        public function createItem() {

        }

        public function deleteItem($id) {}

        public function updateItemOrder($id,$old_order,$new_order) {}

        public function getOrder($id) {}

        public function getParent($id) {}

        public function getChild($id) {}
    }
