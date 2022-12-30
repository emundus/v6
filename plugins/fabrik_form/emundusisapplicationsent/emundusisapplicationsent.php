<?php
/**
 * @version 2: emundusisapplicationsent 2018-12-04 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Locks access to a file if the file is not of a certain status.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.juseremundus
 * @since       3.0
 */

class PlgFabrik_FormEmundusisapplicationsent extends plgFabrik_Form {


    /**
     * Status field
     *
     * @var  string
     */
    protected $URLfield = '';

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
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
            $m_campaign = new EmundusModelCampaign();

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
            $copy_exclude_forms = $eMConfig->get('copy_exclude_forms', []);
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

            $current_fnum = !empty($fnum) ? $fnum : $user->fnum;
            $current_phase = $m_campaign->getCurrentCampaignWorkflow($current_fnum);
            if (!empty($current_phase) && !empty($current_phase->end_date)) {
                $current_end_date = $current_phase->end_date;
                $current_start_date = $current_phase->start_date;
            }  else if ($this->getParam('admission', 0) == 1) {
                $current_end_date = @$user->fnums[$current_fnum]->admission_end_date;
                $current_start_date = @$user->fnums[$current_fnum]->admission_start_date;
            } else {
                $current_end_date = !empty(@$user->fnums[$current_fnum]->end_date) ? @$user->fnums[$current_fnum]->end_date : @$user->end_date;
                $current_start_date = @$user->fnums[$current_fnum]->start_date;
            }

            $is_campaign_started = strtotime(date($now)) >= strtotime($current_start_date);
            if (!$is_campaign_started && !in_array($user->id, $applicants)) {
                // STOP HERE, the campaign or step is not started yet. Redirect to main page
                $mainframe->enqueueMessage(JText::_('APPLICATION_PERIOD_NOT_STARTED'), 'error');
                $mainframe->redirect('/');
            }

            $is_dead_line_passed = strtotime(date($now)) > strtotime($current_end_date);

            $edit_status = array();
            if (!empty($current_phase) && !empty($current_phase->entry_status)) {
                $edit_status = $current_phase->entry_status;
            }
            $edit_status = array_merge(explode(',', $this->getParam('applicationsent_status', 0)), $edit_status);
            $is_app_sent = !in_array(@$user->status, $edit_status);
            $can_edit = EmundusHelperAccess::asAccessAction(1, 'u', $user->id, $fnum);
            $can_read = EmundusHelperAccess::asAccessAction(1, 'r', $user->id, $fnum);

            // once access condition is not correct, redirect page
            $reload_url = true;

