<?php
    class EmundusWorkflowItem {
        public function __construct($config = array()) {
            //Do stuff
        }

        //create new item
        public function createItem($data) {
            //insert into --> table "jos_emundus_workflow_item"
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            if(!empty($data)) {
                $query->insert($db->quoteName('#__emundus_workflow_item'))
                    ->columns($db->quoteName(array_keys($data)))
                    ->values(implode(',', $db->Quote(array_values($data))));
                try {
                    $db->setQuery($query);
                    return $db->execute();
                }
                catch (Exception $e) {
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }

        public function updateItem($item, $old_params, $new_params) {

        }

        public function duplicateItem() {

        }
        //delete item
        public function deleteItem($data) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            if(count($data) > 0) {
                try {
                    $wf_conditions = [$db->quoteName('id').' IN ('.implode(", ", array_values($data)).')'];
                    $query->delete($db->quoteName('#__emundus_workflow_item'))->where($wf_conditions);
                    $db->setQuery($query);
                    return $db->execute();
                }
                catch(Exception $e) {
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }
        //get order of item
        public function getOrder($id) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            if(!empty($id)) {
                try {
                    //Do Stuff
                }
                catch(Exception $e) {
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }

        //get parent of item
        public function getParent($id) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            if(!empty($id)) {
                try {
                    //Do Stuff
                } catch (Exception $e) {
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }

        //get child of parent
        public function getChild($id) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            if(!empty($id)) {
                try {
                    //Do Stuff
                }
                catch(Exception $e) {
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }


    }
