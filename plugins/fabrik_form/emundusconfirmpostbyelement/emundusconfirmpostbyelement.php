<?php
/**
 * @version 2: emundusconfirmpost 2018-09-06 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Valide l'envoie d'un dossier de candidature et change le statut.
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

class PlgFabrik_FormEmundusconfirmpostbyelement extends plgFabrik_Form
{
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
    public function getFieldName($pname, $short = false)
    {
        $params = $this->getParams();

        if ($params->get($pname) == '')
        {
            return '';
        }

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
    public function getParam($pname, $default = '')
    {
        $params = $this->getParams();

        if ($params->get($pname) == '')
        {
            return $default;
        }

        return $params->get($pname);
    }

    /**
     * Main script.
     *
     * @return  void
     * @throws Exception
     */
    public function onAfterProcess() {

        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $student = JFactory::getSession()->get('emundusUser');

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
        $can_edit_after_deadline    = $eMConfig->get('can_edit_after_deadline', '0');
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

        $steps = $this->getParam('emundusconfirmpost_status', '1');
        $values = $this->getParam('emundusconfirmpost_values', '');
        $element = $this->getParam('emundusconfirmpost_element', '');

        try {
            if(!empty($values) && !empty($element)){
                $elt = explode('___',$element);
                $vals = explode(',',$values);
                $query = $db->getQuery(true);

                $query->select($elt[1])
                    ->from($db->quoteName($elt[0]))
                    ->where($db->quoteName('fnum') . ' = ' . $db->quote($student->fnum));
                $db->setQuery($query);
                $res = $db->loadResult();

                $key = array_search($res, $vals);

                if($key === false){
                    $step = explode(',',$steps)[0];
                } else {
                    $step = explode(',',$steps)[$key];
                }
            } else {
                $step = explode(',',$steps)[0];
            }
        } catch (Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }

        $offset = $app->get('offset', 'UTC');
        try {
            $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
            $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
            $now = $dateTime->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            echo $e->getMessage() . '<br />';
        }

        $current_phase = $m_campaign->getCurrentCampaignWorkflow($student->fnum);
        if (!empty($current_phase) && !empty($current_phase->end_date)) {
            $is_dead_line_passed = strtotime(date($now)) > strtotime($current_phase->end_date) || strtotime(date($now)) < strtotime($current_phase->start_date);
        } else if ($this->getParam('admission', 0) == 1) {
            $is_dead_line_passed = strtotime(date($now)) > strtotime(@$student->fnums[$student->fnum]->admission_end_date) || strtotime(date($now)) < strtotime(@$student->fnums[$student->fnum]->admission_start_date);
        } else {
            $is_dead_line_passed = (strtotime(date($now)) > strtotime(@$student->fnums[$student->fnum]->end_date)) ? true : false;
        }

        // Check campaign limit, if the limit is obtained, then we set the deadline to true
        $isLimitObtained = $m_campaign->isLimitObtained($student->fnums[$student->fnum]->campaign_id);

        // If we've passed the deadline and the user cannot submit (is not in the list of exempt users), block him.
        if ((($is_dead_line_passed && $can_edit_after_deadline != 1) || $isLimitObtained === true) && !in_array($student->id, $id_applicants)) {
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
            $query = 'UPDATE #__emundus_uploads SET can_be_deleted = 0 WHERE user_id = '.$student->id. ' AND fnum like '.$db->Quote($student->fnum);
            $db->setQuery($query);

            try {
                $db->execute();
            } catch (Exception $e) {
                // catch any database errors.
                JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            }

        }

        JPluginHelper::importPlugin('emundus');

        if($student->status != $step) {
            $app->triggerEvent('onBeforeStatusChange', [$student->fnum, $step]);
            $app->triggerEvent('callEventHandler', ['onBeforeStatusChange', ['fnum' => $student->fnum, 'state' => $step]]);
        }
        $app->triggerEvent('onBeforeSubmitFile', [$student->id, $student->fnum]);
        $app->triggerEvent('callEventHandler', ['onBeforeSubmitFile', ['user' => $student->id, 'fnum' => $student->fnum]]);

        $query = 'UPDATE #__emundus_campaign_candidature SET submitted=1, date_submitted=' . $db->Quote($now) . ', status='.$step.' WHERE applicant_id='.$student->id.' AND campaign_id='.$student->campaign_id. ' AND fnum like '.$db->Quote($student->fnum);
        $db->setQuery($query);

        try {
            $db->execute();

        } catch (Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }

        $query = 'UPDATE #__emundus_declaration SET time_date=' . $db->Quote($now) . ' WHERE user='.$student->id. ' AND fnum like '.$db->Quote($student->fnum);
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }
        JFactory::getApplication()->triggerEvent('onAfterSubmitFile', [$student->id, $student->fnum]);
        JFactory::getApplication()->triggerEvent('callEventHandler', ['onAfterSubmitFile', ['user' => $student->id, 'fnum' => $student->fnum]]);

        $student->candidature_posted = 1;

        // Send emails defined in trigger
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
				$tags = $m_emails->setTags($student->id, $post, $fnum, '', $application_form_name.$export_path);
                $application_form_name = preg_replace($tags['patterns'], $tags['replacements'], $application_form_name);
                $application_form_name = $m_emails->setTagsFabrik($application_form_name, array($fnum));

                // Format filename
                $application_form_name = $m_emails->stripAccents($application_form_name);
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
                    $export_path = $m_emails->setTagsFabrik($export_path, array($fnum));

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
    }

    /**
     * Raise an error - depends on whether you are in admin or not as to what to do
     *
     * @param array   &$err   Form models error array
     * @param string   $field Name
     * @param string   $msg   Message
     *
     * @return  void
     * @throws Exception
     */

    protected function raiseError(&$err, $field, $msg)
    {
        $app = JFactory::getApplication();

        if ($app->isClient('administrator'))
        {
            $app->enqueueMessage($msg, 'notice');
        }
        else
        {
            $err[$field][0][] = $msg;
        }
    }
}
