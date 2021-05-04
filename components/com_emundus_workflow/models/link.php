<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_workflow/models');

class EmundusworkflowModellink extends JModelList {
    var $db = null;
    var $query = null;
    var $workflow_model = null;

    public function __construct($config = array()) {
        parent::__construct($config);

        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);

        $this->workflow_model = JModelLegacy::getInstance('workflow', 'EmundusworkflowModel');          /// get workflow_model
    }

    //// create link between item
    public function createLink($data) {
        if(!empty($data)) {
            try {
                $this->query->clear()
                    ->insert($this->db->quoteName('#__emundus_workflow_links'))
                    ->columns($this->db->quoteName(array_keys($data)))
                    ->values(implode(',', $this->db->quote(array_values($data))));
                $this->db->setQuery($this->query);
                $this->db->execute();
                $_newID = $this->db->insertid();

                $this->workflow_model->workflowLastActivity($data['workflow_id']);

                return (object)['message'=>true,'data'=>$_newID];
            }
            catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot create link : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //DELETE LINK BETWEEN TWO ITEMS
    public function deleteLink($data) {
        if(!empty($data)) {
            try {
                $this->query->clear()
                    ->delete($this->db->quoteName('#__emundus_workflow_links'))
                    ->where($this->db->quoteName('#__emundus_workflow_links.id') . '=' . (int)$data['id']);

                $this->db->setQuery($this->query);
                $this->db->execute();

                /// update workflow logs
                $this->workflow_model->workflowLastActivity($data['workflow_id']);

                return (object)['message'=>true];
            }
            catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot delete link : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //get link by item (_from or _to)
    public function getLinkByItem($mode=null,$data) {
        if(!empty($data)) {
            try {
                if ($mode == 'from') {
                    //get link by from
                    $this->query->clear()
                        ->select('#__emundus_workflow_links.*')
                        ->from($this->db->quoteName('#__emundus_workflow_links'))
                        ->where($this->db->quoteName('#__emundus_workflow_links.from') . '=' . (int)$data['_from']);
                }

                else if ($mode == 'to') {
                    //get link by to
                    $this->query->clear()
                        ->select('#__emundus_workflow_links.*')
                        ->from($this->db->quoteName('#__emundus_workflow_links'))
                        ->where($this->db->quoteName('#__emundus_workflow_links.to') . '=' . (int)$data['_to']);
                }

                else {
                    //get link by both from and to
                    $this->query->clear()
                        ->select('#__emundus_workflow_links.*')
                        ->from($this->db->quoteName('#__emundus_workflow_links'))
                        ->where($this->db->quoteName('#__emundus_workflow_links.from') . '=' . (int)$data['_from'])
                        ->andWhere($this->db->quoteName('#__emundus_workflow_links.from') . '=' . (int)$data['_to']);
                }

                $this->db->setQuery($this->query);
                return $this->db->loadObjectList();
            }
            catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot get link by item : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //get all links by step
    public function getAllLinksByStep($data) {
        if(!empty($data)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_workflow_links.*')
                    ->from($this->db->quoteName('#__emundus_workflow_links'))
                    ->where($this->db->quoteName('#__emundus_workflow_links.step_id') . '=' . (int)$data);

                $this->db->setQuery($this->query);
                return $this->db->loadObjectList();
            }
            catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot get links : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

//    // auto-find the item having the same output status --> params --> $data['wid']
//    public function matchAllLinksByWorkflow($wid) {
//        if(!empty($wid)) {
//            try {
//                ///find all 'params' of workflow --> return an object list
//                $this->query->clear()
//                    ->select('#__emundus_workflow_item.*')
//                    ->from($this->db->quoteName('#__emundus_workflow_item'))
//                    ->where($this->db->quoteName('#__emundus_workflow_item.workflow_id') . '=' . (int)$wid);
//
//                $this->db->setQuery($this->query);
//                $_allParams = $this->db->loadObjectList();
//
//                $_inputStatusList = array();
//                $_outputStatusList = array();
//
//                $_exportData = array();
//
//                foreach ($_allParams as $key => $value) {
//                    if ($value->item_id == 1 || $value->item_id == 5 || $value->item_name == 'Initialisation' || $value->item_name == 'Cloture' || $value->item_id == 4 || $value->item_name == 'Message') {
//                        unset($_allParams[$key]);
//                    } else {
//                        $_outputStatusList[$value->id] = json_decode($value->params)->outputStatus;
//                        $_inputStatusList[$value->id] = json_decode($value->params)->inputStatus;
//                    }
//                }
//
//                foreach ($_inputStatusList as $key => $val) {
//                    $_inArray = explode(',', $val);
//
//                    foreach ($_outputStatusList as $k => $v) {
//                        if (in_array($v, $_inArray)) {
//
//                            $_lst = $key . '...' . $k;
//                            if ($key !== $k) {
//                                array_push($_exportData, $_lst);
//                            } else {
//                            }
//                        }
//                    }
//                }
//
//                return $_exportData;
//            } catch (Exception $e) {
//                JLog::add('component/com_emundus_workflow/models/item | Cannot find suitable link between item : ' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
//                return $e->getMessage();
//            }
//        }
//        else {
//            return false;
//        }
//    }

//    public function matchLinkByItem($data) {
//        $db = JFactory::getDbo();
//        $query = $db->getQuery(true);           //get current params
//        $query1 = $db->getQuery(true);          //get all remaining params
//
//        try {
//            $query->clear()
//                ->select('#__emundus_workflow_item.*')
//                ->from($db->quoteName('#__emundus_workflow_item'))
//                ->where($db->quoteName('#__emundus_workflow_item.id') . '=' . (int)$data['id']);
//            $db->setQuery($query);
//
//            $_currentParams = $db->loadObject();
//            $_currentInputStatus = explode(',', json_decode($_currentParams->params)->inputStatus);     //return an array
//
//            $query1->clear()
//                ->select('#__emundus_workflow_item.*')
//                ->from($db->quoteName('#__emundus_workflow_item'))
//                ->where($db->quoteName('#__emundus_workflow_item.workflow_id') . '=' . (int)$data['wid'])
//                ->andWhere($db->quoteName('#__emundus_workflow_item.id') . '!=' . (int)$data['id']);
//
//            $db->setQuery($query1);
//            $_allParams = $db->loadObjectList();
//
//            //// match $_currentParams vs $_allParams
//            foreach($_allParams as $key => $value) {
//                if($value->item_id == 1 || $value->item_id == 4 || $value->item_id == 5 || $value->item_name == 'Initialisation' || $value->item_name == 'Message' || $value->item_name == 'Cloture') {
//                    unset($_allParams[$key]);
//                }
//                else {
//                    if(in_array(json_decode($value->params)->outputStatus,$_currentInputStatus)) {
//                        return $_allParams[$key]->id;
//                    }
//                    else {}
//                }
//            }
//        }
//        catch(Exception $e) {
//            JLog::add('component/com_emundus_workflow/models/item | Cannot find suitable link : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
//            return $e->getMessage();
//        }
//    }

//    //check whether there are links from an item --> if yes --> remove
//    public function checkExistLink($data) {
//
//        $db = JFactory::getDbo();
//        $query = $db->getQuery(true);
//
//        try {
//            $query->clear()
//                ->select('count(*)')
//                ->from($db->quoteName('#__emundus_workflow_links'))
//                ->where($db->quoteName('#__emundus_workflow_links.to') . '=' . (int)$data);
//            $db->setQuery($query);
//
//            $_count = $db->loadResult();
//
//            if((int)$_count > 0 ) {
//                return true;
//            }
//            else {
//                return false;
//            }
//            die; //// fill in up
//        }
//        catch(Exception $e) {
//            JLog::add('component/com_emundus_workflow/models/item | Cannot check the link : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
//            return $e->getMessage();
//        }
//    }
}