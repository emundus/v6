<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_workflow/models');

class EmundusworkflowModelstep extends JModelList {
        //// constructor
    var $db = null;
    var $query = null;

    public function __construct($config=array()) {
        parent::__construct($config);
        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);
    }

    ////get all steps of workflow
    public function getAllStepsByWorkflow($data) {
        try {
            $this->query->clear()
                ->select('#__emundus_workflow_step.*')
                ->from($this->db->quoteName('#__emundus_workflow_step'))
                ->where($this->db->quoteName('#__emundus_workflow_step.workflow_id') . '=' . (int)$data);

            $this->db->setQuery($this->query);

            return $this->db->loadObjectList();   //get all steps by workflow
        }
        catch(Exception $e) {
            JLog::add('component/com_emundus_workflow/models/step | Cannot get all steps' . preg_replace("/[\r\n]/"," ",$this->query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
            return $e->getMessage();
        }
    }

    ////create new step --> data['step_name'], data['workflow_id'], data['step_in'], data['step_out']
    public function createStep($data) {
        if(!empty($data) or isset($data)) {
            try {
                $this->query->clear()
                    ->insert($this->db->quoteName('#__emundus_workflow_step'))
                    ->columns($this->db->quoteName(array_keys($data)))
                    ->values(implode(',', $this->db->quote(array_values($data))));

                $this->db->setQuery($this->query);

                $this->db->execute();
                return $this->db->insertid();
            }
            catch(Exception $e) {
                JLog::add('component/com_emundus_workflow/models/step | Cannot create new step' . preg_replace("/[\r\n]/"," ",$this->query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    ////delete a step which exists
    public function deleteStep($data) {
        if(!empty($data) or !isset($data)) {
            $this->query->clear()
                ->delete($this->db->quoteName('#__emundus_workflow_step'))
                ->where($this->query->quoteName('#__emundus_workflow_step.id') . '=' . (int)$data);

            $this->db->setQuery($this->query);
            return $this->db->execute();
        }
        else {
            return false;
        }
    }

    ////get all items of step
    public function getAllItemByStep($sid) {
        //do something here
    }
    ///
}