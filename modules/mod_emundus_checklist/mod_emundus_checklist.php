<?php
/**
 * @version		$Id: mod_emundus_checklist.php
 * @package		Joomla
 * @copyright	Copyright (C) 2016 emundus.fr. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.'/components/com_emundus/helpers/cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();

$document = JFactory::getDocument();
$document->addStyleSheet('modules/mod_emundus_checklist/style/emundus_checklist.css?'.$hash);

$user = JFactory::getSession()->get('emundusUser');

if (isset($user->fnum) && !empty($user->fnum)) {
    require_once(dirname(__FILE__).DS.'helper.php');
    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
    require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'checklist.php');
    include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
    require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
    require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
    require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
    require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
    $m_checklist = new EmundusModelChecklist();
    $m_application = new EmundusModelApplication();
    $m_campaign = new EmundusModelCampaign();
    $m_profile = new EmundusModelProfile();
    $m_files = new EmundusModelFiles();
    $m_emails = new EmundusModelEmails();

    $db = JFactory::getDBO();
    $app = JFactory::getApplication();

    $jinput = $app->input;
    $option = $jinput->get('option');
    $view = $jinput->get('view');

    $show_forms = $params->get('show_forms', 1);
    $show_mandatory_documents = $params->get('show_mandatory_documents', 1);
    $show_optional_documents = $params->get('show_optional_documents', 0);
    $show_duplicate_documents = $params->get('show_duplicate_documents', 1);
	$show_preliminary_documents = $params->get('show_preliminary_documents', 0);
	$forms_title = $params->get('forms_title', JText::_('FORMS'));
    $mandatory_documents_title = $params->get('mandatory_documents_title', JText::_('MANDATORY_DOCUMENTS'));
    $optional_documents_title = $params->get('optional_documents_title', JText::_('OPTIONAL_DOCUMENTS'));
	$preliminary_documents_title = $params->get('preliminary_documents_title', JText::_('PRELIMINARY_DOCUMENTS'));
	$admission = $params->get('admission', 0);
    $show_send = $params->get('showsend', 1);

    $eMConfig = JComponentHelper::getParams('com_emundus');
    $id_applicants = $eMConfig->get('id_applicants', '0');
    $exceptions = explode(',',$id_applicants);
    $applicant_files_path = $eMConfig->get('applicant_files_path', 'images/emundus/files/');
    $application_fee = $eMConfig->get('application_fee', 0);

    $checkout_url = null;
    if ($application_fee) {
        $fnumInfos = $m_files->getFnumInfos($user->fnum);
        $order = $m_application->getHikashopOrder($fnumInfos);
        $paid = !empty($order);
        $cart = $m_application->getHikashopCartUrl($user->profile);
        $cartorder = null;

        if (!$paid || !empty($cart)) {

            // If students with a scholarship have a different fee.
            // The form ID will be appended to the URL, taking him to a different checkout page.
            if (isset($scholarship_document)) {

                // See if applicant has uploaded the required scolarship form.
                try {

                    $query = 'SELECT count(id) FROM #__emundus_uploads
								WHERE attachment_id = ' . $scholarship_document . '
								AND fnum LIKE ' . $db->Quote($user->fnum);

                    $db->setQuery($query);
                    $uploaded_document = $db->loadResult();

                } catch (Exception $e) {
                    JLog::Add('Error in plugin/isApplicationCompleted at SQL query : ' . $query, Jlog::ERROR, 'plugins');
                }

                // If he hasn't, no discount for him.
                if ($uploaded_document == 0) {
                    $scholarship_document = NULL;
                } else {
                    $scholarship = true;
                }

            }
            if (!empty($cart)) {
                $cartorder = $m_application->getHikashopCart($fnumInfos);
                $checkout_url = 'cart' . $user->profile;
            } elseif (!$paid) {
                $orderCancelled = false;

                $checkout_url = $m_application->getHikashopCheckoutUrl($user->profile . $scholarship_document);
                if (strpos($checkout_url, '${') !== false) {
                    $checkout_url = $m_emails->setTagsFabrik($checkout_url, [$user->fnum]);
                }
                if(!empty($checkout_url)) {
                    $checkout_url = 'index.php?option=com_hikashop&ctrl=product&task=cleancart&return_url=' . urlencode(base64_encode($checkout_url)) . '&usekey=fnum&rowid=' . $user->fnum;
                }

                $cancelled_orders = $m_application->getHikashopOrder($fnumInfos, true);

                if (!empty($cancelled_orders)) {
                    $orderCancelled = true;
                }
            }

        } else {
            $checkout_url = 'index.php';
        }
    }

    $menuid = $app->getMenu()->getActive()->id;
    $query='SELECT id, link FROM #__menu WHERE alias like "checklist%" AND menutype like "%'.$user->menutype.'"';
    $db->setQuery( $query );
    $itemid = $db->loadAssoc();

    $and = ($show_duplicate_documents != -1)?' AND esap.duplicate='.$show_duplicate_documents:'';
    $query = 'SELECT esa.value, esap.id, esa.id as _id, esap.mandatory, esap.duplicate
		FROM #__emundus_setup_attachment_profiles esap
		JOIN #__emundus_setup_attachments esa ON esa.id = esap.attachment_id
		WHERE esap.displayed = 1 '.$and.' AND esap.profile_id ='.$user->profile.'
		ORDER BY esa.ordering';
    $db->setQuery( $query );
    $documents = $db->loadObjectList();

    $mandatory_documents = array();
    $optional_documents = array();

    if (count($documents) > 0) {
        foreach ($documents as $document) {
            if ($document->mandatory == 1)
                $mandatory_documents[] = $document;
            else
                $optional_documents[] = $document;
        }
    }

    $query = $db->getQuery(true);

    $query->select('eu.*, esa.value as attachment_name, esa.id as esa_id')
        ->from($db->quoteName('#__emundus_uploads','eu'))
        ->leftJoin($db->quoteName('#__emundus_setup_attachment_profiles','esap').' ON '.$db->quoteName('eu.attachment_id').' = '.$db->quoteName('esap.attachment_id'))
        ->leftJoin($db->quoteName('#__emundus_setup_attachments','esa').' ON '.$db->quoteName('esap.attachment_id').' = '.$db->quoteName('esa.id'))
        ->where($db->quoteName('esap.displayed') . ' = 1')
        ->andWhere($db->quoteName('esap.profile_id') . ' = ' . $db->quote($user->profile))
        ->andWhere($db->quoteName('eu.fnum') . ' like ' . $db->quote($user->fnum))
        ->andWhere($db->quoteName('eu.user_id') . ' = ' . $db->quote($user->id))
        ->group('esap.mandatory,esap.ordering,esa.id')
        ->order('esap.mandatory DESC,esap.ordering');
    $db->setQuery($query);
    $uploads = $db->loadObjectList();

	$units = array( 'B', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb');

	foreach ($uploads as $upload){
        $file = $applicant_files_path . $user->id . '/' . $upload->filename;
        $bytes = filesize($file);

        if($bytes) {
	        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
	        $upload->filesize = number_format($bytes / pow(1024, $power), 0, '.', ',') . ' ' . $units[$power];
            /*$decimals = 0;

            $factor = floor((strlen($bytes) - 1) / 3);
            if ($factor > 0) $sz = 'KMGT';
            $upload->filesize = sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor - 1] . 'o';*/
        } else {
            $upload->filesize = 0;
        }
    }

    $forms = @EmundusHelperMenu::buildMenuQuery($user->profile);
	$keys_to_remove = array();
	foreach($forms as $key => $form) {
		$m_params = json_decode($form->menu_params, true);
		if(isset($m_params['menu_show']) && $m_params['menu_show'] == 0)
		{
			$keys_to_remove[] = $key;
		}
	}
	foreach($keys_to_remove as $key) {
		unset($forms[$key]);
	}
	$forms = array_values($forms);

    // Prepare display of send button
    $application = @modEmundusChecklistHelper::getApplication($user->fnum);
    $status_for_send = explode(',', $eMConfig->get('status_for_send', 0));

    $confirm_form_url = $m_checklist->getConfirmUrl().'&usekey=fnum&rowid='.$user->fnum;
    $uri = JUri::getInstance();
    $is_confirm_url = false;

    if (preg_match('/formid=[0-9]+&/', $confirm_form_url, $matches)) {
        if (!empty($matches) && strpos($uri->getQuery(), $matches[0]) !== false) {
            $is_confirm_url = true;
        }
    }

    $current_phase = $m_campaign->getCurrentCampaignWorkflow($user->fnum);
    $current_phase = !empty($current_phase->id) ? $current_phase : null;
    $attachments_progress = $m_application->getAttachmentsProgress($user->fnum);
    $forms_progress 	= $m_application->getFormsProgress($user->fnum);

    $app = JFactory::getApplication();
    $offset = $app->get('offset', 'UTC');
    $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
    $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
    $now = $dateTime->format('Y-m-d H:i:s');

    if (!empty($user->end_date)) {
        $is_dead_line_passed = strtotime(date($now)) > strtotime($user->end_date);

        if (!empty($current_phase) && !empty($current_phase->end_date)) {
            $is_dead_line_passed = strtotime(date($now)) > strtotime($current_phase->end_date);
        } elseif ($admission) {
            $is_dead_line_passed = strtotime(date($now)) > strtotime($user->admission_end_date);
        }
    }
    if (!empty($current_phase)) {
        $is_app_sent = !in_array($user->status, $current_phase->entry_status);
        $status_for_send = array_merge($status_for_send, $current_phase->entry_status);
		$show_preliminary_documents = $show_preliminary_documents && $current_phase->display_preliminary_documents;
    } elseif (!empty($user->status)) {
        $is_app_sent = $user->status != 0;
    }

    $id_applicants = $eMConfig->get('id_applicants', '0');
    $applicants = explode(',',$id_applicants);
    $can_edit_after_deadline = $eMConfig->get('can_edit_after_deadline', '0');
    $application_fee = $eMConfig->get('application_fee', 0);
    $application_fee = (!empty($application_fee) && !empty($m_profile->getHikashopMenu($user->profile)));
    if ($application_fee) {
        $fnumInfos = $m_files->getFnumInfos($user->fnum);

        $order = $m_application->getHikashopOrder($fnumInfos);
        $cart = $m_application->getHikashopCartUrl($user->profile);
        $paid = !empty($order);
    }
    //

	if ($show_preliminary_documents) {
		include_once(JPATH_BASE . '/modules/mod_emundus_campaign_dropfiles/helper.php');
		$dropfiles_helper = new modEmundusCampaignDropfilesHelper();

		if (!empty($current_phase) && $current_phase->specific_documents) {
			$preliminary_documents = $dropfiles_helper->getFiles(null, $user->campaign_id, $user->fnum);
		} else {
			$preliminary_documents = $dropfiles_helper->getFiles(null, $user->campaign_id);
		}
	}

    require(JModuleHelper::getLayoutPath('mod_emundus_checklist'));
}
