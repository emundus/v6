<?php
    class EmundusWorkflowConditionController {
        var $model= null;

        public function __construct($config=array()) {
            $this->model = $this->getModel("workflowcondition");
            //Do Stuff
        }

        public function createConditionByItem($item_id,$data) {}

        public function updateConditionByItem($item_id,$old_condition,$new_condition) {}

        public function getConditionByItem($item_id) {}
    }