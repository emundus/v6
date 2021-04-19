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
    var $_commonModel = null;

    public function __construct(&$subject, $config = array()) {
        parent::__construct($subject, $config);
        $this->db = JFactory::getDbo();
        $this->query = $this->db->getQuery(true);
        $this->_commonModel = JModelLegacy::getInstance('common', 'EmundusworkflowModel');
    }

    /**
     * Get an element name
     *
     * @param   string  $pname  Params property name to look up
     * @param   bool    $short  Short (true) or full (false) element name, default false/full
     *
     * @return	string	element full name
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
     * @param   string  $pname    Params property name to get the value for
     * @param   array   $data     Posted form data
     * @param   mixed   $default  Default value
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

            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus_workflow'.DS.'models'.DS.'common.php');        /// import workflow mode

//            $_eMWorkflow = new EmundusworkflowModelcommon;

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

            // ***************************** use $this->_commonModel to check the constraint date
            //// get the start_date, end_date from $user->fnum and $user->status

            $_startDate = $this->_commonModel->getStepByFnumAndStatus($user->fnum,$user->status)->start_date;
            $_endDate = $this->_commonModel->getStepByFnumAndStatus($user->fnum,$user->status)->end_date;

            /// retrieve all editable status from stepflow --> $is_editable_status
            $_editable_status = $this->_commonModel->updateSessionTree($user->fnum,$user->status)->editable_status;              //// gettype --> array
            $_is_editable_status = !in_array($user->status, $_editable_status);

            // *****************************

            if(!empty($fnum)) {
                $is_dead_line_passed = ($now > $_endDate || $now < $_startDate) ? true : false;
                $is_campaign_started = ($now >= $_startDate) ? true : false;
            }
            else{
                $is_dead_line_passed = ($now > $_endDate || $now < $_startDate) ? true : false;
                $is_campaign_started = ($now >= $_startDate) ? true : false;
            }

//            var_dump($formid);
//            die;
            //// ************* end of date time checking

            /// corriger ici --> changer $this->getParam('applicationsent_status', 0) par la liste des status d'edition de cette etape
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
                        //if ((!$is_dead_line_passed && $isLimitObtained !== true) || in_array($user->id, $applicants) || ($is_app_sent && !$is_dead_line_passed && $can_edit_until_deadline && $isLimitObtained !== true) || $can_edit) {
                        if ((!$is_dead_line_passed && $isLimitObtained !== true) || in_array($user->id, $applicants) || ($_is_editable_status && !$is_dead_line_passed && $can_edit_until_deadline && $isLimitObtained !== true) || $can_edit) {
                            var_dump('editable1');
                            $reload_url = false;
                        }
                    }
                    //try to access detail view or other
                    else {
                        var_dump('un-editable1');
                        $reload_url = false;
                    }
                }
                // FNUM sent not like user fnum (partner or bad FNUM)
                else {
                    $document = JFactory::getDocument();
                    $document->addStyleSheet("media/com_fabrik/css/fabrik.css" );

                    if ($view == 'form') {
                        if ($can_edit) {
                            var_dump('editable2');
                            $reload_url = false;
                        }
                    } else {
                        //try to access detail view or other
                        if ($can_read) {
                            var_dump('un-editable2');
                            $reload_url = false;
                        }
                    }
                }
            }

            if (isset($user->fnum) && !empty($user->fnum)) {

                if (in_array($user->id, $applicants)) {

                    if ($reload_url) {
                        var_dump('editable3');
                        $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload);
                    }

                } else {

                    if ($is_dead_line_passed || !$is_campaign_started || $isLimitObtained === true) {
                        var_dump('un-editable3');
                        if ($reload_url) {
                            if ($isLimitObtained === true) {
                                JError::raiseNotice(401, JText::_('LIMIT_OBTAINED'));
                            } else {
                                JError::raiseNotice(401, JText::_('PERIOD'));
                            }
                            var_dump('un-editable4');
                            $mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload);
                        }

                    } else {

                        //if ($is_app_sent) {
                        if ($_is_editable_status) {
                            if ($can_edit_until_deadline != 0) {
                                if ($reload_url) {
                                    var_dump('editable5');
                                    $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload);
                                }
                            } else {
                                if ($reload_url) {
                                    var_dump('un-editable5');
                                    $mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload);
                                }
                            }
                        } else {
                            if ($reload_url) {
                                var_dump('editable6');
                                $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload);
                            }
                        }

                    }
                }


            }
            else {

                if ($can_edit == 1) {
                    return true;
                } else {
                    if ($can_read == 1) {
                        if ($reload < 3) {
                            $reload++;
                            var_dump('un-editable7');
                            $mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$fnum."&r=".$reload);
                        }
                    } else {
                        JError::raiseNotice('ACCESS_DENIED', JText::_('ACCESS_DENIED'));
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
                        $query = 'SELECT '.implode(',', $elements).' FROM '.$table->db_table_name.' WHERE user='.$user->id;
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
                                            $query = 'SELECT '.implode(',', $d['element_name']).' FROM '.$d['table'].' WHERE parent_id='.$parent_id;
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
                            var_dump('editable8');
                            $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload);
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

    ////// on after process
    public function onAfterProcess() {
        $app = JFactory::getApplication();

        include_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'export.php');

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.submit.php'), JLog::ALL, array('com_emundus'));

        // Get params set in eMundus component configuration
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $can_edit_until_deadline    = $eMConfig->get('can_edit_until_deadline', 0);
        $application_form_order     = $eMConfig->get('application_form_order', null);
        $attachment_order           = $eMConfig->get('attachment_order', null);
        $application_form_name      = $eMConfig->get('application_form_name', "application_form_pdf");
        $export_pdf                 = $eMConfig->get('export_application_pdf', 0);
        $export_path                = $eMConfig->get('export_path', null);
        $id_applicants              = explode(',',$eMConfig->get('id_applicants', '0'));

        $m_application  = new EmundusModelApplication;
        $m_files        = new EmundusModelFiles;
        $m_emails       = new EmundusModelEmails;
        $m_campaign = new EmundusModelCampaign;

        $offset = $app->get('offset', 'UTC');
        try {
            $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
            $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
            $now = $dateTime->format('Y-m-d H:i:s');
        }
        catch (Exception $e) {
            echo $e->getMessage() . '<br />';
        }

        $student = JFactory::getSession()->get('emundusUser');

        //// get start_date and end_date from $_commonModel
        $_startDate = $this->_commonModel->getStepByFnumAndStatus($student->fnum,$student->status)->start_date;
        $_endDate = $this->_commonModel->getStepByFnumAndStatus($student->fnum,$student->status)->end_date;

        $is_dead_line_passed = ($now > $_endDate || $now < $_startDate) ? true : false;

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
            $query = 'UPDATE #__emundus_uploads SET can_be_deleted = 0 WHERE user_id = '.$student->id. ' AND fnum like '.$this->db->Quote($student->fnum);
            $this->db->setQuery($query);

            try {
                $this->db->execute();
            } catch (Exception $e) {
                // catch any database errors.
                JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            }
        }

        $jinput = $app->input();

        $formid = $jinput->get('formid');

        $_lastPage = $this->_commonModel->getLastPage($student->menutype)->link;
        $_lastFormID = (explode('formid=', $_lastPage))[1];

        if($formid == $_lastFormID) {

            JPluginHelper::importPlugin('emundus');
            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onBeforeSubmitFile', [$student->id, $student->fnum]);

            // get the output status
            if (!is_null($student->output_status)) {
                //// update to new output_status
                $query = 'UPDATE #__emundus_campaign_candidature SET submitted=1, date_submitted=NOW(), status=' . $student->output_status . ' WHERE applicant_id=' . $student->id . ' AND campaign_id=' . $student->campaign_id . ' AND fnum like ' . $this->db->Quote($student->fnum);
            } else {
                /// use the default status
                $query = 'UPDATE #__emundus_campaign_candidature SET submitted=1, date_submitted=NOW(), status=' . $this->getParam('emundusconfirmpost_status', '1') . ' WHERE applicant_id=' . $student->id . ' AND campaign_id=' . $student->campaign_id . ' AND fnum like ' . $this->db->Quote($student->fnum);
            }
            $this->db->setQuery($query);

            try {
                $this->db->execute();

            } catch (Exception $e) {
                JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            }

            $query = 'UPDATE #__emundus_declaration SET time_date=NOW() WHERE user=' . $student->id . ' AND fnum like ' . $this->db->Quote($student->fnum);
            $this->db->setQuery($query);

            try {
                $this->db->execute();
            } catch (Exception $e) {
                JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            }
            $dispatcher->trigger('onAfterSubmitFile', [$student->id, $student->fnum]);

            $student->candidature_posted = 1;

            // Send emails defined in trigger
            if (!is_null($student->output_status)) {
                $step = $student->output_status;
            } else {
                $step = $this->getParam('emundusconfirmpost_status', '1');
            }

            $code = array($student->code);
            $to_applicant = '0,1';
            $m_emails->sendEmailTrigger($step, $code, $to_applicant, $student);

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
        }
        else {
            /// do nothing here
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
