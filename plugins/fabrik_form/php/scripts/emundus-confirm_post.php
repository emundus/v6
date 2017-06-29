<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1.5: confirm_post.php 89 2017-06-20 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2016 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Marquer le dossier comme envoyé + Envoi automatique d'un email suivant les triggers définis
 */

$db = JFactory::getDBO();
$student =  JFactory::getUser();
$app = JFactory::getApplication();
$email_from_sys = $app->getCfg('mailfrom');

include_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
include_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'export.php');

jimport('joomla.log.log');
JLog::addLogger(
    array(
        // Sets file name
        'text_file' => 'com_emundus.submit.php'
    ),
    // Sets messages of all log levels to be sent to the file
    JLog::ALL,
    // The log category/categories which should be recorded in this file
    // In this case, it's just the one category from our extension, still
    // we need to put it inside an array
    array('com_emundus')
);

// Get params set in eMundus component configuration 
$eMConfig = JComponentHelper::getParams('com_emundus');
$can_edit_until_deadline    = $eMConfig->get('can_edit_until_deadline', 0);
$application_fee            = $eMConfig->get('application_fee', 0);
$application_form_order     = $eMConfig->get('application_form_order', null);
$attachment_order           = $eMConfig->get('attachment_order', null);
$application_form_name      = $eMConfig->get('application_form_name', "application_form_pdf");

$application = new EmundusModelApplication;
$filesModel = new EmundusModelFiles;
$campaigns = new EmundusModelCampaign;
$emails = new EmundusModelEmails;

// Application fees
if ($application_fee == 1) {
    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');

    $fnumInfos = $filesModel->getFnumInfos($student->fnum);
    if (count($fnumInfos) > 0) {
        $paid = count($application->getHikashopOrder($fnumInfos))>0?1:0;

        if (!$paid) {
            $checkout_url = $application->getHikashopCheckoutUrl($student->profile);
            $mainframe->redirect(JRoute::_($checkout_url));
        }
    } else $mainframe->redirect('index.php');
}
// get current applicant course
$campaign = $campaigns->getCampaignByID($student->campaign_id);

// Applicant cannot delete this attachments now
if (!$can_edit_until_deadline) {
    $query = 'UPDATE #__emundus_uploads SET can_be_deleted = 0 WHERE user_id = '.$student->id. ' AND fnum like '.$db->Quote($student->fnum);
    $db->setQuery( $query );
    try {
        $db->execute();
    } catch (Exception $e) {
        // catch any database errors.
        JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
    }
}

// Insert data in #__emundus_campaign_candidature
$query = 'UPDATE #__emundus_campaign_candidature SET submitted=1, date_submitted=NOW(), status=1 WHERE applicant_id='.$student->id.' AND campaign_id='.$student->campaign_id. ' AND fnum like '.$db->Quote($student->fnum);
$db->setQuery($query);
try {
    $db->execute();
} catch (Exception $e) {
    // catch any database errors.
    JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
}
$query = 'UPDATE #__emundus_declaration SET time_date=NOW() WHERE user='.$student->id. ' AND fnum like '.$db->Quote($student->fnum);
$db->setQuery($query);
try {
    $db->execute();
} catch (Exception $e) {
    // catch any database errors.
    JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
}

$student->candidature_posted = 1;

// Send emails defined in trigger
$step = 1;
$code = array($student->code);
$to_applicant = '0,1';
$trigger_emails = $emails->sendEmailTrigger($step, $code, $to_applicant, $student);

// TODO: Build filename from tags

// Format filename
$application_form_name = preg_replace('/[^A-Za-z0-9 _ .-]/','', $application_form_name);
$application_form_name = strtolower($application_form_name);

$fnum = $student->fnum;
$fnumInfo = $filesModel->getFnumInfos($student->fnum);

if (file_exists(JPATH_BASE . DS . 'tmp' . DS . $application_form_name))
    $files_list = array(JPATH_BASE . DS . 'tmp' . DS . $application_form_name);
else $files_list = array();

$start = 0;
$limit = 1;

if (is_numeric($fnum) && !empty($fnum)) {
    if (!empty($application_form_order)) {
        $application_form_order = explode(',',$application_form_order);
        // buildformpdf but with gid
        $files_list[] = EmundusHelperExport::buildFormPDF($fnumsInfo[$fnum], $fnumsInfo[$fnum]['applicant_id'], $fnum, $forms);
    } else
        $files_list[] = EmundusHelperExport::buildFormPDF($fnumInfo, $fnumInfo['applicant_id'], $fnum, 1, $application_form_name);

    if (!empty($attachment_order)) {
        $attachment_order = explode(',',$attachment_order);
        foreach ($attachment_order as $attachment_id) {
            // Get file attachements corresponding to fnum and type id
            $files[] = $application->getAttachmentsByFnum($fnum, null, $attachment_id);
        } 
    } else {
        // Get all file attachements corresponding to fnum
        $files = $application->getAttachmentsByFnum($fnum, null, null);
    }
    foreach ($files as $file) {
        $tmpArray = array();
        EmundusHelperExport::getAttachmentPDF($files_list, $tmpArray, $file, $fnumsInfo[$fnum]['applicant_id']);
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
    // Ouput pdf, this is where we give him his name
    $pdf->Output(JPATH_BASE . DS . 'tmp' . DS . $application_form_name.".pdf", 'F');

    $dataresult = [
        'start' => $start, 
        'limit' => $limit,  
        'forms' => $forms,
        'msg' => JText::_('FILES_ADDED').' : '.$fnum
    ];
    $result = ['status' => true, 'json' => $dataresult];
} else {
    $dataresult = [
        'start' => $start, 
        'limit' => $limit, 
        'forms' => $forms,
        'msg' => JText::_('ERROR_NO_FILE_TO_ADD').' : '.$fnum
    ];
    $result = ['status' => false, 'json' => $dataresult];
}
echo json_encode((object) $result);
exit();

?>  