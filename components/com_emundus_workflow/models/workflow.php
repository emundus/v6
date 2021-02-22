<?php
    defined('_JEXEC') or die('Restricted access');
    jimport('joomla.application.component.model');

    class EmundusWorkflowGeneral extends JModelList {
        public function __construct($config=array()) {
            parent::_construct($config);
            //Do Stuff
        }

        public function createWorkflow() {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
        }

        public function deleteWorkflow($id) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            try {

            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        }

        public function getCampaign($id) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            try {
                $query->select('campaign')
                    ->from($db->quoteName('#__emundus_workflow','w'))
                    ->where($db->quoteName('w.id') . ' = '. $db->quote($id));

                $db->setQuery($query);
                return $db->execute();
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        }

//        public function getLastUser($id) {
//            $db = JFactory::getDbo();
//            $query = $db->getQuery(true);
//            try {
//                $query->select('users')
//                    ->from($db->quoteName('#__emundus_workflow','w'))
//                    ->where($db->quoteName('w.id') . ' = '. $db->quote($id));
//
//                $db->setQuery($query);
//                return $db->execute();
//            }
//            catch(Exception $e) {
//                return $e->getMessage();
//            }
//        }
//
//        public function getLastCreated($id) {
//            $db = JFactory::getDbo();
//            $query = $db->getQuery(true);
//
//            try {
//                $query->select('created_at')
//                    ->from($db->quoteName('#__emundus_workflow','w'))
//                    ->where($db->quoteName('w.id') . ' = '. $db->quote($id));
//
//                $db->setQuery($query);
//                return $db->execute();
//            }
//            catch(Exception $e) {
//                return $e->getMessage();
//            }
//        }
//
//        public function getLastUpdated($id) {
//            $db = JFactory::getDbo();
//            $query = $db->getQuery(true);
//            try {
//                $query->select('updated_at')
//                    ->from($db->quoteName('#__emundus_workflow','w'))
//                    ->where($db->quoteName('w.id') . ' = '. $db->quote($id));
//
//                $db->setQuery($query);
//                return $db->execute();
//            }
//            catch(Exception $e) {
//                return $e->getMessage();
//            }
//        }
    }
