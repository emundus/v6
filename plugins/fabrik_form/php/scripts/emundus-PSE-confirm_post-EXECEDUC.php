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
$student = JFactory::getSession()->get('emundusUser');
$app = JFactory::getApplication();
$email_from_sys = $app->getCfg('mailfrom');

include_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
include_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'export.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'profile.php');

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
$export_pdf                 = $eMConfig->get('export_application_pdf', 0);
$export_path                = $eMConfig->get('export_path', null);

$application = new EmundusModelApplication;
$filesModel = new EmundusModelFiles;
$campaigns = new EmundusModelCampaign;
$emails = new EmundusModelEmails;
$m_profile = new EmundusModelProfile;

$application_fee  		= (!empty($application_fee) && !empty($m_profile->getHikashopMenu($user->profile)));

// Application fees
if ($application_fee) {
    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');

    $fnumInfos = $filesModel->getFnumInfos($student->fnum);
    if (count($fnumInfos) > 0) {
        $paid = !empty($application->getHikashopOrder($fnumInfos));

        if (!$paid) {
            $checkout_url = $application->getHikashopCheckoutUrl($student->profile);
            $mainframe->redirect(JRoute::_($checkout_url));
        }
    } else $mainframe->redirect('index.php');
}
// get current applicant course
$campaign = $campaigns->getCampaignByID($student->campaign_id);

// Database UPDATE data
//// Applicant cannot delete this attachments now
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

// If pdf exporting is activated
if ($export_pdf == 1) {
    $fnum = $student->fnum;
    $fnumInfo = $filesModel->getFnumInfos($student->fnum);
    $files_list = array();

    // Build pdf file
    if (is_numeric($fnum) && !empty($fnum)) {
        // Check if application form is in custom order
        if (!empty($application_form_order)) {
            $application_form_order = explode(',',$application_form_order);
            $files_list[] = EmundusHelperExport::buildFormPDF($fnumInfo, $fnumInfo['applicant_id'], $fnum, 1, $application_form_order);
        } else
            $files_list[] = EmundusHelperExport::buildFormPDF($fnumInfo, $fnumInfo['applicant_id'], $fnum, 1);

        // Check if pdf attachements are in custom order
        if (!empty($attachment_order)) {
            $attachment_order = explode(',',$attachment_order);
            foreach ($attachment_order as $attachment_id) {
                // Get file attachements corresponding to fnum and type id
                $files[] = $application->getAttachmentsByFnum($fnum, null, $attachment_id);
            } 
        } else {
            // Get all file attachements corresponding to fnum
            $files[] = $application->getAttachmentsByFnum($fnum, null, null);
        }
        // Break up the file array and get the attachement files
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

        // Build filename from tags, we are using helper functions found in the email model, not sending emails ;)
        $post = array('FNUM' => $fnum);
        $tags = $emails->setTags($student->id, $post, $fnum, '', $application_form_name);
        $application_form_name = preg_replace($tags['patterns'], $tags['replacements'], $application_form_name);
        $application_form_name = $emails->setTagsFabrik($application_form_name, array($fnum));
        
        // Format filename
        $application_form_name = $emails->stripAccents($application_form_name);
        $application_form_name = preg_replace('/[^A-Za-z0-9 _.-]/','', $application_form_name);
        $application_form_name = preg_replace('/\s/', '', $application_form_name);
        $application_form_name = strtolower($application_form_name);
        
        // If a file exists with that name, delete it
        if (file_exists(JPATH_BASE . DS . 'tmp' . DS . $application_form_name))
            unlink(JPATH_BASE . DS . 'tmp' . DS . $application_form_name);  

        // Ouput pdf with desired file name
        $pdf->Output(JPATH_BASE . DS . 'tmp' . DS . $application_form_name.".pdf", 'F');

        // If export path is defined
        if (!empty($export_path)) {
            if (!file_exists(JPATH_BASE.DS.$export_path)) {
                mkdir(JPATH_BASE.DS.$export_path);
                chmod(JPATH_BASE.DS.$export_path, 0755);
            }
            if (file_exists(JPATH_BASE.DS.$export_path.$application_form_name.".pdf")) {
                unlink(JPATH_BASE.DS.$export_path.$application_form_name.".pdf");
            }
            copy(JPATH_BASE.DS.'tmp'.DS.$application_form_name.".pdf", JPATH_BASE.DS.$export_path.$application_form_name.".pdf");
        }
        if (file_exists(JPATH_BASE.DS."images".DS."emundus".DS."files".DS.$student->id.DS.$fnum."_application_form_pdf.pdf"))
                    unlink(JPATH_BASE.DS."images".DS."emundus".DS."files".DS.$student->id.DS.$fnum."_application_form_pdf.pdf");
        copy(JPATH_BASE.DS.'tmp'.DS.$application_form_name.".pdf", JPATH_BASE.DS."images".DS."emundus".DS."files".DS.$student->id.DS.$fnum."_application_form_pdf.pdf");
    }
}

// Copy file for other selected courses
try
{
    include_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');

    // get previous selected programmes
    $query = 'SELECT r.campaign_id 
                FROM `#__emundus_session` as s
                LEFT JOIN `#__emundus_session_397_repeat` as r ON s.id=r.parent_id
                WHERE s.fnum like '.$db->Quote($student->fnum);
    $db->setQuery( $query );
    $campaign_ids = $db->loadColumn();

    $fnum_from = $student->fnum;
    $status = 1;

    foreach ($campaign_ids as $key => $campaign_id) {
        // create new fnum
        $fnum_to = date('YmdHis').str_pad($campaign_id, 7, '0', STR_PAD_LEFT).str_pad($student->id, 7, '0', STR_PAD_LEFT);
        
        $query = 'SELECT * FROM #__emundus_campaign_candidature WHERE fnum like '.$db->Quote($fnum_from);
        $db->setQuery( $query );
        $application_file = $db->loadAssoc();

        if (count($application_file) > 0) {
            $application_file['fnum'] = $fnum_to;
            $application_file['copied'] = 1;
            unset($application_file['id']);

    // 2. Copie definition of fnum for new file
            $query = 'INSERT INTO #__emundus_campaign_candidature (`applicant_id`, `user_id`, `campaign_id`, `submitted`, `date_submitted`, `cancelled`, `fnum`, `status`, `published`, `copied`) 
                    VALUES ('.$application_file['applicant_id'].', '.$student->id.', '.$campaign_id.', '.$application_file['submitted'].', '.$db->Quote($application_file['date_submitted']).', '.$application_file['cancelled'].', '.$db->Quote($fnum_to).', '.$status.', 1, 1)';
            $db->setQuery( $query );
            $db->execute();
        }

    // 3. Duplicate file for new fnum
        $result = EmundusModelApplication::copyApplication($fnum_from, $fnum_to);
        if ($result) {
    // 4. Duplicate attachment for new fnum
            $result = EmundusModelApplication::copyDocuments($fnum_from, $fnum_to);

        }
    }
}
catch(Exception $e)
{
    $error = JUri::getInstance().' :: USER ID : '.$student->id.' -> '.$e->getMessage();
    JLog::add($error, JLog::ERROR, 'com_emundus');
}
?>  