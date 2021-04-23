<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_workflow/models');

class EmundusworkflowModelcommon extends JModelList {
    var $db = null;
    var $query = null;
    var $session = null;
    var $aid = null;

    public function __construct($config = array()) {
        parent::__construct($config);
        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);

        $this->session = JFactory::getSession();
        $this->aid = $this->session->get('emundusUser');
    }

    //get all published forms --> use table [ jos_emundus_setup_profiles ] && published == 1
    public function getAllFormsPublished() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_setup_profiles'))
                ->where($db->quoteName('#__emundus_setup_profiles.published = 1'));

            $db->setQuery($query);

            return $db->loadObjectList();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }


    //get all status --> use table [ jos_emundus_setup_status ]
    public function getAllStatus() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_setup_status'));

            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    //get all associated group --> use table [ jos_emundus_setup_groups ]
    public function getAllAssociatedGroup() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_setup_groups'));

            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    //get all messages --> use table
    public function getAllMessages() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_setup_emails'));

            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
    }

    //// --------------- use this code snippet to workflow plugin -----------------------
    /// get the profile id from fnum and status [beta version] --> multi-input status
    public function updateSessionTree($fnum, $sid=null) {
        if(!empty($fnum)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_workflow_step.*')
                    ->from($this->db->quoteName('#__emundus_workflow_step'))
                    ->leftJoin($this->db->quoteName('#__emundus_workflow') . 'ON' . $this->db->quoteName('#__emundus_workflow_step.workflow_id') . '=' . $this->db->quoteName('#__emundus_workflow.id'))
                    ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature') . 'ON' . $this->db->quoteName('#__emundus_workflow.campaign_id') . '=' . $this->db->quoteName('#__emundus_campaign_candidature.campaign_id'))
                    ->where($this->db->quoteName('#__emundus_campaign_candidature.fnum') . '=' . $fnum);

                $this->db->setQuery($this->query);
                $_stepParams = $this->db->loadObjectList();

                $this->aid->workflow = $_stepParams[0]->workflow_id;

                $_tempInputList = "";
                $_tempOutputList = "";

                foreach($_stepParams as $key => $value) {
                    $_tempInputList .= json_decode($value->params)->inputStatus . ',';
                    $_tempOutputList .= json_decode($value->params)->outputStatus . ',';

                    $_inArray = explode(',', json_decode($value->params)->inputStatus);
                    $_outArray = explode(',', json_decode($value->params)->outputStatus);

                    //// case 1 --> if status exists in input status of a step --> get its profile id (view=form), and update the session tree

                    if(in_array($sid, $_inArray)) {
                        $_stepID = $value->id;
                        $this->getProfileByStep($_stepID);

                        $this->aid->profile = json_decode($this->getProfileByStep($_stepID)[0]->params)->formNameSelected;
                        $this->aid->step_id = $_stepID;
                        $this->aid->message = 'Step and Profile Found in Workflow -- Profile with Edition permission';
                        $this->aid->editable_status = $_inArray;
                        $this->aid->output_status = json_decode($value->params)->outputStatus;
                        return $this->aid;
                    }
                }

                //// case 2 --> if status only exists in output status --> get its profile id (view=detail), and update the session tree

                $_inArrayString = explode(',', substr_replace($_tempInputList ,"",-1));
                $_outArrayString = explode(',', substr_replace($_tempOutputList ,"",-1));

                if(!in_array($sid, $_inArrayString) && in_array($sid, $_outArrayString)) {
                    $_stepIndex = array_search($sid, $_outArrayString);
                    $_stepData = $_stepParams[$_stepIndex];

                    $_stepID = $_stepData->id;
                    $this->getProfileByStep($_stepID);

                    $this->aid->profile = json_decode($this->getProfileByStep($_stepID)[0]->params)->formNameSelected;
                    $this->aid->step_id = $_stepID;
                    $this->aid->message = '-- Profile with Read-only permission --';
                    $this->aid->editable_status = null;
                    $this->aid->output_status = null;
                    return $this->aid;
                }

                /// case 3 --> if status does not exist in both Input and Output status --> get the default profile of associated campaign
                else if(!in_array($sid, $_inArrayString) && !in_array($sid, $_outArrayString)) {
                    $this->getDefaultProfileByFnum($fnum);
                    $this->aid->profile = $this->getDefaultProfileByFnum($fnum);
                    $this->aid->step_id = null;
                    $this->aid->message = '-- No profile found, get the default profile of associated campaign --';
                    $this->aid->editable_status = null;
                    $this->aid->output_status = null;
                    return $this->aid;
                }
                else {
                    return false;
                }

            }
            catch(Exception $e) {
                JLog::add('Could not get profile by fnum -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //// get the profile if it exists in step flow
    public function getProfileByStep($step) {
        if(!empty($step) or isset($step)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_workflow_item.*')
                    ->from($this->db->quoteName('#__emundus_workflow_item'))
                    ->leftJoin($this->db->quoteName('#__emundus_workflow_step') . 'ON' . $this->db->quoteName('#__emundus_workflow_item.step_id') . '=' . $this->db->quoteName('#__emundus_workflow_step.id'))
                    ->where($this->db->quoteName('#__emundus_workflow_item.item_id') . '=' . 2)
                    ->andWhere($this->db->quoteName('#__emundus_workflow_step.id') . '=' . (int)$step);
                $this->db->setQuery($this->query);
                return $this->db->loadObjectList();
            }
            catch(Exception $e) {
                JLog::add('Could not get profile by step -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //// get the default profile of by fnum
    public function getDefaultProfileByFnum($fnum) {
        if(!empty($fnum) or isset($fnum)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_setup_profiles.*')
                    ->from($this->db->quoteName('#__emundus_setup_profiles'))
                    ->leftJoin($this->db->quoteName('#__emundus_setup_campaigns') . 'ON' . $this->db->quoteName('#__emundus_setup_profiles.id') . '=' . $this->db->quoteName('#__emundus_setup_campaigns.profile_id'))
                    ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature') . 'ON' . $this->db->quoteName('#__emundus_setup_campaigns.id') . '=' . $this->db->quoteName('#__emundus_campaign_candidature.campaign_id'))
                    ->where($this->db->quoteName('#__emundus_campaign_candidature.fnum') . '=' . $fnum);

                $this->db->setQuery($this->query);
                return $this->db->loadResult();
            }
            catch(Exception $e) {
                JLog::add('Could not get default profile by campaign -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //// get the menu type from profile id
    public function getMenuTypeByProfile($pid) {
        if(!empty($pid) or isset($pid)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_setup_profiles.*')
                    ->from($this->db->quoteName('#__emundus_setup_profiles'))
                    ->where($this->db->quoteName('#__emundus_setup_profiles.id') . '=' . (int)$pid);
                $this->db->setQuery($this->query);

                $_data = $this->db->loadObject();

                $this->aid->menutype = $_data->menutype;
                $this->aid->profile_label = $_data->label;
                return $this->db->loadObject(); /// return the menu type
            }
            catch(Exception $e) {
                JLog::add('Could not get menu type by profile -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //// update the profile id from fnum and profile id --> table jos_emundus_users
    public function updateUserProfile($fnum,$pid) {
        if(!empty($fnum) or isset($fnum)) {
            try {
                /// first query --> get the user_id by fnum
                $this->query->clear()
                    ->select('#__emundus_campaign_candidature.*')
                    ->from($this->db->quoteName('#__emundus_campaign_candidature'))
                    ->where($this->db->quoteName('#__emundus_campaign_candidature.fnum') . '=' . $fnum);

                $this->db->setQuery($this->query);
                $_userID = $this->db->loadObject()->user_id;

                /// second query --> update the data of jos_emundus_users from $_userID
                $this->query->clear()
                    ->update('#__emundus_users')
                    ->set($this->db->quoteName('#__emundus_users.profile') . '=' . (int)$pid)
                    ->where($this->db->quoteName('#__emundus_users.user_id') . '=' . (int)$_userID);

                $this->db->setQuery($this->query);
                $this->db->execute();
            }
            catch(Exception $e) {
                JLog::add('Could not update the user profile -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }


    //// ********************************** used for customize emundusisapplicationonsent **********************************

    /// for testing
    public function getStepByFnumAndStatus($fnum, $sid) {
        $_exportData = array('start_date' => null, 'end_date' => null, 'step' => null, 'my_status' => $sid);
        if((!empty($fnum) and !empty($sid)) or (isset($fnum) and isset($sid))) {
            try {
                //// first --> get all steps
                $this->query->clear()
                    ->select('#__emundus_workflow_step.*')
                    ->from($this->db->quoteName('#__emundus_workflow_step'))
                    ->leftJoin($this->db->quoteName('#__emundus_workflow') . 'ON' . $this->db->quoteName('#__emundus_workflow.id') . '=' . $this->db->quoteName('#__emundus_workflow_step.workflow_id'))
                    ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature') . 'ON' . $this->db->quoteName('#__emundus_workflow.campaign_id') . '=' . $this->db->quoteName('#__emundus_campaign_candidature.campaign_id'))
                    ->where($this->db->quoteName('#__emundus_campaign_candidature.fnum') . '=' . $fnum);
                $this->db->setQuery($this->query);
                $_rawStepData = $this->db->loadObjectList();

                /// from $_rawStepData --> get the step id if dossier status is in inputStatus
                foreach($_rawStepData as $key => $value) {
                    $_inStatusList = explode(',', json_decode($value->params)->inputStatus);

                    $_tempInputList .= json_decode($value->params)->inputStatus . ',';
                    $_tempOutputList .= json_decode($value->params)->outputStatus . ',';

                    if(in_array($sid,$_inStatusList)) {
                        $_stepID = $value->id;
                        $_startDate = $this->getDateTimeByStep($_stepID)->start_date;
                        $_endDate = $this->getDateTimeByStep($_stepID)->end_date;

                        $_exportData['start_date'] = $_startDate;
                        $_exportData['end_date'] = $_endDate;
                        $_exportData['step'] = $_stepID;

                        return (object)$_exportData;
                    }
                }

                $_inArrayString = explode(',', substr_replace($_tempInputList ,"",-1));
                $_outArrayString = explode(',', substr_replace($_tempOutputList ,"",-1));

                if(!in_array($sid, $_inArrayString) && in_array($sid, $_outArrayString)) {
                    $_stepIndex = array_search($sid, $_outArrayString);
                    $_stepData = $_rawStepData[$_stepIndex];

                    $_stepID = $_stepData->id;

                    $_startDate = $this->getDateTimeByStep($_stepID)->start_date;
                    $_endDate = $this->getDateTimeByStep($_stepID)->end_date;

                    $_exportData['start_date'] = $_startDate;
                    $_exportData['end_date'] = $_endDate;
                    $_exportData['step'] = $_stepID;

                    return (object)$_exportData;
                }

                //// if step is not found --> return the profile of status (find in jos_emundus_setup_status and jos_emundus_campaign_workflow)
                $_profileID = $this->getProfileByStatus($sid);

                if(!empty($_profileID) or !isset($_profileID)) {
                    // get 'date_admission' [start // end] --> mode = profile
                    $_startDate = $this->getDateFromCampaign(null, $_profileID, 'profile')->admission_start_date;
                    $_endDate = $this->getDateFromCampaign(null, $_profileID, 'profile')->admission_end_date;
                    $_exportData['start_date'] = $_startDate;
                    $_exportData['end_date'] = $_endDate;

                    return (object)$_exportData;
                }
                else {
                    // get default date of campaign --> mode = default
                    $_startDate = $this->getDateFromCampaign($fnum, null, 'default')->start_date;
                    $_endDate = $this->getDateFromCampaign($fnum, null, 'default')->end_date;
                    $_exportData['start_date'] = $_startDate;
                    $_exportData['end_date'] = $_endDate;

                    return (object)$_exportData;
                }

            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    /// get the date of step flow
    public function getDateTimeByStep($sid) {
        if(!empty($sid) or isset($sid)) {
            try {
                $this->query->clear()
                    ->select('#__emundus_workflow_step.start_date, #__emundus_workflow_step.end_date')
                    ->from($this->db->quoteName('#__emundus_workflow_step'))
                    ->where($this->db->quoteName('#__emundus_workflow_step.id') . '=' . (int)$sid);
                $this->db->setQuery($this->query);
                return $this->db->loadObject();
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    /// get the profile by status
    public function getProfileByStatus($sid) {

        if(!empty($sid) or isset($sid)) {
            try {
                $_profile = array();
                $this->query->clear()
                    ->select('#__emundus_setup_status.*')
                    ->from($this->db->quoteName('#__emundus_setup_status'))
                    ->where($this->db->quoteName('#__emundus_setup_status.step') . '=' . (int)$sid);

                $this->db->setQuery($this->query);
                $_firstProfile = $this->db->loadObject()->profile;

                $this->query->clear()
                    ->select('#__emundus_campaign_workflow.*')
                    ->from($this->db->quoteName('#__emundus_campaign_workflow'))
                    ->where($this->db->quoteName('#__emundus_campaign_workflow.status') . '=' . (int)$sid);

                $this->db->setQuery($this->query);
                $_secondProfile = $this->db->loadObject()->profile;

                if((is_null($_firstProfile) or empty($_firstProfile) or !isset($_firstProfile)) and (!is_null($_secondProfile) or !empty($_secondProfile) or isset($_secondProfile))) {
                    $_profile = $_secondProfile;
                }

                else if((!is_null($_firstProfile) or !empty($_firstProfile) or isset($_firstProfile)) and (is_null($_secondProfile) or empty($_secondProfile) or !isset($_secondProfile))) {
                    $_profile = $_firstProfile;
                }

                else if((is_null($_firstProfile) or empty($_firstProfile) or isset($_firstProfile)) and (is_null($_secondProfile) or empty($_secondProfile) or isset($_secondProfile))) {
                    ///
                }

                else {
                    array_push($_profile, [$_firstProfile,$_secondProfile]);
                }

                return $_profile;
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //// get date from campaign --> $mode = true
    public function getDateFromCampaign($fnum=null, $pid=null, $mode) {
        if(!empty($mode) or isset($mode)) {
            $_exportData = null;
            try {
                if($mode == 'profile') {
                    $this->query->clear()
                        ->select('#__emundus_setup_campaigns.*')
                        ->from($this->db->quoteName('#__emundus_setup_campaigns'))
                        ->where($this->db->quoteName('#__emundus_setup_campaigns.profile_id') . '=' . (int)$pid);
                }
                else if($mode == 'default') {
                    //// get the default date by campaign <-- fnum
                    $this->query->clear()
                        ->select('#__emundus_setup_campaigns.*')
                        ->from($this->db->quoteName('#__emundus_setup_campaigns'))
                        ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature') . 'ON' . $this->db->quoteName('#__emundus_campaign_candidature.campaign_id') . '=' . $this->db->quoteName('#__emundus_setup_campaigns.id'))
                        ->where($this->db->quoteName('#__emundus_campaign_candidature.fnum') . '=' . $fnum);
                }
                else {
                    /// do nothing
                }
                $this->db->setQuery($this->query);
                return $this->db->loadObject();
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    //// get the last page --> used to run confirm post
    public function getLastPage($menutype) {
        if(!empty($menutype)) {
            try {
                $this->query->clear()
                    ->select('#__menu.*')
                    ->from($this->db->quoteName('#__menu'))
                    ->where($this->db->quoteName('#__menu.menutype') . '=' . '"' . $menutype . '"');
                $this->db->setQuery($this->query);
                $_rawData = $this->db->loadObjectList();

                $_orderList = array('left' => array(), 'right' => array());

                /// find the max lft and rgt from $_rawData
                foreach ($_rawData as $key => $value) {
                    $_orderList['left'][$value->id] = $value->lft;
                    $_orderList['right'][$value->id] = $value->rgt;
                }

                $_isLastPage = (array_search(max(array_values($_orderList['left'])), $_orderList['left']) == array_search(max(array_values($_orderList['right'])), $_orderList['right'])) ? true : false;

                if ($_isLastPage) {
                    $_lastPage = array_search(max(array_values($_orderList['left'])), $_orderList['left']);

                    foreach ($_rawData as $_key => $_value) {
                        if ($_value->id == $_lastPage) {
                            return $_value;
                        }
                    }
                } else {
                    $_lastPage = false;
                    return false;
                }
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