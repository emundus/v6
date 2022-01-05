<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

jimport('joomla.application.component.model');
JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_workflow/models');

/**
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.juseremundus
 * @since       3.0
 */

class PlgFabrik_FormEmundusprocessworkflow extends plgFabrik_Form {
    /**
     * Status field
     *
     * @var  string
     */
    protected $URLfield = '';

    var $db = null;
    var $query = null;

    public function __construct(&$subject, $config = array()) {
        parent::__construct($subject, $config);
        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);
    }

    /**
     * Get an element name
     *
     * @param string $pname Params property name to look up
     * @param bool $short Short (true) or full (false) element name, default false/full
     *
     * @return    string    element full name
     */
    public function getFieldName($pname, $short = false) {
        $params = $this->getParams();

        if ($params->get($pname) == '')
            return '';

        $elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));

        return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
    }

    /**
     * Get the fields value regardless of whether its in joined data or no
     *
     * @param string $pname Params property name to get the value for
     * @param array $data Posted form data
     * @param mixed $default Default value
     *
     * @return  mixed  value
     */
    public function getParam($pname, $default = '') {
        $params = $this->getParams();

        if ($params->get($pname) == '') {
            return $default;
        }

        return $params->get($pname);
    }

    /**
     * Main script.
     *
     * @return  bool
     */
    public function onBeforeLoad() {

        $mainframe = JFactory::getApplication();

        if (!$mainframe->isAdmin()) {
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');

            jimport('joomla.log.log');
            JLog::addLogger(['text_file' => 'com_emundus.isApplicationSent.php'], JLog::ALL, ['com_emundus']);

            $formModel = $this->getModel();
            $listModel =  $formModel->getListModel();

            $user = JFactory::getSession()->get('emundusUser');

            if (empty($user)) {
                $user = JFactory::getUser();
            }

            $eMConfig = JComponentHelper::getParams('com_emundus');
            $copy_application_form = $eMConfig->get('copy_application_form', 0);
            $can_edit_until_deadline = $eMConfig->get('can_edit_until_deadline', '0');
            $can_edit_after_deadline = $eMConfig->get('can_edit_after_deadline', '0');

            $id_applicants = $eMConfig->get('id_applicants', '0');
            $applicants = explode(',',$id_applicants);

            $offset = $mainframe->get('offset', 'UTC');

            try {
                $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
                $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
                $now = $dateTime->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                echo $e->getMessage() . '<br />';
            }

            $jinput = $mainframe->input;
            $view = $jinput->get('view');
            $fnum = $jinput->get->get('rowid', null);
            $itemid = $jinput->get('Itemid');
            $reload = $jinput->get('r', 0);
            $reload++;

            $m_profile = new EmundusModelProfile;

            $last_page = $m_profile->getLastPage($user->menutype)->link;
            $last_form_id = (explode('formid=', $last_page))[1];

            $res = $m_profile->getStepByFnum($user->fnum);
            
            if($res->step !== null) {
                $phase = $res->step;
            } else {
                $phase = 'none';
            }

            /* get start date // end date of each formulaire */
            $start_date = $res->start_date;
            $end_date = $res->end_date;

            /* get editable status // output status */
            $editable_status = $res->editable_status;
            $output_status = $res->output_status;

            /* can edit formulaire */
            $can_edit_form = !in_array($user->status, $editable_status);

//            if ($this->getParam('admission', 0) == 1) {
//                if(!empty($fnum)) {
//                    $is_dead_line_passed = (strtotime(date($now)) > strtotime(@$user->fnums[$fnum]->admission_end_date) || strtotime(date($now)) < strtotime(@$user->fnums[$fnum]->admission_start_date)) ? true : false;
//                    $is_campaign_started = (strtotime(date($now)) >= strtotime(@$user->fnums[$fnum]->admission_start_date)) ? true : false;
//                }
//                else{
//                    $is_dead_line_passed = (strtotime(date($now)) > strtotime(@$user->fnums[$user->fnum]->admission_end_date) || strtotime(date($now)) < strtotime(@$user->fnums[$user->fnum]->admission_start_date)) ? true : false;
//                    $is_campaign_started = (strtotime(date($now)) >= strtotime(@$user->fnums[$user->fnum]->admission_start_date)) ? true : false;
//                }
//            }
//            else {
            if(!empty($fnum)) {
                $is_dead_line_passed = ($now > $end_date || $now < $start_date) ? true : false;
                $is_campaign_started = ($now > $start_date || $now < $end_date) ? true : false;
            }
            else{
                $is_dead_line_passed = ($now > $end_date || $now < $start_date) ? true : false;
                $is_campaign_started = ($now >= $start_date) ? true : false;
            }
            //}

            $is_app_sent = !in_array(@$user->status, explode(',', $this->getParam('applicationsent_status', 0)));
            $can_edit = EmundusHelperAccess::asAccessAction(1, 'u', $user->id, $fnum);
            $can_read = EmundusHelperAccess::asAccessAction(1, 'r', $user->id, $fnum);

            // once access condition is not correct, redirect page
            $reload_url = true;

            // FNUM sent by URL is like user fnum (means an applicant trying to open a file)
            if (!empty($fnum)) {

                // Check campaign limit, if the limit is obtained, then we set the deadline to true
                $m_campaign = new EmundusModelCampaign;

                $isLimitObtained = $m_campaign->isLimitObtained($user->fnums[$fnum]->campaign_id);

                if ($fnum == @$user->fnum) {
                    //try to access edit view
                    if ($view == 'form') {
                        if ((!$is_dead_line_passed && $isLimitObtained !== true) || in_array($user->id, $applicants) || ($can_edit_form && !$is_dead_line_passed && $can_edit_until_deadline && $isLimitObtained !== true) || ($is_dead_line_passed && $can_edit_after_deadline && $isLimitObtained !== true) || $can_edit) {
                            $reload_url = false;
                        }
                    }
                    //try to access detail view or other
                    else {
                        if(!$can_edit && $can_edit_form){
                            $mainframe->enqueueMessage(JText::_('APPLICATION_READ_ONLY'), 'error');
                        } elseif ($is_dead_line_passed){
                            $mainframe->enqueueMessage(JText::_('APPLICATION_PERIOD_PASSED'), 'error');
                        } elseif (!$is_campaign_started){
                            $mainframe->enqueueMessage(JText::_('APPLICATION_PERIOD_NOT_STARTED'), 'error');
                        }
                        $reload_url = false;
                    }
                }
                // FNUM sent not like user fnum (partner or bad FNUM)
                else {
                    $document = JFactory::getDocument();
                    $document->addStyleSheet("media/com_fabrik/css/fabrik.css" );

                    if ($view == 'form') {
                        if ($can_edit) {
                            $reload_url = false;
                        }
                    } else {
                        //try to access detail view or other
                        if ($can_read) {
                            $reload_url = false;
                        }
                    }
                }
            }

            if (isset($user->fnum) && !empty($user->fnum)) {

                if (in_array($user->id, $applicants)) {

                    if ($reload_url) {
                        $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload . '&phase=' . $phase);
                    }

                } else {

                    if (($is_dead_line_passed && $can_edit_after_deadline == 0) || !$is_campaign_started || $isLimitObtained === true) {
                        if ($reload_url) {
                            if ($isLimitObtained === true) {
                                $mainframe->enqueueMessage(JText::_('APPLICATION_LIMIT_OBTAINED'), 'error');
                            } else {
                                $mainframe->enqueueMessage(JText::_('APPLICATION_PERIOD_PASSED'), 'error');
                            }
                            $mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload . '&phase=' . $phase);
                        }

                    } else {

                        if ($can_edit_form) {
                            if ($can_edit_until_deadline != 0 || $can_edit_after_deadline != 0) {
                                if ($reload_url) {
                                    $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload . '&phase=' . $phase);
                                }
                            } else {
                                if ($reload_url) {
                                    $mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload . '&phase=' . $phase);
                                }
                            }
                        } else {
                            if ($reload_url) {
                                $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload . '&phase=' . $phase);
                            }
                        }

                    }
                }

            } else {

                if ($can_edit == 1) {
                    return true;
                } else {
                    if ($can_read == 1) {
                        if ($reload < 3) {
                            $reload++;
                            $mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$fnum."&r=".$reload .'&phase=' . $phase);
                        }
                    } else {
                        $mainframe->enqueueMessage(JText::_('ACCESS_DENIED'), 'error');
                        $mainframe->redirect("index.php");
                    }
                }
            }

            if ($copy_application_form == 1 && isset($user->fnum)) {
                if (empty($formModel->getRowId())) {
                    $db = JFactory::getDBO();
                    $table = $listModel->getTable();
                    $table_elements = $formModel->getElementOptions(false, 'name', false, false, array(), '', true);
                    $rowid = $formModel->data["rowid"];

                    $elements = array();
                    foreach ($table_elements as $key => $element) {
                        $elements[] = $element->value;
                    }

                    // check if data stored for current user
                    try {
                        $query = 'SELECT '.implode(',', $db->quoteName($elements)).' FROM '.$table->db_table_name.' WHERE user='.$user->id;
                        $db->setQuery($query);
                        $stored = $db->loadAssoc();
                        if (count($stored) > 0) {
                            // update form data
                            $parent_id = $stored['id'];
                            unset($stored['id']);
                            unset($stored['fnum']);

                            try {
                                $query = 'INSERT INTO '.$table->db_table_name.' (`fnum`, `'.implode('`,`', array_keys($stored)).'`) VALUES('.$db->Quote($rowid).', '.implode(',', $db->Quote($stored)).')';
                                $db->setQuery($query);
                                $db->execute();
                                $id = $db->insertid();

                            } catch (Exception $e) {
                                $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                                JLog::add($error, JLog::ERROR, 'com_emundus');
                            }

                            // get data and update current form
                            $groups = $formModel->getFormGroups(true);
                            $data = array();
                            if (count($groups) > 0) {
                                foreach ($groups as $key => $group) {
                                    $group_params = json_decode($group->gparams);
                                    if (isset($group_params->repeat_group_button) && $group_params->repeat_group_button == 1) {

                                        $query = 'SELECT table_join FROM #__fabrik_joins WHERE group_id = '.$group->group_id.' AND table_key LIKE "id" AND table_join_key LIKE "parent_id"';
                                        $db->setQuery($query);
                                        try {
                                            $repeat_table = $db->loadResult();
                                        } catch (Exception $e) {
                                            $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                                            JLog::add($error, JLog::ERROR, 'com_emundus');
                                            $repeat_table = $table->db_table_name.'_'.$group->group_id.'_repeat';
                                        }

                                        $data[$group->group_id]['repeat_group'] = $group_params->repeat_group_button;
                                        $data[$group->group_id]['group_id'] = $group->group_id;
                                        $data[$group->group_id]['element_name'][] = $group->name;
                                        $data[$group->group_id]['table'] = $repeat_table;
                                    }
                                }
                                if (count($data) > 0) {
                                    foreach ($data as $key => $d) {

                                        try {
                                            $query = 'SELECT '.implode(',', $db->quoteName($d['element_name'])).' FROM '.$d['table'].' WHERE parent_id='.$parent_id;
                                            $db->setQuery( $query );
                                            $stored = $db->loadAssoc();

                                            if (count($stored) > 0) {
                                                // update form data
                                                unset($stored['id']);
                                                unset($stored['parent_id']);

                                                try {
                                                    $query = 'INSERT INTO '.$d['table'].' (`parent_id`, `'.implode('`,`', array_keys($stored)).'`) VALUES('.$id.', '.implode(',', $db->Quote($stored)).')';
                                                    $db->setQuery( $query );
                                                    $db->execute();
                                                } catch (Exception $e) {
                                                    $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                                                    JLog::add($error, JLog::ERROR, 'com_emundus');
                                                }
                                            }

                                        } catch (Exception $e) {
                                            $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                                            JLog::add($error, JLog::ERROR, 'com_emundus');
                                        }
                                    }
                                }
                            }
                            // sync documents uploaded
                            // 1. get list of uploaded documents for previous file defined as duplicated
                            $fnums = $user->fnums;
                            unset($fnums[$user->fnum]);

                            if (count($fnums) > 0) {
                                $previous_fnum = array_keys($fnums);
                                $query = 'SELECT eu.*, esa.nbmax
											FROM #__emundus_uploads as eu
											LEFT JOIN #__emundus_setup_attachments as esa on esa.id=eu.attachment_id
											LEFT JOIN #__emundus_setup_attachment_profiles as esap on esap.attachment_id=eu.attachment_id AND esap.profile_id='.$user->profile.'
											WHERE eu.user_id='.$user->id.'
											AND eu.fnum like '.$db->Quote($previous_fnum[0]).'
											AND esap.duplicate=1';
                                $db->setQuery( $query );
                                $stored = $db->loadAssocList();

                                if (count($stored) > 0) {
                                    // 2. copy DB définition and duplicate files in applicant directory
                                    foreach ($stored as $key => $row) {
                                        $src = $row['filename'];
                                        $ext = explode('.', $src);
                                        $ext = $ext[count($ext)-1];;
                                        $cpt = 0-(int)(strlen($ext)+1);
                                        $dest = substr($row['filename'], 0, $cpt).'-'.$row['id'].'.'.$ext;
                                        $nbmax = $row['nbmax'];
                                        $row['filename'] = $dest;
                                        unset($row['id']);
                                        unset($row['fnum']);
                                        unset($row['nbmax']);

                                        try {
                                            $query = 'SELECT count(id) FROM #__emundus_uploads WHERE user_id='.$user->id.' AND attachment_id='.$row['attachment_id'].' AND fnum like '.$db->Quote($user->fnum);
                                            $db->setQuery($query);
                                            $cpt = $db->loadResult();

                                            if ($cpt < $nbmax) {
                                                $query = 'INSERT INTO #__emundus_uploads (`fnum`, `'.implode('`,`', array_keys($row)).'`) VALUES('.$db->Quote($user->fnum).', '.implode(',', $db->Quote($row)).')';
                                                $db->setQuery($query);
                                                $db->execute();
                                                $id = $db->insertid();
                                                $path = EMUNDUS_PATH_ABS.$user->id.DS;

                                                if (!copy($path.$src, $path.$dest)) {
                                                    $query = 'UPDATE #__emundus_uploads SET filename='.$src.' WHERE id='.$id;
                                                    $db->setQuery($query);
                                                    $db->execute();
                                                }
                                            }

                                        } catch (Exception $e) {
                                            $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                                            JLog::add($error, JLog::ERROR, 'com_emundus');
                                        }
                                    }
                                }
                            }
                            $reload++;
                            $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload.'&phase=' . $phase);
                        }
                    } catch (Exception $e) {
                        $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                        JLog::add($error, JLog::ERROR, 'com_emundus');
                    }
                }
            }
        }
        return true;
    }


    public function onAfterProcess() {
        $app = JFactory::getApplication();

        include_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'emails.php');
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'application.php');
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'campaign.php');
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');

        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'export.php');
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.processWorkflow.php'), JLog::ALL, array('com_emundus'));

        $eMConfig = JComponentHelper::getParams('com_emundus');

        $can_edit_until_deadline = $eMConfig->get('can_edit_until_deadline', 0);
        $can_edit_after_deadline = $eMConfig->get('can_edit_after_deadline', '0');

        $application_form_order = $eMConfig->get('application_form_order', null);
        $attachment_order = $eMConfig->get('attachment_order', null);
        $application_form_name = $eMConfig->get('application_form_name', "application_form_pdf");
        $export_pdf = $eMConfig->get('export_application_pdf', 0);
        $export_path = $eMConfig->get('export_path', null);

        $id_applicants = explode(',', $eMConfig->get('id_applicants', '0'));
        $applicants = explode(',',$id_applicants);

        $m_application = new EmundusModelApplication;
        $m_files = new EmundusModelFiles;
        $m_emails = new EmundusModelEmails;
        $m_campaign = new EmundusModelCampaign;
        $m_profile = new EmundusModelProfile;

        $applicant_profiles = $m_profile->getApplicantsProfilesArray();

        $copy_form = (int)$this->getParam('emundusprocessworkflow_copy_form', '0');             // copy_form --> emundusredirect

        $user = JFactory::getSession()->get('emundusUser');
        $levels = JAccess::getAuthorisedViewLevels($user->id);

        $jinput = JFactory::getApplication()->input;
        $formid = $jinput->get('formid');
        $itemid = $jinput->get('Itemid');
        $view = $jinput->get('view');

        $_reload = $jinput->get('r');

        /* get phase id from url */
        $phase = $jinput->get('phase');

        /* get fnum from url */
        $fnum = $jinput->get->get('rowid', null);
        
        /* check if ELEMENT__STATUS exists in order to update status (temporarily) */
        $raw_status = $m_profile->getStatusByElement($user->fnum);
        foreach($raw_status as $k => $v) {
            if($formid !== $v['form']) {
                unset($raw_status[$k]);
            }
        }


        
        if(count($raw_status) > 0) {
            if (count($raw_status) > 1) {
                $raw_status = end($raw_status);
            } else {
                $raw_status = current($raw_status);
            }

            $temp_status = $raw_status['estatus'];

            /* update status to $temp_status */
            $this->query = 'UPDATE #__emundus_campaign_candidature SET status=' . $temp_status . ' WHERE applicant_id=' . $user->id . ' AND campaign_id=' . $user->campaign_id . ' AND fnum like ' . $this->db->Quote($user->fnum);
            $this->db->setQuery($this->query);
            $this->db->execute();
        }

        /* check if ELEMENT__PROFILE exists */
        $raw_profile = $m_profile->getProfileByElement($user->fnum);

        /* check if raw['formid'] satisfies with current formid */
        foreach($raw_profile as $k => $v) {
            if($formid !== $v['form']) {
                unset($raw_profile[$k]);
            }
        }

        if(count($raw_profile) > 0) {
            if (count($raw_profile) > 1) {
                $raw_profile = end($raw_profile);
            } else {
                $raw_profile = current($raw_profile);
            }

            $user->menutype = $raw_profile['menutype'];
            $user->profile = $raw_profile['profile'];
            $link = $m_application->getFirstPage();
            
            /* call $m_profile->getStepByFnum to detect edit or read-only */
            $raw = $m_profile->getStepByFnum($user->fnum);

            $start_date = $raw->start_date;
            $end_date = $raw->end_date;

            /* get editable status // output status */
            $editable_status = $raw->editable_status;
            $output_status = $raw->output_status;

            /* check status, time before deciding */
            $can_edit_form = !in_array($user->status, $editable_status);

            /* get now moment */
            $offset = $app->get('offset', 'UTC');
            $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
            $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
            $now = $dateTime->format('Y-m-d H:i:s');

            if(!empty($fnum)) {
                $is_dead_line_passed = ($now > $end_date || $now < $start_date) ? true : false;
                $is_campaign_started = ($now > $start_date || $now < $end_date) ? true : false;
            }
            else{
                $is_dead_line_passed = ($now > $end_date || $now < $start_date) ? true : false;
                $is_campaign_started = ($now >= $start_date) ? true : false;
            }

            // once access condition is not correct, redirect page
            $reload_url = true;

            $can_edit = EmundusHelperAccess::asAccessAction(1, 'u', $user->id, $fnum);
            $can_read = EmundusHelperAccess::asAccessAction(1, 'r', $user->id, $fnum);

            $read_url = str_replace('view=form', 'view=details', $link);

            
            if (!empty($fnum)) {
                // Check campaign limit, if the limit is obtained, then we set the deadline to true
                $m_campaign = new EmundusModelCampaign;

                $isLimitObtained = $m_campaign->isLimitObtained($user->fnums[$fnum]->campaign_id);

                if ($fnum == @$user->fnum) {
                    //try to access edit view
                    if(!$can_edit && !$can_edit_form){
                        $app->enqueueMessage(JText::_('APPLICATION_READ_ONLY'), 'error');
                    } elseif ($is_dead_line_passed){
                        $app->enqueueMessage(JText::_('APPLICATION_PERIOD_PASSED'), 'error');
                    } elseif (!$is_campaign_started){
                        $app->enqueueMessage(JText::_('APPLICATION_PERIOD_NOT_STARTED'), 'error');
                    }
                }
                // FNUM sent not like user fnum (partner or bad FNUM)
                else {
                    $document = JFactory::getDocument();
                    $document->addStyleSheet("media/com_fabrik/css/fabrik.css" );

                    if ($view == 'form') {
                        if ($can_edit) {
                            $reload_url = false;
                        }
                    } else {
                        //try to access detail view or other
                        if ($can_read) {
                            $reload_url = false;
                        }
                    }
                }
            }

            if (isset($user->fnum) && !empty($user->fnum)) {

                if (in_array($user->id, $applicants)) {
                    $app->redirect($link . '&usekey=fnum&rowid=' . $user->fnum . '&r=' . $_reload.'&phase=' . $phase);
                } else {
                    if (($is_dead_line_passed && $can_edit_after_deadline == 0) || !$is_campaign_started || $isLimitObtained === true) {
                        if ($isLimitObtained === true) {
                            $app->enqueueMessage(JText::_('APPLICATION_LIMIT_OBTAINED'), 'error');
                        } else {
                            $app->enqueueMessage(JText::_('APPLICATION_PERIOD_PASSED'), 'error');
                        }

                        $app->redirect($read_url . '&usekey=fnum&rowid=' . $user->fnum . '&r=' . $_reload.'&phase=' . $phase);
                    } else {
                        if ($can_edit_form) {
                            if ($can_edit_until_deadline != 0 || $can_edit_after_deadline != 0) {
                                $app->redirect($link . '&usekey=fnum&rowid=' . $user->fnum . '&r=' . $_reload.'&phase=' . $phase);
                            } else {
                                $app->redirect($read_url . '&usekey=fnum&rowid=' . $user->fnum . '&r=' . $_reload.'&phase=' . $phase);
                            }
                        } else {
                            $app->redirect($link . '&usekey=fnum&rowid=' . $user->fnum . '&r=' . $_reload.'&phase=' . $phase);
                        }

                    }
                }

            }
        }



        /// get the next url --> formid, menutype
        $this->query = 'SELECT CONCAT(link,"&Itemid=",id) FROM #__menu WHERE published=1 AND menutype = "' . $user->menutype . '" AND access IN (' . implode(',', $levels) . ') AND parent_id != 1 AND lft = 2+(
					SELECT menu.lft FROM `#__menu` AS menu WHERE menu.published=1 AND menu.parent_id>1 AND menu.menutype="' . $user->menutype . '" AND SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)=' . $formid . ')';

        $this->db->setQuery($this->query);
        $link = $this->db->loadResult();

        if (empty($link)) {
            try {
                $this->query = 'SELECT CONCAT(link,"&Itemid=",id) FROM #__menu WHERE published=1 AND menutype = "' . $user->menutype . '"  AND access IN (' . implode(',', $levels) . ')
						AND parent_id != 1 AND lft = 4+(
								SELECT menu.lft
								FROM `#__menu` AS menu
								WHERE menu.published=1 AND menu.parent_id>1 AND menu.menutype="' . $user->menutype . '"
								AND SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)=' . $formid . ')';

                $this->db->setQuery($this->query);
                $link = $this->db->loadResult();
            } catch (Exception $e) {
                $error = JUri::getInstance() . ' :: USER ID : ' . $user->id . ' -> ' . $e->getMessage();
                JLog::add($error, JLog::ERROR, 'com_emundus');
            }

            if (empty($link)) {
                try {
                    $this->query = 'SELECT CONCAT(link,"&Itemid=",id) FROM #__menu WHERE published=1 AND menutype = "' . $user->menutype . '" AND type!="separator" AND published=1 AND alias LIKE "checklist%"';
                    $this->db->setQuery($this->query);
                    $link = $this->db->loadResult();

                } catch (Exception $e) {
                    $error = JUri::getInstance() . ' :: USER ID : ' . $user->id . ' -> ' . $e->getMessage();
                    JLog::add($error, JLog::ERROR, 'com_emundus');
                }

                if (empty($link)) {
                    try {
                        $this->query = 'SELECT CONCAT(link,"&Itemid=",id) FROM #__menu WHERE published=1 AND menutype = "' . $user->menutype . '" AND type LIKE "component" AND published=1 AND level = 1 ORDER BY id ASC';
                        $this->db->setQuery($this->query);
                        $link = $this->db->loadResult();
                    } catch (Exception $e) {
                        $error = JUri::getInstance() . ' :: USER ID : ' . $user->id . ' -> ' . $e->getMessage();
                        JLog::add($error, JLog::ERROR, 'com_emundus');
                    }
                }
            }
        }

        $last_page = $m_profile->getLastPage($user->menutype)->link;
        $last_form_id = (explode('formid=', $last_page))[1];

        $next_form_id = (explode('&Itemid=', explode('formid=', $link)[1]))[0];

