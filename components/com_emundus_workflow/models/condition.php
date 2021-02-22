<?php
    defined('_JEXEC') or die('Restricted access');
    jimport('joomla.application.component.model');

    class EmundusModelWorkflowCondition extends JModelList {

        public function __construct($config=array()) {
            parent::_construct($config);
        }

        public function createConditionByItem($item_id,$data) {
            //check if $item_id is condition bloc or not --> if yes, check $data is empty or not
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $db->setQuery($query);
            return $db->execute();

        }

        public function updateConditionByItem($item_id,$data) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $db->setQuery($query);
            return $db->execute();
            //Do Stuff
        }

        public function getConditionByItem($item_id) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $db->setQuery($query);
            return $db->execute();
            //Do Stuff
        }
    }