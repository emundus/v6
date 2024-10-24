<?php
/**
 * @version		$Id: events.php 14401 2022-09-09 14:10:00Z brice.hubinet@emundus.fr $
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2005 - 2022 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');

include_once(JPATH_SITE . '/components/com_emundus/helpers/fabrik.php');

/**
 * Emundus Component Events Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class EmundusHelperEvents {

    /**
     * @param $params
     * Parameters available : $params['formModel']
     *
     * @return bool
     *
     * @throws Exception
     * @since version 1.33.0
     */
    function onBeforeLoad($params) : bool{
        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.helper_events.php'), JLog::ALL, array('com_emundus.helper_events'));

        try {
            $this->isApplicationSent($params);

            $user = JFactory::getSession()->get('emundusUser');

            if(isset($user->fnum)) {
                require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'form.php');
                require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
                $mForm = new EmundusModelForm();
                $mProfile = new EmundusModelProfile();

                $prid = $mProfile->getProfileByFnum($user->fnum);
                $submittion_page = $mForm->getSubmittionPage($prid);
                $submittion_page_id = (int)explode('=', $submittion_page->link)[3];

                if ($submittion_page_id === $params['formModel']->id) {
                    $this->isApplicationCompleted($params);
                }
            }

            return true;
        } catch (Exception $e) {
            JLog::add('Error when run event onBeforeLoad | '.$e->getMessage().' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            return false;
        }
    }

    /**
     * @param $params
     * Parameters available : $params['formModel']
     *
     * @return bool
     *
     * @since version 1.33.0
     */
    function onBeforeStore($params) : bool
    {
        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.helper_events.php'), JLog::ALL, array('com_emundus.helper_events'));

        try {
	        $eMConfig = JComponentHelper::getParams('com_emundus');
	        $enable_forms_logs = $eMConfig->get('log_forms_update', 0);
			if ($enable_forms_logs) {
                $forms_to_log = $eMConfig->get('log_forms_update_forms', []);
                $this->logUpdateForms($params, $forms_to_log);
			}

            return true;
        } catch (Exception $e) {
            JLog::add('Error when run event onBeforeStore | '.$e->getMessage().' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            return false;
        }
    }

    /**
     * @param $params
     * Parameters available : $params['formModel']
     *
     * @return bool
     *
     * @since version 1.33.0
     */
    function onAfterProcess($params) : bool{
        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.helper_events.php'), JLog::ALL, array('com_emundus.helper_events'));

        try {
            $user = JFactory::getSession()->get('emundusUser');

            if(isset($user->fnum)) {
                require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'form.php');
                require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
                $mForm = new EmundusModelForm();
                $mProfile = new EmundusModelProfile();

                $prid = $mProfile->getProfileByFnum($user->fnum);
                $submittion_page = $mForm->getSubmittionPage($prid);
                $submittion_page_id = (int)explode('=', $submittion_page->link)[3];

				$this->applicationUpdating($user->fnum);

                if ($submittion_page_id != $params['formModel']->id) {
                    $this->redirect($params);
                } else {
                    $this->confirmpost($params);
                }
            } else {
	            $fnum = '';
	            $keys = array_keys(JFactory::getApplication()->input->getArray());
	            foreach ($keys as $key) {
		            if(strpos($key, '___fnum')) {
			            $fnum = JFactory::getApplication()->input->getString($key, '');
			            break;
		            }
	            }

	            if(!empty($fnum)) {
		            require_once (JPATH_SITE.'/components/com_emundus/models/files.php');
		            $mFile = new EmundusModelFiles();
		            $applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];

		            EmundusModelLogs::log($user->id, $applicant_id, $fnum, 1, 'u', 'COM_EMUNDUS_ACCESS_FILE_UPDATE', 'COM_EMUNDUS_ACCESS_FILE_UPDATED_BY_COORDINATOR');
		            $this->applicationUpdating($fnum);
	            }
            }

            return true;
        } catch (Exception $e) {
            JLog::add('Error when run event onBeforeLoad | '.$e->getMessage().' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            return false;
        }
    }

    private function getFormsIdFromTableNames($table_names): array
    {
        $form_ids = [];
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        foreach ($table_names as $table_name) {
            $query->clear()
                ->select('form_id')
                ->from('#__fabrik_lists')
                ->where('db_table_name = '.$db->quote($table_name));
            $db->setQuery($query);
            $form_ids = array_merge($form_ids, $db->loadColumn());
        }

        return $form_ids;
    }

    function isApplicationSent($params) : bool{
        $mainframe = JFactory::getApplication();

        if (!$mainframe->isAdmin()) {
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'date.php');

            $m_campaign = new EmundusModelCampaign;
            $m_users = new EmundusModelUsers;

            $formModel = $params['formModel'];
            $listModel =  $params['formModel']->getListModel();
            $form_id = $formModel->id;

            $emundusUser = JFactory::getSession()->get('emundusUser');
            $user = $emundusUser;

            if (empty($user)) {
                $user = JFactory::getUser();
            }

            $eMConfig = JComponentHelper::getParams('com_emundus');
            $copy_application_form = $eMConfig->get('copy_application_form', 0);
            $copy_application_form_type   = $eMConfig->get('copy_application_form_type', 0);
            $copy_application_form_or_table = $eMConfig->get('copy_application_form_or_table', 'form');
            $copy_exclude_forms      = $eMConfig->get('copy_exclude_forms', []);
            $copy_include_forms      = $eMConfig->get('copy_include_forms', []);
            $copy_include_tables     = $eMConfig->get('copy_include_tables', []);
            if ($copy_application_form_or_table == 'table' && !empty($copy_include_tables)) {
                $copy_include_forms = $this->getFormsIdFromTableNames($copy_include_tables);
                $copy_exclude_forms = [];
            }
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

            if (empty($fnum)) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->select('db_table_name')
                    ->from($db->quoteName('#__fabrik_lists'))
                    ->where($db->quoteName('form_id') . ' = ' . $db->quote($form_id));
                $db->setQuery($query);
                $db_table_name = $db->loadResult();

                if(!empty($db_table_name)){
                    $fnum = $jinput->get->get($db_table_name.'___fnum', null);
                }
            }

            $current_fnum = !empty($fnum) ? $fnum : $user->fnum;
            $current_phase = $m_campaign->getCurrentCampaignWorkflow($current_fnum);
            if (!empty($current_phase) && !empty($current_phase->end_date)) {
                $current_end_date = $current_phase->end_date;
                $current_start_date = $current_phase->start_date;
            } else {
                $current_end_date = !empty(@$user->fnums[$current_fnum]->end_date) ? @$user->fnums[$current_fnum]->end_date : @$user->end_date;
                $current_start_date = @$user->fnums[$current_fnum]->start_date;
            }

            $is_campaign_started = strtotime(date($now)) >= strtotime($current_start_date);
            if (!$is_campaign_started && !in_array($user->id, $applicants)) {
                // STOP HERE, the campaign or step is not started yet. Redirect to main page
                $mainframe->enqueueMessage(JText::_('COM_EMUNDUS_EVENTS_APPLICATION_PERIOD_NOT_STARTED'), 'warning');
                $mainframe->redirect('/');
            }

            $is_dead_line_passed = strtotime(date($now)) > strtotime($current_end_date);

            if (!empty($current_phase) && !empty($current_phase->entry_status)) {
                $edit_status = $current_phase->entry_status;
            } else {
				$status_for_send = explode(',',$eMConfig->get('status_for_send', '0'));
                $edit_status = array_unique(array_merge(['0'], $status_for_send));
            }

            $is_app_sent = !in_array(@$user->status, $edit_status);
            $can_edit = EmundusHelperAccess::asAccessAction(1, 'u', $user->id, $fnum);
            $can_read = EmundusHelperAccess::asAccessAction(1, 'r', $user->id, $fnum);

            // once access condition is not correct, redirect page
            $reload_url = true;

            // FNUM sent by URL is like user fnum (means an applicant trying to open a file)
            if (!empty($fnum)) {

                // Check campaign limit, if the limit is obtained, then we set the deadline to true
                $mProfile = new EmundusModelProfile;
                $fnumDetail = $mProfile->getFnumDetails($fnum);

                $isLimitObtained = $m_campaign->isLimitObtained($user->fnums[$fnum]->campaign_id);

                if ($fnum == @$user->fnum) {
                    //try to access edit view
                    if ($view == 'form') {
                        if (
							(!$is_app_sent && !$is_dead_line_passed && $isLimitObtained !== true)
							|| in_array($user->id, $applicants)
							|| ($is_app_sent && !$is_dead_line_passed && $can_edit_until_deadline)
							|| ($is_dead_line_passed && $can_edit_after_deadline && ((!$is_app_sent && $isLimitObtained !== true) || $is_app_sent))
							|| $can_edit) {
                            $reload_url = false;
                        }
                    }
                    //try to access detail view or other
                    else {
                        if (!$can_edit && $is_app_sent) {
                            $mainframe->enqueueMessage(JText::_('COM_EMUNDUS_EVENTS_APPLICATION_READ_ONLY'), 'warning');
                        } else if ($fnumDetail['published'] == -1) {
                            $mainframe->enqueueMessage(JText::_('COM_EMUNDUS_EVENTS_APPLICATION_DELETED_FILE'), 'warning');
                        } else if ($is_dead_line_passed) {
                            $mainframe->enqueueMessage(JText::_('COM_EMUNDUS_EVENTS_APPLICATION_PERIOD_PASSED'), 'warning');
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
                            if ($reload < 3) {
                                $reload++;
                                $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$fnum."&r=".$reload);
                            }
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
                                $mainframe->enqueueMessage(JText::_('COM_EMUNDUS_EVENTS_APPLICATION_LIMIT_OBTAINED'), 'warning');
                            } else {
                                $mainframe->enqueueMessage(JText::_('COM_EMUNDUS_EVENTS_APPLICATION_PERIOD_PASSED'), 'warning');
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

	        $db = JFactory::getDBO();
	        $query = $db->getQuery(true);

			$query->select('fnum_from')
				->from($db->quoteName('#__emundus_campaign_candidature_links'))
				->where($db->quoteName('fnum_to') . ' LIKE ' . $db->quote($fnum));
			$db->setQuery($query);
			$fnum_linked = $db->loadResult();

			$profile_details = $m_users->getUserById(JFactory::getUser()->id)[0];

	        $check_forms = !in_array($formModel->getId(), $copy_exclude_forms);
	        if($copy_application_form_type == 1)
	        {
		        $check_forms = in_array($formModel->getId(), $copy_include_forms);
	        }


	        if ($copy_application_form == 1 && isset($user->fnum) && ($check_forms || !empty($fnum_linked))) {

                if (empty($formModel->getRowId())) {
                    $table = $listModel->getTable();
                    $table_elements = $formModel->getElementOptions(false, 'name', false, false, array(), '', true);

                    $elements = array();
                    foreach ($table_elements as $element) {
	                    if($element->value !== $table->db_table_name.'.parent_id')
	                    {
		                    $elements[] = $element->value;
	                    }
                    }

                    // check if data stored for current user
                    try {
						$query = $db->getQuery(true);

	                    $query->select('count(id)')
		                    ->from($db->quoteName($table->db_table_name))
		                    ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($user->fnum));
	                    $db->setQuery($query);
	                    $already_cloned = $db->loadResult();

                        if ($already_cloned == 0) {

                            $data_mode = $params['plugin_options']->get('trigger_confirmpost_data_mode',2);
                            if (!empty($fnum_linked)) { // priority to the linked file

                                $query->clear()
                                    ->select(implode(',', $db->quoteName($elements)))
                                    ->from($db->quoteName($table->db_table_name))
                                    ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum_linked));

                                $query->order('id DESC');
                                $db->setQuery($query);
                                $stored = $db->loadAssoc();


                            } elseif ($data_mode == 0 || $data_mode == 2) { // data from old file or both
                                $query->clear()
                                    ->select(implode(',', $db->quoteName($elements)))
                                    ->from($db->quoteName($table->db_table_name))
                                    ->where($db->quoteName('user') . ' = ' . $user->id);

                                $query->order('id DESC');
                                $db->setQuery($query);
                                $stored = $db->loadAssoc();
                            }

                            if (!empty($stored)) {
                                // update form data
                                $parent_id = $stored['id'];
                                unset($stored['id']);
                                unset($stored['fnum']);

                                foreach ($stored as $key => $store) {
                                    if (empty($formModel->data[$table->db_table_name . '___' . $key]) || empty($formModel->data[$table->db_table_name . '___' . $key . '_raw'])) {
                                        // get the element plugin, and params
                                        $query->clear()
                                            ->select('fe.plugin,fe.params')
                                            ->from($db->quoteName('#__fabrik_elements', 'fe'))
                                            ->leftJoin($db->quoteName('#__fabrik_formgroup', 'ffg') . ' ON ' . $db->quoteName('ffg.group_id') . ' = ' . $db->quoteName('fe.group_id'))
                                            ->where($db->quoteName('ffg.form_id') . ' = ' . $form_id)
                                            ->where($db->quoteName('fe.name') . ' = ' . $db->quote($key))
                                            ->where($db->quoteName('fe.published') . ' = 1');
                                        $db->setQuery($query);
                                        $elt = $db->loadObject();

                                        // if this element is date plugin, we need to check the time storage format (UTC of Local time)
                                        if ($elt->plugin === 'date') {
                                            // storage format (UTC [0], Local [1])
                                            $timeStorageFormat = json_decode($elt->params)->date_store_as_local;

                                            $store = EmundusHelperDate::displayDate($store, 'Y-m-d H:i:s', $timeStorageFormat);
                                        }

                                        $formModel->data[$table->db_table_name . '___' . $key] = $store;
                                        $formModel->data[$table->db_table_name . '___' . $key . '_raw'] = $store;
                                    }
                                }

                                $groups = $formModel->getFormGroups(true);
                                if (count($groups) > 0) {
                                    foreach ($groups as $group) {
                                        $group_params = json_decode($group->gparams);
                                        if (isset($group_params->repeat_group_button) && $group_params->repeat_group_button == 1 && !in_array($group->name, ['id', 'parent_id', 'fnum', 'user', 'date_time'])) {
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
                                                    // Use this line to not crash the following count as $formModel->data[$repeat_table . '___id'] returns null if the element does not exist yet (like onload)
                                                    $instances = (!empty($formModel->data[$repeat_table . '___id'])) ? $formModel->data[$repeat_table . '___id'] : [];

                                                    if (count($instances) < count($stored)) {
                                                        $formModel->data[$repeat_table . '___id'][] = "";
                                                        $formModel->data[$repeat_table . '___id_raw'][] = "";
                                                        $formModel->data[$repeat_table . '___parent_id'][] = "";
                                                        $formModel->data[$repeat_table . '___parent_id_raw'][] = "";
                                                    }

                                                    $formModel->data[$repeat_table . '___' . $group->name][] = $store;
                                                    $formModel->data[$repeat_table . '___' . $group->name . '_raw'][] = $store;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            if ($data_mode == 1 || $data_mode == 2) { // data from profile or both
                                // Check if we can fill a value with our profile
                                $profile_elements = array_keys(get_object_vars($profile_details));
                                foreach ($elements as $element) {
                                    $elt_name = explode('.', $element)[1];
                                    if (in_array($elt_name, $profile_elements)) {
                                        if (!empty($profile_details->{$elt_name}) && empty($formModel->data[$table->db_table_name . '___' . $elt_name]) || empty($formModel->data[$table->db_table_name . '___' . $elt_name . '_raw'])) {
                                            $formModel->data[$table->db_table_name . '___' . $elt_name] = $profile_details->{$elt_name};
                                            $formModel->data[$table->db_table_name . '___' . $elt_name . '_raw'] = $profile_details->{$elt_name};
                                        }
                                    }
                                }
                            }
                        }

                        // sync documents uploaded
                        // 1. get list of uploaded documents for previous file defined as duplicated
                        $query = $db->getQuery(true);
                        $query->clear()
                            ->select('count(id)')
                            ->from($db->quoteName('#__emundus_uploads'))
                            ->where($db->quoteName('user_id') . ' = ' . $user->id)
                            ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($user->fnum));
                        $db->setQuery($query);
                        $attachments_already_cloned = $db->loadResult();

                        $fnums = $user->fnums;
                        unset($fnums[$user->fnum]);

                        if (!empty($fnums) && $attachments_already_cloned == 0) {
                            $previous_fnum = array_keys($fnums);

                            $query->clear()
                                ->select('eu.*, esa.nbmax')
                                ->from($db->quoteName('#__emundus_uploads','eu'))
                                ->leftJoin($db->quoteName('#__emundus_setup_attachments','esa').' ON '.$db->quoteName('esa.id').' = '.$db->quoteName('eu.attachment_id'))
                                ->leftJoin($db->quoteName('#__emundus_setup_attachment_profiles','esap').' ON '.$db->quoteName('esap.attachment_id').' = '.$db->quoteName('eu.attachment_id') . ' AND ' . $db->quoteName('esap.profile_id') . ' = ' . $user->profile)
                                ->where($db->quoteName('eu.user_id') . ' = ' . $user->id);
                            if(!empty($fnum_linked)){
                                $query->andWhere($db->quoteName('eu.fnum') . ' LIKE ' . $db->quote($fnum_linked));
                            } else {
                                $query->andWhere($db->quoteName('eu.fnum') . ' LIKE ' . $db->quote($previous_fnum[0]));
                            }
                            $query->andWhere($db->quoteName('esap.duplicate') . ' = 1');
                            $db->setQuery($query);
                            $stored = $db->loadAssocList();

                            if (!empty($stored)) {
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

    function isApplicationCompleted($params) : bool{
        $mainframe 	= JFactory::getApplication();
        $jinput 	= $mainframe->input;
        $itemid 	= $jinput->get('Itemid');

        if ($jinput->get('view') == 'form') {
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');

            $user = JFactory::getSession()->get('emundusUser');

            $params	= JComponentHelper::getParams('com_emundus');
            $scholarship_document_id 	= $params->get('scholarship_document_id', NULL);
            $application_fee = $params->get('application_fee', 0);
            $use_session = $params->get('use_session', 0);

            $mApplication = new EmundusModelApplication;
            $mEmails = new EmundusModelEmails;
            $mProfile = new EmundusModelProfile;
            $mFiles = new EmundusModelFiles;
            $application_fee = (!empty($application_fee) && !empty($mProfile->getHikashopMenu($user->profile)));

            //$validations = $mApplication->checkFabrikValidations($user->fnum, true, $itemid);
            $attachments_progress = $mApplication->getAttachmentsProgress($user->fnum);
            $forms_progress = $mApplication->getFormsProgress($user->fnum);

	        $db    = JFactory::getDbo();
	        $query = $db->getQuery(true);

	        $profile_by_status = $mProfile->getProfileByStatus($user->fnum,$use_session);

	        if (empty($profile_by_status['profile'])) {
		        $query->select('esc.profile_id AS profile_id, ecc.campaign_id AS campaign_id')
			        ->from($db->quoteName('#__emundus_setup_campaigns', 'esc'))
			        ->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'ecc') . ' ON ' . $db->quoteName('ecc.campaign_id') . ' = ' . $db->quoteName('esc.id'))
			        ->where($db->quoteName('ecc.fnum') . ' LIKE ' . $db->quote($user->fnum));
		        $db->setQuery($query);
		        $profile_by_status = $db->loadAssoc();
	        }

	        $profile    = !empty($profile_by_status["profile_id"]) ? $profile_by_status["profile_id"] : $profile_by_status["profile"];
	        $profile_id = (!empty($user->fnums[$user->fnum]) && $user->profile != $profile && $user->applicant === 1) ? $user->profile : $profile;

	        $forms    = EmundusHelperMenu::getUserApplicationMenu($profile_id);

			// Check if we have qcm forms
	        $forms_ids = array_column($forms, 'form_id');
	        $items_ids = [];
	        foreach($forms as $form) {
		        $items_ids[$form->form_id] = $form->id;
	        }
			if(!empty($forms_ids) && !empty($items_ids))
			{
				$qcm_complete = $this->checkQcmCompleted($user->fnum, $forms_ids, $items_ids);
				if ($qcm_complete['status'] === false)
				{
					$mainframe->enqueueMessage(JText::sprintf($qcm_complete['msg']));
					$mainframe->redirect($qcm_complete['link']);
				}
			}

	        if ($attachments_progress < 100 || $forms_progress < 100) {

		        foreach ($forms as $form) {
			        $query->clear()
				        ->select('count(*)')
				        ->from($db->quoteName($form->db_table_name))
				        ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($user->fnum));
			        $db->setQuery($query);
			        $cpt = $db->loadResult();

			        if ($cpt == 0) {
				        $mainframe->redirect('index.php?option=com_fabrik&view=form&formid=' . $form->form_id . '&Itemid=' . $form->id . '&usekey=fnum&rowid=' . $user->fnum . '&r=1', JText::_('INCOMPLETE_APPLICATION'));
			        }
		        }

		        $mainframe->redirect("index.php?option=com_emundus&view=checklist&Itemid=" . $itemid, JText::_('INCOMPLETE_APPLICATION'));
	        }

            if ($application_fee) {
                $fnumInfos = $mFiles->getFnumInfos($user->fnum);

                // If students with a scholarship have a different fee.
                // The form ID will be appended to the URL, taking him to a different checkout page.
                if (isset($scholarship_document_id)) {
                    $db = JFactory::getDbo();

                    // See if applicant has uploaded the required scolarship form.
                    try {
                        $query = 'SELECT count(id) FROM #__emundus_uploads
					WHERE attachment_id = '.$scholarship_document_id.'
					AND fnum LIKE '.$db->Quote($user->fnum);

                        $db->setQuery($query);
                        $uploaded_document = $db->loadResult();
                    } catch (Exception $e) {
                        JLog::Add('Error in plugin/isApplicationCompleted at SQL query : '.$query, Jlog::ERROR, 'plugins');
                    }

                    $pay_scholarship = $params->get('pay_scholarship', 0);

                    // If he hasn't, no discount for him. If he has, exit to regular procedure.
                    if (!empty($uploaded_document) && !$pay_scholarship) {
                        return true;
                    }

                    if (empty($uploaded_document)) {
                        $scholarship_document_id = null;
                    } else if (!empty($pay_scholarship)  && empty($mApplication->getHikashopOrder($fnumInfos))) {
                        $scholarship_product = $params->get('scholarship_product', 0);
                        if (!empty($scholarship_product)) {
                            if($params->get('hikashop_session', 0)) {
                                // check if there is not another cart open
                                $hikashop_user = JFactory::getSession()->get('emundusPayment');
                                if (!empty($hikashop_user->fnum) && $hikashop_user->fnum != $user->fnum) {
                                    $user->fnum = $hikashop_user->fnum;
                                    JFactory::getSession()->set('emundusUser', $user);

                                    $mainframe->enqueueMessage(JText::_('ANOTHER_HIKASHOP_SESSION_OPENED'), 'error');
                                    $mainframe->redirect('/');
                                }
                            }
                            $return_url = $mApplication->getHikashopCheckoutUrl($user->profile);
                            $return_url = preg_replace('/&product_id=[0-9]+/', "&product_id=$scholarship_product", $return_url);
	                        $checkout_url = 'index.php?option=com_hikashop&ctrl=product&task=cleancart&return_url=' . urlencode(base64_encode($return_url));
                            $mainframe->redirect($checkout_url);
                        }
                    }
                }

                // This allows users who have started a bank transfer or cheque to go through even if it has not been marked as received yet.
                $accept_other_payments = $params->get('accept_other_payments', 0);

                if (count($fnumInfos) > 0) {
                    $checkout_cart_url = $mApplication->getHikashopCartUrl($user->profile);
                    if (!empty($checkout_cart_url)) {
                        if($params->get('hikashop_session', 0)) {
                            // check if there is not another cart open
                            $hikashop_user = JFactory::getSession()->get('emundusPayment');
                            if (!empty($hikashop_user->fnum) && $hikashop_user->fnum != $user->fnum) {
                                $user->fnum = $hikashop_user->fnum;
                                JFactory::getSession()->set('emundusUser', $user);

                                $mainframe->enqueueMessage(JText::_('ANOTHER_HIKASHOP_SESSION_OPENED'), 'error');
                                $mainframe->redirect('/');
                            }
                        }
                        JPluginHelper::importPlugin('emundus','custom_event_handler');
                        \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onBeforeEmundusRedirectToHikashopCart', ['url' => $checkout_cart_url, 'fnum' => $user->fnum, 'user' => $user]]);
                        $mainframe->redirect($checkout_cart_url);
                    } else {
                        $checkout_url = $mApplication->getHikashopCheckoutUrl($user->profile . $scholarship_document_id);

                        if (strpos($checkout_url,'${') !== false) {
                            $checkout_url = $mEmails->setTagsFabrik($checkout_url, [$user->fnum], true);
                        }
                        // If $accept_other_payments is 2 : that means we do not redirect to the payment page.
                        if ($accept_other_payments != 2 && empty($mApplication->getHikashopOrder($fnumInfos)) && $attachments_progress >= 100 && $forms_progress >= 100) {
                            if($params->get('hikashop_session', 0)) {
                                // check if there is not another cart open
                                $hikashop_user = JFactory::getSession()->get('emundusPayment');
                                if (!empty($hikashop_user->fnum) && $hikashop_user->fnum != $user->fnum) {
                                    $user->fnum = $hikashop_user->fnum;
                                    JFactory::getSession()->set('emundusUser', $user);

                                    $mainframe->enqueueMessage(JText::_('ANOTHER_HIKASHOP_SESSION_OPENED'), 'error');
                                    $mainframe->redirect('/');
                                }
                            }
                            // Profile number and document ID are concatenated, this is equal to the menu corresponding to the free option (or the paid option in the case of document_id = NULL)
	                        $checkout_url = 'index.php?option=com_hikashop&ctrl=product&task=cleancart&return_url=' . urlencode(base64_encode($checkout_url));
                            $mainframe->redirect($checkout_url);
                        }
                    }
                } else {
                    $mainframe->redirect('index.php');
                }
            }
        }

        return true;
    }

    function redirect($params) {
        $db = JFactory::getDBO();
        $user = JFactory::getSession()->get('emundusUser');

        $jinput = JFactory::getApplication()->input;
        $formid = $jinput->get('formid');

        require_once (JPATH_SITE.'/components/com_emundus/models/profile.php');
        require_once (JPATH_SITE.'/components/com_emundus/models/application.php');
        require_once (JPATH_SITE.'/components/com_emundus/models/files.php');
        require_once (JPATH_SITE.'/components/com_emundus/models/logs.php');
        require_once (JPATH_SITE.'/components/com_emundus/helpers/access.php');
        $mProfile = new EmundusModelProfile();
        $mApplication = new EmundusModelApplication();
        $mFile = new EmundusModelFiles();

        $applicant_profiles = $mProfile->getApplicantsProfilesArray();
        $applicant_id = ($mFile->getFnumInfos($user->fnum))['applicant_id'];

        $link = 'index.php';

        if (in_array($user->profile, $applicant_profiles) && EmundusHelperAccess::asApplicantAccessLevel($user->id)) {
            $levels = JAccess::getAuthorisedViewLevels($user->id);

            if(isset($user->fnum)) {
                $mApplication->getFormsProgress($user->fnum);
                $mApplication->getAttachmentsProgress($user->fnum);
            }

            try {
                $query = 'SELECT CONCAT(link,"&Itemid=",id)
						FROM #__menu
						WHERE published=1 AND menutype = "'.$user->menutype.'" AND access IN ('.implode(',', $levels).')
						AND parent_id != 1
						AND lft > (
								SELECT menu.lft
								FROM `#__menu` AS menu
								WHERE menu.published=1 AND menu.parent_id>1 AND menu.menutype="'.$user->menutype.'"
								AND SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 4), "&", 1)='.$formid.') ORDER BY lft';
                $db->setQuery($query);
                $link = $db->loadResult();
            } catch (Exception $e) {
                $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                JLog::add($error, JLog::ERROR, 'com_emundus');
            }

            if (empty($link)) {
                $query = 'SELECT CONCAT(link,"&Itemid=",id)
							FROM #__menu
							WHERE published=1 AND menutype = "'.$user->menutype.'" AND type!="separator" AND published=1 AND alias LIKE "checklist%"';

                $db->setQuery($query);
                try {
                    $link = $db->loadResult();
                } catch (Exception $e) {
                    $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                    JLog::add($error, JLog::ERROR, 'com_emundus');
                }

                if (!empty($link)) {
                    $query = $db->getQuery(true);
                    $query->select('COUNT(id)')
                        ->from('#__emundus_setup_attachment_profiles')
                        ->where('profile_id = ' . $user->profile)
                        ->orWhere('campaign_id = ' . $user->fnums[$user->fnum]->campaign_id);

                    $db->setQuery($query);
                    try {
                        $profileDocuments = $db->loadResult();

                        if ($profileDocuments < 1) {
                            $link = "";
                        }
                    } catch (Exception $e) {
                        JLog::add('Error trying to find document attached to profiles, unable to say if we can redirect to submission page directly', JLog::ERROR, 'com_emundus.events');
                    }
                }

                if (empty($link)) {
                    try {
                        $query = 'SELECT CONCAT(link,"&Itemid=",id) 
						FROM #__menu 
						WHERE published=1 AND menutype = "'.$user->menutype.'" AND type LIKE "component" AND published=1 AND level = 1 ORDER BY id ASC';
                        $db->setQuery($query);
                        $link = $db->loadResult();
                    } catch (Exception $e) {
                        $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                        JLog::add($error, JLog::ERROR, 'com_emundus');
                    }
                }
            }

            EmundusModelLogs::log($user->id, $applicant_id, $user->fnum, 1, 'u', 'COM_EMUNDUS_ACCESS_FILE_UPDATE', 'COM_EMUNDUS_ACCESS_FILE_UPDATED_BY_APPLICANT');
        } else {
            try {
                $query = 'SELECT db_table_name FROM `#__fabrik_lists` WHERE `form_id` ='.$formid;
                $db->setQuery($query);
                $db_table_name = $db->loadResult();
            } catch (Exception $e) {
                $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                JLog::add($error, JLog::ERROR, 'com_emundus');
            }

            $fnum = $jinput->get($db_table_name.'___fnum');
            $s1 = $jinput->get($db_table_name.'___user', null, 'POST');
            $s2 = $jinput->get('sid', '', 'GET');
            $student_id = !empty($s2)?$s2:$s1;

            $sid = is_array($student_id)?$student_id[0]:$student_id;

            try {
                $query = 'UPDATE `'.$db_table_name.'` SET `user`='.$sid.' WHERE fnum like '.$db->Quote($fnum);
                $db->setQuery($query);
                $db->execute();
            } catch (Exception $e) {
                $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
                JLog::add($error, JLog::ERROR, 'com_emundus');
            }

            EmundusModelLogs::log($user->id, $applicant_id, $fnum, 1, 'u', 'COM_EMUNDUS_ACCESS_FILE_UPDATE', 'COM_EMUNDUS_ACCESS_FILE_UPDATED_BY_COORDINATOR');

            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>';
            echo '<script src="https://code.jquery.com/jquery-3.3.1.slim.js" integrity="sha256-fNXJFIlca05BIO2Y5zh1xrShK3ME+/lYZ0j+ChxX2DA=" crossorigin="anonymous"></script>';
            die("<script>
              $(document).ready(function () {
                Swal.fire({
                  position: 'top',
                  type: 'success',
                  title: '".JText::_('SAVED')."',
                  showConfirmButton: false,
                  timer: 2000,
                  onClose: () => {
                    window.close();
                  }
                })
              });
            </script>");
        }


	    if(empty($link)) {
		    $link = $_SERVER['REQUEST_URI'];
	    }

        header('Location: '.$link);
        exit();
    }

    function confirmpost($params) : bool{
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $student = JFactory::getSession()->get('emundusUser');

        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'export.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');
        $mApplication  = new EmundusModelApplication;
        $mFiles        = new EmundusModelFiles;
        $mEmails       = new EmundusModelEmails;
        $mCampaign     = new EmundusModelCampaign;

        $applicant_id = ($mFiles->getFnumInfos($student->fnum))['applicant_id'];

        // Get params set in eMundus component configuration
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $can_edit_until_deadline    = $eMConfig->get('can_edit_until_deadline', 0);
        $can_edit_after_deadline    = $eMConfig->get('can_edit_after_deadline', '0');
        $application_form_order     = $eMConfig->get('application_form_order', null);
        $attachment_order           = $eMConfig->get('attachment_order', null);
        $application_form_name      = $eMConfig->get('application_form_name', "application_form_pdf");
        $export_pdf                 = $eMConfig->get('export_application_pdf', 0);
        $export_path                = $eMConfig->get('export_path', null);
        $id_applicants              = explode(',',$eMConfig->get('id_applicants', '0'));
        $new_status                 = $eMConfig->get('default_send_status', 1);


        $offset = $app->get('offset', 'UTC');
        $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
        $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
        $now = $dateTime->format('Y-m-d H:i:s');


        $current_phase = $mCampaign->getCurrentCampaignWorkflow($student->fnum);
        if (!empty($current_phase) && !empty($current_phase->id)) {
            if (!is_null($current_phase->output_status)) {
                $new_status = $current_phase->output_status;
            }

            if (!empty($current_phase->end_date)) {
                $is_dead_line_passed = strtotime(date($now)) > strtotime($current_phase->end_date) || strtotime(date($now)) < strtotime($current_phase->start_date);
            } else {
                $is_dead_line_passed = strtotime(date($now)) > strtotime(@$student->fnums[$student->fnum]->end_date);
            }
        }

        // Check campaign limit, if the limit is obtained, then we set the deadline to true
        $isLimitObtained = $mCampaign->isLimitObtained($student->fnums[$student->fnum]->campaign_id);

        // If we've passed the deadline and the user cannot submit (is not in the list of exempt users), block him.
        if ((($is_dead_line_passed && $can_edit_after_deadline != 1) || $isLimitObtained === true) && !in_array($student->id, $id_applicants)) {
            if ($isLimitObtained === true) {
                $params['formModel']->formErrorMsg = JText::_('COM_EMUNDUS_EVENTS_APPLICATION_LIMIT_OBTAINED');
            } else {
                $params['formModel']->formErrorMsg = JText::_('CANDIDATURE_PERIOD_TEXT');
            }
            return false;
        }

        if (!$can_edit_until_deadline) {
            $query = 'UPDATE #__emundus_uploads SET can_be_deleted = 0 WHERE user_id = '.$student->id. ' AND fnum like '.$db->Quote($student->fnum);
            $db->setQuery($query);

            try {
                $db->execute();
            } catch (Exception $e) {
                // catch any database errors.
                JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            }
        }

        $old_status = $student->fnums[$student->fnum]->status;
        JPluginHelper::importPlugin('emundus','custom_event_handler');
        \Joomla\CMS\Factory::getApplication()->triggerEvent('onBeforeSubmitFile', [$student->id, $student->fnum]);
        \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onBeforeSubmitFile', ['user' => $student->id, 'fnum' => $student->fnum]]);

        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__emundus_campaign_candidature'))
            ->set($db->quoteName('submitted') . ' = 1')
            ->set($db->quoteName('date_submitted') . ' = ' . $db->quote($now))
            ->set($db->quoteName('status') . ' = ' . $new_status)
            ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($student->fnum));

        try {
            $db->setQuery($query);
            $updated = $db->execute();
        } catch (Exception $e) {
            $updated = false;
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }

        if ($updated && $old_status != $new_status) {
            $this->logUpdateState($old_status, $new_status, $student->id, $applicant_id, $student->fnum);
            \Joomla\CMS\Factory::getApplication()->triggerEvent('onAfterStatusChange', [$student->fnum, $new_status]);
            \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onAfterStatusChange', ['fnum' => $student->fnum, 'state' => $new_status, 'old_state' => $old_status]]);
        }

        $query = 'UPDATE #__emundus_declaration SET time_date=' . $db->Quote($now) . ' WHERE user='.$student->id. ' AND fnum like '.$db->Quote($student->fnum);
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
        \Joomla\CMS\Factory::getApplication()->triggerEvent('onAfterSubmitFile', [$student->id, $student->fnum]);
        \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onAfterSubmitFile', ['user' => $student->id, 'fnum' => $student->fnum]]);

        $student->candidature_posted = 1;

        // Send emails defined in trigger
        $code = array($student->code);
        $to_applicant = '0,1';
        $mEmails->sendEmailTrigger($new_status, $code, $to_applicant, $student);

        // If pdf exporting is activated
        if ($export_pdf == 1) {
            $fnum = $student->fnum;
            $fnumInfo = $mFiles->getFnumInfos($student->fnum);
            $files_list = array();

            // Build pdf file
            if (is_numeric($fnum) && !empty($fnum)) {
                // Check if application form is in custom order
                if (!empty($application_form_order)) {
                    $application_form_order = explode(',',$application_form_order);
                    $files_list[] = EmundusHelperExport::buildFormPDF($fnumInfo, $fnumInfo['applicant_id'], $fnum, 1, $application_form_order);
                } else {
                    $files_list[] = EmundusHelperExport::buildFormPDF($fnumInfo, $fnumInfo['applicant_id'], $fnum, 1);
                }

                // Check if pdf attachements are in custom order
                if (!empty($attachment_order)) {
                    $attachment_order = explode(',',$attachment_order);
                    foreach ($attachment_order as $attachment_id) {
                        // Get file attachements corresponding to fnum and type id
                        $files[] = $mApplication->getAttachmentsByFnum($fnum, null, $attachment_id);
                    }
                } else {
                    // Get all file attachements corresponding to fnum
                    $files[] = $mApplication->getAttachmentsByFnum($fnum, null, null);
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
                $tags = $mEmails->setTags($student->id, $post, $fnum, '', $application_form_name.$export_path);
                $application_form_name = preg_replace($tags['patterns'], $tags['replacements'], $application_form_name);
                $application_form_name = $mEmails->setTagsFabrik($application_form_name, array($fnum));

                // Format filename
                $application_form_name = $mEmails->stripAccents($application_form_name);
                $application_form_name = preg_replace('/[^A-Za-z0-9 _.-]/','', $application_form_name);
                $application_form_name = preg_replace('/\s/', '', $application_form_name);
                $application_form_name = strtolower($application_form_name);

                // If a file exists with that name, delete it
                if (file_exists(JPATH_BASE . DS . 'tmp' . DS . $application_form_name)) {
                    unlink(JPATH_BASE . DS . 'tmp' . DS . $application_form_name);
                }

                // Ouput pdf with desired file name
                $pdf->Output(JPATH_BASE . DS . 'tmp' . DS . $application_form_name.".pdf", 'F');

                // If export path is defined
                if (!empty($export_path)) {
                    $export_path = preg_replace($tags['patterns'], $tags['replacements'], $export_path);
                    $export_path = $mEmails->setTagsFabrik($export_path, array($fnum));

                    // Sanitize and build filename.
                    $export_path = strtr(utf8_decode($export_path), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
                    $export_path = strtolower($export_path);
                    $export_path = preg_replace('`\s`', '-', $export_path);
                    $export_path = str_replace(',', '', $export_path);
                    $directories = explode('/', $export_path);

                    $d = '';
                    foreach ($directories as $dir) {
                        $d .= $dir.'/';
                        if (!file_exists(JPATH_BASE.DS.$d)) {
                            mkdir(JPATH_BASE.DS.$d);
                            chmod(JPATH_BASE.DS.$d, 0755);
                        }
                    }
                    if (file_exists(JPATH_BASE.DS.$export_path.$application_form_name.".pdf")) {
                        unlink(JPATH_BASE.DS.$export_path.$application_form_name.".pdf");
                    }
                    copy(JPATH_BASE.DS.'tmp'.DS.$application_form_name.".pdf", JPATH_BASE.DS.$export_path.$application_form_name.".pdf");
                }
                if (file_exists(JPATH_BASE.DS."images".DS."emundus".DS."files".DS.$student->id.DS.$fnum."_application_form_pdf.pdf")) {
                    unlink(JPATH_BASE.DS."images".DS."emundus".DS."files".DS.$student->id.DS.$fnum."_application_form_pdf.pdf");
                }
                copy(JPATH_BASE.DS.'tmp'.DS.$application_form_name.".pdf", JPATH_BASE.DS."images".DS."emundus".DS."files".DS.$student->id.DS.$fnum."_application_form_pdf.pdf");
            }
        }

        EmundusModelLogs::log($student->id, $applicant_id, $student->fnum, 1, 'u', 'COM_EMUNDUS_ACCESS_FILE_UPDATE', 'COM_EMUNDUS_ACCESS_FILE_SENT_BY_APPLICANT');

        $redirect_message = !empty($params['plugin_options']) && !empty($params['plugin_options']->get('trigger_confirmpost_success_msg')) ? JText::_($params['plugin_options']->get('trigger_confirmpost_success_msg')) : JText::_('APPLICATION_SENT');

		if(!empty($params['plugin_options'])) {
			$go_to_next_step = false;
			if (intval($params['plugin_options']->get('trigger_confirmpost_redirect_to_next_step_first_page_url')) === 1) {
				$current_phase = $mCampaign->getCurrentCampaignWorkflow($student->fnum);

				if (!empty($current_phase->id)) {
					$go_to_next_step = true;
				}
			}

			if ($go_to_next_step) {
                $redirect_url = 'index.php?option=com_emundus&task=openfile&fnum='.$student->fnum;
            } else {
                $redirect_url = !empty($params['plugin_options']->get('trigger_confirmpost_redirect_url'))  ? JText::_($params['plugin_options']->get('trigger_confirmpost_redirect_url')) : EmundusHelperMenu::getHomepageLink();
				if($params['plugin_options']->get('trigger_confirmpost_display_success_msg',1) == 1)
				{
					$app->enqueueMessage($redirect_message, 'success');
				}
            }

        } else {
			if($params['plugin_options']->get('trigger_confirmpost_display_success_msg',1) == 1)
			{
				$app->enqueueMessage($redirect_message, 'success');
			}
            $redirect_url = EmundusHelperMenu::getHomepageLink();
        }

        $app->redirect($redirect_url);

        return true;
    }

	function onAfterProgramCreate($params) : bool{
		jimport('joomla.log.log');
		JLog::addLogger(array('text_file' => 'com_emundus.helper_events.php'), JLog::ALL, array('com_emundus.helper_events'));

		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$code = $params['data']['jos_emundus_setup_programmes___code_raw'];
			$evaluation_form = $params['data']['jos_emundus_setup_programmes___evaluation_form_raw'];

			if(is_array($evaluation_form)) {
				$evaluation_form = $evaluation_form[0];
			}

			if(!empty($evaluation_form)) {
				require_once (JPATH_SITE.'/components/com_emundus/models/form.php');
				$m_form = new EmundusModelForm();

				$programs = $m_form->getProgramsByForm($evaluation_form);
				$codes = array_map(function($program) {
					return $program['code'];
				}, $programs);

				$codes[] = $code;
				$codes = array_unique($codes);

				$m_form->associateFabrikGroupsToProgram($evaluation_form,$codes);
			} else {
				$query->clear()
					->update($db->quoteName('#__emundus_setup_programmes'))
					->set($db->quoteName('fabrik_group_id') . ' = ' . $db->quote(''))
					->where($db->quoteName('code') . ' LIKE ' . $db->quote($code));
				$db->setQuery($query);
				$db->execute();
			}

			if(!empty($code))
			{
				$eMConfig            = JComponentHelper::getParams('com_emundus');
				$all_rights_group_id = $eMConfig->get('all_rights_group', 1);

				$query->clear()
					->select('id')
					->from($db->quoteName('#__emundus_setup_groups_repeat_course'))
					->where($db->quoteName('course') . ' LIKE ' . $db->quote($code))
					->where($db->quoteName('parent_id') . ' = ' . $db->quote($all_rights_group_id));
				$db->setQuery($query);
				$exists = $db->loadResult();

				if(!empty($exists))
				{
					return true;
				}

				$columns = array('parent_id', 'course');
				$values  = array($db->quote($all_rights_group_id), $db->quote($code));

				$query->clear()
					->insert($db->quoteName('#__emundus_setup_groups_repeat_course'))
					->columns($db->quoteName($columns))
					->values(implode(',', $values));
				$db->setQuery($query);
				$db->execute();
			}

			return true;
		}
		catch (Exception $e)
		{
			JLog::add('Error when run event onAfterProgramCreate | ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			return false;
		}

	}

    private function logUpdateForms($params, $forms_to_log = []) : bool
    {
        $logged = false;

        $form_data = $params['formModel']->formData;
        if (!empty($forms_to_log) && in_array($form_data['formid'], $forms_to_log)) {
            $emundusUser = JFactory::getSession()->get('emundusUser');
            $fnum = $emundusUser->fnum;

            if (empty($fnum)) {
                foreach($form_data as $key => $value) {
                    if (strpos($key, '___fnum') !== false) {
                        $fnum = $value;
                        break;
                    }
                }
            }

            if (!empty($fnum)) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->select('applicant_id')
                    ->from($db->quoteName('#__emundus_campaign_candidature','ecc'))
                    ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));

                try {
                    $db->setQuery($query);
                    $applicant_id = $db->loadResult();
                } catch (Exception $e) {
                    JLog::add("Failed to get applicant_id from fnum $fnum : " . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
                }

                if (!empty($applicant_id)) {
                    $form_elements = $this->getFormElements($form_data['formid']);

                    if (!empty($form_elements)) {
                        include_once(JPATH_ROOT . '/components/com_emundus/models/application.php');

                        if (class_exists('EmundusModelApplication')) {
                            $query->clear()
                                ->select('label')
                                ->from($db->quoteName('#__fabrik_forms'))
                                ->where($db->quoteName('id') . ' = ' . $form_data['formid']);

                            try {
                                $db->setQuery($query);
                                $form_label = JText::_($db->loadResult());
                            } catch (Exception $e) {
                                JLog::add("Failed to get applicant_id from fnum $fnum : " . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
                            }

                            $m_application = new EmundusModelApplication();
                            $user = JFactory::getUser();
                            $logged_elements = [];

                            foreach ($form_elements as $element) {
                                $element_key = $element->db_table_name . '___' . $element->name;
                                $new_value = $form_data[$element_key];
                                $raw_element_key = $element_key . '_raw';

                                switch ($element->plugin) {
                                    case 'checkbox':
                                        $new_value = array_key_exists($raw_element_key, $form_data) ? $form_data[$raw_element_key] : $form_data[$element_key];
                                        $new_value = is_array($new_value) ? json_encode(array_values($new_value)) : $new_value;
                                        break;
                                    case 'dropdown':
                                        $new_value = array_key_exists($raw_element_key, $form_data) ? $form_data[$raw_element_key] : $form_data[$element_key];
                                        $params = json_decode($params, true);

                                        if (!$params['multiple']) {
                                            $new_value = current($new_value);
                                        }
                                        break;
                                    case 'cascadingdropdown':
                                    case 'databasejoin':
                                        $new_value = array_key_exists($raw_element_key, $form_data) ? $form_data[$raw_element_key] : $form_data[$element_key];
                                        $new_value = is_array($new_value) ? implode(',', $new_value) : $new_value;
                                        break;
                                }

                                $old_value = $m_application->getValuesByElementAndFnum($fnum, $element->id, $form_data['formid']);
                                $new_value = EmundusHelperFabrik::formatElementValue($element->name, $new_value, $element->group_id, $applicant_id);

                                if ($old_value != $new_value) {
                                    $log_params = [
                                        'description' => '[' . $form_label . ']',
                                        'element' =>  JText::_($element->label),
                                        'old' => $old_value,
                                        'new' => $new_value
                                    ];

                                    $logged_elements[] = EmundusModelLogs::log($user->id, $applicant_id, $fnum, 1, 'u', 'COM_EMUNDUS_ACCESS_FILE_UPDATE', json_encode(['updated' => [$log_params]], JSON_UNESCAPED_UNICODE));
                                }
                            }


                            $logged = !in_array(false, $logged_elements);
                        }
                    }
                }
            }
        }

        return $logged;
    }

    private function getFormElements($form_id) {
        $elements = [];

        if (!empty($form_id)) {
            $excluded_name = ['fnum', 'time_date', 'user', 'date_time'];
            $excluded_plugins = ['display', 'internalid'];

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('fe.id, fe.name, fe.plugin, fe.label, fe.params, fe.group_id, fe.default, fl.db_table_name, fg.params as group_params')
                ->from($db->quoteName('#__fabrik_elements', 'fe'))
	            ->innerJoin($db->quoteName('#__fabrik_groups','fg').' ON '.$db->quoteName('fg.id').' = '.$db->quoteName('fe.group_id'))
	            ->innerJoin($db->quoteName('#__fabrik_formgroup','ffg').' ON '.$db->quoteName('ffg.group_id').' = '.$db->quoteName('fe.group_id'))
	            ->innerJoin($db->quoteName('#__fabrik_lists','fl').' ON '.$db->quoteName('fl.form_id').' = '.$db->quoteName('ffg.form_id'))
                ->where($db->quoteName('ffg.form_id') . ' = ' . $form_id)
                ->where($db->quoteName('fe.published') . ' = 1')
                ->where($db->quoteName('fe.hidden') . ' != -1')
                ->where($db->quoteName('fe.name') . ' NOT IN (' . implode(',', $db->quote($excluded_name)) . ')')
                ->where($db->quoteName('fe.plugin') . ' NOT IN (' . implode(',', $db->quote($excluded_plugins)) . ')');

            try {
                $db->setQuery($query);
                $elements = $db->loadObjectList();
            } catch (Exception $e) {
                JLog::add('Failed to get elements from form id ' . $form_id . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            }
        }

        return $elements;
    }

    private function logUpdateState($old_status, $new_status, $user_id, $applicant_id, $fnum) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('step, value')
            ->from('#__emundus_setup_status')
            ->where('step IN (' . implode(',', array($old_status, $new_status)) .  ')');
        $db->setQuery($query);

        try {
            $status_labels = $db->loadObjectList('step');

            EmundusModelLogs::log($user_id, $applicant_id, $fnum, 13, 'u', 'COM_EMUNDUS_ACCESS_STATUS_UPDATE', json_encode(array(
                "updated" => array(
                    array(
                        'old' => $status_labels[$old_status]->value,
                        'new' => $status_labels[$new_status]->value,
                        'old_id' => $old_status,
                        'new_id' => $new_status
                    )
                )
            )), JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            JLog::add('Error getting status labels in plugin confirmpost at line: ' . __LINE__ . ' in file: ' . __FILE__ . ' with message: ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
        }
    }

	private function applicationUpdating($fnum){
		$result = false;

		try {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

            require_once(JPATH_SITE.'/components/com_emundus/helpers/date.php');
            $h_date = new EmundusHelperDate();
            $now = $h_date->getNow();

			$query->update($db->quoteName('#__emundus_campaign_candidature'))
				->set($db->quoteName('updated') . ' = ' . $db->quote($now))
				->set($db->quoteName('updated_by') . ' = ' . JFactory::getUser()->id)
				->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));
			$db->setQuery($query);
			$result = $db->execute();
		}
		catch (Exception $e) {
			JLog::add('Error when try to log update of application: ' . __LINE__ . ' in file: ' . __FILE__ . ' with message: ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return $result;
	}

	private function checkQcmCompleted($fnum,$forms_ids,$items_ids)
	{
		$result = ['status' => true, 'msg' => '', 'link' => ''];

		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$db->setQuery('show tables');
			$existingTables = $db->loadColumn();
			if (in_array('jos_emundus_setup_qcm', $existingTables))
			{

				$query->clear()
					->select('distinct sq.id,sq.form_id,sq.group_id')
					->from($db->quoteName('#__emundus_setup_qcm', 'sq'))
					->where($db->quoteName('sq.form_id') . ' IN (' . implode(',', $db->quote($forms_ids)) . ')');
				$db->setQuery($query);
				$qcms     = $db->loadObjectList();
				$qcms_ids = array_column($qcms, 'id');

				if (!empty($qcms))
				{
					$query->clear()
						->select('count(id)')
						->from($db->quoteName('#__emundus_qcm_applicants', 'qa'))
						->where($db->quoteName('qa.fnum') . ' LIKE ' . $db->quote($fnum))
						->where($db->quoteName('qa.qcmid') . ' IN (' . implode(',', $db->quote($qcms_ids)) . ')');
					$db->setQuery($query);
					$applicants_qcms = $db->loadResult();

					if (sizeof($qcms) == $applicants_qcms)
					{
						foreach ($qcms as $qcm)
						{
							$query->clear()
								->select('questions')
								->from($db->quoteName('#__emundus_qcm_applicants'))
								->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum))
								->andWhere($db->quoteName('qcmid') . ' = ' . $db->quote($qcm->id));
							$db->setQuery($query);
							$q_numbers = sizeof(explode(',', $db->loadResult()));

							$query->clear()
								->select('db_table_name')
								->from($db->quoteName('#__fabrik_lists'))
								->where($db->quoteName('form_id') . ' = ' . $db->quote($qcm->form_id));
							$db->setQuery($query);
							$table = $db->loadResult();

							$query->clear()
								->select('table_join')
								->from($db->quoteName('#__fabrik_joins'))
								->where($db->quoteName('group_id') . ' = ' . $db->quote($qcm->group_id))
								->where($db->quoteName('join_from_table') . ' = ' . $db->quote($table))
								->where($db->quoteName('table_join_key') . ' = ' . $db->quote('parent_id'));
							$db->setQuery($query);
							$repeat_table = $db->loadResult();

							if (!empty($repeat_table))
							{
								$query->clear()
									->select('count(rt.id) as answers')
									->from($db->quoteName($repeat_table, 'rt'))
									->leftJoin($db->quoteName($table, 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('rt.parent_id'))
									->where($db->quoteName('t.fnum') . ' LIKE ' . $db->quote($fnum));
								$db->setQuery($query);
								$answers_given = $db->loadResult();

								if ((int) $answers_given != $q_numbers)
								{
									$result['status'] = false;
									$result['msg']    = 'PLEASE_COMPLETE_QCM_BEFORE_SEND';
									$result['link']   = "index.php?option=com_fabrik&view=form&formid=" . $qcm->form_id . "&Itemid=" . $items_ids[$qcm->form_id] . "&usekey=fnum&rowid=" . $fnum . "&r=1";

									// We break the loop because we have found a qcm that is not completed
									return $result;
								}
							}
							else
							{
								$result['status'] = false;
								$result['msg']    = 'PLEASE_COMPLETE_QCM_BEFORE_SEND';
								$result['link']   = "index.php?option=com_fabrik&view=form&formid=" . $qcm->form_id . "&Itemid=" . $items_ids[$qcm->form_id] . "&usekey=fnum&rowid=" . $fnum . "&r=1";
							}
						}
					}
					else
					{
						$result['status'] = false;
						$result['msg']    = 'PLEASE_COMPLETE_QCM_BEFORE_SEND';
						$result['link']   = "index.php?option=com_fabrik&view=form&formid=" . $qcms[0]->form_id . "&Itemid=" . $items_ids[$qcms[0]->form_id] . "&usekey=fnum&rowid=" . $fnum . "&r=1";

						// We break the loop because we have found a qcm that is not completed
						return $result;
					}
				}
			}
		}
		catch (Exception $e)
		{
			JLog::add('Error when try to check if qcm is completed: ' . __LINE__ . ' in file: ' . __FILE__ . ' with message: ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return $result;
	}
}
