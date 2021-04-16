<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_workflow/models');

//require_once (JPATH_SITE.DS.'components'.DS.'com_emundus_workflow'.DS.'models'.DS.'common.php');

class plgEmundusSetup_workflow extends JPlugin {

        var $db = null;
        var $query = null;
        var $_model = null;
        var $session = null;
        var $aid = null;

        function __construct(&$subject, $config = array()) {
            parent::__construct($subject, $config);

            $this->db = JFactory::getDbo();
            $this->query = $this->db->getQuery(true);

            $this->_model = JModelLegacy::getInstance('common', 'EmundusworkflowModel');

            jimport('joomla.log.log');
            JLog::addLogger(array('text_file' => 'com_emundus.setupWorkflow.php'), JLog::ALL, array('com_emundus_setupWorkflow'));

            $this->session = JFactory::getSession();
            $this->aid = $this->session->get('emundusUser');
        }

        public function onOpenFile($fnum,$sid) {
            /// description --> when open file --> register the session $aid --> menutype // profile_id
            $this->_model->updateSessionTree($fnum,$sid);

            /// get the menu type from profile
            $this->_model->getMenuTypeByProfile($this->aid->profile);

            //update the user profile
            $this->_model->updateUserProfile($fnum, $this->aid->profile);
        }

        public function getTest($fnum) {
            /// get now date time
            $mainframe = JFactory::getApplication();
            $offset = $mainframe->get('offset', 'UTC');

            $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
            $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
            $now = $dateTime->format('Y-m-d H:i:s');

            /// get step by fnum and status
            $_step = $this->_model->getStepByFnumAndStatus($fnum,$this->aid->status);

            $_startDate = json_decode($_step)->start_date;
            $_endDate = json_decode($_step)->end_date;

            var_dump(gettype($_startDate));die;
        }

//        //// from 2 params ==> fnum
//        public function getProfileByFnum($fnum,$sid=null) {
//            if(!empty($fnum) or isset($fnum)) {
//                try {
//                    $this->query
//                        ->select('#__emundus_workflow_step.*')
//                        ->from($this->db->quoteName('#__emundus_workflow_step'))
//                        ->leftJoin($this->db->quoteName('#__emundus_workflow') . 'ON' . $this->db->quoteName('#__emundus_workflow.id') . '=' . $this->db->quoteName('#__emundus_workflow_step.workflow_id'))
//                        ->leftJoin($this->db->quoteName('#__emundus_campaign_candidature') . 'ON' . $this->db->quoteName('#__emundus_workflow.campaign_id') . '=' . $this->db->quoteName('#__emundus_campaign_candidature.campaign_id'))
//                        ->where($this->db->quoteName('#__emundus_campaign_candidature.fnum') . '=' . $fnum);
//
//                    /// right now, we will get step.id, step.workflow_id, step.params
//                    $this->db->setQuery($this->query);
//                    $_results = $this->db->loadObjectList();
//
//                    $_tempInputList = "";
//                    $_tempOutputList = "";
//
//                    $session = JFactory::getSession();
//                    $aid = $session->get('emundusUser');
//
//                    $aid->workflow = $_results[0]->id;
//
//                    foreach($_results as $key => $value) {
//                        $_tempInputList .= json_decode($value->params)->inputStatus . ',';
//                        $_tempOutputList .= json_decode($value->params)->outputStatus . ',';
//
//                        /// input status is in one of the step flow --> get this step id --> ....
//                        if((json_decode($value->params)->inputStatus) == $sid) {
//                            $_stepID = $value->id;
//                            $this->getProfileByStep($_stepID);  //// get profile by step id
//
//                            $aid->profile = json_decode($this->getProfileByStep($_stepID)[0]->params)->formNameSelected;       // profile_id
//                            $aid->step_id = $_stepID;
//                            $aid->message = 'step and profile founded';
//                            continue;          ///stop the loop
//                        }
//
//                        else { }
//                    }
//
//                    /// trim the last character
//                    $_inArrayString = explode(',', substr_replace($_tempInputList ,"",-1));
//                    $_outArrayString = explode(',', substr_replace($_tempOutputList ,"",-1));
//
//                    //// if the input status is not in $_inArrayString, but in $_outArrayString
//                    if(!in_array($sid, $_inArrayString) && in_array($sid, $_outArrayString)) {
//                        // get the first profile of this step flow
////                        $this->getFirstProfileByStep($sid);
//                        $this->getFirstProfileOfFirstStep($sid);
//
//                        $aid->profile = json_decode($this->getFirstProfileOfFirstStep($sid)->params)->formNameSelected;
//                        $aid->step_id = null;
//                        $aid->message = 'get the first profile of the first step';
//                    }
//                    else if(!in_array($sid, $_inArrayString) && !in_array($sid, $_outArrayString)) {
//                        // get the default profile of this campaign
//                        $this->getDefaultProfileByFnum($fnum);
//                        $aid->profile = $this->getDefaultProfileByFnum($fnum);
//                        $aid->step_id = null;
//                        $aid->message = 'get the default profile of campaign';
//                    }
//                    else {
//                        ////
//                    }
//
//                    return $aid;
//                }
//                catch(Exception $e) {
//                    JLog::add('Could not get profile by fnum -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_setupWorkflow');
//                    return $e->getMessage();
//                }
//            }
//            else {
//                return false;
//            }
//        }
    }
