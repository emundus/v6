<?php
/**
 * @package        Joomla
 * @subpackage     Emundus
 * @copyright      Copyright (C) 2016 emundus.fr. All rights reserved.
 * @license        GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

use setasign\Fpdi\Tcpdf\Fpdi;

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');

jimport('joomla.application.component.model');
JModelLegacy::addIncludePath(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models');


/**
 * Content Component Query Helper
 *
 * @static
 * @package        Joomla
 * @subpackage     eMundus
 * @since          1.5
 */
class EmundusHelperExport
{

	/**
	 * @param         $fnumInfos
	 * @param         $sid
	 * @param         $fnum
	 * @param   int   $form_post
	 * @param   null  $form_ids
	 * @param   null  $options
	 * @param   null  $application_form_order
	 * @param   null  $elements
	 *
	 * @return string
	 */
	public static function buildFormPDF($fnumInfos, $sid, $fnum, $form_post = 0, $form_ids = null, $options = null, $application_form_order = null, $elements = null): string
	{
		$file        = JPATH_LIBRARIES . DS . 'emundus' . DS . 'pdf_' . $fnumInfos['training'] . '.php';
		$file_custom = JPATH_LIBRARIES . DS . 'emundus' . DS . 'custom' . DS . 'pdf_' . $fnumInfos['training'] . '.php';

		if (!file_exists($file) && !file_exists($file_custom)) {
			$file = JPATH_LIBRARIES . DS . 'emundus' . DS . 'pdf.php';
		}
		else {
			if (file_exists($file_custom)) {
				$file = $file_custom;
			}
		}

		if (!file_exists(EMUNDUS_PATH_ABS . $sid)) {
			mkdir(EMUNDUS_PATH_ABS . $sid);
			chmod(EMUNDUS_PATH_ABS . $sid, 0755);
		}

		// Prevent including PDF library twice.
		if (!function_exists('application_form_pdf')) {
			require_once($file);
		}
		$result = application_form_pdf($sid, $fnum, false, $form_post, $form_ids, $options, $application_form_order, null, null, $elements);

		if ($result) {
			$result = EMUNDUS_PATH_ABS . $sid . DS . $fnum . '_application.pdf';
		}

		return $result;
	}

	/*
	 * @static
	 * @params mandatory
	 *      --> $fnum::info [Array]
	 *      --> $sid
	 *      --> $forms = 1 (always)
	 *      --> $elements (Object)
	 *      --> $options (Array) [null]
	 * */
	public function buildCustomizedPDF($fnumInfos, $forms, $elements, $options = null, $application_form_order = null)
	{
		$_profile_model = JModelLegacy::getInstance('profile', 'EmundusModel');   /// invoke model of profile

		$file = JPATH_LIBRARIES . DS . 'emundus' . DS . 'pdf_' . $fnumInfos['training'] . '.php';

		if (!file_exists($file)) {
			$file = JPATH_LIBRARIES . DS . 'emundus' . DS . 'pdf.php';
		}

		if (!file_exists(EMUNDUS_PATH_ABS . $fnumInfos['applicant_id'])) {
			mkdir(EMUNDUS_PATH_ABS . $fnumInfos['applicant_id']);
			chmod(EMUNDUS_PATH_ABS . $fnumInfos['applicant_id'], 0755);
		}

		// Prevent including PDF library twice.
		if (!function_exists('application_form_pdf')) {
			require_once($file);
		}

		application_form_pdf($fnumInfos['applicant_id'], $fnumInfos['fnum'], false, $forms, null, $options, null, null, null, $elements);       /// create pdf file for each fnum

		return EMUNDUS_PATH_ABS . $fnumInfos['applicant_id'] . DS . $fnumInfos['fnum'] . '_application.pdf';
	}


	public static function buildHeaderPDF($fnumInfos, $sid, $fnum, $options = null)
	{
		$file = JPATH_LIBRARIES . DS . 'emundus' . DS . 'pdf_' . $fnumInfos['training'] . '.php';

		if (!file_exists($file)) {
			$file = JPATH_LIBRARIES . DS . 'emundus' . DS . 'pdf.php';
		}

		if (!file_exists(EMUNDUS_PATH_ABS . $sid)) {
			mkdir(EMUNDUS_PATH_ABS . $sid);
			chmod(EMUNDUS_PATH_ABS . $sid, 0755);
		}

		require_once($file);

		application_header_pdf($sid, $fnum, false, $options);


		return EMUNDUS_PATH_ABS . $sid . DS . $fnum . '_header.pdf';
	}


	/**
	 * Check whether pdf is encrypted or password protected.
	 *
	 * @param   String  $file
	 *
	 * @return bool
	 */
	public static function pdftest_is_encrypted($file)
	{
		require_once(JPATH_LIBRARIES . DS . 'emundus' . DS . 'fpdi.php');

		$pdf = new ConcatPdf();
		$pdf->setSourceFile($file);

		if ($pdf->currentParser->isEncrypted()) {
			return false;
		}
		else {
			return true;
		}
	}