//        if($formid !== $_nextFormID)          --> keep this line
        if ($formid !== $last_form_id) {
            $app->redirect($link . '&usekey=fnum&rowid=' . $user->fnum . '&r=' . $_reload.'&phase=' . $phase);

            /// insert emundusredirect plugin here ---
            if ($copy_form === 1 && isset($user->fnum)) {

                JLog::addLogger(['text_file' => 'com_emundus.duplicate.php'], JLog::ALL, ['duplicate']);

                // Get some form definition
                $data = $this->getProcessData();
                $table = explode('___', key($data));
                $table_name = $table[0];
                $fnums = $user->fnums;
                unset($fnums[$user->fnum]);

                $fabrik_repeat_group = array();

                if (!empty($data['fabrik_repeat_group'])) {
                    foreach ($data['fabrik_repeat_group'] as $key => $value) {
                        $fabrik_repeat_group[] = $key;
                    }
                }

                // only repeated groups
                $fabrik_group_rowids_key = array();

                if (!empty($data['fabrik_group_rowids'])) {
                    foreach ($data['fabrik_group_rowids'] as $key => $value) {
                        $repeat_table_name = $table_name . '_' . $key . '_repeat';
                        $this->query = 'SELECT id FROM ' . $repeat_table_name . ' WHERE parent_id=' . $data['rowid'];
                        $this->db->setQuery($this->query);
                        $fabrik_group_rowids_key[$key] = $this->db->loadColumn();
                    }
                }

                // Only if other application files found
                if (!empty($fnums)) {
                    $this->query = 'SELECT * FROM ' . $table_name . ' WHERE id=' . $data['rowid'];
                    $this->db->setQuery($this->query);
                    $parent_data = $this->db->loadAssoc();
                    unset($parent_data['fnum']);
                    unset($parent_data['id']);

                    // new record
                    if (isset($data['usekey_newrecord']) && $data['usekey_newrecord'] == 1) {
                        // Parent table
                        $parent_id = array();
                        foreach ($fnums as $key => $fnum) {
                            $this->query = 'INSERT INTO `' . $table_name . '` (`' . implode('`,`', array_keys($parent_data)) . '`, `fnum`) VALUES ';
                            $this->query .= '(' . implode(',', $this->db->Quote($parent_data)) . ', ' . $this->db->Quote($key) . ')';

                            $this->db->setQuery($this->query);

                            try {
                                $this->db->execute();
                                $parent_id[] = $this->db->insertid();

                            } catch (Exception $e) {
                                $error = JUri::getInstance() . ' :: USER ID : ' . $user->id . ' -> ' . $e->getMessage();
                                JLog::add($error, JLog::ERROR, 'com_emundus');
                            }
                        }

                        // Repeated table
                        foreach ($fabrik_group_rowids_key as $key => $rowids) {
                            if (count($rowids) > 0) {
                                $repeat_table_name = $table_name . '_' . $key . '_repeat';
                                $this->query = 'SELECT * FROM `' . $repeat_table_name . '` WHERE id IN (' . implode(',', $rowids) . ')';
                                try {
                                    $this->db->setQuery($this->query);
                                    $repeat_data = $this->db->loadAssocList();
                                } catch (Exception $e) {
                                    $error = JUri::getInstance() . ' :: USER ID : ' . $user->id . ' -> ' . $e->getMessage();
                                    JLog::add($error, JLog::ERROR, 'com_emundus');
                                }

                                if (!empty($repeat_data)) {
                                    foreach ($parent_id as $parent) {
                                        $parent_data = array();
                                        foreach ($repeat_data as $key => $d) {
                                            unset($d['parent_id']);
                                            unset($d['id']);
                                            $columns = '`' . implode('`,`', array_keys($d)) . '`';
                                            $parent_data[] = '(' . implode(',', $this->db->Quote($d)) . ', ' . $parent . ')';
                                        }
                                        $this->query = 'INSERT INTO `' . $repeat_table_name . '` (' . $columns . ', `parent_id`) VALUES ';
                                        $this->query .= implode(',', $parent_data);
                                        $this->db->setQuery($this->query);

                                        try {
                                            $this->db->execute();
                                        } catch (Exception $e) {
                                            $error = JUri::getInstance() . ' :: USER ID : ' . $user->id . ' -> ' . $e->getMessage();
                                            JLog::add($error, JLog::ERROR, 'com_emundus');
                                        }
                                    }
                                }
                            }
                        }

                    } else {
                        // Parent table
                        $updated_fnum = array();
                        foreach ($fnums as $fnum => $f) {
                            $this->query = 'UPDATE `' . $table_name . '` SET ';
                            $parent_update = array();
                            foreach ($parent_data as $key => $value) {
                                $parent_update[] = '`' . $key . '`=' . $this->db->Quote($value);
                            }
                            $this->query .= implode(',', $parent_update);
                            $this->query .= ' WHERE fnum like ' . $this->db->Quote($fnum);

                            $this->db->setQuery($this->query);
                            try {
                                $this->db->execute();
                                $updated_fnum[] = $fnum;
                            } catch (Exception $e) {
                                $error = JUri::getInstance() . ' :: USER ID : ' . $user->id . ' -> ' . $e->getMessage();
                                JLog::add($error, JLog::ERROR, 'com_emundus');
                            }
                        }

                        if (!empty($updated_fnum)) {
                            $this->query = 'SELECT id FROM `' . $table_name . '` WHERE fnum IN (' . implode(',', $this->db->Quote($updated_fnum)) . ')';
                            $this->db->setQuery($this->query);
                            $parent_id = $this->db->loadColumn();
                        }

                        // Repeated table
                        foreach ($fabrik_group_rowids_key as $key => $rowids) {
                            if (!empty($rowids)) {
                                $repeat_table_name = $table_name . '_' . $key . '_repeat';
                                $this->query = 'SELECT * FROM `' . $repeat_table_name . '` WHERE id IN (' . implode(',', $rowids) . ')';
                                try {
                                    $this->db->setQuery($this->query);
                                    $repeat_data = $this->db->loadAssocList('id');
                                } catch (Exception $e) {
                                    $error = JUri::getInstance() . ' :: USER ID : ' . $user->id . ' -> ' . $e->getMessage();
                                    JLog::add($error, JLog::ERROR, 'com_emundus');
                                }
                                if (!empty($parent_id)) {
                                    $this->query = 'DELETE FROM `' . $repeat_table_name . '` WHERE parent_id IN (' . implode(',', $parent_id) . ')';
                                    $this->db->setQuery($this->query);
                                    try {
                                        $this->db->execute();
                                    } catch (Exception $e) {
                                        $error = JUri::getInstance() . ' :: USER ID : ' . $user->id . ' -> ' . $e->getMessage();
                                        JLog::add($error, JLog::ERROR, 'com_emundus');
                                    }

                                    if (!empty($repeat_data)) {
                                        foreach ($parent_id as $parent) {
                                            $parent_data = array();
                                            foreach ($repeat_data as $key => $d) {
                                                unset($d['parent_id']);
                                                unset($d['id']);
                                                $columns = '`' . implode('`,`', array_keys($d)) . '`';
                                                $parent_data[] = '(' . implode(',', $this->db->Quote($d)) . ', ' . $parent . ')';
                                            }

                                            $this->query = 'INSERT INTO `' . $repeat_table_name . '` (' . $columns . ', `parent_id`) VALUES ';
                                            $this->query .= implode(',', $parent_data);
                                            $this->db->setQuery($this->query);
                                            try {
                                                $this->db->execute();
                                            } catch (Exception $e) {
                                                $error = JUri::getInstance() . ' :: USER ID : ' . $user->id . ' -> ' . $e->getMessage();
                                                JLog::add($error, JLog::ERROR, 'com_emundus');
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $toStatus = (int)$this->getParam('emundusprocessworkflow_field_status', '-1');

                if (isset($toStatus) && $toStatus != -1 && isset($user->fnum)) {
                    $this->query = $this->db->getQuery(true);
                    // Conditions for which status should be updated.
                    // We only want to update the user's status to another value if it's 0 (NOT SENT).
                    $conditions = [
                        $this->db->quoteName('fnum') . ' LIKE ' . $user->fnum,
                        $this->db->quoteName('status') . ' = 0'
                    ];

                    $this->query->update($this->db->quoteName('#__emundus_campaign_candidature'))
                        ->set([$this->db->quoteName('status') . ' = ' . $toStatus])
                        ->where($conditions);

                    try {
                        $this->db->setQuery($this->query);
                        $this->db->execute();
                    } catch (Exception $e) {
                        JLog::add('Error updating file status : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
                    }
                }

                require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'application.php');
                $m_application = new EmundusModelApplication();

                /*
                * REDIRECTION ONCE DUPLICATION IS DONE
                */
                require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');

                if (in_array($user->profile, $applicant_profiles) && EmundusHelperAccess::asApplicantAccessLevel($user->id)) {
                    $levels = JAccess::getAuthorisedViewLevels($user->id);

                    if (isset($user->fnum)) {
                        $m_application->getFormsProgress($user->fnum);
                        $m_application->getAttachmentsProgress($user->fnum);
                    }
                } else {

                    try {
                        $this->query = 'SELECT db_table_name FROM `#__fabrik_lists` WHERE `form_id` =' . $formid;
                        $this->db->setQuery($this->query);
                        $db_table_name = $this->db->loadResult();

                    } catch (Exception $e) {
                        $error = JUri::getInstance() . ' :: USER ID : ' . $user->id . ' -> ' . $e->getMessage();
                        JLog::add($error, JLog::ERROR, 'com_emundus');
                    }

                    $fnum = $jinput->get($db_table_name . '___fnum');
                    $s1 = JRequest::getVar($db_table_name . '___user', null, 'POST');
                    $s2 = JRequest::getVar('sid', '', 'GET');
                    $student_id = !empty($s2) ? $s2 : $s1;

                    $sid = is_array($student_id) ? $student_id[0] : $student_id;

                    try {

                        $this->query = 'UPDATE `' . $db_table_name . '` SET `user`=' . $sid . ' WHERE fnum like ' . $this->db->Quote($fnum);
                        $this->db->setQuery($this->query);
                        $this->db->execute();

                    } catch (Exception $e) {
                        $error = JUri::getInstance() . ' :: USER ID : ' . $user->id . ' -> ' . $e->getMessage();
                        JLog::add($error, JLog::ERROR, 'com_emundus');
                    }

                    $link = JRoute::_('index.php?option=com_fabrik&view=form&formid=' . $formid . '&usekey=fnum&rowid=' . $fnum . '&tmpl=component');       // add $phase

                    echo "<hr>";
                    echo '<h1><img src="' . JURI::base() . '/media/com_emundus/images/icones/admin_val.png" width="80" height="80" align="middle" /> ' . JText::_("SAVED") . '</h1>';
                    echo "<hr>";
                    exit;
                }

                if ($this->getParam('emundusprocessworkflow_notify_complete_file', 0) == 1) {

                    require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'application.php');
                    require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'checklist.php');
                    $m_application = new EmundusModelApplication;
                    $m_checklist = new EmundusModelChecklist();

                    $attachments = $m_application->getAttachmentsProgress($user->fnum);
                    $forms = $m_application->getFormsProgress($user->fnum);
                    $send_file_url = $m_checklist->getConfirmUrl() . '&usekey=fnum&rowid=' . $user->fnum;

                    if ($attachments >= 100 && $forms >= 100) {

                        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>';
                        echo '<script src="https://code.jquery.com/jquery-3.3.1.slim.js" integrity="sha256-fNXJFIlca05BIO2Y5zh1xrShK3ME+/lYZ0j+ChxX2DA=" crossorigin="anonymous"></script>';
                        die("<script>
                                    $(document).ready(() => {
                                        Swal.fire({
                                            position: 'top',
                                            type: 'info',
                                            title: '" . JText::_('PLG_FABRIK_FORM_EMUNDUSREDIRECT_FILE_COMPLETE') . "',
                                            confirmButtonText: '" . JText::_('PLG_FABRIK_FORM_EMUNDUSREDIRECT_SEND_FILE') . "',
                                            showCancelButton: true,
                                            cancelButtonText: '" . JText::_('PLG_FABRIK_FORM_EMUNDUSREDIRECT_CONTINUE') . "',
                                            onClose: () => {
                                                window.location.href = '" . $link . "';
                                            }
                                        })
                                        .then(confirm => {
                                            if (confirm.value) {
                                                window.location.href = '" . $send_file_url . "';
                                            } else {
                                                window.location.href = '" . $link . "';
                                            }
                                        })
                                  });
                              </script>");
                    }
                }
            }
        }
        else {
            /* confirm post page */
            /// insert emundusconfirmpost (with some changes of worflow) here
            $app = JFactory::getApplication();

//            $app->redirect($last_page . '&Itemid=' . $itemid . '&usekey=fnum&rowid=' . $user->fnum);

            $offset = $app->get('offset', 'UTC');

            try {
                $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
                $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
                $now = $dateTime->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                echo $e->getMessage() . '<br />';
            }

            $student = JFactory::getSession()->get('emundusUser');

            //// get start_date and end_date from $_commonModel
            $res = $m_profile->getStepByFnum($student->fnum);

            /* get start date // end date of each formulaire */
            $start_date = $res->start_date;
            $end_date = $res->end_date;

            $is_dead_line_passed = ($now > $end_date || $now < $start_date) ? true : false;

            // Check campaign limit, if the limit is obtained, then we set the deadline to true
            $isLimitObtained = $m_campaign->isLimitObtained($student->fnums[$student->fnum]->campaign_id);

            // If we've passed the deadline and the user cannot submit (is not in the list of exempt users), block him.
            if (($is_dead_line_passed || $isLimitObtained === true) && !in_array($student->id, $id_applicants)) {
                if ($isLimitObtained === true) {
                    $this->getModel()->formErrorMsg = JText::_('LIMIT_OBTAINED');
                } else {
                    $this->getModel()->formErrorMsg = JText::_('CANDIDATURE_PERIOD_TEXT');
                }
                return false;
            }

            // Database UPDATE data
            //// Applicant cannot delete this attachments now
            if (!$can_edit_until_deadline) {
                $this->query = 'UPDATE #__emundus_uploads SET can_be_deleted = 0 WHERE user_id = ' . $student->id . ' AND fnum like ' . $this->db->Quote($student->fnum);
                $this->db->setQuery($this->query);

                try {
                    $this->db->execute();
                } catch (Exception $e) {
                    // catch any database errors.
                    JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
                }
            }

            JPluginHelper::importPlugin('emundus');
            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onBeforeSubmitFile', [$student->id, $student->fnum]);

            // get the output status
            if (!empty($res->output_status)) {
                $this->query = 'UPDATE #__emundus_campaign_candidature SET submitted=1, date_submitted=' . $this->db->quote($now) . ', status=' . current($res->output_status) . ' WHERE applicant_id=' . $student->id . ' AND campaign_id=' . $student->campaign_id . ' AND fnum like ' . $this->db->Quote($student->fnum);
            } else {
                /// use the default status
                $this->query = 'UPDATE #__emundus_campaign_candidature SET submitted=1, date_submitted=' . $this->db->quote($now) . ', status=' . $this->getParam('emundusprocessworkflow_output_status', '') . ' WHERE applicant_id=' . $student->id . ' AND campaign_id=' . $student->campaign_id . ' AND fnum like ' . $this->db->Quote($student->fnum);
            }
            $this->db->setQuery($this->query);

            try {
                $this->db->execute();
            } catch (Exception $e) {
                JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            }

            $this->query = 'UPDATE #__emundus_declaration SET time_date=NOW() WHERE user=' . $student->id . ' AND fnum like ' . $this->db->Quote($student->fnum);
            $this->db->setQuery($this->query);

            try {
                $this->db->execute();
            } catch (Exception $e) {
                JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            }
            $dispatcher->trigger('onAfterSubmitFile', [$student->id, $student->fnum]);

            $student->candidature_posted = 1;

            // Send emails defined in trigger
            if (!is_null($student->output_status)) {
                $step = $res->output_status;
            } else {
                $step = $this->getParam('emundusprocessworkflow_output_status', '');
            }

            $code = array($student->code);
            $to_applicant = '0,1';

            //            $m_emails->sendEmailTrigger($step, $code, $to_applicant, $student, $student->campaign_id);                 // add campaign here from session

            // If pdf exporting is activated
            if ($export_pdf == 1) {
                $fnum = $student->fnum;
                $fnumInfo = $m_files->getFnumInfos($student->fnum);
                $files_list = array();

                // Build pdf file
                if (is_numeric($fnum) && !empty($fnum)) {
                    // Check if application form is in custom order
                    if (!empty($application_form_order)) {
                        $application_form_order = explode(',', $application_form_order);
                        $files_list[] = EmundusHelperExport::buildFormPDF($fnumInfo, $fnumInfo['applicant_id'], $fnum, 1, $application_form_order);
                    } else {
                        $files_list[] = EmundusHelperExport::buildFormPDF($fnumInfo, $fnumInfo['applicant_id'], $fnum, 1);
                    }

                    // Check if pdf attachements are in custom order
                    if (!empty($attachment_order)) {
                        $attachment_order = explode(',', $attachment_order);
                        foreach ($attachment_order as $attachment_id) {
                            // Get file attachements corresponding to fnum and type id
                            $files[] = $m_application->getAttachmentsByFnum($fnum, null, $attachment_id);
                        }
                    } else {
                        // Get all file attachements corresponding to fnum
                        $files[] = $m_application->getAttachmentsByFnum($fnum, null, null);
                    }
                    // Break up the file array and get the attachement files
                    foreach ($files as $file) {
                        $tmpArray = array();
                        EmundusHelperExport::getAttachmentPDF($files_list, $tmpArray, $file, $fnumInfo['applicant_id']);
                    }
                }

                if (count($files_list) > 0) {
                    // all PDF in one file
                    require_once(JPATH_LIBRARIES . DS . 'emundus' . DS . 'fpdi.php');
                    $pdf = new ConcatPdf();

                    $pdf->setFiles($files_list);
                    $pdf->concat();
                    if (isset($tmpArray)) {
                        foreach ($tmpArray as $fn) {
                            unlink($fn);
                        }
                    }

                    // Build filename from tags, we are using helper functions found in the email model, not sending emails ;)
                    $post = array('FNUM' => $fnum, 'CAMPAIGN_YEAR' => $fnumInfo['year'], 'PROGRAMME_CODE' => $fnumInfo['training']);
                    $tags = $m_emails->setTags($student->id, $post, $fnum);
                    $application_form_name = preg_replace($tags['patterns'], $tags['replacements'], $application_form_name);
                    $application_form_name = $m_emails->setTagsFabrik($application_form_name, array($fnum));

                    // Format filename
                    $application_form_name = $m_emails->stripAccents($application_form_name);
                    $application_form_name = preg_replace('/[^A-Za-z0-9 _.-]/', '', $application_form_name);
                    $application_form_name = preg_replace('/\s/', '', $application_form_name);
                    $application_form_name = strtolower($application_form_name);

                    // If a file exists with that name, delete it
                    if (file_exists(JPATH_BASE . DS . 'tmp' . DS . $application_form_name)) {
                        unlink(JPATH_BASE . DS . 'tmp' . DS . $application_form_name);
                    }

                    // Ouput pdf with desired file name
                    $pdf->Output(JPATH_BASE . DS . 'tmp' . DS . $application_form_name . ".pdf", 'F');

                    // If export path is defined
                    if (!empty($export_path)) {
                        $export_path = preg_replace($tags['patterns'], $tags['replacements'], $export_path);
                        $export_path = $m_emails->setTagsFabrik($export_path, array($fnum));

                        // Sanitize and build filename.
                        $export_path = strtr(utf8_decode($export_path), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
                        $export_path = strtolower($export_path);
                        $export_path = preg_replace('`\s`', '-', $export_path);
                        $export_path = str_replace(',', '', $export_path);
                        $directories = explode('/', $export_path);

                        $d = '';
                        foreach ($directories as $dir) {
                            $d .= $dir . '/';
                            if (!file_exists(JPATH_BASE . DS . $d)) {
                                mkdir(JPATH_BASE . DS . $d);
                                chmod(JPATH_BASE . DS . $d, 0755);
                            }
                        }
                        if (file_exists(JPATH_BASE . DS . $export_path . $application_form_name . ".pdf")) {
                            unlink(JPATH_BASE . DS . $export_path . $application_form_name . ".pdf");
                        }
                        copy(JPATH_BASE . DS . 'tmp' . DS . $application_form_name . ".pdf", JPATH_BASE . DS . $export_path . $application_form_name . ".pdf");
                    }
                    if (file_exists(JPATH_BASE . DS . "images" . DS . "emundus" . DS . "files" . DS . $student->id . DS . $fnum . "_application_form_pdf.pdf")) {
                        unlink(JPATH_BASE . DS . "images" . DS . "emundus" . DS . "files" . DS . $student->id . DS . $fnum . "_application_form_pdf.pdf");
                    }
                    copy(JPATH_BASE . DS . 'tmp' . DS . $application_form_name . ".pdf", JPATH_BASE . DS . "images" . DS . "emundus" . DS . "files" . DS . $student->id . DS . $fnum . "_application_form_pdf.pdf");
                }
            }

            $app->redirect($this->getParam('emundusprocessworkflow_redirect_jump_page'), JText::_($this->getParam('emundusprocessworkflow_thanks_message')));
        }
    }

    protected function raiseError(&$err, $field, $msg) {
        $app = JFactory::getApplication();

        if ($app->isAdmin()) {
            $app->enqueueMessage($msg, 'notice');
        }
        else {
            $err[$field][0][] = $msg;
        }
    }
}