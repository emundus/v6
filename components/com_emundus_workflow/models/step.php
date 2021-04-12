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

    //// get all steps of workflow --> params ==> wid
    /// get all steps --> get current params of this step
    public function getAllStepsByWorkflow($wid) {
        if(!empty($wid) or isset($wid)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_workflow_step.*')
                    ->from($this->db->quoteName('#__emundus_workflow_step'))
                    ->where($this->db->quoteName('#__emundus_workflow_step.workflow_id') . '=' . (int)$wid);

                $this->db->setQuery($this->query);      //set query string
                return $this->db->loadObjectList();     //get all steps by workflow
            }
            catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/step | Cannot get all steps by workflow' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //// create new step --> params ==> data['workflow_id']
    public function createStep($data) {
        if(!empty($data) or isset($data)) {
            try {

                // step 1 --> create step
                $this->query->clear()
                    ->insert($this->db->quoteName('#__emundus_workflow_step'))
                    ->columns($this->db->quoteName(array_keys($data)))
                    ->values(implode(',', $this->db->quote(array_values($data))));

                $this->db->setQuery($this->query);
                $this->db->execute();
                $_step_id = $this->db->insertid();

                // step 2 --> update workflow::last_saved, workflow::saved_by --> params ==> $_step_id
                $data['saved_by'] = JFactory::getUser()->id;
                $data['last_saved'] = date('Y-m-d H:i:s');

                /// from $_step_id --> get workflow_id
                $this->query->clear()
                    ->update($this->db->quoteName('#__emundus_workflow'))
                    ->set($this->db->quoteName('#__emundus_workflow.updated_at') . '=' . $this->db->quote($data['last_saved']) .
                        ',' . $this->db->quoteName('#__emundus_workflow.user_id') . '=' . (int)$data['saved_by'])
                    ->where($this->db->quoteName('#__emundus_workflow.id') . '=' . (int)$data['workflow_id']);

                $this->db->setQuery($this->query);
                $this->db->execute();

                return array("step_id" => $_step_id, "message" => $this->db->execute());
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

    //// delete a step which exists --> params ==> $id
    public function deleteStep($data) {

        if(!empty($data) or !isset($data)) {
            try {
                $wid = $data['wid'];

                $this->query->clear()
                    ->delete($this->db->quoteName('#__emundus_workflow_step'))
                    ->where($this->query->quoteName('#__emundus_workflow_step.id') . '=' . (int)$data['id']);

                $this->db->setQuery($this->query);
                $this->db->execute();

                // track the log of workflow
                $data = array('saved_by' => JFactory::getUser()->id, 'last_saved' => date('Y-m-d H:i:s'));

                /// from $_step_id --> get workflow_id
                $this->query->clear()
                    ->update($this->db->quoteName('#__emundus_workflow'))
                    ->set($this->db->quoteName('#__emundus_workflow.updated_at') . '=' . $this->db->quote($data['last_saved']) .
                        ',' . $this->db->quoteName('#__emundus_workflow.user_id') . '=' . (int)$data['saved_by'])
                    ->where($this->db->quoteName('#__emundus_workflow.id') . '=' . (int)$wid);

                $this->db->setQuery($this->query);
                $this->db->execute();

                return array('message' => $this->db->execute());
            }
            catch(Exception $e) {
                JLog::add('component/com_emundus_workflow/models/step | Cannot delete step' . preg_replace("/[\r\n]/"," ",$this->query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //// update step params --> input :: step_id + step_params
    public function updateStepParams($data) {
        if(!empty($data) or isset($data)) {
            try {
                $wid = $data['wid'];
                unset($data['params']['id']);

                //// case 1 --> change the step label
                if($data['step_label'] and empty($data['params'])) {
                    $this->query->clear()
                        ->update($this->db->quoteName('#__emundus_workflow_step'))
                        ->set($this->db->quoteName('#__emundus_workflow_step.step_label') . '=' . $this->db->quote($data['step_label']))
                        ->where($this->db->quoteName('#__emundus_workflow_step.id') . '=' . (int)$data['id']);
                }

                else {
                    $this->query->clear()
                        ->update($this->db->quoteName('#__emundus_workflow_step'))
                        ->set($this->db->quoteName('#__emundus_workflow_step.params') . '=' . $this->db->quote(json_encode($data['params'])))
                        ->where($this->db->quoteName('#__emundus_workflow_step.id') . '=' . (int)$data['id']);
                }

                $this->db->setQuery($this->query);
                $this->db->execute();

                // track the log of workflow
                $data = array('saved_by' => JFactory::getUser()->id, 'last_saved' => date('Y-m-d H:i:s'));

                /// from $_step_id --> get workflow_id
                $this->query->clear()
                    ->update($this->db->quoteName('#__emundus_workflow'))
                    ->set($this->db->quoteName('#__emundus_workflow.updated_at') . '=' . $this->db->quote($data['last_saved']) .
                        ',' . $this->db->quoteName('#__emundus_workflow.user_id') . '=' . (int)$data['saved_by'])
                    ->where($this->db->quoteName('#__emundus_workflow.id') . '=' . (int)$wid);

                $this->db->setQuery($this->query);
                $this->db->execute();

                return array('message' => $this->db->execute());
            }
            catch(Exception $e) {
                JLog::add('component/com_emundus_workflow/models/step | Cannot update step params' . preg_replace("/[\r\n]/"," ",$this->query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    /// get status name from stats name
    public function getStatusAttributsFromStep($sid) {
        if(!empty($sid) or isset($sid)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_setup_status.*')
                    ->from($this->db->quoteName('#__emundus_setup_status'))
                    ->where($this->db->quoteName('#__emundus_setup_status.step') . '=' . (int)$sid);
                $this->db->setQuery($this->query);
                return $this->db->loadObject();
            }
            catch(Exception $e) {
                JLog::add('component/com_emundus_workflow/models/step | Cannot get status value from status step' . preg_replace("/[\r\n]/"," ",$this->query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return false;
            }
        }
        else {
            return false;
        }
    }

    //// get current params of step --> params ==> sid
    public function getCurrentParamsByStep($sid) {

        if(!empty($sid) or isset($sid)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_workflow_step.params')
                    ->from($this->db->quoteName('#__emundus_workflow_step'))
                    ->where($this->db->quoteName('#__emundus_workflow_step.id') . '=' . (int)$sid);

                $this->db->setQuery($this->query);
                $_rawCurrentParams = $this->db->loadObject();       //get current params (raw info)

                //// parse this raw info into array
                $_exportArray = array('inputStatus' => array(), 'outputStatus' => array(), 'startDate' => array(), 'endDate' => array(), 'notes' => array());

                $_exportArray['inputStatus'] = json_decode($_rawCurrentParams->params)->inputStatus;
                $_exportArray['outputStatus'] = json_decode($_rawCurrentParams->params)->outputStatus;
                $_exportArray['startDate'] = json_decode($_rawCurrentParams->params)->startDate;
                $_exportArray['endDate'] = json_decode($_rawCurrentParams->params)->endDate;
                $_exportArray['notes'] = json_decode($_rawCurrentParams->params)->notes;

                $_exportArray['inputStatusName'] = ($this->getStatusAttributsFromStep($_exportArray['inputStatus']))->value;
                $_exportArray['outputStatusName'] = ($this->getStatusAttributsFromStep($_exportArray['outputStatus']))->value;

                return $_exportArray;
            }
            catch(Exception $e) {
                JLog::add('component/com_emundus_workflow/models/step | Cannot get current params of step' . preg_replace("/[\r\n]/"," ",$this->query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    ////get all available status (input + output) --> if $input == empty --> set (-1) // if $output == empty --> set (-1) // input = workflow_id, step_id
    public function getAvailableStatus($data, $mode) {
        if(!empty($data) or isset($data)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_workflow_step.*')
                    ->from($this->db->quoteName('#__emundus_workflow_step'))
                    ->where($this->db->quoteName('#__emundus_workflow_step.workflow_id') . '=' . (int)$data['wid'])         //get all steps by workflow id
                    ->andWhere($this->db->quoteName('#__emundus_workflow_step.id') . '!=' . (int)$data['sid']);             //get all steps differ from this current step
                $this->db->setQuery($this->query);

                $_rawData = $this->db->loadAssocList();         //raw data --> need to parsed

                //// to get input (output) status from params --> do this: json_decode($....->params)->inputStatus (outputStatus)
                /// iif inputStatus --> null --> set (-1) --> same thing for output
                $_statusList = array('inputStatusList' => array(), 'outputStatusList' => array());

                foreach ($_rawData as $key=>$value) {

                    if(json_decode($value['params'])->inputStatus == "" and !empty(json_decode($value['params'])->outputStatus)) {
                        array_push($_statusList['inputStatusList'], "-1");
                        array_push($_statusList['outputStatusList'], json_decode($value['params'])->outputStatus);
                    }

                    else if (!empty(json_decode($value['params'])->inputStatus) and json_decode($value['params'])->outputStatus == "") {
                        array_push($_statusList['inputStatusList'], json_decode($value['params'])->inputStatus);
                        array_push($_statusList['outputStatusList'], "-1");
                    }

                    else if(json_decode($value['params'])->inputStatus == "" and json_decode($value['params'])->outputStatus == "") {
                        array_push($_statusList['inputStatusList'], "-1");
                        array_push($_statusList['outputStatusList'], "-1");
                    }

                    else {
                        array_push($_statusList['inputStatusList'], json_decode($value['params'])->inputStatus);
                        array_push($_statusList['outputStatusList'], json_decode($value['params'])->outputStatus);
                    }
                }

                // now, we get the $_statusList --> remove all empty string (if any)
                $_t_in = array_filter(array_values($_statusList['inputStatusList']), 'strlen' );
                $_t_out = array_filter(array_values($_statusList['outputStatusList']), 'strlen' );

                $_lst = "-1,";
                if($mode == 'in') {
                    foreach($_t_in as $key=>$value) {
                        $_lst .= $value . ",";
                    }
                }

                else if($mode == 'out') {
                    foreach($_t_out as $key=>$value) {
                        $_lst .= $value . ",";
                    }
                }

                else {
                    exit;
                }

                $_lastString = substr_replace($_lst ,"",-1);

                if($_lastString == "") { array_push($_lastString, -1); }

                // get all available status --> NOT IN $_lastString
                $this->query->clear()
                    ->select('#__emundus_setup_status.*')
                    ->from($this->db->quoteName('#__emundus_setup_status'))
                    ->where($this->db->quoteName('#__emundus_setup_status.step') . 'NOT IN (' . $_lastString . ')');

                $this->db->setQuery($this->query);
                return $this->db->loadObjectList();
            }
            catch(Exception $e) {
                JLog::add('component/com_emundus_workflow/models/step | Cannot get available status' . preg_replace("/[\r\n]/"," ",$this->query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }
}