	public static function get_pdf_prop($file)
	{
		$f = fopen($file, 'rb');
		if (!$f) {
			return false;
		}

		//Read the last 16KB
		fseek($f, -16384, SEEK_END);
		$s = fread($f, 16384);

		//Extract cross-reference table and trailer
		if (!preg_match("/xref[\r\n]+(.*)trailer(.*)startxref/s", $s, $a)) {
			return false;
		}
		$xref    = $a[1];
		$trailer = $a[2];

		//Extract Info object number
		if (!preg_match('/Info ([0-9]+) /', $trailer, $a)) {
			return false;
		}
		$object_no = @$a[1];

		//Extract Info object offset
		$lines  = preg_split("/[\r\n]+/", $xref);
		$line   = @$lines[1 + $object_no];
		$offset = (int) $line;
		if ($offset == 0) {
			return false;
		}

		//Read Info object
		fseek($f, $offset, SEEK_SET);
		$s = fread($f, 1024);
		fclose($f);

		//Extract properties
		if (!preg_match('/<<(.*)>>/Us', $s, $a))
			return false;
		$n    = preg_match_all('|/([a-z]+) ?\((.*)\)|Ui', $a[1], $a);
		$prop = array();
		for ($i = 0; $i < $n; $i++)
			$prop[$a[1][$i]] = $a[2][$i];

		return $prop;
	}

	public static function isEncrypted($file)
	{
		$f = fopen($file, 'rb');
		if (!$f)
			return false;

		//Read the last 320KB
		fseek($f, -323840, SEEK_END);
		$s = fread($f, 323840);

		//Extract Info object number
		return preg_match('/Encrypt ([0-9]+) /', $s);
	}

	public static function getAttachmentPDF(&$exports, &$tmpArray, $files, $sid)
	{
		if (!empty($files)) {

			$nb_application_forms = 0;
			foreach ($files as $file) {
				if (strrpos($file->filename, 'application_form') === false) {
					$exFileName = explode('.', $file->filename);
					$filePath   = EMUNDUS_PATH_ABS . $file->user_id . DS . $file->filename;
					if (file_exists($filePath) && filesize($filePath) != 0) {
						if (strtolower($exFileName[1]) != 'pdf') {
							$fn         = EmundusHelperExport::makePDF($file->filename, $exFileName[1], $sid);
							$exports[]  = $fn;
							$tmpArray[] = $fn;
						}
						else {
							if (EmundusHelperExport::isEncrypted($filePath)) {
								$fn         = EmundusHelperExport::makePDF($file->filename, $exFileName[1], $sid);
								$exports[]  = $fn;
								$tmpArray[] = $fn;
							}
							else {
								$exports[] = $filePath;
							}
						}
					}
				}
				else {
					$nb_application_forms++;
				}
			}

			if (sizeof($files) === $nb_application_forms) {
				return false;
			}
		}

		return $exports;
	}

	public static function getEvalPDF($fnum, $options = null)
	{

		$user = JFactory::getSession()->get('emundusUser');
		$user = empty($user) ? JFactory::getUser() : $user;

		if (!EmundusHelperAccess::asPartnerAccessLevel($user->id) && !in_array($fnum, array_keys($user->fnums))) {
			die(JText::_('ACCESS_DENIED'));
		}

		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'campaign.php');

		$m_profile  = new EmundusModelProfile();
		$m_campaign = new EmundusModelCampaign();

		$name    = $fnum . '-evaluation.pdf';
		$tmpName = JPATH_SITE . DS . 'tmp' . DS . $name;
		//$exports[] = $tmpName;

		if (!empty($fnum)) {
			$candidature = $m_profile->getFnumDetails($fnum);
			$campaign    = $m_campaign->getCampaignByID($candidature['campaign_id']);
		}

		$file = JPATH_LIBRARIES . DS . 'emundus' . DS . 'pdf_evaluation_' . $campaign['training'] . '.php';

		if (!file_exists($file))
			$file = JPATH_LIBRARIES . DS . 'emundus' . DS . 'pdf_evaluation.php';

		require_once($file);

		pdf_evaluation($user->id, $fnum, false, $tmpName, $options);

