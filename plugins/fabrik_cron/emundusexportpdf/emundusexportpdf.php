<?php
/**
 * A cron task to email a recall to incomplet applications
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.email
 * @copyright   Copyright (C) 2015 emundus.fr - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

/**
 * A cron task to export to PDF files to a local directory
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusexportpdf
 * @since       3.0
 */

class PlgFabrik_Cronemundusexportpdf extends PlgFabrik_Cron {

	/**
	 * Check if the user can use the plugin
	 *
	 * @param   string  $location  To trigger plugin on
	 * @param   string  $event     To trigger plugin on
	 *
	 * @return  bool can use or not
	 */
	public function canUse($location = null, $event = null) {
		return true;
	}

	/**
	 * Do the plugin action
	 *
	 * @param array  &$data data
	 *
	 * @return  int  number of records updated
	 * @throws Exception
	 */
	public function process(&$data, &$listModel) {
		jimport('joomla.mail.helper');
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'export.php');

		// LOGGER
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.emundusexportpdf.info.php'], JLog::INFO, 'com_emundus.emundusexportpdf');
		JLog::addLogger(['text_file' => 'com_emundus.emundusexportpdf.error.php'], JLog::ERROR, 'com_emundus.emundusexportpdf');

		$m_files = new EmundusModelFiles;
		$m_application  = new EmundusModelApplication;
		$m_emails  = new EmundusModelEmails;

		$params = $this->getParams();

		// Get params set in eMundus component configuration
		$eMConfig = JComponentHelper::getParams('com_emundus');
		$application_form_order     = $eMConfig->get('application_form_order', null);
		$attachment_order           = $eMConfig->get('attachment_order', null);
		$application_form_name      = $eMConfig->get('application_form_name', "application_form_pdf");
		$export_path                = $eMConfig->get('export_path', null);

		$export_campaign = $params->get('export_campaign', '');
		$export_status = $params->get('export_status', '1');
		$days_from_deadline = $params->get('days_from_deadline', '1');
		
		$this->log = '';

		// Get list of fnum to export
		$db = FabrikWorker::getDbo();

		$query = 'SELECT ecc.fnum, DATEDIFF(now(), esc.end_date) as days
					FROM #__emundus_campaign_candidature as ecc
					LEFT JOIN #__users as u ON u.id=ecc.applicant_id
					LEFT JOIN #__emundus_users as eu ON eu.user_id=u.id
					LEFT JOIN #__emundus_setup_campaigns as esc ON esc.id=ecc.campaign_id
					WHERE ecc.published = 1 
						AND u.block = 0 
						AND esc.published = 1 
						AND ecc.status in ('.$export_status.') 
						AND DATEDIFF(now(), esc.end_date) IN ('.$days_from_deadline.')';
		
		if ($export_campaign != '') {
			$query .= 'AND esc.id IN ('.$export_campaign.') ';
		}

		$db->setQuery($query);

		try {
			$fnums = $db->loadObjectList();
		} catch (Exception $e) {
			JLog::add('Error getting files to be exported: '.$query, JLog::ERROR, 'com_emundus.emundusexportpdf');
			return false;
		}

