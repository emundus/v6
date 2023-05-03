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
    function onBeforeStore($params) : bool{
        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.helper_events.php'), JLog::ALL, array('com_emundus.helper_events'));

        try {
            //TODO : Log forms updates with emundus parameter by form id
	        $eMConfig = JComponentHelper::getParams('com_emundus');
	        $enable_forms_logs = $eMConfig->get('log_forms_update', 0);
	        $forms_to_log = $eMConfig->get('log_forms_update_forms', '');

			if($enable_forms_logs) {
				$this->logUpdateForms($params,$forms_to_log);
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

                if ($submittion_page_id != $params['formModel']->id) {
                    $this->redirect($params);
                } else {
                    $this->confirmpost($params);
                }
            }

            return true;
        } catch (Exception $e) {
            JLog::add('Error when run event onBeforeLoad | '.$e->getMessage().' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            return false;
        }
    }

    function isApplicationSent($params) : bool{
        $mainframe = JFactory::getApplication();

        if (!$mainframe->isAdmin()) {
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
            $m_campaign = new EmundusModelCampaign;

            $formModel = $params['formModel'];
            $listModel =  $params['formModel']->getListModel();

            $emundusUser = JFactory::getSession()->get('emundusUser');
            $user = $emundusUser;

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

            $edit_status = array();
            if (!empty($current_phase) && !empty($current_phase->entry_status)) {
                $edit_status = array_merge($edit_status, $current_phase->entry_status);
            } else {
                $edit_status[] = 0;
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
                        if ((!$is_dead_line_passed && $isLimitObtained !== true) || in_array($user->id, $applicants) || ($is_app_sent && !$is_dead_line_passed && $can_edit_until_deadline && $isLimitObtained !== true) || ($is_dead_line_passed && $can_edit_after_deadline && $isLimitObtained !== true) || $can_edit) {
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

                        $query = 'SELECT count(id) FROM #__emundus_uploads WHERE user_id='.$user->id.' AND fnum like '.$db->Quote($user->fnum);
                        $db->setQuery($query);
                        $already_cloned = $db->loadResult();

                        if (!empty($stored) && $already_cloned == 0) {
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

            $mApplication = new EmundusModelApplication;
            $mEmails = new EmundusModelEmails;
            $mProfile = new EmundusModelProfile;
            $mFiles = new EmundusModelFiles;
            $application_fee = (!empty($application_fee) && !empty($mProfile->getHikashopMenu($user->profile)));

            //$validations = $mApplication->checkFabrikValidations($user->fnum, true, $itemid);
            $attachments = $mApplication->getAttachmentsProgress($user->fnum);
            $forms = $mApplication->getFormsProgress($user->fnum);

            if ($attachments < 100 || $forms < 100) {
                $mainframe->redirect( "index.php?option=com_emundus&view=checklist&Itemid=".$itemid, JText::_('INCOMPLETE_APPLICATION'));
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
                        JPluginHelper::importPlugin('emundus','custom_event_handler');
                        \Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onBeforeEmundusRedirectToHikashopCart', ['url' => $checkout_cart_url, 'fnum' => $user->fnum, 'user' => $user]]);
                        $mainframe->redirect($checkout_cart_url);
                    } else {
                        $checkout_url = $mApplication->getHikashopCheckoutUrl($user->profile . $scholarship_document_id);

                        if (strpos($checkout_url,'${') !== false) {
                            $checkout_url = $mEmails->setTagsFabrik($checkout_url, [$user->fnum], true);
                        }
                        // If $accept_other_payments is 2 : that means we do not redirect to the payment page.
                        if ($accept_other_payments != 2 && empty($mApplication->getHikashopOrder($fnumInfos)) && $attachments >= 100 && $forms >= 100) {
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

    function redirect($params) : bool{
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
        $new_status                 = 1;


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

        $query = 'UPDATE #__emundus_campaign_candidature SET submitted=1, date_submitted=' . $db->Quote($now) . ', status='.$new_status.' WHERE applicant_id='.$student->id.' AND campaign_id='.$student->campaign_id. ' AND fnum like '.$db->Quote($student->fnum);
        $db->setQuery($query);

        try {
            $updated = $db->execute();
        } catch (Exception $e) {
            $updated = false;
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }


        if ($updated && $old_status != $new_status) {
            $this->logUpdateState($old_status, $new_status, $student->id, $applicant_id, $student->fnum);
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
        $redirect_url = !empty($params['plugin_options']) && !empty($params['plugin_options']->get('trigger_confirmpost_redirect_url')) ? JText::_($params['plugin_options']->get('trigger_confirmpost_redirect_url')) : 'index.php';
        $app->enqueueMessage($redirect_message, 'success');
        $app->redirect($redirect_url);

        return true;
    }

    function logUpdateForms($params,$forms_to_log = '') : bool
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $user = JFactory::getSession()->get('emundusUser');

        $excludeElements = ['id', 'time_date', 'user', 'fnum'];
        $timeElements = ['birthday', 'birthday_remove_slashes', 'date', 'jdate', 'time', 'timer', 'timestamp', 'years'];
        $checkElements = ['radiobutton', 'dropdown', 'yesno'];
        $multipleElements = ['checkbox'];

        /* get process data */
	    $must_be_logged = true;
        $formData = $params['formModel']->formData;
        $formid = $formData['formid'];
        $keys = array_keys($formData);

	    if(!empty($forms_to_log)){
		    $forms_to_log = explode(',',$forms_to_log);
			if(!in_array($formid,$forms_to_log)){
				$must_be_logged = false;
			}
	    }

		if($must_be_logged) {
			$query->select('label')
				->from($db->quoteName('jos_fabrik_forms', 'jff'))
				->where($db->quoteName('jff.id') . ' = ' . $db->quote($formid));
			$db->setQuery($query);
			$formLabel = $db->loadResult();

			$jinput = JFactory::getApplication()->input;
			$formid = $jinput->get('formid');


			/* old data */
			$parentTable = '';
			$elements    = [];
			$oldData     = [];
			$results     = [];

			$fnum = !empty($user->fnum) ? $user->fnum : null;

			foreach ($formData as $key => $value) {
				if (strpos($key, '___')) {
					$table_name  = explode('___', $key)[0];
					$column_name = explode('___', $key)[1];

					//TODO : Get parent table using jos_fabrik_joins, not working if multiple groups as repeatable
					if (strpos($key, '___id') && !strpos($key,'repeat')) {
						if(empty($parentTable)) {
							$parentTable = $table_name;
						}
					}

					if (strpos($key, '___fnum') && empty($fnum)) {
						$fnum = $value;
					}

					if ($column_name !== null && strpos($column_name, '_raw') === false && strpos($column_name, '-') === false && !in_array($column_name, $excludeElements)) {
						$elements[] = $key;
					}
				}
			}

			if (!empty($fnum)) {
				try {
					// Get old datas
					foreach ($elements as $element) {
						$table_name  = explode('___', $element)[0];
						$column_name = explode('___', $element)[1];

						if (!strpos($element, 'repeat')) {
							$query->clear()
								->select($db->quoteName($table_name . '.' . $column_name))
								->from($db->quoteName($parentTable))
								->where($db->quoteName($parentTable . '.fnum') . ' = ' . $db->quote($fnum));
						}
						else
						{
							$query->clear()
								->select($table_name . '.' . $column_name)
								->from($db->quoteName($table_name))
								->leftJoin($db->quoteName($parentTable) . ' ON ' . $db->quoteName($parentTable . '.id') . ' = ' . $db->quoteName($table_name . '.parent_id'))
								->where($db->quoteName($parentTable . '.fnum') . ' = ' . $db->quote($fnum));
						}
						$db->setQuery($query);
						$res                                         = $db->loadColumn();
						$oldData[$table_name . '___' . $column_name] = $res;
					}

					$intersectKey = array_keys(array_intersect_key($oldData, $formData));

					foreach ($intersectKey as $iKey) {
						$diffs = $this->dataFormCompare($oldData, $formData, $iKey);

						if (!empty($diffs)) {
							$column_name = explode('___', $iKey)[1];
							/* get element data (getObject) */

							$query->clear()
								->select("distinct jfe.id as element_id, jfe.name as element_name, jfe.label as element_label, jfe.params as element_params, jfg.id as group_id, jfg.name as group_name, jfg.label as group_label, jff1.id as form_id, jff1.label as form_label, jfe.plugin, instr(jfg.params, '\"repeat_group_button\":\"1\"') as group_repeat")
								->from($db->quoteName('#__fabrik_elements', 'jfe'))
								->leftJoin($db->quoteName('#__fabrik_groups', 'jfg') . ' ON ' . $db->quoteName('jfe.group_id') . ' = ' . $db->quoteName('jfg.id'))
								->leftJoin($db->quoteName('#__fabrik_formgroup', 'jff') . ' ON ' . $db->quoteName('jff.group_id') . ' = ' . $db->quoteName('jfg.id'))
								->leftJoin($db->quoteName('#__fabrik_forms', 'jff1') . ' ON ' . $db->quoteName('jff.form_id') . ' = ' . $db->quoteName('jff1.id'))
								->where($db->quoteName('jff1.id') . ' = ' . $formid)
								->andWhere($db->quoteName('jfe.hidden') . ' != 1')
								->andWhere($db->quoteName('jfe.published') . ' = 1')
								->andWhere($db->quoteName('jfe.name') . ' LIKE ' . $db->quote($column_name));
							$db->setQuery($query);
							$element = $db->loadAssoc();

							if (empty($element)) {
								continue;
							}

							if ($element['group_repeat'] == 0) {

								// flat old data and new data
								$diffs['old_data'] = reset($diffs['old_data']);
								$diffs['new_data'] = reset($diffs['new_data']);

								if (in_array($element['plugin'], $timeElements)) {
									if (strtotime($diffs['old_data']) === strtotime($diffs['new_data'])) {
										continue;
									}
								}

								if (in_array($element['plugin'], $checkElements) or in_array($element['plugin'], $multipleElements)) {
									$optSubValues = json_decode($element['element_params'])->sub_options->sub_values;
									$optSubLabels = json_decode($element['element_params'])->sub_options->sub_labels;

									$oldsValues = $newsValues = [];
									$oldsLabels = $newsLabels = [];

									if (in_array($element['plugin'], $checkElements)) {
										/* find the index of subValues from $diffs['old_data'] and $diffs['new_data'] */
										$oldArrayIndex = array_search($diffs['old_data'], $optSubValues);
										$newArrayIndex = array_search($diffs['new_data'], $optSubValues);

										/* get oldValues and newValues */
										$oldsValues = [$diffs['old_data']];
										$newsValues = [$diffs['new_data']];

										/* using ternary operator */
										$oldsLabels = $oldArrayIndex !== false ? JText::_($optSubLabels[$oldArrayIndex]) : null;
										$newsLabels = $newArrayIndex !== false ? JText::_($optSubLabels[$newArrayIndex]) : null;
									}
									elseif (in_array($element['plugin'], $multipleElements)) {
										/* replace the substring "[" and "]" by empty string */
										$olds = str_replace('[', '', $diffs['old_data']);
										$olds = str_replace(']', '', $olds);
										$olds = str_replace(',', ';', $olds);
										$olds = str_replace('"', '', $olds);

										/* convert $diffs['old_data'] to (string) by explode (";") */
										$olds = explode(';', $olds);

										/* null condition */
										$olds = empty(trim($olds)) !== false ? $olds : [''];

										$olds = is_array($olds) === false ? array($olds) : $olds;
										$news = is_array($diffs['new_data']) === false ? array($diffs['new_data']) : $diffs['new_data'];

										foreach ($olds as $_old) {
											$_oIndex      = array_search($_old, $optSubValues);
											$_oLabels     = $_oIndex !== false ? JText::_($optSubLabels[$_oIndex]) : null;
											$oldsLabels[] = $_oLabels;
											$oldsValues[] = $_old;
										}

										foreach ($news as $_new) {
											$_nIndex      = array_search($_new, $optSubValues);
											$_nLabels     = $_nIndex !== false ? JText::_($optSubLabels[$_nIndex]) : null;
											$newsLabels[] = $_nLabels;
											$newsValues[] = $_new;
										}
									}

									if ($oldsValues === $newsValues) {
										$oldsLabels = $newsLabels = "";
									}
									else {
										$oldsLabels = count($oldsLabels) > 1 ? (empty(trim(implode('', $oldsLabels))) === true ? '' : implode('', $oldsLabels)) : (is_array($oldsLabels) === true ? $oldsLabels[0] : $oldsLabels);
										$newsLabels = count($newsLabels) > 1 ? (empty(trim(implode('', $newsLabels))) === true ? '' : implode('', $newsLabels)) : (is_array($newsLabels) === true ? $newsLabels[0] : $newsLabels);
									}
									$diffs['old_data'] = $oldsLabels;
									$diffs['new_data'] = $newsLabels;
								}

								if (in_array($element['plugin'], ['databasejoin', 'cascadingdropdown'])) {
									//TODO : HANDLE THE CONCAT LABEL OF DATABASE JOIN with {shortlang}, {thistable}
									/* get label of this element by $diffs['old_data'] and $diffs['new_data'] */
									$query->clear()
										->select('*')
										->from($db->quoteName('#__fabrik_joins', 'jfj'))
										->where($db->quoteName('jfj.element_id') . ' = ' . $db->quote($element['element_id']));
									$db->setQuery($query);
									$joinResults = $db->loadObject();

									$joinlabel = json_decode($joinResults->params, true)['join-label'];
									$joinKey   = $joinResults->table_join_key;
									$joinFrom  = $joinResults->table_join;

									$query->clear()->select($db->quoteName($joinlabel))->from($db->quoteName($joinFrom))->where($db->quoteName($joinKey) . ' = ' . $db->quote($diffs['old_data']));
									$db->setQuery($query);
									$diffs['old_data'] = $db->loadResult();

									$query->clear()->select($db->quoteName($joinlabel))->from($db->quoteName($joinFrom))->where($db->quoteName($joinKey) . ' = ' . $db->quote($diffs['new_data']));

									$db->setQuery($query);
									$diffs['new_data'] = $db->loadResult();
								}

								$results[$iKey] = array_merge($element, $diffs);
							}
							else {
								/* group repeat is always an array nD with n >= 1 */
								if ($element['plugin'] != 'databasejoin' and $element['plugin'] != 'cascadingdropdown') {
									//TODO : TIME, DATE PLUGIN WITH REPEAT GROUP
									// check or select plugins //
									if (in_array($element['plugin'], $checkElements) or in_array($element['plugin'], $multipleElements)) {
										/* get subValues and subLabels */
										$optSubValues = json_decode($element['element_params'])->sub_options->sub_values;
										$optSubLabels = json_decode($element['element_params'])->sub_options->sub_labels;
										/* *********** */

										$oldsValues = $newsValues = array();
										$oldsLabels = $newsLabels = array();

										if (in_array($element['plugin'], $checkElements)) {
											$olds = is_array($diffs['old_data']) === false ? array($diffs['old_data']) : $diffs['old_data'];
											$news = is_array($diffs['new_data']) === false ? array($diffs['new_data']) : $diffs['new_data'];

											foreach ($olds as $_old) {
												$_oIndex      = array_search($_old, $optSubValues);
												$_oLabels     = $_oIndex !== false ? JText::_($optSubLabels[$_oIndex]) : null;
												$oldsLabels[] = $_oLabels;
												$oldsValues[] = $_old;
											}

											foreach ($news as $_new) {
												$_nIndex      = array_search($_new, $optSubValues);
												$_nLabels     = $_nIndex !== false ? JText::_($optSubLabels[$_nIndex]) : null;
												$newsLabels[] = $_nLabels;
												$newsValues[] = $_new;
											}

										}
										else if (in_array($element['plugin'], $multipleElements)) {
											$diffs['old_data'] = is_array($diffs['old_data']) === true ? $diffs['old_data'] : [$diffs['old_data']];

											$olds = array_map(
												function ($x) {
													$x = str_replace('[', '', $x);
													$x = str_replace(']', '', $x);
													$x = str_replace(',', ';', $x);

													return str_replace('"', '', $x);
												}, array_values($diffs['old_data']));

											////
											$olds = array_map(function ($x) {
												return empty(trim(explode(";", $x))) !== false ? explode(';', $x) : [''];
											}, array_values($olds));
											$olds = call_user_func_array('array_merge', $olds);

											$olds = is_array($olds) === false ? [$olds] : $olds;
											$news = is_array($diffs['new_data']) === false ? [$diffs['new_data']] : $diffs['new_data'];

											foreach ($olds as $_old) {
												$_oIndex      = array_search($_old, $optSubValues);
												$_oLabels     = $_oIndex !== false ? JText::_($optSubLabels[$_oIndex]) : null;
												$oldsLabels[] = $_oLabels;
												$oldsValues[] = $_old;
											}

											foreach ($news as $_new) {
												$_nIndex      = array_search($_new, $optSubValues);
												$_nLabels     = $_nIndex !== false ? JText::_($optSubLabels[$_nIndex]) : null;
												$newsLabels[] = $_nLabels;
												$newsValues[] = $_new;
											}
										}

										if (array_values($oldsValues) === array_values($newsValues)) {
											$oldsLabels = $newsLabels = '';
										}
										else {
											$oldsLabels = count($oldsLabels) > 1 ? (empty(trim(implode("", $oldsLabels))) === true ? '' : implode('', $oldsLabels)) : (is_array($oldsLabels) === true ? $oldsLabels[0] : $oldsLabels);
											$newsLabels = count($newsLabels) > 1 ? (empty(trim(implode("", $newsLabels))) === true ? '' : implode('', $newsLabels)) : (is_array($newsLabels) === true ? $newsLabels[0] : $newsLabels);
										}
										$diffs['old_data'] = $oldsLabels;
										$diffs['new_data'] = $newsLabels;


									}
									else {
										$diffs['old_data'] = count($diffs['old_data']) > 1 ? (empty(trim(implode('', $diffs['old_data']))) === true ? '' : implode('', $diffs['old_data'])) : (is_array($diffs['old_data']) === true ? $diffs['old_data'][0] : $diffs['old_data']);
										$diffs['new_data'] = count($diffs['new_data']) > 1 ? (empty(trim(implode('', $diffs['new_data']))) === true ? '' : implode('', $diffs['new_data'])) : (is_array($diffs['new_data']) === true ? $diffs['new_data'][0] : $diffs['new_data']);
									}
								}
								else {
									$query->clear()
										->select('*')
										->from($db->quoteName('#__fabrik_joins', 'jfj'))
										->where($db->quoteName('jfj.element_id') . ' = ' . $db->quote($element['element_id']));
									$db->setQuery($query);
									$joinResults = $db->loadObject();

									/* get the join results */
									$joinlabel = json_decode($joinResults->params, true)['join-label'];
									$joinKey   = $joinResults->table_join_key;
									$joinFrom  = $joinResults->table_join;

									$oldsLabels = $newsLabels = array();

									foreach ($diffs['old_data'] as $_old) {
										$query->clear()->select($db->quoteName($joinlabel))->from($db->quoteName($joinFrom))->where($db->quoteName($joinKey) . ' = ' . $db->quote($_old));
										$db->setQuery($query);
										$oldsLabels[] = $db->loadResult();
									}

									if (empty(trim(implode('', $diffs['new_data'])))) {
										$diffs['new_data'] = '';
									}
									else {
										foreach ($diffs['new_data'] as $_new) {
											$query->clear()->select($db->quoteName($joinlabel))->from($db->quoteName($joinFrom))->where($db->quoteName($joinKey) . ' = ' . $db->quote($_new));
											$db->setQuery($query);
											$newsLabels[] = $db->loadResult();
										}
									}

									$diffs['old_data'] = count($oldsLabels) > 1 ? (empty(trim(implode('', $oldsLabels))) === true ? '' : implode('', $oldsLabels)) : (is_array($oldsLabels) === true ? $oldsLabels[0] : $oldsLabels);
									$diffs['new_data'] = count($newsLabels) > 1 ? (empty(trim(implode('', $newsLabels))) === true ? '' : implode('', $newsLabels)) : (is_array($newsLabels) === true ? $newsLabels[0] : $newsLabels);
								}
							}

							$results[$iKey] = array_merge($element, $diffs);
						}

					}
					$logger = [];

					if (!empty($results)) {
						foreach ($results as $result) {
							$logsStd = new stdClass();

							if (($result['old_data'] === null or empty(trim($result['old_data']))) and ($result['new_data'] === null or empty(trim($result['new_data'])))) {
								continue;
							}
							else {
								$logsStd->description = '[' . JText::_($formLabel) . ']';
								$logsStd->element     = JText::_($result['element_label']) . ' : ';
								$logsStd->old         = $result['old_data'];
								$logsStd->new         = $result['new_data'];
								$logger[]             = $logsStd;
							}
						}
					}

					# parse to JSON (json encode)
					$logParams = ['updated' => $logger];

					/* REGISTER LOGS TO DATABASE, DO NOT NEED USING THE SESSION IN THIS CASE */
					require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');
					$mFile        = new EmundusModelFiles();
					$applicant_id = ($mFile->getFnumInfos($fnum))['applicant_id'];


					/* get form id from POST */


					if (!empty($logParams['updated'])) {
						EmundusModelLogs::log($user->id, $applicant_id, $fnum, 1, 'u', 'COM_EMUNDUS_ACCESS_FILE_UPDATE', json_encode($logParams, JSON_UNESCAPED_UNICODE));
					}
				}
				catch (Exception $e) {
					JLog::add('Error construct form logs at line: ' . __LINE__ . ' in file: ' . __FILE__ . ' with message: ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
				}
			}
		}

        return true;
    }

    private function logUpdateState($old_status, $new_status, $user_id, $applicant_id, $fnum){
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

    private function dataFormCompare($old, $new, $key): array
    {
        $diffElements = [];

        if (!is_array($new[$key])) {
            $new[$key] = [$new[$key]];
        }

        if (is_array(current($new[$key])) === false) {
            $new[$key] = array_values($new[$key]);
        } elseif (count($new[$key]) >= 1) {    // the sub array
            $new[$key] = call_user_func_array('array_merge', array_values($new[$key]));
        }

        if (trim(implode('', array_values($old[$key]))) === trim(implode('', array_values($new[$key])))) {
            return [];
        } elseif (array_values($old[$key]) !== array_values($new[$key])) {
            if (empty(trim(implode('', $old[$key]))) and empty(trim(implode('', $new[$key])))) {
                return [];
            } else {
                $diffElements['key_data'] = $key;
                $diffElements['old_data'] = $old[$key];
                $diffElements['new_data'] = $new[$key];
            }
        } else {
            return [];
        }

        return $diffElements;
    }
}