		return $tmpName;
	}

	public static function getDecisionPDF($fnum, $options = null)
	{
		$user = JFactory::getSession()->get('emundusUser');
		$user = empty($user) ? JFactory::getUser() : $user;

		if (!EmundusHelperAccess::asPartnerAccessLevel($user->id) && !in_array($fnum, array_keys($user->fnums)))
			die(JText::_('ACCESS_DENIED'));

		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'campaign.php');

		$m_profile  = new EmundusModelProfile();
		$m_campaign = new EmundusModelCampaign();

		$name    = $fnum . '-decision.pdf';
		$tmpName = JPATH_SITE . DS . 'tmp' . DS . $name;
		//$exports[] = $tmpName;

		if (!empty($fnum)) {
			$candidature = $m_profile->getFnumDetails($fnum);
			$campaign    = $m_campaign->getCampaignByID($candidature['campaign_id']);
		}

		$file = JPATH_LIBRARIES . DS . 'emundus' . DS . 'pdf_decision_' . $campaign['training'] . '.php';

		if (!file_exists($file))
			$file = JPATH_LIBRARIES . DS . 'emundus' . DS . 'pdf_decision.php';

		require_once($file);
		pdf_decision($user->id, $fnum, false, $tmpName, $options);

		return $tmpName;
	}

	public static function getAdmissionPDF($fnum, $options = null)
	{

		$user = JFactory::getSession()->get('emundusUser');
		$user = empty($user) ? JFactory::getUser() : $user;

		if (!EmundusHelperAccess::asPartnerAccessLevel($user->id) && !in_array($fnum, array_keys($user->fnums))) {
			die(JText::_('ACCESS_DENIED'));
		}

		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'campaign.php');

		$m_profile  = new EmundusModelProfile();
		$m_campaign = new EmundusModelCampaign();

		$eMConfig = JComponentHelper::getParams('com_emundus');
		$fileName = $eMConfig->get('application_admission_name', null);

		if (is_null($fileName)) {
			$name = $fnum . '-admission.pdf';
		}
		else {
			require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'checklist.php');
			$m_checklist = new EmundusModelChecklist;
			$post        = array(
				'FNUM' => $fnum,
			);
			$name        = $m_checklist->formatFileName($fileName, $fnum, $post) . '.pdf';
		}

		$tmpName = JPATH_SITE . DS . 'tmp' . DS . $name;

		if (!empty($fnum)) {
			$candidature = $m_profile->getFnumDetails($fnum);
			$campaign    = $m_campaign->getCampaignByID($candidature['campaign_id']);
		}

		$file = JPATH_LIBRARIES . DS . 'emundus' . DS . 'pdf_admission_' . $campaign['training'] . '.php';

		if (!file_exists($file)) {
			$file = JPATH_LIBRARIES . DS . 'emundus' . DS . 'pdf_admission.php';
		}

		require_once($file);
		pdf_admission($user->id, $fnum, false, $tmpName, $options);

		return $tmpName;
	}

	public static function makePDF($fileName, $ext, $aid, $i = 0)
	{
		require_once(JPATH_LIBRARIES . '/emundus/vendor/autoload.php');

		include_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
		$imgExt = array('jpeg', 'jpg', 'png', 'gif', 'svg');
		$pdf    = new Fpdi();
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('eMundus');
		$pdf->SetTitle($fileName);

		$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('helvetica', '', 8);
		$pdf->AddPage();

		if (in_array(strtolower($ext), $imgExt)) {
			$pdf->setJPEGQuality(75);
			if ($ext == 'svg')
				$pdf->ImageSVG(EMUNDUS_PATH_ABS . $aid . DS . $fileName, '', '', '', '', '', '', '', true, 300, '', false, false, 0, false, false, true);
			else
				$pdf->Image(EMUNDUS_PATH_ABS . $aid . DS . $fileName, '', '', '', '', '', '', '', true, 300, '', false, false, 0, false, false, true);
		}
		else {
			$htmlData = JText::_('COM_EMUNDUS_EXPORTS_ENCRYPTED_FILE') . ' : ';
			$htmlData .= '<a href="' . JURI::base(true) . DS . EMUNDUS_PATH_REL . DS . $aid . DS . $fileName . '">' . JURI::base(true) . DS . EMUNDUS_PATH_REL . DS . $aid . DS . $fileName . '</a>';
			$pdf->startTransaction();
			$start_y    = $pdf->GetY();
			$start_page = $pdf->getPage();
			$pdf->writeHTMLCell(0, '', '', $start_y, $htmlData, 'B', 1);
		}
		$tmpName = JPATH_SITE . DS . 'tmp' . DS . "$aid-$fileName.pdf";
		$pdf->Output($tmpName, 'F');

		return $tmpName;
	}

	/**
	 * Gets the content of a Joomla article.
	 * Used for defining articles as PDF templates.
	 *
	 * @param $id
	 */
	function getArticle($id)
	{

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query
			->select($db->quoteName('c.introtext'))
			->from($db->quoteName('#__content', 'c'))
			->where($db->quoteName('c.id') . ' = ' . intval($id));
		$db->setQuery($query);

		try {
			return $db->loadResult();
		}
		catch (Exception $e) {
			return null;
		}

	}
}
