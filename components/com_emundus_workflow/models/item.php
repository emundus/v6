<?php
    defined('_JEXEC') or die('Restricted access');
    jimport('joomla.application.component.model');

    class EmundusModelWorkflowItem extends JModelList {
        public function __construct($config = array()) {
            parent::__construct($config);
            //getInstance
        }

        //create new bloc
        public function createItem($data) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            if(!empty($data)) {
                $query->insert($db->quoteName('#__emundus_workflow_item'))
                    ->columns($db->quoteName(array_keys($data)))
                    ->values(implode(',', $db->quote(array_values($data))));
                try {
                    $db->setQuery($query);
                    return $db->execute();
                }
                catch (Exception $e) {
                    JLog::add('component/com_emundus_workflow/models/workflow | Cannot create new item ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }

        public function updateItemOrder($id, $old_order, $new_order) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            if(!empty($id)) {
                try {
                    $db->setQuery($query);
                    return $db->execute();
                }
                catch(Exception $e) {
                    JLog::add('component/com_emundus_workflow/models/workflow | Cannot update item order ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }

         public function duplicateItem($data) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            if(!empty($data)) {
                try {
                    $query->insert($db->quoteName('#__emundus_workflow_item'))
                        ->columns($db->quoteName(array_keys($data)))
                        ->values(implode(',', $db->quote(array_values($data))));

                } catch (Exception $e) {
                    JLog::add('component/com_emundus_workflow/models/workflow | Cannot duplicate item ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
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
                    JLog::add('component/com_emundus_workflow/models/workflow | Cannot delete item ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }

        //get all item type
        public function getAllItems() {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            try {
                $query->select('wit.item_name')
                    ->from($db->quoteName('#__emundus_workflow_item_type','wit'));

                $db->setQuery($query);
                $db->execute();
            }
            catch(Exception $e) {
                JLog::add('component/com_emundus_workflow/models/workflow | Cannot get all item types' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }

        //get order of item
        public function getOrder($id) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            if(!empty($id)) {
                try {
                    //query
                    $query->select('wi.ordering')
                        ->from($db->quoteName('#__emundus_workflow_item','wi'))
                        ->where($db->quoteName('wi.id') . ' = '. $db->quote($id));

                    $db->setQuery($query);
                    return $db->execute();
                }
                catch(Exception $e) {
                    JLog::add('component/com_emundus_workflow/models/workflow | Cannot get item order ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }

        //get parent of item :: parent can be an array
        public function getParent($id) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            if(!empty($id)) {
                try {
                    //query
                    $query->select('wi.parent')
                        ->from($db->quoteName('#__emundus_workflow_item','wi'))
                        ->where($db->quoteName('wi.id') . ' = '. $db->quote($id));

                    $db->setQuery($query);
                    return $db->execute();
                } catch (Exception $e) {
                    JLog::add('component/com_emundus_workflow/models/workflow | Cannot get item parent ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }

        //get child of item :: child can be an array
        public function getChild($id) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            if(!empty($id)) {
                try {
                    //query
                    $query->select('wi.child')
                        ->from($db->quoteName('#__emundus_workflow_item','wi'))
                        ->where($db->quoteName('wi.id') . ' = '. $db->quote($id));

                    $db->setQuery($query);
                    return $db->execute();
                }
                catch(Exception $e) {
                    JLog::add('component/com_emundus_workflow/models/workflow | Cannot get item child ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                    return $e->getMessage();
                }
            }
            else {
                return false;
            }
        }
    }