            // FNUM sent by URL is like user fnum (means an applicant trying to open a file)
            if (!empty($fnum)) {

                // Check campaign limit, if the limit is obtained, then we set the deadline to true
                $m_profile = new EmundusModelProfile;
                $fnumDetail = $m_profile->getFnumDetails($fnum);

                $isLimitObtained = $m_campaign->isLimitObtained($user->fnums[$fnum]->campaign_id);

                if ($fnum == @$user->fnum) {
                    //try to access edit view
                    if ($view == 'form') {
                        if ((!$is_dead_line_passed && $isLimitObtained !== true) || in_array($user->id, $applicants) || ($is_app_sent && !$is_dead_line_passed && $can_edit_until_deadline && $isLimitObtained !== true) || ($is_dead_line_passed && $can_edit_after_deadline && $isLimitObtained !== true) || $can_edit) {
                            $reload_url = false;
                        }
                    }
                    //try to access detail view or other
                    else {
                        if (!$can_edit && $is_app_sent) {
                            $mainframe->enqueueMessage(JText::_('APPLICATION_READ_ONLY'), 'error');
                        } else if ($fnumDetail['published'] == -1) {
                            $mainframe->enqueueMessage(JText::_('DELETED_FILE'), 'error');
                        } else if ($is_dead_line_passed) {
                            $mainframe->enqueueMessage(JText::_('APPLICATION_PERIOD_PASSED'), 'error');
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
                        $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload);
                    }

                } else {

                    if (($is_dead_line_passed && $can_edit_after_deadline == 0) || $isLimitObtained === true) {
                        if ($reload_url) {
                            if ($isLimitObtained === true) {
                                $mainframe->enqueueMessage(JText::_('APPLICATION_LIMIT_OBTAINED'), 'error');
                            } else {
                                $mainframe->enqueueMessage(JText::_('APPLICATION_PERIOD_PASSED'), 'error');
                            }
                            $mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload);
                        }

                    } else {

                        if ($is_app_sent) {
                            if ($can_edit_until_deadline != 0 || $can_edit_after_deadline != 0) {
                                if ($reload_url) {
                                    $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload);
                                }
                            } else {
                                if ($reload_url) {
                                    $mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload);
                                }
                            }
                        } else {
                            if ($reload_url) {
                                $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&r=".$reload);
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
                            $mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$fnum."&r=".$reload);
                        }
                    } else {
                        $mainframe->enqueueMessage(JText::_('ACCESS_DENIED'), 'error');
                        $mainframe->redirect("index.php");
                    }
                }
            }

            if ($copy_application_form == 1 && isset($user->fnum) && !in_array($formModel->getId(), $copy_exclude_forms)) {
                if (empty($formModel->getRowId())) {
                    $db = JFactory::getDBO();
                    $table = $listModel->getTable();
                    $table_elements = $formModel->getElementOptions(false, 'name', false, false, array(), '', true);
                    $rowid = $formModel->data["rowid"];

                    $elements = array();
                    foreach ($table_elements as $element) {
                        $elements[] = $element->value;
                    }

                    // check if data stored for current user
                    try {
                        $query = 'SELECT '.implode(',', $db->quoteName($elements)).' FROM '.$table->db_table_name.' WHERE user='.$user->id;
                        $db->setQuery($query);
                        $stored = $db->loadAssoc();
                        if (!empty($stored)) {
                            // update form data
                            $parent_id = $stored['id'];
                            unset($stored['id']);
                            unset($stored['fnum']);

                            foreach ($stored as $key => $store) {
                                $formModel->data[$table->db_table_name . '___' . $key] = $store;
                                $formModel->data[$table->db_table_name . '___' . $key . '_raw'] = $store;
                            }

                            $groups = $formModel->getFormGroups(true);
                            if (count($groups) > 0) {
                                foreach ($groups as $group) {
                                    $group_params = json_decode($group->gparams);
                                    if (isset($group_params->repeat_group_button) && $group_params->repeat_group_button == 1 && !in_array($group->name,['id','parent_id','fnum','user','date_time'])) {
                                        $query = 'SELECT table_join FROM #__fabrik_joins WHERE group_id = ' . $group->group_id . ' AND table_key LIKE "id" AND table_join_key LIKE "parent_id"';
                                        $db->setQuery($query);
                                        try {
                                            $repeat_table = $db->loadResult();
                                        } catch (Exception $e) {
                                            $error = JUri::getInstance() . ' :: USER ID : ' . $user->id . ' -> ' . $e->getMessage();
                                            JLog::add($error, JLog::ERROR, 'com_emundus');
                                            $repeat_table = $table->db_table_name . '_' . $group->group_id . '_repeat';
                                        }

                                        $query = 'SELECT ' . $db->quoteName($group->name) . ' FROM ' . $repeat_table . ' WHERE parent_id=' . $parent_id;
                                        $db->setQuery($query);
                                        $stored = $db->loadColumn();

                                        if (!empty($stored)) {
                                            foreach ($stored as $store) {
                                                $formModel->data[$repeat_table . '___' . $group->name][] = $store;
                                                $formModel->data[$repeat_table . '___' . $group->name . '_raw'][] = $store;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // sync documents uploaded
                        // 1. get list of uploaded documents for previous file defined as duplicated
                        $fnums = $user->fnums;
                        unset($fnums[$user->fnum]);

                        if (!empty($fnums)) {
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

                            $query = 'SELECT count(id) FROM #__emundus_uploads WHERE user_id='.$user->id.' AND fnum like '.$db->Quote($user->fnum);
                            $db->setQuery($query);
                            $already_cloned = $db->loadResult();

                            if (!empty($stored) && $already_cloned == 0) {
                                // 2. copy DB définition and duplicate files in applicant directory
                                foreach ($stored as $row) {
                                    $src = $row['filename'];
                                    $ext = explode('.', $src);
                                    $ext = $ext[count($ext)-1];;
                                    $cpt = 0-(int)(strlen($ext)+1);
                                    $dest = substr($row['filename'], 0, $cpt).'-'.$row['id'].'.'.$ext;
                                    $nbmax = $row['nbmax'];
                                    $row['filename'] = $dest;
                                    $row['campaign_id'] = $fnumDetail['campaign_id'];
                                    unset($row['id']);
                                    unset($row['fnum']);
                                    unset($row['nbmax']);
                                    unset($row['inform_applicant_by_email']);
                                    unset($row['is_validated']);
                                    $row['can_be_deleted'] = 1;
                                    if(empty($row['modified_by'])){
                                        unset($row['modified_by']);
                                    }
                                    $row['pdf_pages_count'] = (int)$row['pdf_pages_count'];

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
                        if ($reload_url) {
                            $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=" . $jinput->get('formid') . "&Itemid=" . $itemid . "&usekey=fnum&rowid=" . $fnum . "&r=" . $reload);
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
}
