<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_workflow/models');

class EmundusworkflowModelstep extends JModelList {
    //// constructor
    var $db = null;
    var $query = null;
    var $workflow_model = null;

    public function __construct($config = array()) {
        parent::__construct($config);

        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);

        $this->workflow_model = JModelLegacy::getInstance('workflow', 'EmundusworkflowModel');          /// get workflow_model
    }

    //// get all steps of workflow --> params ==> wid
    /// get all steps --> get current params of this step
    public function getAllStepsByWorkflow($wid) {
        if (!empty($wid) or isset($wid)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_workflow_step.*')
                    ->from($this->db->quoteName('#__emundus_workflow_step'))
                    ->where($this->db->quoteName('#__emundus_workflow_step.workflow_id') . '=' . (int)$wid)
                    ->order('#__emundus_workflow_step.ordering ASC');

                $this->db->setQuery($this->query);      //set query string
                return $this->db->loadObjectList();     //get all steps by workflow
            } catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/step | Cannot get all steps by workflow' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    //// create new step --> params ==> data['workflow_id']
    public function createStep($data) {
        if (!empty($data) or isset($data)) {
            try {
                // step 1 --> get the current ordering --> if nothing previous step --> return 0, else increment 1
                $this->query->clear()
                    ->select('max(#__emundus_workflow_step.ordering)')
                    ->from($this->db->quoteName('#__emundus_workflow_step'))
                    ->where($this->db->quoteName('#__emundus_workflow_step.workflow_id') . '=' . (int)$data['workflow_id']);
                $this->db->setQuery($this->query);
                $_currentOrdering = $this->db->loadResult();

                if(is_null($_currentOrdering)) {
                    $_currentOrdering = 0;
                } else {
                    $_currentOrdering += 1;
                }
                $data['ordering'] = $_currentOrdering;

                // step 2 --> create step normally
                $this->query->clear()
                    ->insert($this->db->quoteName('#__emundus_workflow_step'))
                    ->columns($this->db->quoteName(array_keys($data)))
                    ->values(implode(',', $this->db->quote(array_values($data))));

                $this->db->setQuery($this->query);
                $this->db->execute();
                $_step_id = $this->db->insertid();

                // step 3 --> update last saving time
                $this->workflow_model->workflowSavingTrigger((int)$data['workflow_id']);

                return (object)["step_id" => $_step_id, "ordering" => $_currentOrdering, "message" => $this->db->execute()];
            } catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/step | Cannot create new step' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    //// delete a step which exists --> params ==> $id
    public function deleteStep($data) {
        if (!empty($data) or !isset($data)) {
            try {
                $wid = $data['wid'];

                /// step 1 --> get the current ordering of this step
                $this->query->clear()
                    ->select('#__emundus_workflow_step.ordering')
                    ->from($this->db->quoteName('#__emundus_workflow_step'))
                    ->where($this->db->quoteName('#__emundus_workflow_step.id') . '=' . (int)$data['id']);
                $this->db->setQuery($this->query);
                $_currentOrder = $this->db->loadResult();       /// current order

                /// step 2 --> grab all steps from this workflow which have ordering > current_ordering

                $this->query->clear()
                    ->select('#__emundus_workflow_step.*')
                    ->from($this->db->quoteName('#__emundus_workflow_step'))
                    ->where($this->db->quoteName('#__emundus_workflow_step.workflow_id') . '=' . $wid)
                    ->andWhere($this->db->quoteName('#__emundus_workflow_step.ordering') . '>' . (int)$_currentOrder)
                    ->order('#__emundus_workflow_step.ordering ASC');
                $this->db->setQuery($this->query);
                $_stepList = $this->db->loadObjectList();   //// list of available steps

                //// if $_stepList --> empty --> deleted step == last step of workflow --> do nothing
                if(!empty($_stepList)) {
                    /// step 3 --> update new orders
                    foreach ($_stepList as $key => $value) {
                        /// update the ordering of $value->id
                        $this->query->clear()
                            ->update($this->db->quoteName('#__emundus_workflow_step'))
                            ->set($this->db->quoteName('#__emundus_workflow_step.ordering') . '=' . (int)($value->ordering - 1))
                            ->where($this->db->quoteName('#__emundus_workflow_step.id') . '=' . (int)$value->id);
                        $this->db->setQuery($this->query);
                        $this->db->execute();
                    }
                }
                else {
                    //// do nothing
                }

                // step 4 --> delete stepflow
                $this->query->clear()
                    ->delete($this->db->quoteName('#__emundus_workflow_step'))
                    ->where($this->query->quoteName('#__emundus_workflow_step.id') . '=' . (int)$data['id']);

                $this->db->setQuery($this->query);
                $this->db->execute();

                // step 5 --> update last saving time
                $this->workflow_model->workflowSavingTrigger($wid);
                return (object)['message'=>$this->db->execute()];
            } catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/step | Cannot delete step' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    //// update step params --> input :: step_id + step_params
    public function updateStepParams($data) {
        if (!empty($data) or isset($data)) {
            if (!isset($data['params'])) {
                //// just update label
                $this->query->clear()
                    ->update($this->db->quoteName('#__emundus_workflow_step'))
                    ->set($this->db->quoteName('#__emundus_workflow_step.step_label') . '=' . $this->db->quote($data['step_label']))
                    ->where($this->db->quoteName('#__emundus_workflow_step.id') . '=' . (int)$data['id']);

                $this->db->setQuery($this->query);
                $this->db->execute();

                $this->workflow_model->workflowSavingTrigger($data['workflow_id']);

            } else {
                /// ************************************************************************************************************
                $_string = "";
                if (isset($data['params']['inputStatus'])) {
                    foreach ($data['params']['inputStatus'] as $key => $value) {
                        if ($value == "true") {
                            $_string .= (string)$key . ",";
                            $_lastString = substr_replace($_string, "", -1);
                            $data['params']['inputStatus'] = $_lastString;
                        } else {
                        }
                    }
                } else {
                }
                /// ************************************************************************************************************

                try {
                    $wid = $data['wid'];
                    unset($data['params']['id']);

                    //// case 1 --> change the step label
                    if ($data['step_label'] and empty($data['params'])) {
                        $this->query->clear()
                            ->update($this->db->quoteName('#__emundus_workflow_step'))
                            ->set($this->db->quoteName('#__emundus_workflow_step.step_label') . '=' . $this->db->quote($data['step_label']))
                            ->where($this->db->quoteName('#__emundus_workflow_step.id') . '=' . (int)$data['id']);
                    } else {
                        $this->query->clear()
                            ->update($this->db->quoteName('#__emundus_workflow_step'))
                            ->set($this->db->quoteName('#__emundus_workflow_step.params') . '=' . $this->db->quote(json_encode($data['params'])))
                            ->set($this->db->quoteName('#__emundus_workflow_step.step_label') . '=' . $this->db->quote($data['step_label']))
                            ->set($this->db->quoteName('#__emundus_workflow_step.start_date') . '=' . $this->db->quote($data['start_date']))
                            ->set($this->db->quoteName('#__emundus_workflow_step.end_date') . '=' . $this->db->quote($data['end_date']))
                            ->where($this->db->quoteName('#__emundus_workflow_step.id') . '=' . (int)$data['id']);
                    }

                    $this->db->setQuery($this->query);
                    $this->db->execute();

                    $this->workflow_model->workflowSavingTrigger((int)$wid);
                    return (object)['message'=>true];
                } catch (Exception $e) {
                    JLog::add('component/com_emundus_workflow/models/step | Cannot update step params' . preg_replace("/[\r\n]/", " ", $this->query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                    return $e->getMessage();
                }
            }
        }
        else {
            return false;
        }
    }

    /// get (list) status name from (list) of steps
    public function getListStatusNameFromStep($data) {
        if(!empty($data) or isset($data)) {

            try {
                $this->query->clear()
                    ->select('#__emundus_setup_status.*')
                    ->from($this->db->quoteName('#__emundus_setup_status'))
                    ->where($this->db->quoteName('#__emundus_setup_status.step') . 'IN (' . $data . ')');
                $this->db->setQuery($this->query);
                return $this->db->loadObjectList();
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    /// get status name from stats name (only one step)
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
                return $e->getMessage();
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
                    ->select('#__emundus_workflow_step.*')
                    ->from($this->db->quoteName('#__emundus_workflow_step'))
                    ->where($this->db->quoteName('#__emundus_workflow_step.id') . '=' . (int)$sid)
                    ->order('#__emundus_workflow_step.ordering ASC');

                $this->db->setQuery($this->query);
                $_rawCurrentParams = $this->db->loadObject();       //get current params (raw info)

                //// parse this raw info into array
                $_exportArray = array('inputStatus' => array(), 'outputStatus' => array(), 'startDate' => array(), 'endDate' => array(), 'notes' => array(), 'stepLabel' => array(), 'ordering' => array());

                $_exportArray['inputStatus'] = json_decode($_rawCurrentParams->params)->inputStatus;
                $_exportArray['outputStatus'] = json_decode($_rawCurrentParams->params)->outputStatus;
                $_exportArray['startDate'] = $_rawCurrentParams->start_date;
                $_exportArray['endDate'] = $_rawCurrentParams->end_date;
                $_exportArray['notes'] = json_decode($_rawCurrentParams->params)->notes;
                $_exportArray['stepLabel'] = $_rawCurrentParams->step_label;
                $_exportArray['ordering'] = $_rawCurrentParams->ordering;

//                $_exportArray['inputStatusName'] = ($this->getStatusAttributsFromStep($_exportArray['inputStatus']))->value;
//                $_exportArray['outputStatusName'] = ($this->getStatusAttributsFromStep($_exportArray['outputStatus']))->value;

                $_exportArray['inputStatusNames'] = ($this->getListStatusNameFromStep($_exportArray['inputStatus']));
                $_exportArray['outputStatusNames'] = ($this->getListStatusNameFromStep($_exportArray['outputStatus']));

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

    // update the step ordering each time of changing
    public function updateStepOrdering($data,$wid) {
        if(!empty($data)) {
            try {
                foreach($data as $key => $value) {
                    $this->query->clear()
                        ->update($this->db->quoteName('#__emundus_workflow_step'))
                        ->set($this->db->quoteName('#__emundus_workflow_step.ordering') . ' = ' . (int)$key)
                        ->where($this->db->quoteName('#__emundus_workflow_step.id') . ' = ' . (int)$value);
                    $this->db->setQuery($this->query);
                    $this->db->execute();
                }

                /// reload the ordering /// ordering = index
                $this->query->clear()
                    ->select('#__emundus_workflow_step.id, #__emundus_workflow_step.ordering')
                    ->from($this->db->quoteName('#__emundus_workflow_step'))
                    ->where($this->db->quote('#__emundus_workflow_step.workflow_id') . '=' . (int)$wid)
                    ->order('#__emundus_workflow_step.ordering ASC');
                $this->db->setQuery($this->query);

                return ['message'=>true, 'data' => $this->db->loadObjectList()];
            }
            catch(Exception $e) {
                JLog::add('component/com_emundus_workflow/models/step | Cannot update step ordering' . preg_replace("/[\r\n]/"," ",$this->query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }
}