		// Generate emails from template and store it in message table
		foreach ($fnums as $key => $value) {
			JLog::add('Nb files to export: '.count($fnums), JLog::INFO, 'com_emundus.emundusexportpdf');
			$fnum = $value->fnum;
			$fnumInfo = $m_files->getFnumInfos($fnum);
			$files_list = array();

			// Build pdf file
			if (is_numeric($fnum) && !empty($fnum)) {
				// Check if application form is in custom order
				if (!empty($application_form_order)) {
					JLog::add('Fabrik group to export: '.$application_form_order, JLog::INFO, 'com_emundus.emundusexportpdf');
					$application_form_order = explode(',',$application_form_order);
					$files_list[] = EmundusHelperExport::buildFormPDF($fnumInfo, $fnumInfo['applicant_id'], $fnum, 1, $application_form_order);
				} else {
					$files_list[] = EmundusHelperExport::buildFormPDF($fnumInfo, $fnumInfo['applicant_id'], $fnum, 1);
				}
				JLog::add('files_list: '.$files_list[0], JLog::INFO, 'com_emundus.emundusexportpdf');
				JLog::add($fnum.': application exported', JLog::INFO, 'com_emundus.emundusexportpdf');
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
				JLog::add('Nb attachments to export: '.count($files), JLog::INFO, 'com_emundus.emundusexportpdf');
				// Break up the file array and get the attachement files
				foreach ($files as $file) {
					$tmpArray = array();
					EmundusHelperExport::getAttachmentPDF($files_list, $tmpArray, $file, $fnumInfo['applicant_id']);
				}
				JLog::add($fnum.': attachments exported', JLog::INFO, 'com_emundus.emundusexportpdf');
			}

			if (count($files_list) > 0) {
				JLog::add('Nb files to merge: '.count($files_list), JLog::INFO, 'com_emundus.emundusexportpdf');
				// all PDF in one file
				require_once(JPATH_LIBRARIES . DS . 'emundus' . DS . 'fpdi.php');
				$pdf = new ConcatPdf();
				JLog::add('Start merging: '.count($files_list), JLog::INFO, 'com_emundus.emundusexportpdf');
				$pdf->setFiles($files_list);
				JLog::add('Start concat: '.count($files_list), JLog::INFO, 'com_emundus.emundusexportpdf');
				$pdf->concat();
				JLog::add('File merged: '.count($files_list), JLog::INFO, 'com_emundus.emundusexportpdf');
				if (isset($tmpArray)) {
					foreach ($tmpArray as $fn) {
						unlink($fn);
						JLog::add($fn.' unlinked', JLog::INFO, 'com_emundus.emundusexportpdf');
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
				if (file_exists(JPATH_SITE . DS . 'tmp' . DS . $application_form_name)) {
					unlink(JPATH_SITE . DS . 'tmp' . DS . $application_form_name);
				}

				JLog::add('File generated: '.JPATH_SITE . DS . 'tmp' . DS . $application_form_name, JLog::INFO, 'com_emundus.emundusexportpdf');

				// Ouput pdf with desired file name
				$pdf->Output(JPATH_SITE . DS . 'tmp' . DS . $application_form_name.".pdf", 'F');

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
						if (!file_exists(JPATH_SITE.DS.$d)) {
							mkdir(JPATH_SITE.DS.$d);
							chmod(JPATH_SITE.DS.$d, 0755);
						}
					}
					if (file_exists(JPATH_SITE.DS.$export_path.$application_form_name.".pdf")) {
						unlink(JPATH_SITE.DS.$export_path.$application_form_name.".pdf");
					}
					JLog::add('PATH: '.JPATH_SITE.DS.$export_path.$application_form_name.".pdf", JLog::INFO, 'com_emundus.emundusexportpdf');
					copy(JPATH_SITE.DS.'tmp'.DS.$application_form_name.".pdf", JPATH_SITE.DS.$export_path.$application_form_name.".pdf");

					JLog::add('File copied: '.$application_form_name.".pdf", JLog::INFO, 'com_emundus.emundusexportpdf');
				}
				if (file_exists(JPATH_SITE.DS."images".DS."emundus".DS."files".DS.$student->id.DS.$fnum."_application_form_pdf.pdf")) {
					unlink(JPATH_SITE.DS."images".DS."emundus".DS."files".DS.$student->id.DS.$fnum."_application_form_pdf.pdf");
				}
				copy(JPATH_SITE.DS.'tmp'.DS.$application_form_name.".pdf", JPATH_SITE.DS."images".DS."emundus".DS."files".DS.$student->id.DS.$fnum."_application_form_pdf.pdf");

				JLog::add('File generated: '.JPATH_SITE.DS."images".DS."emundus".DS."files".DS.$student->id.DS.$fnum."_application_form_pdf.pdf", JLog::INFO, 'com_emundus.emundusexportpdf');
			}
		}

		$this->log .= "\n process " . count($applicants) . " applicant(s)";

		return count($applicants);
	}
}
